<?php
declare(strict_types=1);

return [
    'name' => env_value('APP_NAME', 'TechWorld Academy'),
    'environment' => env_value('APP_ENV', 'local'),
    'url' => rtrim((string) env_value('APP_URL', env_value('RENDER_EXTERNAL_URL', 'http://localhost')), '/'),
    'contact_email' => env_value('CONTACT_EMAIL', 'hello@techworld.academy'),
];
