<?php
/*
==================================================
ResumeIQ-X Forgot Password Controller
Sends password reset email with a working link
Auto-detects server URL — works on localhost + cloud
==================================================
*/

header("Content-Type: application/json");

require_once "config.php";
require_once "db.php";
require_once "email_helper.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
    exit;
}

$email = trim($_POST["email"] ?? "");

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "message" => "Valid email required"]);
    exit;
}

/* ── Look up user ── */
$db   = getDatabaseConnection();
$stmt = $db->prepare("SELECT id, name FROM users WHERE email = :email LIMIT 1");
$stmt->execute([":email" => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Prevent email enumeration — always return success
    echo json_encode(["status" => true, "message" => "If that email is registered, a reset link has been sent."]);
    exit;
}

/* ── Generate secure token ── */
$token  = bin2hex(random_bytes(32));
$expiry = date("Y-m-d H:i:s", time() + 1800); // 30 minutes

/* ── Store token ── */
$db->prepare("DELETE FROM password_resets WHERE email = :email")
   ->execute([":email" => $email]);

$db->prepare("INSERT INTO password_resets (email, reset_token, expiry_time) VALUES (:email, :token, :expiry)")
   ->execute([":email" => $email, ":token" => $token, ":expiry" => $expiry]);

$db->prepare("UPDATE users SET reset_token = :token, token_expiry = :expiry WHERE id = :id")
   ->execute([":token" => $token, ":expiry" => $expiry, ":id" => $user["id"]]);

/* ── Build reset URL ──
   Auto-detects from the current HTTP request.
   Works on localhost/ResumeIQ-X AND any cloud host.
── */
$scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host       = $_SERVER['HTTP_HOST'] ?? 'localhost';
// SCRIPT_NAME = /ResumeIQ-X/backend_php/forgot_password.php
// dirname twice → /ResumeIQ-X
$scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); // /ResumeIQ-X/backend_php
$projectWeb = dirname($scriptDir); // /ResumeIQ-X
$appUrl     = $scheme . '://' . $host . rtrim($projectWeb, '/');

// Allow .env override for cloud deployments
$envUrl = rtrim(env('APP_URL', ''), '/');
if ($envUrl && $envUrl !== 'http://localhost' && $envUrl !== 'https://localhost') {
    $appUrl = $envUrl;
}

$resetLink = $appUrl . '/frontend/reset_password.html?token=' . urlencode($token);
error_log("[ResumeIQ-X] Password reset link: {$resetLink}");

/* ── Send email ── */
$sent = sendPasswordResetEmail($email, $user["name"], $token, $resetLink);

if (!$sent) {
    error_log("[ResumeIQ-X] Password reset email FAILED for: {$email}");
}

echo json_encode([
    "status"  => true,
    "message" => "Password reset link has been sent to your email. Please check your inbox (and spam folder).",
]);
exit;
