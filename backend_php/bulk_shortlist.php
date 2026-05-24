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

$candidateIds = $_POST['candidate_ids'] ?? [];
$jobId        = (int)($_POST['job_id'] ?? 0);
$actionType   = trim($_POST['action_type'] ?? '');

if (is_string($candidateIds)) $candidateIds = json_decode($candidateIds, true);
if (empty($candidateIds) || !$jobId || !$actionType) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"candidate_ids, job_id, and action_type required"]); exit; }
if (!in_array($actionType, ['accepted','rejected'])) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"action_type must be accepted or rejected"]); exit; }
if (!verifyJobOwnership($jobId, $session['user_id'])) { http_response_code(403); echo json_encode(["status"=>false,"message"=>"You do not own this job posting"]); exit; }

$candidateIds = array_map('intval', $candidateIds);

try {
    $db = getDatabaseConnection();
    $db->beginTransaction();

    $count = 0;
    $appStatus = $actionType === 'accepted' ? 'shortlisted' : 'rejected';

    foreach ($candidateIds as $cid) {
        if (!$cid) continue;
        $stmt = $db->prepare("INSERT INTO shortlist_actions (recruiter_id, candidate_id, job_posting_id, action_type, action_timestamp) VALUES (:rid,:cid,:jid,:action,NOW()) ON DUPLICATE KEY UPDATE action_type=:action2, action_timestamp=NOW()");
        $stmt->execute([':rid'=>$session['user_id'],':cid'=>$cid,':jid'=>$jobId,':action'=>$actionType,':action2'=>$actionType]);
        $stmt = $db->prepare("UPDATE candidate_applications SET status=:status WHERE candidate_id=:cid AND job_posting_id=:jid");
        $stmt->execute([':status'=>$appStatus,':cid'=>$cid,':jid'=>$jobId]);
        $count++;
    }

    // Log bulk activity
    $stmt = $db->prepare("INSERT INTO recruiter_activity (recruiter_id, action_type, action_description, related_entity_type, related_entity_id) VALUES (:rid,:action,:desc,'job_posting',:jid)");
    $stmt->execute([':rid'=>$session['user_id'],':action'=>'bulk_'.$actionType,':desc'=>"Bulk {$actionType} {$count} candidates for job #{$jobId}",':jid'=>$jobId]);

    $db->commit();
    echo json_encode(["status"=>true,"message"=>"{$count} candidates {$actionType} successfully","data"=>["count"=>$count,"action_type"=>$actionType]]);

} catch (PDOException $e) {
    $db->rollBack();
    error_log("[ResumeIQ-X][Bulk Shortlist] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
