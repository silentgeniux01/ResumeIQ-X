<?php
/*
==================================================
Email Configuration Test Script
Tests if email environment variables are loaded correctly
==================================================
*/

require_once "config.php";

header("Content-Type: application/json");

$emailConfig = [
    'MAIL_HOST'         => env('MAIL_HOST', 'NOT SET'),
    'MAIL_PORT'         => env('MAIL_PORT', 'NOT SET'),
    'MAIL_USERNAME'     => env('MAIL_USERNAME', 'NOT SET'),
    'MAIL_PASSWORD'     => env('MAIL_PASSWORD') ? '***SET***' : 'NOT SET',
    'MAIL_FROM_NAME'    => env('MAIL_FROM_NAME', 'NOT SET'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', 'NOT SET'),
];

echo json_encode([
    'status' => true,
    'message' => 'Email configuration loaded',
    'config' => $emailConfig,
    'env_file_exists' => file_exists(dirname(__DIR__) . '/.env'),
    'app_url' => env('APP_URL', 'NOT SET'),
], JSON_PRETTY_PRINT);
