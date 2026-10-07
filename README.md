# HiveTech Portal — Yii2

Полный портал HiveTech для текущей связки **mc-edge → GravitLauncher → MariaDB hivetech_core**.

Проект собран на Yii 2 Advanced-style архитектуре и рассчитан на PHP 8.3 / Nginx / PHP-FPM / MariaDB. `users` является общей identity-таблицей для сайта и GravitLauncher: отдельные игровые пароли и дубли аккаунтов не создаются.

## Что реализовано

### HiveTech ID / безопасность
- регистрация `username + email + password`;
- UUID v4;
- bcrypt cost 10 с `$2b$` для предсказуемой совместимости с Gravit;
- жизненный цикл `active / blocked` для доступа к аккаунту;
- подтверждение email хранится отдельно в `email_verified` и не блокирует вход;
- login по username или email;
- throttling неудачных входов: 5 / 15 минут на identifier+IP;
- восстановление и смена пароля;
- смена пароля обнуляет `accessToken` / `serverID` и отзывает web-сессии;
- смена email сбрасывает `email_verified`, но аккаунт остаётся активным;
- список web-сессий и отзыв отдельных сессий;
- аудит критических действий;
- опциональный Cloudflare Turnstile.

### GravitLauncher
- используется существующая `hivetech_core.users`;
- сохраняются поля `uuid`, `accessToken`, `serverID`, `hwidId`;
- при чистой БД migration создаёт `hwids`, `hwidLog`, `user_permissions` по схеме SQL auth Gravit;
- управление Gravit permissions пользователя из админки;
- skin/cloak endpoints:
  - `/texture/skin/%username%.png`
  - `/texture/cloak/%username%.png`
- скрипт `deploy/configure-gravit-textures.py` обновляет `textureProvider` с backup `LaunchServer.json`.

### Личный кабинет
- профиль и email;
- безопасность / смена пароля;
- сессии;
- скин и плащ;
- баланс;
- покупки;
- активация промокодов;
- support tickets.

### Контент
- новости;
- категории новостей;
- CMS-страницы;
- homepage slider;
- список Minecraft-серверов и server-list ping.

### Support
- тикеты;
- сообщения игрока / staff;
- статусы `open / answered / closed`;
- до 3 вложений по 5 МБ;
- защищённая выдача вложений через controller;
- email при ответе staff.

### Экономика
- внутренний wallet;
- immutable ledger `wallet_transactions`;
- заявки на пополнение;
- рабочий `manual` payment provider для тестового/ручного подтверждения;
- каталог и категории;
- покупка с транзакционной блокировкой кошелька;
- промокоды на баланс или товар;
- очередь выдачи покупки;
- retry/backoff;
- выдача через Minecraft RCON с `{username}` / `{uuid}`.

### Админ-панель
- dashboard;
- пользователи, status, роли;
- Gravit permissions;
- RBAC;
- новости / категории;
- страницы;
- слайды;
- поддержка;
- магазин;
- промокоды;
- платежи;
- серверы/RCON profile;
- настройки;
- аудит.

### RBAC
- `user` — обычный игрок;
- `moderator` — контент и support;
- `admin` — полный доступ.

### Форум
Встроен DiscourseConnect SSO adapter. В качестве `external_id` используется `users.uuid`. Сам форум намеренно не реализован таблицами портала: позже можно подключить отдельный Discourse без переноса аккаунтов.

---

# Развёртывание в текущей инфраструктуре

## 1. mc-edge: пакеты

Уже установленный PHP 8.3 подходит. Проверь/доставь:

```bash
apt update
apt install -y \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-curl \
    php8.3-intl \
    php8.3-xml \
    composer \
    unzip
```

## 2. Распаковка

```bash
mkdir -p /opt/hivetech/portal
unzip hivetech-yii2-portal.zip -d /opt/hivetech/portal
cd /opt/hivetech/portal
./deploy/install.sh
```

`vendor/` намеренно не вложен в архив: установка всегда выполняет Composer и получает зафиксированный Yii 2.0.55, вместо копирования старого `vendor` из референс-проекта.

## 3. Environment

```bash
nano /etc/hivetech/portal.env
```

Минимально заполнить:

```dotenv
YII_ENV=prod
YII_DEBUG=0
APP_NAME=HiveTech
APP_URL=https://YOUR_DOMAIN
ADMIN_URL=https://admin.YOUR_DOMAIN
COOKIE_DOMAIN=.YOUR_DOMAIN
COOKIE_VALIDATION_KEY=LONG_RANDOM_VALUE

HIVETECH_DB_HOST=10.140.0.142
HIVETECH_DB_PORT=3306
HIVETECH_DB_NAME=hivetech_core
HIVETECH_WEB_DB_USER=hivetech_web
HIVETECH_WEB_DB_PASSWORD=YOUR_CURRENT_WEB_PASSWORD
```

Ключ:

```bash
openssl rand -hex 48
```

`portal.env` должен быть `root:www-data 0640`.

## 4. Миграция без выдачи DDL прав приложению

На **mc-data** временно создаём migration user:

```bash
MIGRATE_PASSWORD="$(openssl rand -hex 32)"

mariadb <<SQL
CREATE USER 'hivetech_migrate'@'10.140.0.141'
IDENTIFIED BY '${MIGRATE_PASSWORD}';

GRANT ALL PRIVILEGES
ON hivetech_core.*
TO 'hivetech_migrate'@'10.140.0.141';
SQL

echo "$MIGRATE_PASSWORD"
```

На **mc-edge** временно добавь в `/etc/hivetech/portal.env`:

```dotenv
HIVETECH_MIGRATION_DB_USER=hivetech_migrate
HIVETECH_MIGRATION_DB_PASSWORD=PASSWORD_FROM_MC_DATA
```

И выполнить:

```bash
cd /opt/hivetech/portal
HIVETECH_USE_MIGRATION_DB=1 ./yii migrate --interactive=0
```

После успешной миграции на **mc-data**:

```bash
mariadb -e "DROP USER 'hivetech_migrate'@'10.140.0.141';"
```

Удалить `HIVETECH_MIGRATION_DB_*` из `portal.env`.

Migration не удаляет и не пересоздаёт существующего `testsql`. Для существующего `users` она:
- заполняет отсутствующие UUID;
- делает `uuid NOT NULL`;
- расширяет status до `pending|active|blocked`;
- создаёт wallet и роль `user` для уже существующих пользователей.

## 5. Права hivetech_web

На **mc-data**, после migration:

```bash
mariadb < /path/to/deploy/sql/grant-web.sql
```

Если архив лежит только на mc-edge, скопируй файл или выведи его содержимое и выполни локально на mc-data.

`hivetech_web` не получает DDL и не получает DELETE на `users`.

## 6. PHP-FPM pool

На mc-edge:

```bash
cp deploy/php-fpm/hivetech-portal.conf \
   /etc/php/8.3/fpm/pool.d/hivetech.conf

php-fpm8.3 -t
systemctl restart php8.3-fpm

ls -l /run/php/php8.3-fpm-hivetech.sock
```

## 7. Nginx

Сначала отредактируй hostnames:

```bash
nano deploy/nginx/hivetech-portal.conf
```

Затем:

```bash
cp deploy/nginx/hivetech-portal.conf \
   /etc/nginx/sites-available/hivetech-portal

ln -s /etc/nginx/sites-available/hivetech-portal \
      /etc/nginx/sites-enabled/hivetech-portal

nginx -t
systemctl reload nginx
```

Конфиг создаёт отдельные `server_name`; существующий Nginx virtual host Gravit/LaunchServer не заменяется.

После этого выпусти TLS-сертификат (например certbot) и только после HTTPS оставляй `YII_DEBUG=0`: production cookies выставляются `Secure`.

## 8. Первый admin

Для уже существующего `testsql`:

```bash
cd /opt/hivetech/portal
sudo -u www-data HIVETECH_ENV_FILE=/etc/hivetech/portal.env \
    php yii user/assign-admin testsql
```

Или создать отдельного администратора, который одновременно станет валидным Launcher account:

```bash
sudo -u www-data HIVETECH_ENV_FILE=/etc/hivetech/portal.env \
    php yii user/create-admin admin admin@example.com 'STRONG_PASSWORD'
```

## 9. Cron

```bash
cp deploy/cron/hivetech /etc/cron.d/hivetech
chmod 0644 /etc/cron.d/hivetech
touch /var/log/hivetech-delivery.log /var/log/hivetech-maintenance.log
chown www-data:www-data /var/log/hivetech-*.log
```

## 10. Gravit textureProvider

После появления публичного HTTPS-domain:

```bash
cd /opt/hivetech/portal

python3 deploy/configure-gravit-textures.py \
    /opt/hivetech/launchserver/LaunchServer.json \
    https://YOUR_DOMAIN

systemctl restart hivetech-launchserver
```

Скрипт сначала делает backup LaunchServer.json.

## 11. SMTP

В `/etc/hivetech/portal.env`:

```dotenv
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=...
SMTP_PASSWORD=...
SMTP_ENCRYPTION=tls
MAIL_FROM=no-reply@example.com
MAIL_FROM_NAME=HiveTech
```

Для обычного Gmail-аккаунта:

```dotenv
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your.account@gmail.com
SMTP_PASSWORD=GOOGLE_APP_PASSWORD
SMTP_ENCRYPTION=tls
MAIL_FROM=your.account@gmail.com
MAIL_FROM_NAME=HiveGrid
```

Для Gmail в `SMTP_PASSWORD` используется отдельный App Password, а `MAIL_FROM` лучше оставлять тем же Gmail-адресом, который указан в `SMTP_USER`.

Проверка реальной SMTP-доставки:

```bash
HIVETECH_ENV_FILE=/etc/hivetech/portal.env \
php yii mail/test your-test-address@example.com
```

Если `SMTP_HOST` пуст, Yii использует file transport — удобно для локальной разработки, но команда `mail/test` в таком режиме специально завершится с ошибкой конфигурации вместо создания локального файла.

## 12. Google reCAPTCHA v3

Для форм входа и регистрации используется невидимая reCAPTCHA v3. Чекбокс пользователю не показывается: токен запрашивается непосредственно перед отправкой формы, а backend проверяет `success`, ожидаемый `action` и `score`.

Создай ключи типа **reCAPTCHA v3** для production-домена и укажи их в admin → Настройки → Google reCAPTCHA. Там же можно:

- включать защиту глобально;
- отдельно включать её для входа и регистрации;
- задать минимальный score для каждого действия;
- включить режим наблюдения, в котором score логируется, но низкий score не блокирует пользователя.

Для первого запуска рекомендуется оставить режим наблюдения и пороги `0.5`. Реальные результаты пишутся в:

```text
frontend/runtime/logs/recaptcha.log
```

После накопления нормального трафика пороги можно скорректировать и отключить режим наблюдения.

## 13. RCON

В admin → Servers у сервера укажи `rcon_env_key=MAIN`.

В env:

```dotenv
MINECRAFT_RCON_MAIN_HOST=10.x.x.x
MINECRAFT_RCON_MAIN_PORT=25575
MINECRAFT_RCON_MAIN_PASSWORD=...
```

У товара можно использовать команду:

```text
lp user {username} parent add vip
```

или любую другую серверную команду. Secrets в таблице `servers` не хранятся.

## 14. Discourse SSO

```dotenv
FORUM_URL=https://forum.example.com
FORUM_SSO_SECRET=...
```

В Discourse HiveTech настраивается как внешний authentication provider, SSO URL:

```text
https://YOUR_DOMAIN/forum/sso
```

---

# Что ещё специально оставлено adapter-слоем

## Реальный эквайринг

В архиве работает `manual` provider: пользователь создаёт top-up order, admin подтверждает, wallet получает транзакцию. Для production нужно выбрать конкретного PSP. После выбора добавляется provider adapter + webhook, сама модель `payment_orders / wallets / wallet_transactions` не меняется.

## Игровые сборки / сервер

Портал уже умеет хранить server endpoints, пинговать Minecraft и выдавать покупки через RCON, но игровые сборки/моды и серверный deployment — отдельный следующий этап нашего плана.

---

# Быстрые проверки после установки

```bash
cd /opt/hivetech/portal

php yii help
php yii migrate/history

curl -I https://YOUR_DOMAIN/
curl -I https://YOUR_DOMAIN/register
curl -I https://admin.YOUR_DOMAIN/site/login
```

Проверка общей identity:
1. Зарегистрировать нового пользователя на сайте.
2. Подтвердить email.
3. Убедиться в `users.status=active`, `email_verified=0`, bcrypt length=60 и возможности сразу войти на сайт/в лаунчер.
4. Подтвердить email и убедиться, что `email_verified=1`, а `status` остаётся `active`.
5. Войти этими же username/password в HiveTech Launcher.
6. Block пользователя в админке — сайт и новый launcher login должны перестать работать.

