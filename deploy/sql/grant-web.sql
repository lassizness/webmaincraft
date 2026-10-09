-- Run on mc-data AFTER yii migrate. Adjust host if mc-edge changes.
-- Existing users grant is repeated intentionally so deployment is reproducible.
GRANT SELECT, INSERT,
      UPDATE (`serverID`,`email`,`password`,`status`,`email_verified`,`accessToken`,`last_login_at`,`hwidId`)
ON `hivetech_core`.`users`
TO 'hivetech_web'@'10.140.0.141';

GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`user_tokens` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`web_sessions` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`login_attempts` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT ON `hivetech_core`.`auth_rule` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`auth_item` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`auth_item_child` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`auth_assignment` TO 'hivetech_web'@'10.140.0.141';

GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`news_categories` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`news` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`pages` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`slides` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`support_tickets` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`support_messages` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`support_attachments` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`wallets` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`wallet_transactions` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`payment_orders` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`shop_categories` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`shop_products` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`shop_purchases` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`promo_codes` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`promo_code_usages` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`servers` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`delivery_queue` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`settings` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`audit_log` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`rbac_role_meta` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`moderation_actions` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`moderation_action_history` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`server_moderation_bridge` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT ON `hivetech_core`.`migration` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, UPDATE (`banned`) ON `hivetech_core`.`hwids` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT ON `hivetech_core`.`hwidLog` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`privilege_groups` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`user_privileges` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`privilege_history` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`user_cosmetics` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT ON `hivetech_core`.`privilege_notification_log` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`user_permissions` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`vote_services` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`votes` TO 'hivetech_web'@'10.140.0.141';

GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`telemetry_events` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`player_stats` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`player_sessions` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`player_discoveries` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`server_telemetry_status` TO 'hivetech_web'@'10.140.0.141';

GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`achievement_seasons` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`achievements` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`achievement_conditions` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`achievement_rewards` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`achievement_progress` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`achievement_unlocks` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`player_profile_settings` TO 'hivetech_web'@'10.140.0.141';
GRANT SELECT, INSERT, UPDATE, DELETE ON `hivetech_core`.`achievement_reward_deliveries` TO 'hivetech_web'@'10.140.0.141';
FLUSH PRIVILEGES;
