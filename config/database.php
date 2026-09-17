<?php
/**
 * Konfigurasi Database Aplikasi Buku Tamu
 * Mendukung MySQL (default) dengan fallback otomatis ke SQLite.
 */

return [
    'default' => 'mysql', // 'mysql' atau 'sqlite'
    
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'db_buku_tamu',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    
    'sqlite' => [
        'database' => __DIR__ . '/../storage/buku_tamu.sqlite',
    ]
];
