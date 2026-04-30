<?php
// app/config/database.php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'host' => '127.0.0.1',  // Gunakan IP, bukan localhost
            'database' => getenv('DB_NAME') ?: 'cobaekspor',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'socket' => '/opt/lampp/var/mysql/mysql.sock', // Socket LAMPP
            'port' => 3306,
        ]
    ]
];
