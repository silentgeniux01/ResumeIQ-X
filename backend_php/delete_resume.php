<?php

/*
==================================================
ResumeIQ-X Admin Resume Delete Controller
Secure Admin-Only Resume Removal Engine
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
ADMIN AUTHORIZATION CHECK
==================================================
*/

if(!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin"){

echo json_encode([

"status"=>false,
"message"=>"Unauthorized access"

]);

exit;

}


/*
==================================================
VALIDATE REQUEST METHOD
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
FETCH INPUT
==================================================
*/

$resume_id = $_POST["resume_id"] ?? null;

if(!$resume_id){

echo json_encode([

"status"=>false,
"message"=>"Missing resume ID"

]);

exit;

}


/*
==================================================
CONNECT DATABASE
==================================================
*/

$db = getDatabaseConnection();


/*
==================================================
FETCH FILE PATH
==================================================
*/

$query = "

SELECT file_path

FROM resumes

WHERE id = :id

";

$stmt = $db->prepare($query);

$stmt->execute([

":id"=>$resume_id

]);

$resume = $stmt->fetch();


if(!$resume){

echo json_encode([

"status"=>false,
"message"=>"Resume not found"

]);

exit;

}


$file = $resume["file_path"];


/*
==================================================
DELETE FILE FROM STORAGE
==================================================
*/

if(file_exists($file)){

unlink($file);

}


/*
==================================================
DELETE RELATED ANALYSIS RESULTS
==================================================
*/

$query = "

DELETE FROM analysis_results

WHERE resume_id = :id

";

$stmt = $db->prepare($query);

$stmt->execute([

":id"=>$resume_id

]);


/*
==================================================
DELETE RESUME RECORD
==================================================
*/

$query = "

DELETE FROM resumes

WHERE id = :id

";

$stmt = $db->prepare($query);

$stmt->execute([

":id"=>$resume_id

]);


/*
==================================================
SUCCESS RESPONSE
==================================================
*/

echo json_encode([

"status"=>true,
"message"=>"Resume deleted successfully"

]);

exit;

?>