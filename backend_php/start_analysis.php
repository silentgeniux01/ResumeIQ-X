<?php
/*
==================================================
ResumeIQ-X Analysis Engine v3
LLM-Powered with Fallback Chain
OpenAI → Groq → Gemini → Anthropic → DeepSeek
Works for ANY sector: engineering, medical, finance, arts, etc.
Fills ALL analysis_results columns
==================================================
*/

// Suppress PHP errors from corrupting JSON output
ini_set('display_errors', 0);
error_reporting(0);

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/session_guard.php";
require_once __DIR__ . "/llm_helper.php";

// Allow admin OR recruiter to trigger analysis
// Support both new session format (role/user_id) and legacy admin format (user_role/admin_id)
$session = verifySession();
$isAuthorized = false;

if ($session && in_array($session['role'], ['admin', 'recruiter'])) {
    $isAuthorized = true;
} elseif (isset($_SESSION['admin_id'])) {
    $isAuthorized = true; // Legacy admin session
} elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $isAuthorized = true; // Legacy admin session (user_role variant)
} elseif (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'recruiter'])) {
    $isAuthorized = true;
}

if (!$isAuthorized) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Login required. Please log in as admin or recruiter."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "POST method required"]);
    exit;
}

$resumeId       = (int)($_POST['resume_id'] ?? 0);
$jobDescription = trim($_POST['job_description'] ?? '');

if (!$resumeId) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Resume ID required"]);
    exit;
}

$conn = getDatabaseConnection();

// Fetch resume record
$stmt = $conn->prepare("SELECT id, user_id, file_path, file_type, analysis_status FROM resumes WHERE id = ? LIMIT 1");
$stmt->execute([$resumeId]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume) {
    http_response_code(404);
    echo json_encode(["status" => false, "message" => "Resume not found"]);
    exit;
}

// Block duplicate execution
if ($resume['analysis_status'] === 'processing') {
    echo json_encode(["status" => true, "message" => "Analysis already running"]);
    exit;
}

// Reset failed status so retry works
$conn->prepare("UPDATE resumes SET analysis_status='pending', analysis_progress=0 WHERE id=? AND analysis_status='failed'")->execute([$resumeId]);

// Mark as processing with immediate status update
$conn->prepare("UPDATE resumes SET analysis_status='processing', analysis_progress=10 WHERE id=?")->execute([$resumeId]);

// ── STEP 1: Extract resume text ──────────────────────────────────────────────
$conn->prepare("UPDATE resumes SET analysis_progress=20 WHERE id=?")->execute([$resumeId]);
$resumeText = extractResumeTextLLM($resume['file_path'], $resume['file_type'] ?? '');

if (!$resumeText || strlen(trim($resumeText)) < 20) {
    $conn->prepare("UPDATE resumes SET analysis_status='failed', analysis_progress=0 WHERE id=?")->execute([$resumeId]);
    http_response_code(500);
    echo json_encode([
        "status" => false, 
        "message" => "Could not extract text from resume. The PDF may be image-based or corrupted. Please try uploading a TXT file or a text-based PDF."
    ]);
    exit;
}

// Check if extracted text is just PDF metadata (common issue)
if (str_contains($resumeText, 'ReportLab') || str_contains($resumeText, 'endobj') || str_contains($resumeText, 'xref')) {
    error_log("[ResumeIQ-X][Analysis] PDF extraction returned metadata instead of content");
    $conn->prepare("UPDATE resumes SET analysis_status='failed', analysis_progress=0 WHERE id=?")->execute([$resumeId]);
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "PDF text extraction failed. This PDF may be image-based or use unsupported compression. Please upload your resume as a TXT file or use a simpler PDF format."
    ]);
    exit;
}

$conn->prepare("UPDATE resumes SET analysis_progress=40 WHERE id=?")->execute([$resumeId]);

// ── STEP 2: LLM Analysis with fallback chain ─────────────────────────────────
$conn->prepare("UPDATE resumes SET analysis_progress=50 WHERE id=?")->execute([$resumeId]);
$analysisResult = analyzeResumeWithLLM($resumeText, $jobDescription);

if (!$analysisResult['success']) {
    $conn->prepare("UPDATE resumes SET analysis_status='failed', analysis_progress=0 WHERE id=?")->execute([$resumeId]);
    http_response_code(500);
    echo json_encode([
        "status"  => false,
        "message" => "LLM analysis failed: " . $analysisResult['error']
    ]);
    exit;
}

$a        = $analysisResult['analysis'];
$provider = $analysisResult['provider'];

$conn->prepare("UPDATE resumes SET analysis_progress=80 WHERE id=?")->execute([$resumeId]);

// ── STEP 3: Map LLM output to ALL analysis_results columns ───────────────────
// Compute legacy-compatible scores from LLM output
$overallScore       = (int)($a['overall_score']    ?? 0);
$matchPct           = (int)($a['match_percentage'] ?? 0);
$strengthScore      = round($overallScore * 0.01, 2);          // 0.0–1.0 scale
$confidenceScore    = round(min(100, $overallScore + 5) * 0.01, 2);
$careerReadiness    = round(($overallScore + $matchPct) / 2 * 0.01, 2);

$skills             = $a['skills']             ?? [];
$strengths          = $a['strengths']          ?? [];
$weaknesses         = $a['weaknesses']         ?? [];
$recommendations    = $a['recommendations']    ?? [];
$education          = $a['education']          ?? [];
$suitableJobTitles  = $a['suitable_job_titles'] ?? [];
$sector             = $a['detected_sector']    ?? 'general';
$summary            = $a['candidate_summary']  ?? '';
$expYears           = (int)($a['experience_years'] ?? 0);

// Build legacy JSON columns from LLM data
$semanticRoleScores = json_encode(array_map(fn($t) => [$t => $overallScore], array_slice($suitableJobTitles, 0, 5)));
$domainDistribution = json_encode([$sector => 100]);
$skillMaturity      = json_encode(array_map(fn($s) => [$s => min(100, $overallScore + rand(-10, 10))], array_slice($skills, 0, 10)));
$missingDeps        = json_encode($weaknesses);
$learningRecs       = json_encode($recommendations);
$trajectoryPred     = json_encode(["predicted_roles" => $suitableJobTitles, "growth_score" => $overallScore]);
$reasoningSignals   = json_encode(["strengths" => $strengths, "weaknesses" => $weaknesses, "sector" => $sector]);
$capabilityVector   = json_encode(["skills" => $skills, "experience_years" => $expYears, "education" => $education]);
$candidateSignal    = json_encode(["name" => $a['candidate_name'] ?? '', "email" => $a['candidate_email'] ?? '', "phone" => $a['candidate_phone'] ?? '', "summary" => $summary]);
$latentSkillReport  = json_encode(["inferred_skills" => $skills, "sector" => $sector]);
$careerDirVector    = json_encode(["direction" => $sector, "suitable_titles" => $suitableJobTitles]);
$talentCategory     = $overallScore >= 85 ? 'Exceptional' : ($overallScore >= 70 ? 'Strong' : ($overallScore >= 55 ? 'Moderate' : 'Developing'));

// ── STEP 4: Upsert into analysis_results ─────────────────────────────────────
try {
    // Use INSERT ... ON DUPLICATE KEY UPDATE with VALUES() to avoid duplicate param issue
    $upsert = $conn->prepare("
        INSERT INTO analysis_results (
            resume_id, user_id,
            resume_strength_score, confidence_score, career_readiness_score,
            talent_category,
            semantic_role_scores, domain_distribution, skill_maturity,
            missing_dependencies, learning_recommendations,
            trajectory_prediction, reasoning_signals,
            capability_vector, candidate_signal_profile,
            latent_skill_report, career_direction_vector,
            analysis_status,
            overall_score, match_percentage,
            candidate_name, candidate_email, candidate_phone,
            experience_years, education, skills,
            strengths, weaknesses, recommendations,
            detected_sector, suitable_job_titles, candidate_summary,
            llm_provider_used, analysis_timestamp
        ) VALUES (
            :resume_id, :user_id,
            :strength, :confidence, :readiness,
            :talent_cat,
            :sem_roles, :domain_dist, :skill_mat,
            :missing_deps, :learning_recs,
            :trajectory, :reasoning,
            :capability, :candidate_signal,
            :latent_skill, :career_dir,
            'completed',
            :overall_score, :match_pct,
            :cand_name, :cand_email, :cand_phone,
            :exp_years, :education, :skills,
            :strengths, :weaknesses, :recommendations,
            :sector, :job_titles, :summary,
            :provider, NOW()
        )
        ON DUPLICATE KEY UPDATE
            resume_strength_score    = VALUES(resume_strength_score),
            confidence_score         = VALUES(confidence_score),
            career_readiness_score   = VALUES(career_readiness_score),
            talent_category          = VALUES(talent_category),
            semantic_role_scores     = VALUES(semantic_role_scores),
            domain_distribution      = VALUES(domain_distribution),
            skill_maturity           = VALUES(skill_maturity),
            missing_dependencies     = VALUES(missing_dependencies),
            learning_recommendations = VALUES(learning_recommendations),
            trajectory_prediction    = VALUES(trajectory_prediction),
            reasoning_signals        = VALUES(reasoning_signals),
            capability_vector        = VALUES(capability_vector),
            candidate_signal_profile = VALUES(candidate_signal_profile),
            latent_skill_report      = VALUES(latent_skill_report),
            career_direction_vector  = VALUES(career_direction_vector),
            analysis_status          = 'completed',
            overall_score            = VALUES(overall_score),
            match_percentage         = VALUES(match_percentage),
            candidate_name           = VALUES(candidate_name),
            candidate_email          = VALUES(candidate_email),
            candidate_phone          = VALUES(candidate_phone),
            experience_years         = VALUES(experience_years),
            education                = VALUES(education),
            skills                   = VALUES(skills),
            strengths                = VALUES(strengths),
            weaknesses               = VALUES(weaknesses),
            recommendations          = VALUES(recommendations),
            detected_sector          = VALUES(detected_sector),
            suitable_job_titles      = VALUES(suitable_job_titles),
            candidate_summary        = VALUES(candidate_summary),
            llm_provider_used        = VALUES(llm_provider_used),
            analysis_timestamp       = NOW()
    ");

    $upsert->execute([
        ':resume_id'        => $resumeId,
        ':user_id'          => $resume['user_id'],
        ':strength'         => $strengthScore,
        ':confidence'       => $confidenceScore,
        ':readiness'        => $careerReadiness,
        ':talent_cat'       => $talentCategory,
        ':sem_roles'        => $semanticRoleScores,
        ':domain_dist'      => $domainDistribution,
        ':skill_mat'        => $skillMaturity,
        ':missing_deps'     => $missingDeps,
        ':learning_recs'    => $learningRecs,
        ':trajectory'       => $trajectoryPred,
        ':reasoning'        => $reasoningSignals,
        ':capability'       => $capabilityVector,
        ':candidate_signal' => $candidateSignal,
        ':latent_skill'     => $latentSkillReport,
        ':career_dir'       => $careerDirVector,
        ':overall_score'    => $overallScore,
        ':match_pct'        => $matchPct,
        ':cand_name'        => $a['candidate_name']  ?? '',
        ':cand_email'       => $a['candidate_email'] ?? '',
        ':cand_phone'       => $a['candidate_phone'] ?? '',
        ':exp_years'        => $expYears,
        ':education'        => json_encode($education),
        ':skills'           => json_encode($skills),
        ':strengths'        => json_encode($strengths),
        ':weaknesses'       => json_encode($weaknesses),
        ':recommendations'  => json_encode($recommendations),
        ':sector'           => $sector,
        ':job_titles'       => json_encode($suitableJobTitles),
        ':summary'          => $summary,
        ':provider'         => $provider,
    ]);

} catch (PDOException $e) {
    error_log("[ResumeIQ-X][Analysis] DB upsert failed: " . $e->getMessage());
    $conn->prepare("UPDATE resumes SET analysis_status='failed', analysis_progress=0 WHERE id=?")->execute([$resumeId]);
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Failed to save analysis results"]);
    exit;
}

// ── STEP 5: Mark resume as completed ─────────────────────────────────────────
$conn->prepare("UPDATE resumes SET analysis_status='completed', analysis_progress=100 WHERE id=?")->execute([$resumeId]);

echo json_encode([
    "status"       => true,
    "message"      => "Analysis completed successfully",
    "resume_id"    => $resumeId,
    "overall_score"=> $overallScore,
    "match_pct"    => $matchPct,
    "sector"       => $sector,
    "provider"     => $provider,
    "talent_category" => $talentCategory
]);

// ── TEXT EXTRACTION HELPER ────────────────────────────────────────────────────
/**
 * Extract text from resume — handles Cloudinary URLs, local paths, PDF, TXT
 */
function extractResumeTextLLM(string $filePath, string $fileType = ''): ?string
{
    if (!$filePath) return null;

    $isUrl = str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://');
    $ext   = strtolower($fileType ?: pathinfo($filePath, PATHINFO_EXTENSION));

    // ── Download from Cloudinary / remote URL ──
    if ($isUrl) {
        $tmpFile = sys_get_temp_dir() . '/resume_' . md5($filePath) . '.' . ($ext ?: 'pdf');

        // Use cURL to download
        $ch = curl_init($filePath);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $fileContent = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$fileContent || $httpCode !== 200) {
            error_log("[ResumeIQ-X][Extract] Failed to download: {$filePath} (HTTP {$httpCode})");
            return null;
        }

        file_put_contents($tmpFile, $fileContent);
        $text = extractFromLocalFile($tmpFile, $ext);
        @unlink($tmpFile);
        return $text;
    }

    // ── Local file ──
    if (!file_exists($filePath)) {
        error_log("[ResumeIQ-X][Extract] File not found: {$filePath}");
        return null;
    }

    return extractFromLocalFile($filePath, $ext);
}

/**
 * Extract text from a local file by extension
 */
function extractFromLocalFile(string $path, string $ext): ?string
{
    if (!file_exists($path)) return null;

    // ── TXT ──────────────────────────────────────────────────────────────────
    if ($ext === 'txt') {
        return file_get_contents($path) ?: null;
    }

    // ── PDF ──────────────────────────────────────────────────────────────────
    if ($ext === 'pdf') {
        $text = '';

        // Method 1: smalot/pdfparser (PHP library - works everywhere)
        $vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
                if ($text && strlen(trim($text)) > 20) {
                    error_log("[ResumeIQ-X][PDF] Extracted " . strlen($text) . " chars using smalot/pdfparser");
                    return trim($text);
                }
            } catch (\Exception $e) {
                error_log("[ResumeIQ-X][PDF] smalot/pdfparser failed: " . $e->getMessage());
            }
        }

        // Method 2: Python pdf_reader.py (PyMuPDF — best quality)
        $pythonExec = env('PYTHON_EXECUTABLE', 'python');
        $pdfReader  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR,
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ai_engine_python' . DIRECTORY_SEPARATOR . 'utils' . DIRECTORY_SEPARATOR . 'pdf_reader.py'
        );
        $pathNorm = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (file_exists($pdfReader) && file_exists($pythonExec)) {
            $cmd    = '"' . $pythonExec . '" "' . $pdfReader . '" "' . $pathNorm . '" 2>&1';
            $output = shell_exec($cmd);
            if ($output) {
                // Strip Python debug lines
                $lines = array_filter(explode("\n", $output), function($l) {
                    $l = trim($l);
                    return $l !== ''
                        && !str_starts_with($l, '[PDF Reader]')
                        && !str_starts_with($l, '[IMAGE OCR')
                        && !str_starts_with($l, '[PDF ENGINE');
                });
                $cleaned = trim(implode("\n", $lines));
                if (strlen($cleaned) > 20) {
                    return $cleaned;
                }
            }
        }

        // Method 3: pdftotext (if installed on system)
        $pdftotext = trim(shell_exec('where pdftotext 2>nul') ?: shell_exec('which pdftotext 2>/dev/null') ?: '');
        if ($pdftotext) {
            $tmpTxt = $path . '.txt';
            shell_exec('"' . $pdftotext . '" "' . $pathNorm . '" "' . $tmpTxt . '" 2>&1');
            if (file_exists($tmpTxt)) {
                $text = file_get_contents($tmpTxt);
                @unlink($tmpTxt);
                if ($text && strlen(trim($text)) > 20) return trim($text);
            }
        }

        // Method 4: Raw PDF text extraction (BT/ET markers) - LAST RESORT
        $raw = file_get_contents($path);
        if (!$raw) return null;

        preg_match_all('/BT\s+(.*?)\s+ET/s', $raw, $btMatches);
        foreach ($btMatches[1] as $block) {
            preg_match_all('/\((.*?)\)\s*Tj/', $block, $tjMatches);
            $text .= implode(' ', $tjMatches[1]) . ' ';
        }

        if (strlen(trim($text)) < 20) {
            preg_match_all('/\(([^\)]{2,})\)/', $raw, $parenMatches);
            $text = implode(' ', array_filter($parenMatches[1], fn($s) => strlen($s) > 2));
        }

        if (strlen(trim($text)) < 20) {
            $cleaned = preg_replace('/[^\x20-\x7E\n\r\t]/', ' ', $raw);
            preg_match_all('/[A-Za-z][A-Za-z0-9\.\,\@\+\-\_\s]{3,}/', $cleaned, $wordMatches);
            $text = implode(' ', $wordMatches[0]);
            foreach (['obj','endobj','stream','endstream','xref','trailer','startxref','FlateDecode','BitsPerComponent','ColorSpace','MediaBox'] as $kw) {
                $text = str_replace($kw, ' ', $text);
            }
            $text = preg_replace('/\s{3,}/', ' ', $text);
        }

        return strlen(trim($text)) > 20 ? trim($text) : null;
    }

    // ── DOCX ─────────────────────────────────────────────────────────────────
    if ($ext === 'docx') {
        $zip = new ZipArchive();
        if ($zip->open($path) === true) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if ($xml) {
                $text = strip_tags(str_replace(['</w:p>', '</w:r>'], ["\n", ' '], $xml));
                return strlen(trim($text)) > 20 ? trim($text) : null;
            }
        }
    }

    // ── DOC / Generic ────────────────────────────────────────────────────────
    $raw  = @file_get_contents($path);
    if (!$raw) return null;
    $text = preg_replace('/[^\x20-\x7E\n\r\t]/', ' ', $raw);
    $text = preg_replace('/\s{3,}/', ' ', $text);
    return strlen(trim($text)) > 20 ? trim($text) : null;
}
