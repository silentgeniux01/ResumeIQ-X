<?php
/*
==================================================
ResumeIQ-X Generate Candidate PDF Report
Generates a printable HTML report (browser print to PDF)
==================================================
*/
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo "Authentication required"; exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo "Recruiter role required"; exit; }

$candidateId = (int)($_GET['candidate_id'] ?? 0);
if (!$candidateId) { http_response_code(400); echo "Candidate ID required"; exit; }

try {
    $db = getDatabaseConnection();

    // Verify access
    $accessStmt = $db->prepare("SELECT COUNT(*) as cnt FROM candidate_applications ca JOIN job_postings jp ON ca.job_posting_id=jp.id WHERE ca.candidate_id=:cid AND jp.recruiter_id=:rid");
    $accessStmt->execute([':cid' => $candidateId, ':rid' => $session['user_id']]);
    if ((int)$accessStmt->fetch(PDO::FETCH_ASSOC)['cnt'] === 0) {
        http_response_code(403); echo "Access denied"; exit;
    }

    // Get report data
    $stmt = $db->prepare("SELECT ar.*, u.name as user_name, u.email as user_email FROM analysis_results ar JOIN users u ON ar.user_id=u.id WHERE ar.user_id=:cid AND ar.analysis_status='completed' ORDER BY ar.analysis_timestamp DESC LIMIT 1");
    $stmt->execute([':cid' => $candidateId]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) { http_response_code(404); echo "Report not found"; exit; }

    foreach (['education','skills','strengths','weaknesses','recommendations','suitable_job_titles'] as $f) {
        $r[$f] = json_decode($r[$f] ?? '[]', true) ?: [];
    }

    $appName  = env('APP_NAME', 'ResumeIQ-X');
    $name     = htmlspecialchars($r['candidate_name'] ?: $r['user_name']);
    $email    = htmlspecialchars($r['candidate_email'] ?: $r['user_email']);
    $phone    = htmlspecialchars($r['candidate_phone'] ?? 'N/A');
    $sector   = htmlspecialchars($r['detected_sector'] ?? 'General');
    $summary  = htmlspecialchars($r['candidate_summary'] ?? '');
    $provider = htmlspecialchars($r['llm_provider_used'] ?? '');
    $date     = date('d M Y');

    $skillTags    = implode('', array_map(fn($s) => '<span class="tag">' . htmlspecialchars($s) . '</span>', $r['skills']));
    $strengthList = implode('', array_map(fn($s) => '<li>' . htmlspecialchars($s) . '</li>', $r['strengths']));
    $weaknessList = implode('', array_map(fn($s) => '<li>' . htmlspecialchars($s) . '</li>', $r['weaknesses']));
    $recList      = implode('', array_map(fn($s) => '<li>' . htmlspecialchars($s) . '</li>', $r['recommendations']));
    $eduList      = implode('', array_map(fn($s) => '<li>' . htmlspecialchars(is_array($s) ? implode(' — ', $s) : $s) . '</li>', $r['education']));
    $jobTitles    = htmlspecialchars(implode(', ', $r['suitable_job_titles']));

    $overallScore   = (int)($r['overall_score'] ?? 0);
    $matchPct       = (int)($r['match_percentage'] ?? 0);
    $expYears       = (int)($r['experience_years'] ?? 0);

    header('Content-Type: text/html; charset=UTF-8');

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    $html .= '<title>' . $appName . ' — Candidate Report: ' . $name . '</title>';
    $html .= '<style>
      body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;color:#1e293b;}
      .header{background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;padding:24px;border-radius:12px;margin-bottom:24px;}
      .header h1{margin:0;font-size:22px;} .header p{margin:4px 0;opacity:.85;font-size:13px;}
      .score-row{display:flex;gap:16px;margin-bottom:20px;}
      .score-card{flex:1;background:#f1f5f9;border-radius:10px;padding:16px;text-align:center;}
      .score-card .num{font-size:36px;font-weight:700;color:#6366f1;} .score-card .lbl{font-size:12px;color:#64748b;}
      .section{margin-bottom:20px;} .section h3{color:#4f46e5;border-bottom:2px solid #e2e8f0;padding-bottom:6px;}
      .tag{display:inline-block;background:#e0e7ff;color:#4338ca;padding:3px 10px;border-radius:20px;font-size:12px;margin:3px;}
      ul{padding-left:20px;} li{margin-bottom:4px;font-size:14px;}
      .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
      .info-item{background:#f8fafc;padding:10px;border-radius:8px;font-size:13px;}
      .info-item strong{display:block;color:#64748b;font-size:11px;text-transform:uppercase;}
      .footer{text-align:center;color:#94a3b8;font-size:11px;margin-top:30px;padding-top:16px;border-top:1px solid #e2e8f0;}
      @media print{.no-print{display:none;}}
    </style></head><body>';

    $html .= '<div class="no-print" style="text-align:right;margin-bottom:12px;">';
    $html .= '<button onclick="window.print()" style="background:#6366f1;color:#fff;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-size:14px;">🖨️ Print / Save PDF</button>';
    $html .= '</div>';

    $html .= '<div class="header">';
    $html .= '<h1>⚡ ' . $appName . ' — Candidate Analysis Report</h1>';
    $html .= '<p>Generated: ' . $date . ' | Provider: ' . $provider . '</p>';
    $html .= '</div>';

    $html .= '<div class="score-row">';
    $html .= '<div class="score-card"><div class="num">' . $overallScore . '%</div><div class="lbl">Overall Score</div></div>';
    $html .= '<div class="score-card"><div class="num">' . $matchPct . '%</div><div class="lbl">Job Match</div></div>';
    $html .= '<div class="score-card"><div class="num">' . $expYears . '</div><div class="lbl">Years Experience</div></div>';
    $html .= '</div>';

    $html .= '<div class="section"><h3>👤 Candidate Information</h3><div class="info-grid">';
    $html .= '<div class="info-item"><strong>Name</strong>' . $name . '</div>';
    $html .= '<div class="info-item"><strong>Email</strong>' . $email . '</div>';
    $html .= '<div class="info-item"><strong>Phone</strong>' . $phone . '</div>';
    $html .= '<div class="info-item"><strong>Sector</strong>' . $sector . '</div>';
    $html .= '</div></div>';

    if ($summary) {
        $html .= '<div class="section"><h3>📝 Summary</h3><p>' . $summary . '</p></div>';
    }
    if ($eduList) {
        $html .= '<div class="section"><h3>🎓 Education</h3><ul>' . $eduList . '</ul></div>';
    }
    if ($skillTags) {
        $html .= '<div class="section"><h3>🛠️ Skills</h3><div>' . $skillTags . '</div></div>';
    }
    if ($jobTitles) {
        $html .= '<div class="section"><h3>💼 Suitable Job Titles</h3><p>' . $jobTitles . '</p></div>';
    }
    if ($strengthList) {
        $html .= '<div class="section"><h3>✅ Strengths</h3><ul>' . $strengthList . '</ul></div>';
    }
    if ($weaknessList) {
        $html .= '<div class="section"><h3>⚠️ Weaknesses</h3><ul>' . $weaknessList . '</ul></div>';
    }
    if ($recList) {
        $html .= '<div class="section"><h3>💡 Recommendations</h3><ul>' . $recList . '</ul></div>';
    }

    $html .= '<div class="footer">&copy; ' . $appName . ' — Confidential Candidate Report</div>';
    $html .= '</body></html>';

    echo $html;

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][PDF] " . $e->getMessage());
    http_response_code(500);
    echo "Error generating report";
}
