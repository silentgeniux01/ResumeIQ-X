<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }

$jobId = (int)($_GET['job_id'] ?? 0);
if (!$jobId) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Job ID required"]); exit; }
if (!verifyJobOwnership($jobId, $session['user_id'])) { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Access denied"]); exit; }

try {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT jp.*, (SELECT COUNT(*) FROM candidate_applications ca WHERE ca.job_posting_id=jp.id) as application_count, (SELECT COUNT(*) FROM shortlist_actions sa WHERE sa.job_posting_id=jp.id AND sa.action_type='accepted') as accepted_count, (SELECT COUNT(*) FROM shortlist_actions sa WHERE sa.job_posting_id=jp.id AND sa.action_type='rejected') as rejected_count FROM job_postings jp WHERE jp.id=:id AND jp.recruiter_id=:recruiter_id LIMIT 1");
    $stmt->execute([':id'=>$jobId,':recruiter_id'=>$session['user_id']]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) { http_response_code(404); echo json_encode(["status"=>false,"message"=>"Job posting not found"]); exit; }
    $job['required_skills'] = json_decode($job['required_skills'] ?? '[]', true);
    echo json_encode(["status"=>true,"data"=>$job]);
} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Get Job Details] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
