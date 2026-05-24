#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if ! command -v composer >/dev/null 2>&1; then
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet
  php -r "unlink('composer-setup.php');"
  php composer.phar update --no-interaction
else
  composer update --no-interaction
fi

./vendor/bin/pest --testsuite=Gateway
