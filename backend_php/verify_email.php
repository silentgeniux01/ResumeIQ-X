<?php

/*
==================================================
ResumeIQ-X Email OTP Verification Controller
Verifies OTP and activates user account
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
FETCH INPUT
==================================================
*/

$action = trim($_POST["action"] ?? "verify");  // verify | resend
$email  = trim($_POST["email"]  ?? "");
$otp    = trim($_POST["otp"]    ?? "");


/*
==================================================
VALIDATE EMAIL
==================================================
*/

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "message" => "Valid email required"]);
    exit;
}


/*
==================================================
RESEND OTP ACTION
==================================================
*/

if ($action === "resend") {
    $db   = getDatabaseConnection();
    $stmt = $db->prepare("SELECT name FROM users WHERE email = :email AND account_status = 'pending' LIMIT 1");
    $stmt->execute([":email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => false, "message" => "No pending account found for this email"]);
        exit;
    }

    $newOtp = refreshVerificationOTP($email);
    if (!$newOtp) {
        echo json_encode(["status" => false, "message" => "Failed to generate new OTP"]);
        exit;
    }

    $sent = sendVerificationEmail($email, $user["name"], $newOtp);

    echo json_encode([
        "status"  => true,
        "message" => "New OTP sent to your email",
        "email_sent" => $sent,
    ]);
    exit;
}


/*
==================================================
VERIFY OTP ACTION
==================================================
*/

if (empty($otp)) {
    echo json_encode(["status" => false, "message" => "OTP is required"]);
    exit;
}

$verified = verifyEmailOTP($email, $otp);

if (!$verified) {
    echo json_encode(["status" => false, "message" => "Invalid or expired OTP. Please try again or request a new OTP."]);
    exit;
}


/*
==================================================
SUCCESS — Account is now active
==================================================
*/

echo json_encode([
    "status"   => true,
    "message"  => "Email verified successfully! You can now log in.",
    "redirect" => "../frontend/user_login.html",
]);

exit;
