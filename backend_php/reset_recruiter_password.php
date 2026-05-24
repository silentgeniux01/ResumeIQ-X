<?php
/*
==================================================
Reset Recruiter Password
==================================================
*/

header("Content-Type: application/json");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$email = 'sakshispatil4196@gmail.com';
$newPassword = 'sakshi@123';

try {
    $db = getDatabaseConnection();
    
    // Generate new password hash
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Update password
    $stmt = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->execute([
        ':password' => $hashedPassword,
        ':email' => $email
    ]);
    
    // Verify the user
    $stmt = $db->prepare("SELECT id, email, role, LEFT(password, 20) as hash_preview FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test the password
    $stmt = $db->prepare("SELECT password FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $passwordWorks = password_verify($newPassword, $result['password']);
    
    echo json_encode([
        "status" => true,
        "message" => "Password reset successfully",
        "email" => $email,
        "new_password" => $newPassword,
        "user_found" => $user ? true : false,
        "user_details" => $user,
        "password_verification_test" => $passwordWorks ? "SUCCESS - Password works!" : "FAILED - Password doesn't work"
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
