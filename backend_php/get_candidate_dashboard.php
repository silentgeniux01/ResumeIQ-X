<?php

/*
==================================================
ResumeIQ-X Candidate Dashboard Data Loader
Fetches Stored AI Pipeline Results
Admin-Controlled Visibility Engine
Production Grade Dashboard API
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

require_once "session_guard.php";
require_once "db.php";


/*
==================================================
AUTHENTICATION CHECK
==================================================
*/

requireCandidate();


$userId = $_SESSION["user_id"];


/*
==================================================
FETCH LATEST USER RESUME
==================================================
*/

$db = getDatabaseConnection();

$query = "

SELECT id,analysis_status
FROM resumes
WHERE user_id = :user_id
ORDER BY upload_timestamp DESC
LIMIT 1

";

$stmt = $db->prepare($query);

$stmt->execute([

":user_id"=>$userId

]);

$resume = $stmt->fetch();


if(!$resume){

echo json_encode([

"status"=>false,
"message"=>"Resume not uploaded yet"

]);

exit;

}


$resumeId = $resume["id"];

$status = $resume["analysis_status"];


/*
==================================================
CHECK ANALYSIS STATUS
==================================================
*/

if($status === "pending"){

echo json_encode([

"status"=>false,
"analysis_status"=>"pending",
"message"=>"Wait for admin response"

]);

exit;

}


if($status === "processing"){

echo json_encode([

"status"=>false,
"analysis_status"=>"processing",
"message"=>"Resume is currently being analyzed"

]);

exit;

}


/*
==================================================
FETCH STORED ANALYSIS RESULTS
==================================================
*/

$query = "

SELECT *
FROM analysis_results
WHERE resume_id = :resume_id
LIMIT 1

";

$stmt = $db->prepare($query);

$stmt->execute([

":resume_id"=>$resumeId

]);

$result = $stmt->fetch();


if(!$result){

echo json_encode([

"status"=>false,
"message"=>"Analysis data not found"

]);

exit;

}


/*
==================================================
BUILD DASHBOARD RESPONSE OBJECT
==================================================
*/

$response = [

"resume_score"=>$result["resume_strength_score"],

"confidence_score"=>$result["confidence_score"],

"career_readiness_score"=>$result["career_readiness_score"],

"talent_category"=>$result["talent_category"],

"semantic_role_scores"=>json_decode(
$result["semantic_role_scores"],
true
),

"domain_distribution"=>json_decode(
$result["domain_distribution"],
true
),

"skill_maturity"=>json_decode(
$result["skill_maturity"],
true
),

"missing_dependencies"=>json_decode(
$result["missing_dependencies"],
true
),

"learning_recommendations"=>json_decode(
$result["learning_recommendations"],
true
),

"similar_candidates"=>json_decode(
$result["similar_candidates"],
true
),

"trajectory_prediction"=>json_decode(
$result["trajectory_prediction"],
true
),

"reasoning_signals"=>json_decode(
$result["reasoning_signals"],
true
),

"capability_vector"=>json_decode(
$result["capability_vector"],
true
),

"candidate_signal_profile"=>json_decode(
$result["candidate_signal_profile"],
true
),

"latent_skill_report"=>json_decode(
$result["latent_skill_report"],
true
),

"career_direction_vector"=>json_decode(
$result["career_direction_vector"],
true
)

];


/*
==================================================
SUCCESS RESPONSE
==================================================
*/

echo json_encode([

"status"=>true,

"analysis_status"=>"completed",

"analysis"=>$response

]);

exit;

?>