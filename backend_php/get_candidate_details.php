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
if (!$candidateId) { http_response_code(400); echo json_encode(["status"=>false,"message"=>"Candidate ID required"]); exit; }

try {
    $db = getDatabaseConnection();

    // Verify recruiter has access to this candidate
    $accessStmt = $db->prepare("SELECT COUNT(*) as cnt FROM candidate_applications ca JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE ca.candidate_id=:cid AND jp.recruiter_id=:rid");
    $accessStmt->execute([':cid'=>$candidateId,':rid'=>$session['user_id']]);
    if ((int)$accessStmt->fetch(PDO::FETCH_ASSOC)['cnt'] === 0) {
        http_response_code(403); echo json_encode(["status"=>false,"message"=>"Access denied to this candidate"]); exit;
    }

    // Get full analysis
    $stmt = $db->prepare("
        SELECT ar.*, u.name as user_name, u.email as user_email, u.mobile,
               r.id as resume_id, r.file_path,
               (SELECT sa.action_type FROM shortlist_actions sa WHERE sa.candidate_id=u.id AND sa.recruiter_id=:rid ORDER BY sa.action_timestamp DESC LIMIT 1) as shortlist_status
        FROM analysis_results ar
        JOIN resumes r ON ar.resume_id=r.id
        JOIN users u ON ar.user_id=u.id
        WHERE ar.user_id=:cid AND ar.analysis_status='completed'
        ORDER BY ar.analysis_timestamp DESC LIMIT 1
    ");
    $stmt->execute([':cid'=>$candidateId,':rid'=>$session['user_id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) { http_response_code(404); echo json_encode(["status"=>false,"message"=>"Analysis report not found"]); exit; }

    // Decode JSON fields
    foreach (['education','skills','strengths','weaknesses','recommendations','suitable_job_titles'] as $field) {
        $report[$field] = json_decode($report[$field] ?? '[]', true);
    }
    $report['overall_score']     = (int)$report['overall_score'];
    $report['match_percentage']  = (int)$report['match_percentage'];
    $report['experience_years']  = (int)$report['experience_years'];

    // Get jobs this candidate applied to (for this recruiter)
    $jobsStmt = $db->prepare("SELECT jp.id, jp.title FROM candidate_applications ca JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE ca.candidate_id=:cid AND jp.recruiter_id=:rid");
    $jobsStmt->execute([':cid'=>$candidateId,':rid'=>$session['user_id']]);
    $report['applied_jobs'] = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status"=>true,"data"=>$report]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Candidate Details] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
