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

try {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("DELETE FROM job_postings WHERE id=:id AND recruiter_id=:recruiter_id");
    $stmt->execute([':id'=>$jobId,':recruiter_id'=>$session['user_id']]);
    if ($stmt->rowCount() === 0) { http_response_code(404); echo json_encode(["status"=>false,"message"=>"Job posting not found"]); exit; }
    echo json_encode(["status"=>true,"message"=>"Job posting deleted successfully"]);
} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Delete Job] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Failed to delete job posting"]);
}
