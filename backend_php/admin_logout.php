<?php

/*
==================================================
ResumeIQ-X Admin Logout Controller
Secure Session Destroy Engine
==================================================
*/

session_start();


/*
==================================================
DESTROY SESSION
==================================================
*/

$_SESSION = [];

session_unset();

session_destroy();


/*
==================================================
REDIRECT ADMIN LOGIN PAGE
==================================================
*/

header(

"Location: ../frontend/admin_login.html"

);

exit;

?>