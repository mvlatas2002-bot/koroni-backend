<?php

return [
    'push' => [
        'enabled' => env('NOTIFICATION_PUSH_ENABLED', false),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@koronisa.local'),
    ],
];
