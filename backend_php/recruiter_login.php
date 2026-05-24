<?php
/*
==================================================
ResumeIQ-X Recruiter Login
Authenticates recruiter and creates session
==================================================
*/

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method not allowed"]);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Email and password required"]);
    exit;
}

try {
    $db = getDatabaseConnection();
    
    // Get user by email
    $stmt = $db->prepare("
        SELECT id, name, email, mobile, password, role, account_status, 
               email_verified, mobile_verified, company_name
        FROM users 
        WHERE email = :email AND role = 'recruiter'
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(["status" => false, "message" => "Invalid email or password"]);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["status" => false, "message" => "Invalid email or password"]);
        exit;
    }
    
    // Check if email is verified
    if (!$user['email_verified']) {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "Please verify your email before logging in",
            "requires_verification" => true
        ]);
        exit;
    }
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    /*
    ==================================================
    CLEAR ANY EXISTING CANDIDATE/ADMIN SESSIONS
    CRITICAL: Prevent session contamination
    ==================================================
    */
    
    unset($_SESSION["user_id"]);
    unset($_SESSION["admin_id"]);
    
    /*
    ==================================================
    CREATE RECRUITER SESSION ONLY
    ==================================================
    */
    
    $_SESSION['recruiter_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = 'recruiter';
    $_SESSION['company_name'] = $user['company_name'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "data" => [
            "user_id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "role" => $user['role'],
            "company_name" => $user['company_name'],
            "email_verified" => (bool) $user['email_verified'],
            "mobile_verified" => (bool) $user['mobile_verified']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Recruiter Login] Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Login failed. Please try again."]);
}
