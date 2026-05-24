<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }

$actionType = trim($_GET['action_type'] ?? '');
$jobId      = (int)($_GET['job_id'] ?? 0);

try {
    $db = getDatabaseConnection();
    $where = ["sa.recruiter_id = :rid"];
    $params = [':rid' => $session['user_id']];
    if ($actionType && in_array($actionType, ['accepted','rejected'])) { $where[] = "sa.action_type = :action"; $params[':action'] = $actionType; }
    if ($jobId) { $where[] = "sa.job_posting_id = :jid"; $params[':jid'] = $jobId; }

    $stmt = $db->prepare("SELECT sa.*, u.name, u.email, ar.overall_score, ar.match_percentage, ar.detected_sector, ar.experience_years, jp.title as job_title FROM shortlist_actions sa JOIN users u ON sa.candidate_id=u.id LEFT JOIN analysis_results ar ON ar.user_id=u.id AND ar.analysis_status='completed' LEFT JOIN job_postings jp ON sa.job_posting_id=jp.id WHERE ".implode(' AND ',$where)." ORDER BY sa.action_timestamp DESC");
    $stmt->execute($params);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status"=>true,"data"=>["candidates"=>$candidates,"total"=>count($candidates)]]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Get Shortlisted] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
