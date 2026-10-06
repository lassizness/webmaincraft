HiveTech privileges v1
=======================

This patch integrates paid Minecraft statuses into the EXISTING:
- shop_products / shop_purchases
- wallets / wallet_transactions
- delivery_queue
- RCON delivery worker

FTBUtilities ranks expected on the game server:
- vip
- premium
- elite
- legend

New DB objects:
- privilege_groups
- user_privileges
- privilege_history

shop_products gets:
- product_type
- privilege_group_id
- duration_days

Behavior:
- same status purchase => extends current active_to
- higher status purchase => upgrades and preserves remaining time, then adds purchased days
- lower status purchase while a higher one is active => blocked
- admin grant can replace current status
- expiry => status becomes expired and "ranks remove" is queued
- delivery/process now expires due statuses before processing queue items

Install:
1. Back up portal.
2. Extract archive over /opt/hivetech/portal
3. Run:
   php deploy/patch-privilege-navigation.php
4. Lint:
   find common backend frontend console deploy -type f -name '*.php' -print0 | xargs -0 -n1 php -l
5. Run migration:
   php yii migrate --interactive=0
6. Restart PHP:
   systemctl restart php8.3-fpm

Admin:
- /privilege/index
- edit groups
- create shop products with product_type "Игровой статус"
- choose server, privilege group, duration and price

Frontend:
- /privilege/index

Delivery:
- existing "php yii delivery/process" also handles privilege expiration.
- one purchase creates ranks add/remove RCON queue items automatically.

Test without payment:
- admin -> Privileges -> manual grant to testsql
- then:
  php yii delivery/process
- game server should receive:
  ranks add testsql vip

Check delivery queue:
  SELECT id,user_id,server_id,payload,status,last_error FROM delivery_queue ORDER BY id DESC LIMIT 20;
