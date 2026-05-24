<?php

/*
==================================================
ResumeIQ-X Unified Login Controller
Enterprise Multi-Role Authentication Engine
Supports:
- user
- admin
- recruiter
Session-safe + Middleware-compatible Version
==================================================
*/


/*
==================================================
SAFE SESSION INITIALIZATION
==================================================
*/

if (session_status() === PHP_SESSION_NONE) {

session_start();

}


/*
==================================================
LOAD DATABASE ENGINE
==================================================
*/

require_once "db.php";

header("Content-Type: application/json");


/*
==================================================
ALLOW ONLY POST REQUEST
==================================================
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

echo json_encode([
"status" => false,
"message" => "Invalid request method"
]);

exit;

}


/*
==================================================
FETCH LOGIN DATA
==================================================
*/

$email = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");


/*
==================================================
VALIDATE INPUT
==================================================
*/

if (!$email || !$password) {

echo json_encode([
"status" => false,
"message" => "Email and password required"
]);

exit;

}


/*
==================================================
DATABASE CONNECTION
==================================================
*/

try {

$db = getDatabaseConnection();

} catch (Exception $e) {

echo json_encode([
"status" => false,
"message" => "Database unavailable"
]);

exit;

}


/*
==================================================
FETCH USER RECORD (CANDIDATE ONLY)
CRITICAL: This endpoint ONLY authenticates candidates
Admin and recruiter MUST use their own login endpoints
==================================================
*/

$stmt = $db->prepare("

SELECT
id,
name,
email,
password,
role,
account_status

FROM users

WHERE email = :email
AND role IN ('candidate', 'user')

LIMIT 1

");

$stmt->execute([
":email" => $email
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

echo json_encode([
"status" => false,
"message" => "Invalid email or password"
]);

exit;

}


/*
==================================================
ACCOUNT STATUS CHECK
==================================================
*/

if (($user["account_status"] ?? "inactive") === "pending") {
    echo json_encode([
        "status"  => false,
        "message" => "Email not verified. Please check your email for the OTP and verify your account.",
        "needs_verification" => true,
        "email"   => $user["email"],
    ]);
    exit;
}

if (($user["account_status"] ?? "inactive") !== "active") {
    echo json_encode([
        "status"  => false,
        "message" => "Account inactive. Contact administrator.",
    ]);
    exit;
}


/*
==================================================
PASSWORD VERIFY
==================================================
*/

if (!password_verify($password, $user["password"])) {

echo json_encode([
"status" => false,
"message" => "Invalid password"
]);

exit;

}


/*
==================================================
ROLE VALIDATION - CANDIDATE ONLY
CRITICAL: Reject admin/recruiter attempts
==================================================
*/

$role = strtolower(trim($user["role"]));

// Normalize "user" to "candidate" for consistency
if ($role === "user") {
    $role = "candidate";
    // Also update DB to keep it consistent
    try {
        $db->prepare("UPDATE users SET role='candidate' WHERE id=:id")
           ->execute([":id" => $user["id"]]);
    } catch (Exception $e) { /* non-fatal */ }
}

// CRITICAL: Only allow candidate login through this endpoint
if ($role !== "candidate") {
    echo json_encode([
        "status" => false,
        "message" => "Invalid login endpoint. Please use the correct login page for your role."
    ]);
    exit;
}


/*
==================================================
SECURE SESSION CREATION
(Session fixation protection enabled)
==================================================
*/

session_regenerate_id(true);


/*
==================================================
CLEAR ANY EXISTING ADMIN/RECRUITER SESSIONS
CRITICAL: Prevent session contamination
==================================================
*/

unset($_SESSION["admin_id"]);
unset($_SESSION["recruiter_id"]);


/*
==================================================
STORE CANDIDATE SESSION DATA ONLY
==================================================
*/

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"]  = $user["name"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["user_role"] = "candidate";


/*
==================================================
ROLE BASED REDIRECTION ENGINE
CANDIDATE ONLY - Always redirect to upload page
==================================================
*/

$redirectPage = "../frontend/upload_resume.php";


/*
==================================================
SUCCESS RESPONSE
==================================================
*/

echo json_encode([

"status" => true,

"message" => "Login successful",

"user" => [

"id" => $user["id"],
"name" => $user["name"],
"email" => $user["email"],
"role" => "candidate"

],

"redirect" => $redirectPage

]);

exit;

?>