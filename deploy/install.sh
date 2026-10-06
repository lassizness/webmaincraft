#!/usr/bin/env bash
set -euo pipefail
APP_DIR="${APP_DIR:-/opt/hivetech/portal}"
cd "$APP_DIR"
if ! command -v php >/dev/null; then echo 'PHP not found'; exit 1; fi
php -r 'exit(version_compare(PHP_VERSION,"8.2.0",">=")?0:1);' || { echo 'PHP >= 8.2 required'; exit 1; }
if ! command -v composer >/dev/null; then
  echo 'Composer not found. On Ubuntu: apt install -y composer'
  exit 1
fi
composer install --no-dev --prefer-dist --optimize-autoloader
install -d -o www-data -g www-data -m 0770 frontend/runtime backend/runtime console/runtime frontend/web/assets backend/web/assets storage/uploads/support storage/textures/skins storage/textures/cloaks runtime
if [ ! -f /etc/hivetech/portal.env ]; then
  install -d -o root -g www-data -m 0750 /etc/hivetech
  cp .env.example /etc/hivetech/portal.env
  chown root:www-data /etc/hivetech/portal.env
  chmod 0640 /etc/hivetech/portal.env
  echo 'Created /etc/hivetech/portal.env. Fill secrets before migration/start.'
fi
chown -R www-data:www-data frontend/runtime backend/runtime console/runtime frontend/web/assets backend/web/assets storage runtime
chmod -R u+rwX,g+rwX,o-rwx frontend/runtime backend/runtime console/runtime frontend/web/assets backend/web/assets storage runtime
echo 'Source installed. Next: configure /etc/hivetech/portal.env, run migration with temporary migration DB credentials, apply grants, then enable php-fpm/nginx configs.'
