<?php

/*
==================================================
ResumeIQ-X User Registration Engine
Email OTP Verification Flow
==================================================
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once "db.php";
require_once "email_helper.php";


/*
==================================================
ALLOW ONLY POST REQUESTS
==================================================
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
    exit;
}


/*
==================================================
FETCH INPUT DATA
==================================================
*/

$name     = trim($_POST["name"]     ?? "");
$email    = trim($_POST["email"]    ?? "");
$mobile   = trim($_POST["mobile"]   ?? "");
$password = trim($_POST["password"] ?? "");
$role     = trim($_POST["role"]     ?? "candidate");


/*
==================================================
VALIDATE INPUT
==================================================
*/

if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
    echo json_encode(["status" => false, "message" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "message" => "Invalid email format"]);
    exit;
}

// Strip country code prefix if present (e.g. +91, 0091) — keep only digits
$mobileDigits = preg_replace('/[^0-9]/', '', $mobile);
// If 12 digits starting with 91 (India), strip the 91
if (strlen($mobileDigits) === 12 && substr($mobileDigits, 0, 2) === '91') {
    $mobileDigits = substr($mobileDigits, 2);
}
// Accept 10-digit numbers
if (!preg_match('/^[0-9]{10}$/', $mobileDigits)) {
    echo json_encode(["status" => false, "message" => "Invalid mobile number (10 digits required)"]);
    exit;
}
// Store the clean 10-digit number
$mobile = $mobileDigits;

if (strlen($password) < 6) {
    echo json_encode(["status" => false, "message" => "Password must be at least 6 characters"]);
    exit;
}


/*
==================================================
ROLE SECURITY
==================================================
*/

$allowedRoles = ["candidate", "recruiter"];
if (!in_array($role, $allowedRoles)) {
    $role = "candidate";
}


/*
==================================================
CHECK DUPLICATE USER
==================================================
*/

if (userExists($email, $mobile)) {
    echo json_encode(["status" => false, "message" => "User already exists. Please login."]);
    exit;
}


/*
==================================================
VERIFY OTP BEFORE ACCOUNT CREATION
==================================================
*/

$db = getDatabaseConnection();

// Check if email OTP was verified
$stmt = $db->prepare("SELECT id FROM otp_temp WHERE email=:e AND otp_type='email' AND verified=1 ORDER BY id DESC LIMIT 1");
$stmt->execute([':e' => $email]);
$emailVerified = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$emailVerified) {
    echo json_encode(["status" => false, "message" => "Please verify your email OTP first"]);
    exit;
}

// Check if mobile OTP was verified
$stmt = $db->prepare("SELECT id FROM otp_temp WHERE email=:e AND otp_type='mobile' AND verified=1 ORDER BY id DESC LIMIT 1");
$stmt->execute([':e' => $email]);
$mobileVerified = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mobileVerified) {
    echo json_encode(["status" => false, "message" => "Please verify your mobile OTP first"]);
    exit;
}


/*
==================================================
CREATE ACTIVE USER ACCOUNT
(Email + Mobile verified via OTP)
==================================================
*/

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare(
    'INSERT INTO users (name, email, mobile, password, role, account_status, email_verified, mobile_verified)
     VALUES (:name, :email, :mobile, :password, :role, \'active\', 1, 1)'
);
try {
    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':mobile'   => $mobile,
        ':password' => $hashedPassword,
        ':role'     => $role,
    ]);
    $userId = (int) $db->lastInsertId();
} catch (PDOException $e) {
    error_log('[ResumeIQ-X] register_user failed: ' . $e->getMessage());
    error_log('[ResumeIQ-X] register_user stack trace: ' . $e->getTraceAsString());
    
    // More specific error messages
    $errorMessage = "Registration failed. Please try again.";
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $errorMessage = "This email or mobile number is already registered.";
    } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
        $errorMessage = "Database schema error. Please contact support.";
    } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
        $errorMessage = "Database table error. Please contact support.";
    }
    
    echo json_encode(["status" => false, "message" => $errorMessage, "debug" => $e->getMessage()]);
    exit;
}

if (!$userId) {
    echo json_encode(["status" => false, "message" => "Registration failed. Please try again."]);
    exit;
}


/*
==================================================
SUCCESS RESPONSE
==================================================
*/

// Clean up verified OTPs
$db->prepare("DELETE FROM otp_temp WHERE email=:e AND verified=1")->execute([':e' => $email]);

echo json_encode([
    "status"  => true,
    "message" => "Account created successfully! You can now log in.",
    "email"   => $email,
]);

exit;
