<?php
/*
==================================================
ResumeIQ-X Recruiter Registration
Registers new recruiter with OTP verification
==================================================
*/

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/email_helper.php';
require_once __DIR__ . '/sms_helper.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method not allowed"]);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$password = trim($_POST['password'] ?? '');
$companyName = trim($_POST['company_name'] ?? '');

// Log received data (without password)
error_log("[ResumeIQ-X][Recruiter Register] Received data - Name: $name, Email: $email, Mobile: $mobile, Company: $companyName");

// Validation
if (!$name || !$email || !$mobile || !$password) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Invalid email format"]);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Password must be at least 8 characters"]);
    exit;
}

try {
    $db = getDatabaseConnection();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => false, "message" => "Email already registered"]);
        exit;
    }
    
    // Check if email OTP was verified
    $stmt = $db->prepare("SELECT id FROM otp_temp WHERE email=:e AND otp_type='email' AND verified=1 ORDER BY id DESC LIMIT 1");
    $stmt->execute([':e' => $email]);
    $emailVerified = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emailVerified) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Please verify your email OTP first"]);
        exit;
    }

    // Check if mobile OTP was verified
    $stmt = $db->prepare("SELECT id FROM otp_temp WHERE email=:e AND otp_type='mobile' AND verified=1 ORDER BY id DESC LIMIT 1");
    $stmt->execute([':e' => $email]);
    $mobileVerified = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mobileVerified) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Please verify your mobile OTP first"]);
        exit;
    }
    
    // Hash password with bcrypt (cost factor 10)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Create recruiter account (already verified)
    $stmt = $db->prepare("
        INSERT INTO users (
            name, email, mobile, password, role, company_name,
            account_status, email_verified, mobile_verified
        ) VALUES (
            :name, :email, :mobile, :password, 'recruiter', :company_name,
            'active', 1, 1
        )
    ");
    
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':mobile' => $mobile,
        ':password' => $hashedPassword,
        ':company_name' => $companyName
    ]);
    
    $userId = $db->lastInsertId();
    
    // Clean up verified OTPs
    $db->prepare("DELETE FROM otp_temp WHERE email=:e AND verified=1")->execute([':e' => $email]);
    
    echo json_encode([
        "status" => true,
        "message" => "Recruiter account created successfully! You can now log in.",
        "data" => [
            "user_id" => $userId
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Recruiter Register] Database error: " . $e->getMessage());
    error_log("[ResumeIQ-X][Recruiter Register] Stack trace: " . $e->getTraceAsString());
    error_log("[ResumeIQ-X][Recruiter Register] SQL State: " . $e->getCode());
    http_response_code(500);
    
    // More specific error messages for debugging
    $errorMessage = "Registration failed. Please try again.";
    $debugInfo = $e->getMessage();
    
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $errorMessage = "This email or mobile number is already registered.";
        // Extract which field is duplicate
        if (strpos($e->getMessage(), 'email') !== false) {
            $errorMessage = "This email address is already registered. Please use a different email or login.";
        } elseif (strpos($e->getMessage(), 'mobile') !== false) {
            $errorMessage = "This mobile number is already registered. Please use a different number or login.";
        }
    } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
        $errorMessage = "Database schema error: Missing column. Please contact support.";
        // Extract column name
        preg_match("/Unknown column '([^']+)'/", $e->getMessage(), $matches);
        if (isset($matches[1])) {
            $debugInfo = "Missing column: " . $matches[1];
        }
    } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
        $errorMessage = "Database table error. Please contact support.";
    } elseif (strpos($e->getMessage(), 'Data too long') !== false) {
        $errorMessage = "One of the fields is too long. Please check your input.";
    }
    
    echo json_encode([
        "status" => false, 
        "message" => $errorMessage, 
        "debug" => $debugInfo,
        "sql_state" => $e->getCode()
    ]);
}
