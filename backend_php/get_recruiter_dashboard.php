<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }

$rid = $session['user_id'];

// 5-minute cache using session
$cacheKey = "dashboard_cache_{$rid}";
$cacheExpiry = 300; // 5 minutes
if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['ts']) < $cacheExpiry) {
    echo json_encode(["status" => true, "data" => $_SESSION[$cacheKey]['data'], "cached" => true]);
    exit;
}

try {
    $db = getDatabaseConnection();

    // Total job postings
    $s = $db->prepare("SELECT COUNT(*) as cnt FROM job_postings WHERE recruiter_id=:rid"); $s->execute([':rid'=>$rid]);
    $totalJobs = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Total applications
    $s = $db->prepare("SELECT COUNT(*) as cnt FROM candidate_applications ca JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid"); $s->execute([':rid'=>$rid]);
    $totalApplications = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Qualified candidates (score >= 80)
    $s = $db->prepare("SELECT COUNT(DISTINCT ar.user_id) as cnt FROM analysis_results ar JOIN resumes r ON ar.resume_id=r.id JOIN candidate_applications ca ON ca.candidate_id=ar.user_id JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid AND ar.overall_score>=80 AND ar.analysis_status='completed'"); $s->execute([':rid'=>$rid]);
    $qualifiedCount = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Pending reviews (applied but not shortlisted)
    $s = $db->prepare("SELECT COUNT(DISTINCT ca.candidate_id) as cnt FROM candidate_applications ca JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid AND ca.status='pending'"); $s->execute([':rid'=>$rid]);
    $pendingCount = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Accepted
    $s = $db->prepare("SELECT COUNT(*) as cnt FROM shortlist_actions WHERE recruiter_id=:rid AND action_type='accepted'"); $s->execute([':rid'=>$rid]);
    $acceptedCount = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Rejected
    $s = $db->prepare("SELECT COUNT(*) as cnt FROM shortlist_actions WHERE recruiter_id=:rid AND action_type='rejected'"); $s->execute([':rid'=>$rid]);
    $rejectedCount = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Average scores
    $s = $db->prepare("SELECT AVG(ar.overall_score) as avg_score, AVG(ar.match_percentage) as avg_match FROM analysis_results ar JOIN candidate_applications ca ON ca.candidate_id=ar.user_id JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid AND ar.analysis_status='completed'"); $s->execute([':rid'=>$rid]);
    $avgs = $s->fetch(PDO::FETCH_ASSOC);
    $avgScore = $totalApplications > 0 ? round((float)($avgs['avg_score'] ?? 0), 1) : 0;
    $avgMatch = $totalApplications > 0 ? round((float)($avgs['avg_match'] ?? 0), 1) : 0;

    // Recent activity (last 10)
    $s = $db->prepare("SELECT action_type, action_description, created_at FROM recruiter_activity WHERE recruiter_id=:rid ORDER BY created_at DESC LIMIT 10"); $s->execute([':rid'=>$rid]);
    $recentActivity = $s->fetchAll(PDO::FETCH_ASSOC);

    $responseData = [
        "metrics" => [
            "total_jobs"         => $totalJobs,
            "total_applications" => $totalApplications,
            "qualified_count"    => $qualifiedCount,
            "pending_count"      => $pendingCount,
            "accepted_count"     => $acceptedCount,
            "rejected_count"     => $rejectedCount,
            "avg_score"          => $avgScore,
            "avg_match"          => $avgMatch
        ],
        "recent_activity" => $recentActivity,
        "recruiter" => [
            "name"         => $session['name'],
            "company_name" => $session['company_name']
        ]
    ];

    // Cache for 5 minutes
    $_SESSION[$cacheKey] = ['ts' => time(), 'data' => $responseData];

    echo json_encode([
        "status" => true,
        "data"   => $responseData
    ]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Dashboard] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
