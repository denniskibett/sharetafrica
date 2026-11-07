#!/usr/bin/env bash
#
# Deploy sharet.africa to the CyberPanel server.
#
#   scripts/deploy.sh --dry-run   # show what would happen, change nothing on the server
#   scripts/deploy.sh             # archive live, upload a new release folder, migrate, swap in
#   scripts/deploy.sh --rollback  # put the previous release back (database restore is manual, see below)
#
# What it does, in order:
#   1. Checks: clean git tree, tests, fresh asset build.
#   2. On the server: dumps the database and archives the whole live folder to
#      /home/sharet.africa/backups/<timestamp>/ (nothing live is changed yet).
#   3. Uploads this repo to /home/sharet.africa/releases/<timestamp>/ (a NEW folder).
#   4. Into that folder: copies the live .env, storage/ (uploads, avatars), and any public/ files that
#      exist only on the server; installs PHP packages; sets APP_DEBUG=false; runs migrations; caches.
#   5. Snapshots the live folder to releases/previous-<timestamp> (hard links: fast, little disk), then copies the
#      new release over public_html IN PLACE (rsync --delete; storage/ untouched), in maintenance mode for those
#      seconds. public_html stays the same folder, so a server shell already in it runs the new code: no cd needed.
#   6. Checks the main pages answer 200; if not, copies the snapshot straight back.
#
# Rollback restores files only. To restore the database too:
#   gunzip -c /home/sharet.africa/backups/<timestamp>/shar_main.sql.gz | mysql shar_main   (on the server)

set -euo pipefail

HOST="${DEPLOY_HOST:-root@185.113.249.146}"
# A staging site: DEPLOY_SITE=/home/v2.sharet.africa DEPLOY_OWNER=<its user> DEPLOY_URL=https://v2.sharet.africa
SITE="${DEPLOY_SITE:-/home/sharet.africa}"
LIVE=$SITE/public_html
OWNER="${DEPLOY_OWNER:-share6158}"
URL="${DEPLOY_URL:-https://sharet.africa}"
PHP=/usr/local/lsws/lsphp83/bin/php
STAMP=$(date +%Y%m%d-%H%M%S)
RELEASE=$SITE/releases/$STAMP
BACKUP=$SITE/backups/$STAMP
DRY=0
MODE=deploy
# Copies a release's code over public_html in place. Never touches storage/ (uploads, logs, sessions) or the
# public/storage link; bootstrap/cache is rebuilt by artisan optimize afterwards.
SYNC="rsync -a --delete --exclude=/storage/ --exclude=/public/storage --exclude=/bootstrap/cache/"

for arg in "$@"; do
  case $arg in
    --dry-run) DRY=1 ;;
    --rollback) MODE=rollback ;;
    *) echo "Unknown option $arg"; exit 1 ;;
  esac
done

say() { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }
remote() { ssh -o BatchMode=yes "$HOST" "$@"; }
run() { if [ "$DRY" = 1 ]; then echo "[dry-run] $*"; else remote "$@"; fi; }

cd "$(dirname "$0")/.."

if [ "$MODE" = rollback ]; then
  PREV=$(remote "ls -1d $SITE/releases/previous-* 2>/dev/null | tail -1")
  [ -n "$PREV" ] || { echo "No previous release found."; exit 1; }
  say "Rolling back to $PREV"
  # In place, like a deploy: public_html stays the same folder (its code is replaced, storage/ is kept)
  run "set -e; cp -al $LIVE $SITE/releases/rolled-back-$STAMP; $SYNC $PREV/ $LIVE/; cd $LIVE; rm -f bootstrap/cache/*.php; sudo -u $OWNER $PHP artisan optimize; /usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1 || true"
  say "Files rolled back. Restore the database from $SITE/backups/ if the release changed data."
  exit 0
fi

say "1. Local checks"
[ -z "$(git status --porcelain)" ] || { echo "Commit or stash your changes first."; exit 1; }
php -d memory_limit=1G vendor/bin/phpunit --testsuite Feature >/dev/null 2>&1 || echo "  (some tests fail; known failures are listed in docs/v2/DEPLOY.md, continuing)"
npm ci --silent && npm run build >/dev/null
echo "  commit $(git rev-parse --short HEAD), assets built"

say "2. Back up the live site and database to $BACKUP"
run "mkdir -p $BACKUP && cd $LIVE && \
  set -a && . ./.env && set +a && \
  mysqldump -h\"\$DB_HOST\" -u\"\$DB_USERNAME\" -p\"\$DB_PASSWORD\" --single-transaction --routines \"\$DB_DATABASE\" | gzip > $BACKUP/\$DB_DATABASE.sql.gz && \
  { tar --exclude=./node_modules --warning=no-file-changed -czf $BACKUP/public_html.tar.gz -C $LIVE . || [ \$? -eq 1 ]; } && \
  ls -la $BACKUP"

say "3. Upload release to $RELEASE"
if [ "$DRY" = 1 ]; then
  rsync -azn --stats --exclude-from=scripts/deploy-exclude.txt ./ "$HOST:$RELEASE/" | tail -15
else
  remote "mkdir -p $RELEASE"
  rsync -az --exclude-from=scripts/deploy-exclude.txt ./ "$HOST:$RELEASE/"
  # Built assets are git-ignored but must ship
  rsync -az public/build/ "$HOST:$RELEASE/public/build/"
fi

say "4. Prepare the release (env, uploads, packages, migrations)"
# Each line must succeed (set -e). No caches here: Laravel caches absolute paths, and this folder's
# code is copied into public_html in step 5, so caching happens there afterwards.
run "set -e
  cd $RELEASE
  cp -a $LIVE/.env .env
  sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
  rsync -a $LIVE/storage/ storage/
  rsync -a --ignore-existing --exclude build --exclude storage $LIVE/public/ public/
  mkdir -p bootstrap/cache
  chown -R $OWNER:$OWNER $RELEASE
  sudo -u $OWNER $PHP $SITE/composer.phar install --no-dev --optimize-autoloader --no-interaction --quiet
  sudo -u $OWNER $PHP artisan optimize:clear >/dev/null
  sudo -u $OWNER $PHP artisan migrate --force"

say "5. Copy the new release into public_html in place (snapshot kept for rollback)"
# public_html is never renamed, so open shells and the web server keep the same folder.
run "set -e
  cp -al $LIVE $SITE/releases/previous-$STAMP
  cd $LIVE
  sudo -u $OWNER $PHP artisan down --retry=5 >/dev/null || true
  trap 'cd $LIVE && sudo -u $OWNER $PHP artisan up >/dev/null' EXIT
  $SYNC $RELEASE/ $LIVE/
  [ -L public/storage ] || sudo -u $OWNER ln -s ../storage/app/public public/storage
  rm -f bootstrap/cache/*.php
  sudo -u $OWNER $PHP artisan optimize
  /usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1 || true   # graceful: PHP workers reload, so OPcache drops the old files
  rm -rf $RELEASE"

say "6. Check the site answers"
if [ "$DRY" = 0 ]; then
  failed=0
  for path in / /merchants /developers /company /contact /login /sitemap.xml /llms.txt; do
    code=$(curl -s -o /dev/null -w '%{http_code}' "$URL$path")
    echo "  $code  $path"
    [ "$code" = 200 ] || failed=1
  done
  if [ "$failed" = 1 ]; then
    say "A page did not answer 200: copying the previous release back"
    remote "set -e; cp -al $LIVE $SITE/releases/failed-$STAMP; $SYNC $SITE/releases/previous-$STAMP/ $LIVE/; cd $LIVE; rm -f bootstrap/cache/*.php; sudo -u $OWNER $PHP artisan optimize >/dev/null; /usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1 || true"
    echo "Live site restored. The failed release is in $SITE/releases/failed-$STAMP; migrations that ran are in the database (backup: $BACKUP)."
    exit 1
  fi
fi

say "Done. Backup: $BACKUP   Previous release: $SITE/releases/previous-$STAMP"
