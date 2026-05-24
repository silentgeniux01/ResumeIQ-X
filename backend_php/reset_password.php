<?php

/*
==================================================
ResumeIQ-X Password Reset Controller
Validates token and updates hashed password in DB
==================================================
*/

header("Content-Type: application/json");

require_once "db.php";


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

$token    = trim($_POST["token"]    ?? "");
$password = trim($_POST["password"] ?? "");

if (!$token || !$password) {
    echo json_encode(["status" => false, "message" => "Token and new password are required"]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["status" => false, "message" => "Password must be at least 6 characters"]);
    exit;
}


/*
==================================================
VALIDATE TOKEN (check password_resets table first,
then fall back to users.reset_token)
==================================================
*/

$db = getDatabaseConnection();

// Check password_resets table
$stmt = $db->prepare(
    "SELECT email FROM password_resets WHERE reset_token = :token AND expiry_time > NOW() LIMIT 1"
);
$stmt->execute([":token" => $token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reset) {
    $email = $reset["email"];

    // Fetch user by email
    $stmt2 = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt2->execute([":email" => $email]);
    $user = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => false, "message" => "User not found"]);
        exit;
    }

    $userId = $user["id"];

    // Delete used token
    $db->prepare("DELETE FROM password_resets WHERE reset_token = :token")->execute([":token" => $token]);

} else {
    // Fall back to users.reset_token
    $stmt = $db->prepare(
        "SELECT id FROM users WHERE reset_token = :token AND token_expiry > NOW() LIMIT 1"
    );
    $stmt->execute([":token" => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => false, "message" => "Reset link is invalid or has expired. Please request a new one."]);
        exit;
    }

    $userId = $user["id"];
}


/*
==================================================
UPDATE PASSWORD
==================================================
*/

$hash = password_hash($password, PASSWORD_BCRYPT);

$db->prepare(
    "UPDATE users SET password = :password, reset_token = NULL, token_expiry = NULL WHERE id = :id"
)->execute([":password" => $hash, ":id" => $userId]);


/*
==================================================
SUCCESS
==================================================
*/

echo json_encode([
    "status"   => true,
    "message"  => "Password updated successfully! You can now log in.",
    "redirect" => "../frontend/user_login.html",
]);

exit;
