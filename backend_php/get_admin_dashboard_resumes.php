<?php
ini_set('display_errors', 0);
error_reporting(0);
ob_start();
require_once "session_guard.php";
require_once "db.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
ob_clean();


allowAdminAndRecruiter();


/*
==================================================
DB CONNECTION (PERSISTENT SAFE)
==================================================
*/

try {

$conn = getDatabaseConnection();

} catch(Exception $e){

echo json_encode([
"status"=>false,
"message"=>"Database unavailable"
]);

exit;

}


/*
==================================================
ENTERPRISE OPTIMIZED QUERY
JOIN SAFE + INDEX FRIENDLY
==================================================
*/

try {

$query = "

SELECT

r.id AS resume_id,
COALESCE(u.name,'Unknown') AS name,
COALESCE(u.email,'Unknown') AS email,

r.file_name,
r.file_path,
r.file_type,

COALESCE(r.analysis_status,'pending') AS analysis_status,
COALESCE(r.analysis_progress,0) AS analysis_progress,

r.created_at,

IF(ar.resume_id IS NULL,0,1) AS analysis_exists

FROM resumes r

LEFT JOIN users u
ON u.id = r.user_id

LEFT JOIN analysis_results ar
ON ar.resume_id = r.id

ORDER BY r.id DESC

LIMIT 100

";


$stmt = $conn->prepare($query);
$stmt->execute();

$resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(Exception $e){

echo json_encode([
"status"=>false,
"message"=>"Query failed"
]);

exit;

}


/*
==================================================
FAST EMPTY RESPONSE
==================================================
*/

if(empty($resumes)){

echo json_encode([
"status"=>true,
"count"=>0,
"data"=>[]
]);

exit;

}


/*
==================================================
STATUS NORMALIZATION ENGINE
(UI READY FORMAT)
==================================================
*/

$response = [];

foreach($resumes as $row){

$status = strtolower($row["analysis_status"]);

$progress = (int)$row["analysis_progress"];


/*
RESULT EXISTS → COMPLETED (only if resume status is also completed)
*/

if($row["analysis_exists"] == 1 && $status === "completed"){
    $status = "completed";
}

/*
PIPELINE FINISHED BUT RESULT FLAG MISSED
*/

elseif($progress >= 100 && $status !== "failed"){
    $status = "completed";
}


/*
PROCESSING STATE
*/

elseif($status === "processing"){

$status = "processing ($progress%)";

}


/*
FAILED STATE
*/

elseif($status === "failed"){

$status = "failed";

}


/*
DEFAULT
*/

else{

$status = "pending";

}


$response[] = [

"id" => (int)$row["resume_id"],

"name" => $row["name"],

"email" => $row["email"],

"file_name" => $row["file_name"],

"file_path" => $row["file_path"],

"file_type" => $row["file_type"],

"status" => $status,

"progress" => $progress,

"created_at" => $row["created_at"]

];

}


/*
==================================================
FINAL RESPONSE (STREAM SAFE FORMAT)
==================================================
*/

echo json_encode([

"status"=>true,
"count"=>count($response),
"timestamp"=>time(),
"data"=>$response

], JSON_UNESCAPED_UNICODE);

exit;