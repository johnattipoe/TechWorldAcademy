<?php
declare(strict_types=1);

return [
    'contact_email' => env_value('CONTACT_EMAIL', 'hello@techworld.academy'),
    'mail_from' => env_value('MAIL_FROM'),
    'cloudflare_r2' => [
        'account_id' => env_value('CLOUDFLARE_R2_ACCOUNT_ID'),
        'bucket' => env_value('CLOUDFLARE_R2_BUCKET'),
        'access_key_id' => env_value('CLOUDFLARE_R2_ACCESS_KEY_ID'),
        'secret_access_key' => env_value('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
        'public_url' => env_value('CLOUDFLARE_R2_PUBLIC_URL'),
    ],
];
