<?php
/*
==================================================
ResumeIQ-X Get Candidates with Filtering
Returns paginated, filtered candidate list for recruiter
==================================================
*/
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }

// Filters
$jobId       = (int)($_GET['job_id'] ?? 0);
$minScore    = isset($_GET['min_score']) ? (int)$_GET['min_score'] : null;
$skill       = trim($_GET['skill'] ?? '');
$minExp      = isset($_GET['min_experience']) ? (int)$_GET['min_experience'] : null;
$sector      = trim($_GET['sector'] ?? '');
$page        = max(1, (int)($_GET['page'] ?? 1));
$pageSize    = min(100, max(1, (int)($_GET['page_size'] ?? 25)));
$offset      = ($page - 1) * $pageSize;

try {
    $db = getDatabaseConnection();

    // Base query — only candidates for this recruiter's jobs
    $where = ["jp.recruiter_id = :recruiter_id", "ar.analysis_status = 'completed'"];
    $params = [':recruiter_id' => $session['user_id']];

    if ($jobId) {
        if (!verifyJobOwnership($jobId, $session['user_id'])) {
            http_response_code(403); echo json_encode(["status"=>false,"message"=>"Access denied to this job"]); exit;
        }
        $where[] = "ca.job_posting_id = :job_id";
        $params[':job_id'] = $jobId;
    }
    if ($minScore !== null) { $where[] = "ar.overall_score >= :min_score"; $params[':min_score'] = $minScore; }
    if ($skill)             { $where[] = "JSON_CONTAINS(ar.skills, :skill)"; $params[':skill'] = json_encode($skill); }
    if ($minExp !== null)   { $where[] = "ar.experience_years >= :min_exp"; $params[':min_exp'] = $minExp; }
    if ($sector)            { $where[] = "ar.detected_sector = :sector"; $params[':sector'] = $sector; }

    $whereSQL = implode(' AND ', $where);

    $baseSQL = "FROM analysis_results ar
                JOIN resumes r ON ar.resume_id = r.id
                JOIN users u ON ar.user_id = u.id
                JOIN candidate_applications ca ON ca.candidate_id = u.id
                JOIN job_postings jp ON ca.job_posting_id = jp.id
                WHERE {$whereSQL}";

    // Count total
    $countStmt = $db->prepare("SELECT COUNT(DISTINCT u.id) as total {$baseSQL}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Fetch page
    $dataSQL = "SELECT DISTINCT
                    u.id as candidate_id, u.name, u.email,
                    ar.candidate_phone, ar.overall_score, ar.match_percentage,
                    ar.detected_sector, ar.experience_years, ar.skills,
                    ar.candidate_summary, ar.analysis_timestamp, r.id as resume_id,
                    ca.job_posting_id,
                    (SELECT sa.action_type FROM shortlist_actions sa
                     WHERE sa.candidate_id = u.id AND sa.recruiter_id = :recruiter_id2
                     ORDER BY sa.action_timestamp DESC LIMIT 1) as shortlist_status
                {$baseSQL}
                ORDER BY ar.overall_score DESC
                LIMIT :limit OFFSET :offset";

    $params[':recruiter_id2'] = $session['user_id'];
    $params[':limit']  = $pageSize;
    $params[':offset'] = $offset;

    $stmt = $db->prepare($dataSQL);
    // Bind int params explicitly for LIMIT/OFFSET
    foreach ($params as $key => $val) {
        if ($key === ':limit' || $key === ':offset' || $key === ':min_score' || $key === ':min_exp') {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $val);
        }
    }
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($candidates as &$c) {
        $c['skills'] = json_decode($c['skills'] ?? '[]', true);
        $c['overall_score'] = (int)$c['overall_score'];
        $c['match_percentage'] = (int)$c['match_percentage'];
        $c['experience_years'] = (int)$c['experience_years'];
    }

    echo json_encode([
        "status" => true,
        "data" => [
            "candidates" => $candidates,
            "pagination" => [
                "total" => $total,
                "page" => $page,
                "page_size" => $pageSize,
                "total_pages" => (int)ceil($total / $pageSize)
            ]
        ]
    ]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Get Candidates] " . $e->getMessage());
    http_response_code(500); echo json_encode(["status"=>false,"message"=>"Database error"]);
}
