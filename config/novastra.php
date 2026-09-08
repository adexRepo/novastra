<?php

return [
    'whatsapp' => env('WHATSAPP_ADMIN_NUMBER', '6281234567890'),
    'customer' => [
        'username' => env('CUSTOMER_USERNAME', 'pelanggan'),
        'password' => env('CUSTOMER_PASSWORD'),
        'name' => env('CUSTOMER_NAME', 'Pelanggan Novastra'),
        'email' => env('CUSTOMER_EMAIL', 'pelanggan@example.com'),
    ],
    'admin' => [
        'username' => env('ADMIN_USERNAME', 'admin'),
        'password' => env('ADMIN_PASSWORD'),
        'password_hash' => env('ADMIN_PASSWORD_HASH'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
    ],
    'public_upload_dir' => env('PUBLIC_UPLOAD_DIR') ?: public_path('uploads'),
    'private_upload_dir' => env('PRIVATE_UPLOAD_DIR') ?: storage_path('app/private'),
    'temp_upload_dir' => env('TEMP_UPLOAD_DIR') ?: storage_path('app/tmp'),
];
