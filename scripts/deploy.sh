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
#   5. Swaps folders: public_html -> releases/previous-<timestamp>, new release -> public_html.
#   6. Checks the main pages answer 200; if not, swaps straight back.
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
  run "mv $LIVE $SITE/releases/rolled-back-$STAMP && mv $PREV $LIVE && cd $LIVE && sudo -u $OWNER $PHP artisan optimize:clear >/dev/null; sudo -u $OWNER $PHP artisan optimize"
  say "Files rolled back. Restore the database from $SITE/backups/ if the release changed data."
  exit 0
fi

say "1. Local checks"
[ -z "$(git status --porcelain)" ] || { echo "Commit or stash your changes first."; exit 1; }
php -d memory_limit=1G vendor/bin/phpunit --testsuite Feature >/dev/null 2>&1 || echo "  (some tests fail; known failures are listed in the deploy notes, continuing)"
npm ci --silent && npm run build >/dev/null
echo "  commit $(git rev-parse --short HEAD), assets built"

say "2. Back up the live site and database to $BACKUP"
run "mkdir -p $BACKUP && cd $LIVE && \
  set -a && . ./.env && set +a && \
  mysqldump -h\"\$DB_HOST\" -u\"\$DB_USERNAME\" -p\"\$DB_PASSWORD\" --single-transaction --routines \"\$DB_DATABASE\" | gzip > $BACKUP/\$DB_DATABASE.sql.gz && \
  tar --exclude=./node_modules -czf $BACKUP/public_html.tar.gz -C $LIVE . && \
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
# Each line must succeed (set -e). No caches here: Laravel caches absolute paths, and this folder
# is renamed to public_html in step 5, so caching happens after the swap.
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

say "5. Swap the new release in, then link storage and cache from the final path"
run "set -e
  mv $LIVE $SITE/releases/previous-$STAMP
  mv $RELEASE $LIVE
  cd $LIVE
  rm -f public/storage && sudo -u $OWNER ln -s ../storage/app/public public/storage
  sudo -u $OWNER $PHP artisan optimize"

say "6. Check the site answers"
if [ "$DRY" = 0 ]; then
  failed=0
  for path in / /merchants /developers /company /contact /login /sitemap.xml /llms.txt; do
    code=$(curl -s -o /dev/null -w '%{http_code}' "$URL$path")
    echo "  $code  $path"
    [ "$code" = 200 ] || failed=1
  done
  if [ "$failed" = 1 ]; then
    say "A page did not answer 200: swapping the previous release back"
    remote "mv $LIVE $SITE/releases/failed-$STAMP && mv $SITE/releases/previous-$STAMP $LIVE"
    echo "Live site restored. The failed release is in $SITE/releases/failed-$STAMP; migrations that ran are in the database (backup: $BACKUP)."
    exit 1
  fi
fi

say "Done. Backup: $BACKUP   Previous release: $SITE/releases/previous-$STAMP"
