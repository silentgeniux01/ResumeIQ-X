<?php
/*
==================================================
ResumeIQ-X Create Job Posting
Creates a new job posting for recruiter
==================================================
*/

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

// Verify session and recruiter role
$session = verifySession();
if (!$session) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Authentication required"]);
    exit;
}

if ($session['role'] !== 'recruiter') {
    http_response_code(403);
    echo json_encode(["status" => false, "message" => "Recruiter role required"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method not allowed"]);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$requiredSkills = $_POST['required_skills'] ?? '';
$experienceRequired = (int) ($_POST['experience_required'] ?? 0);
$salaryRange = trim($_POST['salary_range'] ?? '');
$location = trim($_POST['location'] ?? '');
$employmentType = trim($_POST['employment_type'] ?? 'full-time');

// Validation
if (!$title) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Job title is required"]);
    exit;
}

if (!$description) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Job description is required"]);
    exit;
}

// Validate required_skills is valid JSON array
if (is_string($requiredSkills)) {
    $skillsArray = json_decode($requiredSkills, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($skillsArray)) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Required skills must be a valid JSON array"]);
        exit;
    }
} else if (is_array($requiredSkills)) {
    $skillsArray = $requiredSkills;
    $requiredSkills = json_encode($skillsArray);
} else {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Required skills must be an array"]);
    exit;
}

if ($experienceRequired < 0) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Experience required must be non-negative"]);
    exit;
}

if (!in_array($employmentType, ['full-time', 'part-time', 'contract'])) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Employment type must be: full-time, part-time, or contract"]);
    exit;
}

try {
    $db = getDatabaseConnection();
    
    $stmt = $db->prepare("
        INSERT INTO job_postings (
            recruiter_id, title, description, required_skills,
            experience_required, salary_range, location, employment_type
        ) VALUES (
            :recruiter_id, :title, :description, :required_skills,
            :experience_required, :salary_range, :location, :employment_type
        )
    ");
    
    $stmt->execute([
        ':recruiter_id' => $session['user_id'],
        ':title' => $title,
        ':description' => $description,
        ':required_skills' => $requiredSkills,
        ':experience_required' => $experienceRequired,
        ':salary_range' => $salaryRange,
        ':location' => $location,
        ':employment_type' => $employmentType
    ]);
    
    $jobId = $db->lastInsertId();
    
    // Log activity
    $stmt = $db->prepare("
        INSERT INTO recruiter_activity (recruiter_id, action_type, action_description, related_entity_type, related_entity_id)
        VALUES (:recruiter_id, 'job_posted', :description, 'job_posting', :job_id)
    ");
    $stmt->execute([
        ':recruiter_id' => $session['user_id'],
        ':description' => "Posted job: {$title}",
        ':job_id' => $jobId
    ]);
    
    echo json_encode([
        "status" => true,
        "message" => "Job posting created successfully",
        "data" => [
            "job_id" => $jobId,
            "title" => $title
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Create Job] Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Failed to create job posting"]);
}
