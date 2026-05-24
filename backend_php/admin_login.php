<?php


/*
==================================================
SAFE SESSION INITIALIZATION
==================================================
*/

if(session_status() === PHP_SESSION_NONE){

session_start();

}


/*
==================================================
LOAD DATABASE ENGINE
==================================================
*/

require_once "db.php";


/*
==================================================
SET RESPONSE TYPE
==================================================
*/

header("Content-Type: application/json");


/*
==================================================
ALLOW ONLY POST REQUEST
==================================================
*/

if($_SERVER["REQUEST_METHOD"] !== "POST"){

echo json_encode([
"status"=>false,
"message"=>"Invalid request method"
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


if(!$email || !$password){

echo json_encode([
"status"=>false,
"message"=>"Email and password required"
]);

exit;

}


/*
==================================================
CONNECT DATABASE
==================================================
*/

try{

$db = getDatabaseConnection();

}catch(Exception $e){

echo json_encode([
"status"=>false,
"message"=>"Database unavailable"
]);

exit;

}


/*
==================================================
FETCH ADMIN RECORD
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
AND role = 'admin'

LIMIT 1

");

$stmt->execute([
":email"=>$email
]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);


if(!$admin){

echo json_encode([
"status"=>false,
"message"=>"Admin account not found"
]);

exit;

}


/*
==================================================
CHECK ACCOUNT STATUS
==================================================
*/

if(($admin["account_status"] ?? "inactive") === "pending"){
    echo json_encode([
        "status"  => false,
        "message" => "Email not verified. Please check your email for the OTP.",
        "needs_verification" => true,
        "email"   => $admin["email"],
    ]);
    exit;
}

if(($admin["account_status"] ?? "inactive") !== "active"){

echo json_encode([
"status"=>false,
"message"=>"Admin account inactive"
]);

exit;

}


/*
==================================================
VERIFY PASSWORD
==================================================
*/

if(!password_verify($password,$admin["password"])){

echo json_encode([
"status"=>false,
"message"=>"Invalid password"
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
CLEAR ANY EXISTING CANDIDATE/RECRUITER SESSIONS
CRITICAL: Prevent session contamination
==================================================
*/

unset($_SESSION["user_id"]);
unset($_SESSION["recruiter_id"]);


/*
==================================================
STORE ADMIN SESSION VARIABLES ONLY
(Middleware compatible structure)
==================================================
*/

$_SESSION["admin_id"] = $admin["id"];

$_SESSION["user_name"] = $admin["name"];

$_SESSION["user_email"] = $admin["email"];

$_SESSION["user_role"] = "admin";


/*
==================================================
SUCCESS RESPONSE
==================================================
*/

echo json_encode([

"status"=>true,

"message"=>"Admin login successful",

"user"=>[

"id"=>$admin["id"],
"name"=>$admin["name"],
"email"=>$admin["email"],
"role"=>"admin"

],

"redirect"=>"../frontend/admin_dashboard.php"

]);

exit;

?>