<?php

/*
==================================================
ResumeIQ-X Admin Resume Download Controller
Secure File Transfer Engine
Production Grade Version
==================================================
*/


/*
==================================================
START SESSION
==================================================
*/

session_start();


/*
==================================================
LOAD DATABASE ENGINE
==================================================
*/

require_once "db.php";


/*
==================================================
ADMIN OR RECRUITER AUTHORIZATION CHECK
==================================================
*/

if(!isset($_SESSION["user_role"]) || !in_array($_SESSION["user_role"], ["admin", "recruiter"])){

die("Unauthorized access");

}


/*
==================================================
VALIDATE INPUT
==================================================
*/

$resume_id = $_GET["resume_id"] ?? null;

if(!$resume_id){

die("Missing resume ID");

}


/*
==================================================
CONNECT DATABASE
==================================================
*/

$db = getDatabaseConnection();


/*
==================================================
FETCH FILE DATA
==================================================
*/

$query = "

SELECT file_name, file_path

FROM resumes

WHERE id = :id

";

$stmt = $db->prepare($query);

$stmt->execute([

":id"=>$resume_id

]);

$resume = $stmt->fetch();


if(!$resume){

die("Resume not found");

}


$filePath = $resume["file_path"];

$fileName = basename($resume["file_name"]);


/*
==================================================
CHECK FILE EXISTS
==================================================
*/

if(!file_exists($filePath)){

die("File missing on server");

}


/*
==================================================
SET DOWNLOAD HEADERS
==================================================
*/

header("Content-Description: File Transfer");

header("Content-Type: application/octet-stream");

header("Content-Disposition: attachment; filename=\"$fileName\"");

header("Content-Length: " . filesize($filePath));

header("Cache-Control: no-cache, must-revalidate");

header("Expires: 0");


/*
==================================================
SEND FILE
==================================================
*/

readfile($filePath);

exit;

?>