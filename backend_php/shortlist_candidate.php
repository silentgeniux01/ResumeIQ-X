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

$candidateId = (int)($_POST['candidate_id'] ?? 0);
$jobId       = (int)($_POST['job_id'] ?? 0);
$actionType  = trim($_POST['action_type'] ?? '');
$notes       = trim($_POST['notes'] ?? '');

if (!$candidateId || !$jobId || !$actionType) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"candidate_id, job_id, and action_type required"]); exit; }
if (!in_array($actionType, ['accepted','rejected'])) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"action_type must be accepted or rejected"]); exit; }
if (!verifyJobOwnership($jobId, $session['user_id'])) { http_response_code(403); echo json_encode(["status"=>false,"message"=>"You do not own this job posting"]); exit; }

try {
    $db = getDatabaseConnection();

    // Verify candidate exists
    $stmt = $db->prepare("SELECT id FROM users WHERE id=:id LIMIT 1");
    $stmt->execute([':id'=>$candidateId]);
    if (!$stmt->fetch()) { http_response_code(404); echo json_encode(["status"=>false,"message"=>"Candidate not found"]); exit; }

    // Upsert shortlist action (last operation wins)
    $stmt = $db->prepare("INSERT INTO shortlist_actions (recruiter_id, candidate_id, job_posting_id, action_type, notes, action_timestamp) VALUES (:rid,:cid,:jid,:action,:notes,NOW()) ON DUPLICATE KEY UPDATE action_type=:action2, notes=:notes2, action_timestamp=NOW()");
    $stmt->execute([':rid'=>$session['user_id'],':cid'=>$candidateId,':jid'=>$jobId,':action'=>$actionType,':notes'=>$notes,':action2'=>$actionType,':notes2'=>$notes]);

    // Update application status
    $stmt = $db->prepare("UPDATE candidate_applications SET status=:status WHERE candidate_id=:cid AND job_posting_id=:jid");
    $stmt->execute([':status'=>$actionType==='accepted'?'shortlisted':'rejected',':cid'=>$candidateId,':jid'=>$jobId]);

    // Log activity
    $stmt = $db->prepare("INSERT INTO recruiter_activity (recruiter_id, action_type, action_description, related_entity_type, related_entity_id) VALUES (:rid,:action,:desc,'candidate',:cid)");
    $stmt->execute([':rid'=>$session['user_id'],':action'=>'candidate_'.$actionType,':desc'=>"Candidate {$actionType} for job #{$jobId}",':cid'=>$candidateId]);

    echo json_encode(["status"=>true,"message"=>"Candidate ".($actionType==='accepted'?"accepted":"rejected")." successfully","data"=>["action_type"=>$actionType]]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Shortlist] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
