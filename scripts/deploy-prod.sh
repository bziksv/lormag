#!/bin/sh
set -e
cd "$(dirname "$0")/.."
SITE_ROOT="$(pwd)"
PROD_PATH=/var/www/lormag_ru_usr/data/www/lormag.ru
HOST=vilmed
PROD_USER=lormag_ru_usr
REMOTE=origin
BRANCH=main

echo "==> Push local commits (if any)"
git status -sb | head -5
git push "$REMOTE" "$BRANCH"

echo "==> Pull on production via git"
ssh "$HOST" "cd $PROD_PATH &&
  set -e
  git config --global --add safe.directory $PROD_PATH
  if [ -f bitrix/license_key.php ]; then
    cp -a bitrix/license_key.php /tmp/lormag_license_key.php.bak
  fi
  if [ -f bitrix/.settings.php ]; then
    cp -a bitrix/.settings.php /tmp/lormag_settings.php.bak
  fi
  if [ -f bitrix/php_interface/dbconn.php ]; then
    cp -a bitrix/php_interface/dbconn.php /tmp/lormag_dbconn.php.bak
  fi
  if [ ! -d .git ]; then
    git init
    git remote add $REMOTE https://github.com/bziksv/lormag.git
  else
    git remote set-url $REMOTE https://github.com/bziksv/lormag.git 2>/dev/null || true
  fi
  GIT_TERMINAL_PROMPT=0 git fetch $REMOTE $BRANCH
  if git rev-parse --verify $BRANCH >/dev/null 2>&1 && git merge-base --is-ancestor HEAD $REMOTE/$BRANCH 2>/dev/null; then
    git merge --ff-only $REMOTE/$BRANCH
  else
    git checkout -B $BRANCH $REMOTE/$BRANCH 2>/dev/null || git branch -M $BRANCH 2>/dev/null || true
    git reset --hard $REMOTE/$BRANCH
  fi
  if [ ! -f bitrix/license_key.php ] && [ -f /tmp/lormag_license_key.php.bak ]; then
    cp -a /tmp/lormag_license_key.php.bak bitrix/license_key.php
    echo 'restored bitrix/license_key.php'
  fi
  if [ -f /tmp/lormag_settings.php.bak ]; then
    cp -a /tmp/lormag_settings.php.bak bitrix/.settings.php
  fi
  if [ -f /tmp/lormag_dbconn.php.bak ]; then
    cp -a /tmp/lormag_dbconn.php.bak bitrix/php_interface/dbconn.php
  fi
  chown -R ${PROD_USER}:${PROD_USER} .
  rm -rf bitrix/cache/* bitrix/managed_cache/* bitrix/stack_cache/* bitrix/html_pages/* 2>/dev/null || true
  git log -1 --oneline
  echo cache_cleared
"

echo "==> Verify"
/usr/bin/curl -sS -o /dev/null -w 'home %{http_code}\n' --max-time 20 https://lormag.ru/
/usr/bin/curl -sS -o /dev/null -w 'orders %{http_code}\n' --max-time 20 https://lormag.ru/personal/orders/
/usr/bin/curl -sS -o /dev/null -w 'private %{http_code}\n' --max-time 20 https://lormag.ru/personal/private/
echo "==> Done"
