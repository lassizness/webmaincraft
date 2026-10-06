<?php

return [
    'appName' => ht_env('APP_NAME', 'HiveTech'),
    'appUrl' => rtrim((string) ht_env('APP_URL', 'http://localhost'), '/'),
    'adminUrl' => rtrim((string) ht_env('ADMIN_URL', 'http://localhost:8081'), '/'),
    'supportEmail' => ht_env('MAIL_FROM', 'no-reply@localhost'),
    'turnstileSiteKey' => ht_env('TURNSTILE_SITE_KEY', ''),
    'forumUrl' => ht_env('FORUM_URL', ''),
];
