<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }

$candidateId = (int)($_GET['candidate_id'] ?? 0);

try {
    $db = getDatabaseConnection();
    $where = ["rc.recruiter_id = :rid"];
    $params = [':rid' => $session['user_id']];
    if ($candidateId) { $where[] = "rc.candidate_id = :cid"; $params[':cid'] = $candidateId; }

    $stmt = $db->prepare("SELECT rc.*, u.name as candidate_name, u.email as candidate_email, jp.title as job_title FROM recruiter_communications rc JOIN users u ON rc.candidate_id=u.id LEFT JOIN job_postings jp ON rc.job_posting_id=jp.id WHERE ".implode(' AND ',$where)." ORDER BY rc.sent_at DESC LIMIT 100");
    $stmt->execute($params);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status"=>true,"data"=>["history"=>$history,"total"=>count($history)]]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Comm History] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
