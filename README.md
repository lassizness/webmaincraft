# HiveTech Portal

Веб-портал игрового проекта [HiveGrid](https://hivegrid.online). На одном сайте работают регистрация и личный кабинет игроков, новости, магазин, система привилегий, достижения, рейтинги, голосования, поддержка и панель управления. Портал связан с GravitLauncher и игровым сервером Minecraft 1.12.2.

Репозиторий содержит сайт на Yii 2, консольные задания, миграции базы данных и конфигурации развёртывания. Серверные моды Minecraft разрабатываются отдельно. Их исходники, сборка и порядок подключения описаны ниже.

## Состав проекта

| Репозиторий | Назначение | Где работает |
| --- | --- | --- |
| [webmaincraft](https://github.com/lassizness/webmaincraft) | Сайт, личный кабинет, админка, магазин, API, фоновые задания | Веб-сервер с PHP и MariaDB |
| [HiveTechTelemetry](https://github.com/lassizness/HiveTechTelemetry) | Сбор игровых событий, статистика и данные для достижений | Сервер Minecraft, Forge 1.12.2 |
| [hivetech-moderation](https://github.com/lassizness/hivetech-moderation) | Применение блокировок и ограничений чата из админки | Сервер Minecraft, Forge 1.12.2 |
| [hivetech-kits](https://github.com/lassizness/hivetech-kits) | Выдача стартовых наборов обладателям игровых статусов | Сервер Minecraft, Forge 1.12.2 |

Все три мода работают на стороне сервера. Игрокам не нужно устанавливать их в клиент Minecraft. Моды не входят в Composer-зависимости сайта и не собираются при установке PHP-проекта.

### Как связаны компоненты

```text
Игрок / браузер
      |
      v
https://hivegrid.online
      |
      +-- Yii2 frontend: сайт, аккаунт, магазин, достижения
      |
      +-- /admin/: управление проектом и игроками
      |
      +-- /internal/game/: закрытое API игровых модов
      |
      +-- MariaDB: пользователи, заказы, события, наказания
      |
      +-- RCON: очередь игровых команд
                     |
                     v
              Minecraft 1.12.2
                |        |        |
             Telemetry Moderation Kits
```

Авторизация сайта и GravitLauncher использует общую таблицу `users` в `hivetech_core`. Не нужно создавать вторую базу игровых аккаунтов или отдельные пароли для лаунчера. Моды Telemetry и Moderation обращаются к порталу по HTTPS с отдельным Bridge-токеном для каждого игрового сервера. Мод Kits не обращается к веб-API, а получает права через Forge PermissionAPI.

## Возможности портала

### Аккаунты и личный кабинет

- Регистрация с именем игрока, email и паролем, вход по имени или email.
- UUID пользователя и совместимое с GravitLauncher хеширование паролей.
- Подтверждение email, восстановление пароля, смена email и пароля.
- Защита форм авторизации, ограничение частоты неудачных попыток входа.
- Управление веб-сессиями и аудит значимых действий.
- Управление скином и плащом, выдача текстур по URL для лаунчера.
- Баланс, история операций, покупки, промокоды и обращения в поддержку.
- Страница достижений в личном кабинете.
- Настройки профиля и языка интерфейса.

Изменение пароля отзывает связанные токены и веб-сессии. Статус учётной записи и подтверждение email учитываются отдельно.

### Контент и публичные страницы

На сайте есть главная страница, новости с категориями, CMS-страницы, слайдер, список игровых серверов, магазин, рейтинги, голосование и публичные профили игроков. Новости и страницы поддерживают переводы. Публичные маршруты и индексируемый контент обслуживаются самим Yii2.

### Магазин и игровые статусы

Портал хранит внутренний кошелёк и журнал операций, товары, промокоды, платежные заказы и покупки. Выдача игровых товаров идёт через очередь и RCON, поэтому временная недоступность Minecraft не должна приводить к потере заказа.

Для привилегий используются группы и ограниченный срок действия. Портал создаёт команды выдачи и отзыва статуса, ведёт историю и запускает задания на истечение срока. Доступные платежные интеграции определяются настройками проекта; в исходниках присутствуют ручная обработка и адаптер PayPalych/Pally. Боевые платежи нужно отдельно настроить и проверить на тестовых операциях.

### Модерация

Администраторы управляют наказаниями через `/admin`. Портал хранит историю действий, область применения и сроки ограничений. Мод HiveTechModeration на каждом игровом сервере получает актуальные BAN/MUTE и применяет их в игре.

### Статистика и достижения

HiveTechTelemetry передаёт игровые события. Портал связывает события с игроком, обновляет агрегированную статистику, игровые сессии и прогресс достижений. За выполнение достижения можно настроить награду: баланс, RCON-команду, permission или привилегию. Выдача награды обрабатывается отдельно от приёма игровых событий.

### Панель управления

Админка доступна на `https://hivegrid.online/admin/`. В ней есть разделы пользователей, ролей и прав, серверов и RCON, Bridge, модерации, телеметрии, достижений, сезонов, магазина, платежей, привилегий, контента, обращений, голосований, рейтингов, настроек и аудита.

## Структура репозитория

| Каталог | Содержимое |
| --- | --- |
| `frontend/` | Публичный сайт, личный кабинет, веб-маршруты и игровое API |
| `backend/` | Административная панель |
| `common/models/` | Модели пользователей, платежей, наказаний, событий и достижений |
| `common/services/` | Бизнес-логика, обработчики интеграций и сервисы |
| `common/messages/` | Словари интерфейса по языкам |
| `console/controllers/` | Команды обслуживания и фоновые обработчики |
| `console/migrations/` | Миграции схемы и начальных данных |
| `deploy/` | Установка, Nginx, PHP-FPM, cron, SQL и вспомогательные скрипты |
| `docs/` | Дополнительная документация по API, SEO и достижениям |
| `storage/` | Загружаемые данные, в том числе текстуры и вложения |

Публичный корень Nginx: `frontend/web`. Для административной части используется `backend/web/index.php`, опубликованный на том же домене под `/admin/`. Не следует направлять Nginx на корень Git-репозитория.

## Требования

Для сайта:

- Linux с Nginx, PHP-FPM и доступом к MariaDB.
- PHP не ниже 8.2; предоставленная конфигурация развёртывания рассчитана на PHP 8.3.
- Composer, Git, unzip.
- Расширения PHP: `pdo_mysql`, `mbstring`, `fileinfo`, `json`, `curl`, `intl`, `xml`.
- MariaDB с доступом к существующей базе `hivetech_core`.
- HTTPS для публичного сайта, административной части и Bridge API.
- Доступ к RCON игровых серверов, если используются магазин и привилегии.
- SMTP для доставки писем.

В `composer.json` зафиксирован Yii 2.0.55. Зависимости устанавливаются через `composer.lock`. Каталог `vendor/` не должен переноситься из другой установки.

Для игровых модов нужен Minecraft 1.12.2 с совместимым Forge/Cleanroom runtime. Требования к Java для сборки модов отличаются и указаны в соответствующих разделах.

## Установка сайта

Ниже приведён вариант для существующей инфраструктуры с веб-сервером `mc-edge`, MariaDB на `mc-data` и каталогом приложения `/opt/hivetech/portal`. Имена серверов, IP-адреса и пути можно заменить на собственные. Приведённые внутренние IP относятся к текущему развёртыванию и не являются универсальными настройками.

### 1. Установить пакеты

На веб-сервере:

```bash
apt update
apt install -y git unzip composer nginx \
  php8.3-fpm php8.3-cli php8.3-mysql \
  php8.3-mbstring php8.3-curl php8.3-intl php8.3-xml
php -v
composer --version
```

### 2. Получить исходники и зависимости

```bash
mkdir -p /opt/hivetech
git clone https://github.com/lassizness/webmaincraft.git /opt/hivetech/portal
cd /opt/hivetech/portal
./deploy/install.sh
```

Скрипт проверяет версию PHP, выполняет `composer install --no-dev`, создаёт рабочие каталоги и выставляет права на запись для `www-data`. Если файла настроек ещё нет, он создаёт `/etc/hivetech/portal.env` из `.env.example`.

При обновлении установленного сайта повторно клонировать репозиторий не нужно. Используйте `git pull --ff-only` после проверки локальных изменений, затем `composer install`, миграции и перезапуск PHP-FPM.

### 3. Настроить окружение

```bash
nano /etc/hivetech/portal.env
openssl rand -hex 48
```

Вставьте сгенерированное значение в `COOKIE_VALIDATION_KEY`. Основные параметры:

```dotenv
YII_ENV=prod
YII_DEBUG=0
APP_NAME=HiveTech
APP_URL=https://hivegrid.online
ADMIN_URL=https://hivegrid.online/admin
COOKIE_DOMAIN=.hivegrid.online
COOKIE_VALIDATION_KEY=REPLACE_WITH_RANDOM_VALUE

HIVETECH_DB_HOST=10.140.0.142
HIVETECH_DB_PORT=3306
HIVETECH_DB_NAME=hivetech_core
HIVETECH_WEB_DB_USER=hivetech_web
HIVETECH_WEB_DB_PASSWORD=REPLACE_WITH_DATABASE_PASSWORD
```

Параметр `COOKIE_DOMAIN` задавайте в соответствии с действующей схемой доменов. Для единственного хоста можно оставить его пустым. Админка в предоставленном Nginx-конфиге находится под `/admin` того же домена, а не на отдельном административном поддомене.

Файл с секретами должен читаться PHP-FPM, но не должен попадать в Git:

```bash
chown root:www-data /etc/hivetech/portal.env
chmod 0640 /etc/hivetech/portal.env
```

Другие параметры `.env.example`:

| Переменные | Для чего нужны |
| --- | --- |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD`, `SMTP_ENCRYPTION` | Отправка email |
| `MAIL_FROM`, `MAIL_FROM_NAME` | Отправитель писем |
| `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET` | Необязательная защита форм Cloudflare |
| `PAYPALYCH_API_TOKEN`, `PAYPALYCH_SIGNATURE_TOKEN` | Секреты платежного адаптера |
| `MINECRAFT_RCON_MAIN_HOST`, `MINECRAFT_RCON_MAIN_PORT`, `MINECRAFT_RCON_MAIN_PASSWORD` | RCON-профиль с ключом `MAIN` |
| `FORUM_URL`, `FORUM_SSO_SECRET` | Интеграция с внешним форумом |
| `GOOGLE_SITE_VERIFICATION`, `YANDEX_SITE_VERIFICATION`, `BING_SITE_VERIFICATION` | Подтверждение сайта в поисковых системах |
| `VOTE_TRUSTED_PROXY_IPS` | Доверенные reverse proxy для голосований |

Секреты токенов игровых модов в `portal.env` не размещаются. Они вводятся на самом игровом сервере в конфигурации модов.

### 4. Подготовить базу данных и выполнить миграции

У сайта должен быть рабочий пользователь БД с ограниченными правами. Миграции выполняются отдельно, временным пользователем с DDL-доступом. Не выдавайте приложению права на изменение схемы БД на постоянной основе.

На сервере MariaDB создайте временную учётную запись с доступом к `hivetech_core` с IP веб-сервера, после чего добавьте в `/etc/hivetech/portal.env`:

```dotenv
HIVETECH_MIGRATION_DB_USER=hivetech_migrate
HIVETECH_MIGRATION_DB_PASSWORD=REPLACE_WITH_TEMPORARY_PASSWORD
```

На веб-сервере выполните:

```bash
cd /opt/hivetech/portal
HIVETECH_ENV_FILE=/etc/hivetech/portal.env \
HIVETECH_USE_MIGRATION_DB=1 \
php yii migrate --interactive=0
```

После успешной миграции удалите временного пользователя БД и строки `HIVETECH_MIGRATION_DB_*` из env. Схема включает связанные с GravitLauncher таблицы, магазин, модерирование, телеметрию и достижения. Перед миграцией существующей игровой базы обязательно делайте резервную копию.

Права рабочего пользователя задаются SQL-шаблоном `deploy/sql/grant-web.sql`. Проверьте в нём имена пользователей и хостов перед выполнением на MariaDB. Рабочему пользователю не нужны постоянные DDL-права.

Проверка:

```bash
cd /opt/hivetech/portal
php yii migrate/history
php yii help
```

### 5. Подключить PHP-FPM

```bash
cp deploy/php-fpm/hivetech-portal.conf \
  /etc/php/8.3/fpm/pool.d/hivetech.conf
php-fpm8.3 -t
systemctl restart php8.3-fpm
ls -l /run/php/php8.3-fpm-hivetech.sock
```

В файле настроен отдельный пул `hivetech` с пользователем `www-data`. Перед запуском убедитесь, что ему доступны `/etc/hivetech/portal.env` и рабочие каталоги сайта.

### 6. Подключить Nginx и HTTPS

Отредактируйте `server_name` и другие параметры в `deploy/nginx/hivetech-portal.conf`, затем установите конфиг:

```bash
cp deploy/nginx/hivetech-portal.conf \
  /etc/nginx/sites-available/hivetech-portal
ln -s /etc/nginx/sites-available/hivetech-portal \
  /etc/nginx/sites-enabled/hivetech-portal
nginx -t
systemctl reload nginx
```

Шаблон Nginx описывает HTTP-виртуальный хост. HTTPS нужно настроить отдельно, например с помощью Certbot, либо на существующем reverse proxy. Если TLS завершается перед веб-сервером, проверьте передачу правильной схемы и заголовков.

Нельзя публиковать `/etc/hivetech/portal.env`, `vendor/`, `console/`, `common/`, приватные загрузки или любые каталоги за пределами веб-корней.

### 7. Назначить администратора

Если пользователь уже зарегистрирован:

```bash
cd /opt/hivetech/portal
sudo -u www-data env HIVETECH_ENV_FILE=/etc/hivetech/portal.env \
  php yii user/assign-admin EXISTING_USERNAME
```

Для создания администратора:

```bash
sudo -u www-data env HIVETECH_ENV_FILE=/etc/hivetech/portal.env \
  php yii user/create-admin admin admin@example.com 'REPLACE_WITH_STRONG_PASSWORD'
```

После этого проверьте вход на `https://hivegrid.online/admin/`. Не используйте пароль из примера. Не передавайте реальные пароли в общедоступных логах и историях команд.

### 8. Настроить отправку писем

Добавьте SMTP в env. Пример для сервера с STARTTLS:

```dotenv
SMTP_HOST=smtp.example.org
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USER=no-reply@example.org
SMTP_PASSWORD=REPLACE_WITH_SMTP_PASSWORD
MAIL_FROM=no-reply@example.org
MAIL_FROM_NAME=HiveGrid
```

Проверка:

```bash
cd /opt/hivetech/portal
HIVETECH_ENV_FILE=/etc/hivetech/portal.env \
  php yii mail/test recipient@example.org
```

При пустом `SMTP_HOST` приложение использует файловый транспорт для разработки. Это не замена рабочей доставке писем.

### 9. Установить cron

```bash
cp deploy/cron/hivetech /etc/cron.d/hivetech
chmod 0644 /etc/cron.d/hivetech
touch /var/log/hivetech-{delivery,moderation,maintenance,telemetry,achievement}.log
chown www-data:www-data /var/log/hivetech-{delivery,moderation,maintenance,telemetry,achievement}.log
```

Текущий cron выполняет следующие задания:

| Периодичность | Команда | Назначение |
| --- | --- | --- |
| Каждую минуту | `delivery/process 20` | Выдача покупок и игровых команд |
| Раз в 2 минуты | `moderation/expire 200` | Завершение просроченных наказаний |
| Раз в 10 минут | `privilege/expire 100` | Истечение игровых статусов |
| Каждый час, на 12-й минуте | `privilege/notify 200` | Уведомления о статусах |
| Каждый час, на 17-й минуте | `maintenance/cleanup` | Обслуживание данных |
| Раз в 5 минут | `telemetry/close-sessions 300` | Закрытие потерянных игровых сессий |
| Каждый час, на 37-й минуте | `telemetry/cleanup 30 10000 20` | Очистка сырых игровых событий старше 30 дней, пакетами |
| Каждую минуту | `achievement/process-rewards 50` | Выдача наград за достижения |

Команды запускаются из `/opt/hivetech/portal` от `www-data`. Если изменён путь установки, скорректируйте его в `deploy/cron/hivetech`. Для многосерверного развёртывания не запускайте один и тот же cron на нескольких узлах без координации.

## Подключение игровых серверов

### Создание сервера в админке

1. Откройте `/admin/` и раздел серверов.
2. Создайте запись с уникальным кодом сервера. Этот код нужен модам в параметре `serverCode`.
3. Настройте подключение и RCON-профиль, если на сервер должны поступать команды покупок и статусов.
4. Откройте страницу `Bridge` конкретного сервера.
5. Выпустите токен. Сохраните значение `htb_...` сразу: полностью оно показывается только при выпуске или ротации.
6. Убедитесь, что Bridge включён. Для второго игрового сервера выпустите другой токен.

**Один и тот же Bridge-токен используется модулями HiveTechTelemetry и HiveTechModeration на одном игровом сервере.** В БД портала хранится хеш токена, а не его открытый текст. При ротации предыдущий токен немедленно перестаёт подходить: обновите конфиги обоих модов и перезапустите игровой сервер.

Общий адрес API:

```text
https://hivegrid.online/internal/game
```

Токен передаётся с каждым запросом:

```http
Authorization: Bearer htb_REPLACE_WITH_REAL_TOKEN
X-HiveTech-Server: YOUR_SERVER_CODE
```

`YOUR_SERVER_CODE` должен в точности совпадать с кодом в админке. Секреты нельзя помещать в исходники, публичные issue, скриншоты или логи.

## HiveTechTelemetry

Исходники: [lassizness/HiveTechTelemetry](https://github.com/lassizness/HiveTechTelemetry). Актуальная ветка исходников: [master](https://github.com/lassizness/HiveTechTelemetry/tree/master).

Мод собирает игровые события и отправляет их в портал. Он работает без подключения к MariaDB и не блокирует игровой поток HTTP-запросами. Данные сначала агрегируются в памяти и в локальной очереди. При сетевых ошибках отправка повторяется с задержкой.

### Какие события собираются

Базовые события Forge:

- вход и выход игрока, продолжительность игровой сессии;
- смерть, убийства сущностей и PvP;
- добыча и размещение блоков;
- создание, переплавка и подбор предметов;
- переходы по измерениям и достижения Minecraft.

Есть дополнительные адаптеры для IndustrialCraft 2, Applied Energistics 2, Forestry и связанных дополнений, Thermal, Immersive Engineering и Industrial Foregoing. Они расширяют набор событий, когда соответствующие моды доступны, но основная телеметрия работает без них.

Список ключей и особенностей адаптеров приведён в [документации событий](https://github.com/lassizness/HiveTechTelemetry/blob/master/docs/events.md).

### Сборка Telemetry

Нужны JDK 8, Git и доступ к репозиториям Gradle/ForgeGradle. Скрипт `build.sh` использует Gradle 4.10.3 и ищет Java 8 в `/opt/hivetech/runtime/java8` либо `/usr/lib/jvm/java-8-openjdk-amd64`.

```bash
mkdir -p /opt/hivetech/build
git clone https://github.com/lassizness/HiveTechTelemetry.git \
  /opt/hivetech/build/HiveTechTelemetry
cd /opt/hivetech/build/HiveTechTelemetry
chmod +x build.sh
./build.sh
```

Артефакт текущей версии: `build/libs/HiveTechTelemetry-1.1.0.jar`. При повышении версии имя файла изменится. Установите собранный серверный JAR в каталог `mods` игрового сервера:

```bash
install -m 0644 build/libs/HiveTechTelemetry-1.1.0.jar \
  /opt/hivetech/server/mods/
```

### Настройка Telemetry

Запустите игровой сервер один раз для создания файла `config/hivetech-telemetry.cfg` и остановите его. В примере ниже указаны основные секции и параметры:

```text
telemetry {
    B:enabled=true
}
api {
    S:apiUrl=https://hivegrid.online/internal/game
    S:serverCode=YOUR_SERVER_CODE
    S:token=htb_REPLACE_WITH_REAL_TOKEN
}
timing {
    I:batchIntervalSeconds=5
    I:heartbeatIntervalSeconds=60
    I:playtimeIntervalSeconds=60
}
queue {
    I:maxBatchSize=200
    I:maxQueueEvents=250000
}
http {
    I:connectTimeoutSeconds=3
    I:readTimeoutSeconds=8
    I:retryBaseSeconds=5
    I:retryMaxSeconds=300
}
```

Полный шаблон: [docs/hivetech-telemetry.cfg.example](https://github.com/lassizness/HiveTechTelemetry/blob/master/docs/hivetech-telemetry.cfg.example). Очередь хранится в `config/hivetech-telemetry-queue.json`. Не удаляйте этот файл при обновлении мода, если в нём остались неотправленные события.

### Проверка Telemetry

После перезапуска сервера выполните из игровой консоли или под оператором:

```text
/httelemetry status
/httelemetry flush
/httelemetry adapters
```

`status` показывает очередь, буфер, количество принятых и повторных событий, HTTP-статус, ошибки и время последней успешной отправки. `flush` запрашивает немедленную попытку отправки. `adapters` выводит активные адаптеры и версии обнаруженных модов.

В админке проверьте разделы телеметрии и событий. Затем зайдите в игру, добудьте блок, выйдите с сервера и убедитесь, что события появились у правильного игрока.

### API Telemetry

| Метод | Маршрут | Назначение |
| --- | --- | --- |
| POST | `/internal/game/telemetry/events` | Пакет игровых событий |
| POST | `/internal/game/telemetry/heartbeat` | Состояние игрового мода |

В одном пакете допускается от 1 до 500 событий, размер HTTP-тела ограничен 2 МиБ. У каждого события собственный UUID. Повторная передача события с тем же UUID не должна повторно начислять статистику. Неизвестные игроки могут попадать в сырые события, но не в личную статистику и достижения, пока привязка не разрешена.

Подробный контракт, формат JSON и поля событий: [docs/telemetry-api.md](docs/telemetry-api.md).

## HiveTechModeration

Исходники: [lassizness/hivetech-moderation](https://github.com/lassizness/hivetech-moderation).

Мод применяет ограничения, созданные в веб-админке. BAN запрещает участие игрока и отключает уже находящегося на сервере игрока. MUTE подавляет сообщения в игровом чате. Поддерживаются глобальные наказания и наказания, привязанные к определённому игровому серверу.

### Сборка и установка Moderation

Нужна Java 8. Проект использует ForgeGradle 3.x и Gradle 4.10.3, а не Gradle из репозитория HiveTechKits.

```bash
git clone https://github.com/lassizness/hivetech-moderation.git \
  /opt/hivetech/build/hivetech-moderation
cd /opt/hivetech/build/hivetech-moderation
chmod +x build.sh
./build.sh
install -m 0644 build/libs/HiveTechModeration-1.0.0.jar \
  /opt/hivetech/server/mods/
```

Запустите сервер один раз и остановите его перед правкой `config/hivetech-moderation.cfg`.

### Настройка Moderation

В Forge-конфигурации укажите:

```text
bridge {
    B:enabled=true
}
api {
    S:apiUrl=https://hivegrid.online/internal/game
    S:serverCode=YOUR_SERVER_CODE
    S:token=htb_REPLACE_WITH_REAL_TOKEN
}
timing {
    I:syncIntervalSeconds=3
    I:heartbeatIntervalSeconds=60
}
http {
    I:connectTimeoutSeconds=3
    I:readTimeoutSeconds=5
}
```

После рестарта мод загружает снимок активных наказаний, а затем забирает изменения по ревизиям. Локальный кеш сохраняется при обновлениях. Если API временно недоступен, известные запреты продолжают действовать до истечения срока. Неизвестный игрок без наказания в кеше не блокируется только из-за недоступности портала.

### Команды Moderation

```text
/htmod status
/htmod sync
```

Команды доступны операторам с уровнем прав 4. `status` показывает ревизию, размер кеша, время синхронизации и последнюю ошибку. `sync` запускает внеочередную синхронизацию.

Порядок проверки: выпустить токен, убедиться в успешном heartbeat на странице Bridge, создать тестовый MUTE/BAN в админке, проверить применение на игровом сервере, затем отозвать действие и убедиться, что отмена дошла. Тестируйте на отдельной учётной записи.

### API Moderation

| Метод | Маршрут | Назначение |
| --- | --- | --- |
| GET | `/internal/game/moderation/snapshot` | Полный активный список BAN/MUTE |
| GET | `/internal/game/moderation/changes?after=REV&limit=200` | Изменения после заданной ревизии |
| GET | `/internal/game/moderation/player/UUID` | Актуальное состояние одного игрока |
| POST | `/internal/game/bridge/heartbeat` | Состояние мода и синхронизации |

Описание области действия наказаний, ревизий, повторной синхронизации и JSON: [docs/moderation-bridge-api.md](docs/moderation-bridge-api.md).

## HiveTechKits

Исходники: [lassizness/hivetech-kits](https://github.com/lassizness/hivetech-kits).

Мод реализует команду `/kit` для игровых статусов `VIP`, `Premium`, `Elite` и `Legend`. Наборы формирует администратор прямо в игре, содержимое сохраняется на диске сервера. Сайт не отправляет предметы через Telemetry API: Kits получает право на набор через Forge PermissionAPI.

### Сборка Kits

В отличие от двух других модов, HiveTechKits использует Gradle Wrapper 9.6.1 и RetroFuturaGradle 2.0.2. Для запуска Gradle предусмотрена Java 25, а компиляция игрового кода настраивается через Java toolchain. Не запускайте сборку Kits скриптами Gradle 4.10.3 от Telemetry или Moderation.

```bash
git clone https://github.com/lassizness/hivetech-kits.git \
  /opt/hivetech/build/hivetech-kits
cd /opt/hivetech/build/hivetech-kits
java -version
chmod +x gradlew
./gradlew --version
./gradlew clean build
ls -lh build/libs/
```

Если Wrapper не запускается, проверьте JDK, сеть и `JAVA_HOME`. Устанавливайте на сервер итоговый игровой JAR из `build/libs/`, а не исходники и не вспомогательные `sources/javadoc`-артефакты, если они включены.

```bash
# Укажите фактическое имя собранного JAR из build/libs.
install -m 0644 build/libs/hivetechkits-1.0.0.jar \
  /opt/hivetech/server/mods/
```

После установки перезапустите Minecraft.

### Создание наборов

Наборы создаются для четырёх рангов: `vip`, `premium`, `elite` и `legend`.

1. Зайдите в игру с правами оператора уровня 4.
2. Положите предметы будущего набора в основной инвентарь персонажа.
3. Выполните `/hkit save vip` или команду с другим рангом.
4. Аналогично сохраните остальные три набора.
5. Проверьте список командой `/hkit list`.
6. Назначьте тестовому игроку нужное разрешение и проверьте `/kit`.

Мод сохраняет наборы в `config/hivetech-kits/kits/<rank>.dat` в формате NBT. Изменение инвентаря после команды сохранения не меняет уже сохранённый набор.

### Права на наборы

Мод регистрирует узлы:

```text
hivetech.kit.vip
hivetech.kit.premium
hivetech.kit.elite
hivetech.kit.legend
```

Проверка начинается с самого высокого статуса: `legend`, затем `elite`, `premium` и `vip`. Для игрока с несколькими разрешениями используется первый подходящий ранг.

Само наличие оплаченной привилегии в портале ещё не означает, что Forge PermissionAPI видит соответствующий узел. На игровом сервере должен работать провайдер прав, который выдаёт эти permission-узлы при назначении статуса. Проверьте соответствие RCON-команд портала настройкам именно вашего плагина или мода прав. Без этой связки `/kit` сообщит, что подходящего статуса нет.

### Выдача и периодичность

Игрок выполняет:

```text
/kit
```

По умолчанию получить набор можно раз в 30 дней. Период задаётся в:

```text
config/hivetech-kits/settings.properties
cooldown_days=30
```

Для повторной загрузки настроек используется `/hkit reload`. Время последней выдачи по UUID хранится в `config/hivetech-kits/cooldowns.properties`. Кулдаун общий для команды `/kit`, а не отдельный для каждого ранга. При выдаче мод проверяет количество свободных слотов основного инвентаря: на каждый сохранённый стек требуется свободная ячейка.

### Команды администратора Kits

| Команда | Назначение |
| --- | --- |
| `/hkit save vip` | Сохранить содержимое текущего инвентаря как набор VIP |
| `/hkit save premium` | Сохранить набор Premium |
| `/hkit save elite` | Сохранить набор Elite |
| `/hkit save legend` | Сохранить набор Legend |
| `/hkit list` | Показать доступные наборы |
| `/hkit reload` | Заново прочитать настройки и cooldown |
| `/hkit status PLAYER` | Посмотреть ранг и доступность выдачи онлайн-игроку |
| `/hkit reset PLAYER` | Сбросить задержку выдачи онлайн-игроку |

Не удаляйте `config/hivetech-kits/` при обновлении JAR: там находятся наборы и история выдач. В отличие от Telemetry и Moderation, этому моду не нужен Bridge-токен.

## Достижения и обработка событий

Последовательность при получении достижения:

1. Forge-мод формирует событие с уникальным ID и отправляет его пакетом.
2. Портал проверяет Bridge-токен, идентификатор сервера и формат события.
3. Событие сохраняется с защитой от повторного учёта.
4. Обновляются счётчики игрока, сессии и прогресс условий.
5. Если условия выполнены, создаётся запись об открытии достижения.
6. Если у достижения задана награда, создаётся отдельная задача на выдачу.
7. `achievement/process-rewards` проводит операцию, а для RCON добавляет её в очередь `delivery/process`.

Условия внутри одной группы объединяются по AND, разные группы по OR. В наградах доступны `wallet`, `rcon`, `permission` и `privilege`. Повторный пакет Telemetry не должен начислять награду второй раз.

Сырые события и агрегированные показатели имеют разный срок жизни. Текущий cron удаляет сырые записи старше 30 дней по 10 000 строк за пакет, максимум 20 пакетов за запуск. Длительная статистика и уже открытые достижения не должны зависеть от хранения каждого исходного события. Перед изменением сроков хранения проверьте требования к разбору игровых инцидентов.

Полезные команды:

```bash
cd /opt/hivetech/portal
php yii telemetry/close-sessions 300
php yii telemetry/cleanup 30 10000 20
php yii achievement/process-rewards 50
php yii achievement/recalculate
```

Сверяйте допустимые аргументы и объём перерасчёта через `php yii help achievement/recalculate` перед запуском на рабочей БД.

## GravitLauncher и игровые текстуры

Портал использует существующую схему пользователей для авторизации GravitLauncher. Поля и хеширование паролей должны оставаться совместимыми с текущей конфигурацией LaunchServer.

Публичные маршруты текстур:

```text
/texture/skin/USERNAME.png
/texture/cloak/USERNAME.png
```

Для настройки `textureProvider` есть скрипт:

```bash
cd /opt/hivetech/portal
python3 deploy/configure-gravit-textures.py \
  /opt/hivetech/launchserver/LaunchServer.json \
  https://hivegrid.online
```

Скрипт создаёт резервную копию JSON. После проверки конфигурации перезапустите соответствующий сервис LaunchServer согласно настройкам вашего хоста. Нельзя направлять игровые запросы на незащищённые или недоступные по сети URL.

## RCON, выдача покупок и привилегий

Для каждого игрового сервера задайте `rcon_env_key`. Если указано `MAIN`, приложение использует переменные:

```dotenv
MINECRAFT_RCON_MAIN_HOST=127.0.0.1
MINECRAFT_RCON_MAIN_PORT=25575
MINECRAFT_RCON_MAIN_PASSWORD=REPLACE_WITH_RCON_PASSWORD
```

Секрет RCON находится только в env и не должен отображаться в настройках товаров. В шаблонах выдачи могут использоваться переменные игрока, например `{username}` и `{uuid}`. Привилегии настраиваются в админке отдельно от наборов Kits; проверьте совместимость выбранных игровых команд с системой прав на сервере.

Для проверки выдачи:

```bash
cd /opt/hivetech/portal
HIVETECH_ENV_FILE=/etc/hivetech/portal.env php yii delivery/process 20
```

Если товар оплачен, но не выдан, проверьте очередь доставки, доступность RCON, пароль, команды товара и `/var/log/hivetech-delivery.log`. Не повторяйте вручную денежную операцию ради повторной отправки команды.

## Другие интеграции

**Голосования.** В проекте есть обработчики TopG и TopCraft. Конкретные сервисы и callback-параметры настраиваются в административной панели. Для callbacks учитывайте реальные IP доверенных reverse proxy, а не произвольный пользовательский `X-Forwarded-For`.

**Платежи.** В коде есть адаптер PayPalych/Pally и поддержка ручной обработки. Токены API и подписи хранятся в env. Прежде чем принимать реальные платежи, проверьте подпись уведомлений, переходы статусов, обработку повторных callbacks и выдачу товара.

**Форум.** Присутствует адаптер `/forum/sso` для внешнего Discourse. Наличие адаптера не означает, что самостоятельный форум уже поставляется в составе этого репозитория.

**Антибот.** Доступны настройки Google reCAPTCHA v3 в админке и опциональные параметры Cloudflare Turnstile. Для reCAPTCHA желательно сначала включить режим наблюдения, проверить реальные значения score и лишь затем ограничивать вход.

## Локализация и индексация

Интерфейс рассчитан на русский, английский и немецкий языки. Словари размещены в `common/messages/ru`, `common/messages/en` и `common/messages/de` там, где это предусмотрено для категорий переводов. Русский является исходным языком. Новости и CMS-страницы используют отдельные переводы данных.

Для проверки локализации нужно пройти публичные страницы, личный кабинет и админку: не весь динамический контент обязательно переводится той же системой, что строки интерфейса. При добавлении новой категории переводов зарегистрируйте её и в `frontend/config/main.php`, и в `backend/config/main.php`, если категория используется в обеих частях сайта.

Основные SEO-маршруты:

```text
/robots.txt
/sitemap.xml
```

При публикации новых типов публичных страниц проверяйте, что они попадают в sitemap, не содержат закрытых пользовательских данных и используют корректные canonical URL и языковые версии. Подробности: [docs/seo.md](docs/seo.md).

## Обновление проекта

Рекомендуемый порядок для рабочего окружения:

1. Сохранить базу данных, `portal.env`, текстуры, вложения, конфиги Minecraft и локальные очереди модов.
2. Проверить `git status` и список коммитов, которые будут установлены.
3. Получить код через `git pull --ff-only`.
4. Выполнить `composer install --no-dev --prefer-dist --optimize-autoloader`.
5. Выполнить новые миграции временным пользователем с необходимыми правами.
6. Проверить PHP-конфигурацию и перезапустить PHP-FPM.
7. Проверить cron, вход, страницы, платежные callbacks, Bridge API и RCON.
8. Моды Minecraft обновлять отдельно, сохраняя их конфигурацию и данные.

Пример команд после создания резервной копии:

```bash
cd /opt/hivetech/portal
git status --short
git pull --ff-only origin master
composer install --no-dev --prefer-dist --optimize-autoloader
php -l frontend/controllers/InternalGameController.php
php-fpm8.3 -t
systemctl restart php8.3-fpm
```

Миграции намеренно не включены в этот короткий пример: их нужно выполнять через временную привилегированную учётную запись, как описано выше.

## Проверки после развёртывания

```bash
cd /opt/hivetech/portal
php yii help
php yii migrate/history
nginx -t
php-fpm8.3 -t

curl -I https://hivegrid.online/
curl -I https://hivegrid.online/register
curl -I https://hivegrid.online/admin/
curl -I https://hivegrid.online/robots.txt
curl -I https://hivegrid.online/sitemap.xml
```

Дополнительно проверьте вручную:

- Регистрацию нового пользователя, вход на сайт и вход тем же аккаунтом в GravitLauncher.
- Подтверждение email, сброс пароля, отзыв сессий.
- Открытие админки с назначенной ролью и запрет доступа обычному пользователю.
- Доставку тестового email.
- Создание и просмотр обращения в поддержку.
- Баланс, промокоды, тестовую покупку и RCON-доставку.
- Bridge heartbeat двух модов, синхронизацию наказаний.
- Приём событий Telemetry, статистику и выдачу тестового достижения.
- `/kit` с разными наборами и активным/истёкшим cooldown.
- Переводы интерфейса и открытие публичных страниц на всех поддерживаемых языках.

## Диагностика

| Симптом | Что проверить |
| --- | --- |
| `502 Bad Gateway` | Работа PHP-FPM и путь `/run/php/php8.3-fpm-hivetech.sock` |
| `500` на сайте | `frontend/runtime/logs/`, `backend/runtime/logs/`, расширения PHP, доступ к БД |
| Нельзя войти в админку | Роль пользователя, cookie domain, HTTPS, маршрут `/admin/` |
| Bridge возвращает `401` | Код сервера, актуальность токена, заголовок Authorization, состояние Bridge в админке |
| Telemetry не передаёт события | `/httelemetry status`, исходящий HTTPS, конфиг, очередь, время и ошибки |
| Наказание не применяется | `/htmod status`, серверный scope, ревизия, срок BAN/MUTE, синхронизация |
| `/kit` не видит статус | Forge PermissionAPI, узел `hivetech.kit.*`, система игровых прав |
| `/kit` не выдаёт предметы | Наличие сохранённого набора, свободные слоты, cooldown |
| Покупка не выдана | Очередь `delivery_queue`, RCON-профиль, команда выдачи, логи cron |
| Не открывается достижение | Привязка игрока, события Telemetry, группы условий и их значения |
| Награда зависла | `achievement/process-rewards`, очередь доставки, число попыток |
| Не уходят письма | SMTP-параметры, доступ к серверу, отправитель, `mail/test` |

Не публикуйте в issue реальные токены, пароли, cookie, дампы таблиц пользователей и личные данные. В диагностике достаточно HTTP-кода, времени ошибки, анонимизированного сообщения и версии компонента.

## Резервное копирование

Для восстановления работоспособности недостаточно одного Git-репозитория. Помимо исходников сохраняйте:

- дамп `hivetech_core` с согласованным состоянием данных;
- `/etc/hivetech/portal.env` в защищённом хранилище;
- `storage/textures/` и `storage/uploads/`;
- конфигурации PHP-FPM, Nginx и параметры запуска сервисов;
- `LaunchServer.json` и настройки GravitLauncher;
- `mods/`, `config/hivetech-telemetry*.cfg`, `config/hivetech-moderation.cfg`;
- очередь `hivetech-telemetry-queue.json`, если там остались неотправленные события;
- `config/hivetech-kits/` с наборами и cooldown.

После восстановления БД проверяйте соответствие используемых Bridge-токенов сохранённым хешам. Если произошла ротация токенов после создания резервной копии, придётся синхронизировать конфиги модов и состояние Bridge.

## Разработка

Для локальной разработки можно использовать `YII_ENV=dev` и `YII_DEBUG=1`, но не включайте их на публичном сервере. Перед изменением моделей или API необходимо проверить влияние на миграции, общую таблицу пользователей GravitLauncher, RCON-доставку и совместимость уже выпущенных серверных JAR.

Базовая проверка PHP-кода:

```bash
find common frontend backend console deploy -type f -name '*.php' \
  -print0 | xargs -0 -n1 php -l
```

В репозитории также есть workflow `.github/workflows/php-lint.yml`. Изменения схемы БД оформляйте миграциями, а изменения игрового протокола синхронизируйте с исходниками модов.

### Документы по интеграции

- [Moderation Bridge API](docs/moderation-bridge-api.md)
- [Telemetry API](docs/telemetry-api.md)
- [SEO](docs/seo.md)
- [Иконки достижений](docs/achievement-badges-v2.md)
- [Исходники HiveTechTelemetry](https://github.com/lassizness/HiveTechTelemetry)
- [Исходники HiveTechModeration](https://github.com/lassizness/hivetech-moderation)
- [Исходники HiveTechKits](https://github.com/lassizness/hivetech-kits)

Исходный код портала и игровых модов обновляется независимо. Если изменяется контракт Bridge API, сначала проверяйте совместимость установленного мода с обновлением сайта, а только затем выкатывайте изменения на рабочий сервер.
