<?php

/*
==================================================
ResumeIQ-X Logout Controller
Secure Session Termination Engine
Production Grade Version
==================================================
*/


/*
==================================================
START SESSION
Required before destroying it
==================================================
*/

session_start();


/*
==================================================
DETERMINE REDIRECT PAGE BASED ON ROLE
==================================================
*/

$redirectPage = "../frontend/user_login.html"; // Default

if (isset($_SESSION['user_role'])) {
    $role = $_SESSION['user_role'];
    
    if ($role === 'admin') {
        $redirectPage = "../frontend/admin_login.html";
    } elseif ($role === 'recruiter') {
        $redirectPage = "../frontend/recruiter_login.html";
    }
}


/*
==================================================
UNSET ALL SESSION VARIABLES
==================================================
*/

$_SESSION = [];


/*
==================================================
DESTROY SESSION COMPLETELY
==================================================
*/

session_destroy();


/*
==================================================
OPTIONAL: CLEAR SESSION COOKIE
Extra security layer
==================================================
*/

if (ini_get("session.use_cookies")) {

$params = session_get_cookie_params();

setcookie(
session_name(),
'',
time() - 42000,
$params["path"],
$params["domain"],
$params["secure"],
$params["httponly"]
);

}


/*
==================================================
REDIRECT TO APPROPRIATE LOGIN PAGE
==================================================
*/

header("Location: " . $redirectPage);

exit;

?>