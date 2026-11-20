#!/usr/bin/env bash
#
# Switch the live site between sharet.africa v1 and v2 (docs/v2/SWITCHING.md).
#
#   scripts/switch-version.sh status            # which version is live, which are parked
#   scripts/switch-version.sh park              # keep a copy of the live version under versions/<v> (do this once per version)
#   scripts/switch-version.sh v1 [--dry-run]    # put v1 live (e.g. for an audit)
#   scripts/switch-version.sh v2 [--dry-run]    # put v2 back
#
# How it works: the live app is $SITE/public_html (as scripts/deploy.sh leaves it). Parked versions live in
# $SITE/versions/v1 and $SITE/versions/v2; each release root has a .sharet-version file (no file = v1).
# Switching backs up the database, gives the incoming version the live .env and storage, swaps the folders,
# checks the main pages answer 200 and swaps straight back if they don't. Both versions share one database:
# v2 only ever adds tables (ledger_*, …), so v1 runs on it unchanged and v2's records stay untouched while parked.

set -euo pipefail

HOST="${DEPLOY_HOST:-root@185.113.249.146}"
SITE=/home/sharet.africa
LIVE=$SITE/public_html
VERSIONS=$SITE/versions
OWNER=share6158
PHP=/usr/local/lsws/lsphp83/bin/php
STAMP=$(date +%Y%m%d-%H%M%S)
TARGET="${1:-}"
DRY=0
[ "${2:-}" = "--dry-run" ] && DRY=1

say() { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }
remote() { ssh -o BatchMode=yes "$HOST" "$@"; }
run() { if [ "$DRY" = 1 ]; then echo "[dry-run] $*"; else remote "$@"; fi; }
live_version() { remote "cat $LIVE/.sharet-version 2>/dev/null || echo v1"; }

case "$TARGET" in
  status)
    echo "Live: $(live_version)"
    remote "for d in $VERSIONS/*; do [ -d \"\$d\" ] && echo \"Parked: \$(basename \$d) (\$(cat \$d/.sharet-version 2>/dev/null || echo v1)), updated \$(date -r \$d '+%Y-%m-%d %H:%M')\"; done 2>/dev/null || true"
    exit 0 ;;
  park)
    CURRENT=$(live_version)
    say "Parking a copy of the live site as $VERSIONS/$CURRENT"
    run "set -e; mkdir -p $VERSIONS; rm -rf $VERSIONS/$CURRENT.new; cp -a $LIVE $VERSIONS/$CURRENT.new; rm -rf $VERSIONS/$CURRENT; mv $VERSIONS/$CURRENT.new $VERSIONS/$CURRENT"
    exit 0 ;;
  v1|v2) ;;
  *) echo "Usage: $0 status | park | v1 | v2 [--dry-run]"; exit 1 ;;
esac

CURRENT=$(live_version)
[ "$CURRENT" != "$TARGET" ] || { echo "$TARGET is already live."; exit 0; }
remote "test -d $VERSIONS/$TARGET" || { echo "No parked $TARGET at $VERSIONS/$TARGET. Run 'park' while $TARGET is live, or deploy it first."; exit 1; }
FOUND=$(remote "cat $VERSIONS/$TARGET/.sharet-version 2>/dev/null || echo v1")
[ "$FOUND" = "$TARGET" ] || { echo "$VERSIONS/$TARGET says it is $FOUND, not $TARGET. Refusing."; exit 1; }

say "1. Back up the database before switching ($CURRENT → $TARGET)"
run "set -e; mkdir -p $SITE/backups/switch-$STAMP && cd $LIVE && set -a && . ./.env && set +a && \
  mysqldump -h\"\$DB_HOST\" -u\"\$DB_USERNAME\" -p\"\$DB_PASSWORD\" --single-transaction --routines --triggers \"\$DB_DATABASE\" | gzip > $SITE/backups/switch-$STAMP/\$DB_DATABASE.sql.gz"

say "2. Give $TARGET the live settings and uploads"
run "set -e; cd $VERSIONS/$TARGET
  cp -a $LIVE/.env .env
  rsync -a $LIVE/storage/ storage/
  chown -R $OWNER:$OWNER $VERSIONS/$TARGET
  sudo -u $OWNER $PHP artisan optimize:clear >/dev/null"

say "3. Swap: $CURRENT is parked, $TARGET goes live"
run "set -e
  mv $LIVE $VERSIONS/$CURRENT.switching
  mv $VERSIONS/$TARGET $LIVE
  rm -rf $VERSIONS/$CURRENT && mv $VERSIONS/$CURRENT.switching $VERSIONS/$CURRENT
  cd $LIVE && rm -f public/storage && sudo -u $OWNER ln -s ../storage/app/public public/storage
  sudo -u $OWNER $PHP artisan optimize"

say "4. Check the site answers"
if [ "$DRY" = 0 ]; then
  failed=0
  for path in / /merchants /developers /company /contact /login; do
    code=$(curl -s -o /dev/null -w '%{http_code}' "https://sharet.africa$path")
    echo "  $code  $path"; [ "$code" = 200 ] || failed=1
  done
  if [ "$failed" = 1 ]; then
    say "A page did not answer 200: putting $CURRENT back"
    remote "set -e; mv $LIVE $VERSIONS/$TARGET.failed-$STAMP && mv $VERSIONS/$CURRENT $LIVE && cd $LIVE && sudo -u $OWNER $PHP artisan optimize; rm -rf $VERSIONS/$TARGET && mv $VERSIONS/$TARGET.failed-$STAMP $VERSIONS/$TARGET"
    exit 1
  fi
fi
say "Done: $TARGET is live, $CURRENT is parked at $VERSIONS/$CURRENT. Database backup: $SITE/backups/switch-$STAMP"
