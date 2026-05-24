<?php
/*
==================================================
Debug Login Issue
==================================================
*/

header("Content-Type: application/json");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$email = 'sakshispatil4106@gmail.com';
$testPassword = 'sakshi@123'; // Replace with the actual password you're trying

try {
    $db = getDatabaseConnection();
    
    // Get user details
    $stmt = $db->prepare("
        SELECT id, name, email, mobile, password, role, account_status, 
               email_verified, mobile_verified, company_name
        FROM users 
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            "status" => false,
            "error" => "User not found",
            "email_searched" => $email
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Test password verification
    $passwordMatch = password_verify($testPassword, $user['password']);
    
    echo json_encode([
        "status" => true,
        "user_found" => true,
        "user_id" => $user['id'],
        "email" => $user['email'],
        "role" => $user['role'],
        "account_status" => $user['account_status'],
        "email_verified" => (int) $user['email_verified'],
        "mobile_verified" => (int) $user['mobile_verified'],
        "company_name" => $user['company_name'],
        "password_hash_start" => substr($user['password'], 0, 10),
        "password_length" => strlen($user['password']),
        "test_password_used" => $testPassword,
        "password_match" => $passwordMatch,
        "login_would_succeed" => ($passwordMatch && $user['email_verified'] == 1 && $user['role'] == 'recruiter')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
