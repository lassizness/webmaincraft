<?php

$appUrl = rtrim(
    (string) ht_env('APP_URL', 'http://localhost'),
    '/'
);

return [
    'appName' => ht_env('APP_NAME', 'HiveTech'),
    'appUrl' => $appUrl,
    'adminUrl' => rtrim(
        (string) ht_env('ADMIN_URL', $appUrl . '/admin'),
        '/'
    ),
    'supportEmail' => ht_env('MAIL_FROM', 'no-reply@localhost'),
    'turnstileSiteKey' => ht_env('TURNSTILE_SITE_KEY', ''),
    'forumUrl' => ht_env('FORUM_URL', ''),
];
