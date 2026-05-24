<?php
/*
==================================================
ResumeIQ-X Analysis Preview API v2
Returns ALL analysis data — legacy + LLM columns
==================================================
*/
// MUST be first — suppress PHP errors from corrupting JSON
ini_set('display_errors', 0);
error_reporting(0);
ob_start(); // Buffer any stray output

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

// Clean any buffered output before sending JSON
ob_clean();

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/session_guard.php";

// Accept any logged-in user (candidate, admin, recruiter)
$isLoggedIn = isset($_SESSION['user_id'])
           || isset($_SESSION['admin_id'])
           || isset($_SESSION['recruiter_id'])
           || (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin','recruiter','candidate','user']));

if (!$isLoggedIn) {
    echo json_encode(["status" => false, "message" => "Login required"]);
    exit;
}

$resumeId = intval($_GET["resume_id"] ?? 0);
if (!$resumeId) {
    echo json_encode(["status" => false, "message" => "Resume ID missing"]);
    exit;
}

$conn = getDatabaseConnection();

// Get analysis results
$stmt = $conn->prepare("SELECT * FROM analysis_results WHERE resume_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$resumeId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    // Check if resume exists and what status it has
    $rStmt = $conn->prepare("SELECT analysis_status, analysis_progress FROM resumes WHERE id = ? LIMIT 1");
    $rStmt->execute([$resumeId]);
    $resume = $rStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status"  => false,
        "message" => "Analysis not ready",
        "resume_status" => $resume['analysis_status'] ?? 'unknown',
        "progress" => (int)($resume['analysis_progress'] ?? 0)
    ]);
    exit;
}

function safeJson($value, $fallback = []) {
    if (!$value) return $fallback;
    if (is_array($value)) return $value;
    $decoded = json_decode($value, true);
    return $decoded ?: $fallback;
}

function safeFloat($v, $scale = 1) {
    $f = (float)$v;
    // If stored as 0-1 scale, convert to 0-100
    if ($scale === 100 && $f <= 1.0 && $f > 0) return round($f * 100, 1);
    return round($f, 1);
}

// Build unified response — merge legacy + new LLM columns
$response = [
    // ── Core scores (legacy) ──
    "resume_strength_score"  => safeFloat($data["resume_strength_score"] ?? $data["overall_score"] ?? 0, 100),
    "confidence_score"       => safeFloat($data["confidence_score"] ?? 0, 100),
    "career_readiness_score" => safeFloat($data["career_readiness_score"] ?? 0, 100),
    "talent_category"        => $data["talent_category"] ?? "General",

    // ── New LLM scores ──
    "overall_score"          => (int)($data["overall_score"] ?? 0),
    "match_percentage"       => (int)($data["match_percentage"] ?? 0),

    // ── Candidate info ──
    "candidate_name"         => $data["candidate_name"] ?? "",
    "candidate_email"        => $data["candidate_email"] ?? "",
    "candidate_phone"        => $data["candidate_phone"] ?? "",
    "experience_years"       => (int)($data["experience_years"] ?? 0),
    "detected_sector"        => $data["detected_sector"] ?? "general",
    "llm_provider_used"      => $data["llm_provider_used"] ?? "",

    // ── Summary (check both column names) ──
    "summary"                => $data["candidate_summary"] ?? $data["summary"] ?? "",

    // ── LLM arrays ──
    "skills"                 => safeJson($data["skills"] ?? null),
    "education"              => safeJson($data["education"] ?? null),
    "strengths"              => safeJson($data["strengths"] ?? null),
    "weaknesses"             => safeJson($data["weaknesses"] ?? null),
    "recommendations"        => safeJson($data["recommendations"] ?? null),
    "suitable_job_titles"    => safeJson($data["suitable_job_titles"] ?? null),

    // ── Legacy intelligence vectors ──
    "semantic_role_scores"   => safeJson($data["semantic_role_scores"] ?? null),
    "domain_distribution"    => safeJson($data["domain_distribution"] ?? null),
    "skill_maturity"         => safeJson($data["skill_maturity"] ?? null),
    "missing_dependencies"   => safeJson($data["missing_dependencies"] ?? null, []),
    "learning_recommendations" => safeJson($data["learning_recommendations"] ?? null, []),
    "similar_candidates"     => safeJson($data["similar_candidates"] ?? null),
    "trajectory_prediction"  => safeJson($data["trajectory_prediction"] ?? null),
    "reasoning_signals"      => safeJson($data["reasoning_signals"] ?? null),
    "capability_vector"      => safeJson($data["capability_vector"] ?? null),
    "candidate_signal_profile" => safeJson($data["candidate_signal_profile"] ?? null),
    "latent_skill_report"    => safeJson($data["latent_skill_report"] ?? null),
    "career_direction_vector" => safeJson($data["career_direction_vector"] ?? null),
];

echo json_encode(["status" => true, "data" => $response]);
exit;
