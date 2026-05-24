<?php
/*
==================================================
ResumeIQ-X Candidate Status Check API
Returns resume analysis status for logged-in candidate
==================================================
*/
ini_set('display_errors', 0);
error_reporting(0);
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
ob_clean();

require_once __DIR__ . "/db.php";

// Check authentication — support both candidate and admin sessions
if (!isset($_SESSION["user_id"]) && !isset($_SESSION["admin_id"])) {
    echo json_encode(["status" => false, "analysis_status" => "unauthorized", "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION["user_id"] ?? $_SESSION["admin_id"] ?? 0;

$conn = getDatabaseConnection();

// Get latest resume for this user
$stmt = $conn->prepare("
    SELECT r.id, r.analysis_status, r.analysis_progress,
           ar.overall_score, ar.candidate_name, ar.detected_sector,
           ar.skills, ar.strengths, ar.weaknesses, ar.candidate_summary,
           ar.match_percentage, ar.experience_years, ar.suitable_job_titles,
           ar.llm_provider_used
    FROM resumes r
    LEFT JOIN analysis_results ar ON ar.resume_id = r.id
    WHERE r.user_id = ?
    ORDER BY r.id DESC
    LIMIT 1
");
$stmt->execute([$userId]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume) {
    echo json_encode([
        "status"          => true,
        "analysis_status" => "no_resume",
        "message"         => "No resume uploaded yet"
    ]);
    exit;
}

$dbStatus = strtolower($resume["analysis_status"] ?? "pending");
$progress = (int)($resume["analysis_progress"] ?? 0);
$resumeId = (int)$resume["id"];

// Build analysis object if completed
$analysis = null;
if ($dbStatus === "completed" && $resume["overall_score"] !== null) {
    $analysis = [
        "overall_score"       => (int)$resume["overall_score"],
        "match_percentage"    => (int)$resume["match_percentage"],
        "candidate_name"      => $resume["candidate_name"] ?? "",
        "detected_sector"     => $resume["detected_sector"] ?? "",
        "experience_years"    => (int)($resume["experience_years"] ?? 0),
        "skills"              => json_decode($resume["skills"] ?? "[]", true) ?: [],
        "strengths"           => json_decode($resume["strengths"] ?? "[]", true) ?: [],
        "weaknesses"          => json_decode($resume["weaknesses"] ?? "[]", true) ?: [],
        "suitable_job_titles" => json_decode($resume["suitable_job_titles"] ?? "[]", true) ?: [],
        "candidate_summary"   => $resume["candidate_summary"] ?? "",
        "llm_provider_used"   => $resume["llm_provider_used"] ?? "",
    ];
}

echo json_encode([
    "status"          => true,
    "analysis_status" => $dbStatus,
    "progress"        => $progress,
    "resume_id"       => $resumeId,
    "analysis"        => $analysis,
]);
exit;
