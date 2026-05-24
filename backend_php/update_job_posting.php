<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }
if ($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); echo json_encode(["status"=>false,"message"=>"Method not allowed"]); exit; }

$jobId = (int)($_POST['job_id'] ?? 0);
if (!$jobId) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Job ID required"]); exit; }

if (!verifyJobOwnership($jobId, $session['user_id'])) {
    http_response_code(403); echo json_encode(["status"=>false,"message"=>"You do not own this job posting"]); exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$requiredSkills = $_POST['required_skills'] ?? '';
$experienceRequired = (int)($_POST['experience_required'] ?? 0);
$salaryRange = trim($_POST['salary_range'] ?? '');
$location = trim($_POST['location'] ?? '');
$employmentType = trim($_POST['employment_type'] ?? 'full-time');
$status = trim($_POST['status'] ?? 'active');

if (!$title) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Job title required"]); exit; }
if (!$description) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Job description required"]); exit; }
if (!in_array($employmentType, ['full-time','part-time','contract'])) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Invalid employment type"]); exit; }
if (!in_array($status, ['active','closed'])) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Invalid status"]); exit; }

if (is_string($requiredSkills)) {
    $skillsDecoded = json_decode($requiredSkills, true);
    if (json_last_error() !== JSON_ERROR_NONE) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Required skills must be valid JSON array"]); exit; }
} else {
    $requiredSkills = json_encode($requiredSkills);
}

try {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("UPDATE job_postings SET title=:title, description=:description, required_skills=:skills, experience_required=:exp, salary_range=:salary, location=:location, employment_type=:emp_type, status=:status WHERE id=:id AND recruiter_id=:recruiter_id");
    $stmt->execute([':title'=>$title,':description'=>$description,':skills'=>$requiredSkills,':exp'=>$experienceRequired,':salary'=>$salaryRange,':location'=>$location,':emp_type'=>$employmentType,':status'=>$status,':id'=>$jobId,':recruiter_id'=>$session['user_id']]);
    echo json_encode(["status"=>true,"message"=>"Job posting updated successfully"]);
} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Update Job] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Failed to update job posting"]);
}
