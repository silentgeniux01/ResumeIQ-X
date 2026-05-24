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

try {
    $db = getDatabaseConnection();

    // Bar chart: applications per job
    $s = $db->prepare("SELECT jp.title, COUNT(ca.id) as count FROM job_postings jp LEFT JOIN candidate_applications ca ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid GROUP BY jp.id, jp.title ORDER BY count DESC LIMIT 10");
    $s->execute([':rid'=>$rid]);
    $jobApps = $s->fetchAll(PDO::FETCH_ASSOC);
    $barChart = [
        "labels" => array_column($jobApps, 'title'),
        "datasets" => [["label"=>"Applications","data"=>array_map('intval', array_column($jobApps, 'count')),"backgroundColor"=>"rgba(99,102,241,0.7)","borderColor"=>"#6366f1","borderWidth"=>2]]
    ];

    // Pie chart: candidate quality distribution
    $s = $db->prepare("SELECT CASE WHEN ar.overall_score<=50 THEN '0-50' WHEN ar.overall_score<=70 THEN '51-70' WHEN ar.overall_score<=85 THEN '71-85' ELSE '86-100' END as range, COUNT(DISTINCT ar.user_id) as count FROM analysis_results ar JOIN candidate_applications ca ON ca.candidate_id=ar.user_id JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid AND ar.analysis_status='completed' GROUP BY range");
    $s->execute([':rid'=>$rid]);
    $qualityRows = $s->fetchAll(PDO::FETCH_ASSOC);
    $qualityMap = ['0-50'=>0,'51-70'=>0,'71-85'=>0,'86-100'=>0];
    foreach ($qualityRows as $row) { $qualityMap[$row['range']] = (int)$row['count']; }
    $pieChart = [
        "labels" => ["Low (0-50)","Medium (51-70)","Good (71-85)","Excellent (86-100)"],
        "datasets" => [["data"=>array_values($qualityMap),"backgroundColor"=>["#ef4444","#f59e0b","#3b82f6","#10b981"],"borderWidth"=>2]]
    ];

    // Funnel chart: hiring pipeline
    $s = $db->prepare("SELECT COUNT(DISTINCT ca.candidate_id) as total FROM candidate_applications ca JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid"); $s->execute([':rid'=>$rid]);
    $fTotal = (int)$s->fetch(PDO::FETCH_ASSOC)['total'];

    $s = $db->prepare("SELECT COUNT(DISTINCT ar.user_id) as cnt FROM analysis_results ar JOIN candidate_applications ca ON ca.candidate_id=ar.user_id JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE jp.recruiter_id=:rid AND ar.overall_score>=80 AND ar.analysis_status='completed'"); $s->execute([':rid'=>$rid]);
    $fQualified = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    $s = $db->prepare("SELECT COUNT(*) as cnt FROM shortlist_actions WHERE recruiter_id=:rid AND action_type='accepted'"); $s->execute([':rid'=>$rid]);
    $fAccepted = (int)$s->fetch(PDO::FETCH_ASSOC)['cnt'];

    $funnelChart = [
        "labels" => ["Total Applications","Qualified (≥80%)","Accepted","Hired"],
        "datasets" => [["label"=>"Hiring Funnel","data"=>[$fTotal,$fQualified,$fAccepted,0],"backgroundColor"=>["rgba(99,102,241,0.8)","rgba(16,185,129,0.8)","rgba(59,130,246,0.8)","rgba(245,158,11,0.8)"]]]
    ];

    echo json_encode([
        "status" => true,
        "data" => [
            "bar_chart"    => $barChart,
            "pie_chart"    => $pieChart,
            "funnel_chart" => $funnelChart
        ]
    ]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Charts] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
