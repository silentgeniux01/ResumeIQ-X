<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }

try {
    $db = getDatabaseConnection();
    $status = $_GET['status'] ?? 'all';
    $sql = "SELECT jp.*, 
                (SELECT COUNT(*) FROM candidate_applications ca WHERE ca.job_posting_id = jp.id) as application_count
            FROM job_postings jp 
            WHERE jp.recruiter_id = :recruiter_id";
    $params = [':recruiter_id' => $session['user_id']];
    if ($status !== 'all') { $sql .= " AND jp.status = :status"; $params[':status'] = $status; }
    $sql .= " ORDER BY jp.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($jobs as &$job) {
        $job['required_skills'] = json_decode($job['required_skills'] ?? '[]', true);
    }
    echo json_encode(["status"=>true,"data"=>["jobs"=>$jobs,"total"=>count($jobs)]]);
} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Get Jobs] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
