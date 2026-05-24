<?php
/*
==================================================
ResumeIQ-X LLM Helper — Complete Fallback Chain
Cloud Providers → Local Ollama (NEVER fails)

Fallback Order:
  1. MEERA_FORCE_PROVIDER (if set)
  2. Groq       (free, fast — llama-3.3-70b)
  3. OpenAI     (gpt-4o-mini)
  4. Gemini     (gemini-2.0-flash)
  5. Anthropic  (claude-3-5-sonnet)
  6. DeepSeek   (deepseek-chat)
  7. Ollama     (local llama3.1 — ALWAYS available)

Works for ANY resume sector: engineering, medical,
finance, arts, law, education, etc.
==================================================
*/

require_once __DIR__ . '/config.php';

// ── Ollama configuration ──────────────────────────────────────────────────────
define('OLLAMA_HOST',  env('OLLAMA_HOST',  'http://localhost:11434'));
define('OLLAMA_MODEL', env('OLLAMA_MODEL', 'llama3.1:latest'));

/**
 * Main entry point — analyze resume with full fallback chain
 *
 * @param string $resumeText  Full extracted resume text
 * @param string $jobDescription  Optional JD for match scoring
 * @return array ['success'=>bool, 'analysis'=>array|null, 'provider'=>string, 'error'=>string]
 */
function analyzeResumeWithLLM(string $resumeText, string $jobDescription = ''): array
{
    $chain     = _getLLMFallbackChain();
    $lastError = '';

    foreach ($chain as $provider) {
        error_log("[ResumeIQ-X][LLM] Trying provider: {$provider}");

        $result = _callLLMProvider($provider, $resumeText, $jobDescription);

        if ($result['success']) {
            // Normalise field names so both old and new keys work
            $result['analysis'] = _normaliseAnalysis($result['analysis']);
            error_log("[ResumeIQ-X][LLM] ✓ Success with provider: {$provider}");
            return $result;
        }

        $lastError = $result['error'];
        error_log("[ResumeIQ-X][LLM] ✗ Failed with {$provider}: " . substr($lastError, 0, 120));
    }

    return [
        'success'  => false,
        'analysis' => null,
        'provider' => 'none',
        'error'    => "All LLM providers failed. Last error: {$lastError}"
    ];
}

// ── Fallback chain builder ────────────────────────────────────────────────────

function _getLLMFallbackChain(): array
{
    $force         = strtolower(trim(env('MEERA_FORCE_PROVIDER', '')));
    $quotaExceeded = (int) env('OPENAI_QUOTA_EXCEEDED', 0);
    $isRailway     = !empty(env('RAILWAY_ENVIRONMENT', '')) || !empty(env('RAILWAY_PROJECT_ID', ''));

    $chain = [];

    // Forced provider goes first
    if ($force && $force !== 'none' && $force !== 'ollama') {
        $chain[] = $force;
    }

    // Cloud providers in priority order
    $cloudOrder = ['groq', 'openai', 'gemini', 'anthropic', 'deepseek'];
    foreach ($cloudOrder as $p) {
        if (in_array($p, $chain)) continue;          // already added as forced
        if ($p === 'openai' && $quotaExceeded) continue; // skip if quota flag set
        $chain[] = $p;
    }

    // Ollama local is ONLY added if NOT on Railway (localhost doesn't work on Railway)
    if (!$isRailway && !in_array('ollama', $chain)) {
        $chain[] = 'ollama';
    }

    return $chain;
}

// ── Provider dispatcher ───────────────────────────────────────────────────────

function _callLLMProvider(string $provider, string $resumeText, string $jobDescription): array
{
    return match ($provider) {
        'openai'    => _callOpenAI($resumeText, $jobDescription),
        'groq'      => _callGroq($resumeText, $jobDescription),
        'gemini'    => _callGemini($resumeText, $jobDescription),
        'anthropic' => _callAnthropic($resumeText, $jobDescription),
        'deepseek'  => _callDeepSeek($resumeText, $jobDescription),
        'ollama'    => _callOllama($resumeText, $jobDescription),
        default     => ['success' => false, 'analysis' => null, 'provider' => $provider, 'error' => "Unknown provider: {$provider}"]
    };
}

// ── Prompt generator ─────────────────────────────────────────────────────────

function _generateAnalysisPrompt(string $resumeText, string $jobDescription): string
{
    // Truncate very long resumes to avoid token limits
    $resumeText = mb_substr($resumeText, 0, 6000);
    
    error_log("[ResumeIQ-X][LLM] Resume text length: " . strlen($resumeText));
    error_log("[ResumeIQ-X][LLM] Resume text preview: " . substr($resumeText, 0, 200));

    // Sanitize: remove non-UTF8 and control characters that break JSON
    $resumeText = mb_convert_encoding($resumeText, 'UTF-8', 'UTF-8');
    $resumeText = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]/u', ' ', $resumeText);
    $resumeText = preg_replace('/\s{3,}/', ' ', $resumeText);

    if ($jobDescription) {
        $jobDescription = mb_convert_encoding($jobDescription, 'UTF-8', 'UTF-8');
        $jobDescription = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]/u', ' ', $jobDescription);
    }

    $jdSection = $jobDescription
        ? "JOB DESCRIPTION (match against this):\n" . mb_substr($jobDescription, 0, 1500) . "\n\n"
        : '';

    return <<<PROMPT
You are an expert resume analyzer. Analyze the resume below for ANY sector (engineering, medical, finance, law, arts, education, etc.) and return ONLY a valid JSON object — no markdown, no explanation.

RESUME:
{$resumeText}

{$jdSection}Return this exact JSON structure:
{
  "overall_score": <integer 0-100>,
  "match_percentage": <integer 0-100>,
  "candidate_name": "<full name or Unknown>",
  "candidate_email": "<email or empty string>",
  "candidate_phone": "<phone or empty string>",
  "experience_years": <integer>,
  "education": ["<degree — institution>"],
  "skills": ["<skill1>", "<skill2>"],
  "strengths": ["<strength1>", "<strength2>"],
  "weaknesses": ["<weakness1>", "<weakness2>"],
  "recommendations": ["<recommendation1>"],
  "detected_sector": "<engineering|medical|finance|law|arts|education|marketing|hr|other>",
  "suitable_job_titles": ["<title1>", "<title2>"],
  "candidate_summary": "<2-3 sentence professional summary>"
}
PROMPT;
}

// ── Response parser ───────────────────────────────────────────────────────────

function _parseLLMResponse(string $response): ?array
{
    $response = trim($response);
    
    error_log("[ResumeIQ-X][LLM] Raw response length: " . strlen($response));
    error_log("[ResumeIQ-X][LLM] Raw response preview: " . substr($response, 0, 500));

    // Strip markdown code fences
    $response = preg_replace('/^```(?:json)?\s*/m', '', $response);
    $response = preg_replace('/\s*```$/m', '', $response);
    $response = trim($response);

    // Try direct decode first
    $json = json_decode($response, true);
    if (is_array($json)) {
        error_log("[ResumeIQ-X][LLM] Parsed JSON successfully");
        error_log("[ResumeIQ-X][LLM] Candidate name: " . ($json['candidate_name'] ?? 'not set'));
        error_log("[ResumeIQ-X][LLM] Overall score: " . ($json['overall_score'] ?? 'not set'));
        error_log("[ResumeIQ-X][LLM] Skills count: " . count($json['skills'] ?? []));
        return $json;
    }

    // Extract first {...} block
    if (preg_match('/\{[\s\S]*\}/m', $response, $m)) {
        $json = json_decode($m[0], true);
        if (is_array($json)) {
            error_log("[ResumeIQ-X][LLM] Parsed JSON from extracted block");
            return $json;
        }
    }

    error_log("[ResumeIQ-X][LLM] Failed to parse JSON response");
    return null;
}

/**
 * Normalise field names — LLMs sometimes use 'email' instead of 'candidate_email', etc.
 */
function _normaliseAnalysis(array $a): array
{
    // Remap common alternate keys
    $a['candidate_email'] = $a['candidate_email'] ?? $a['email'] ?? '';
    $a['candidate_phone'] = $a['candidate_phone'] ?? $a['phone'] ?? '';
    $a['detected_sector'] = $a['detected_sector'] ?? $a['sector'] ?? 'general';
    $a['suitable_job_titles'] = $a['suitable_job_titles'] ?? $a['job_titles'] ?? [];
    $a['candidate_summary']   = $a['candidate_summary']   ?? $a['summary']    ?? '';

    // Ensure arrays
    foreach (['education','skills','strengths','weaknesses','recommendations','suitable_job_titles'] as $f) {
        if (!isset($a[$f]) || !is_array($a[$f])) $a[$f] = [];
    }

    // Clamp scores
    $a['overall_score']    = max(0, min(100, (int)($a['overall_score']    ?? 0)));
    $a['match_percentage'] = max(0, min(100, (int)($a['match_percentage'] ?? 0)));
    $a['experience_years'] = max(0, (int)($a['experience_years'] ?? 0));

    return $a;
}

// ── Cloud provider implementations ───────────────────────────────────────────

function _callOpenAI(string $resumeText, string $jobDescription): array
{
    $apiKey = env('OPENAI_API_KEY', '');
    if (!$apiKey) return _err('openai', 'API key not configured');

    $data = [
        'model'       => 'gpt-4o-mini',
        'messages'    => [
            ['role' => 'system', 'content' => 'You are an expert resume analyzer. Respond with valid JSON only.'],
            ['role' => 'user',   'content' => _generateAnalysisPrompt($resumeText, $jobDescription)]
        ],
        'temperature' => 0.2,
        'max_tokens'  => 2000,
    ];

    $res = _httpPost('https://api.openai.com/v1/chat/completions', $data, [
        "Authorization: Bearer {$apiKey}"
    ]);

    if (!$res['ok']) return _err('openai', "HTTP {$res['code']}: " . substr($res['body'], 0, 200));

    $body = json_decode($res['body'], true);
    $text = $body['choices'][0]['message']['content'] ?? null;
    if (!$text) return _err('openai', 'Empty response');

    $analysis = _parseLLMResponse($text);
    return $analysis ? _ok('openai', $analysis) : _err('openai', 'JSON parse failed');
}

function _callGroq(string $resumeText, string $jobDescription): array
{
    $apiKey = env('GROQ_API_KEY', '');
    if (!$apiKey) {
        error_log("[ResumeIQ-X][LLM][Groq] API key not configured");
        return _err('groq', 'API key not configured');
    }

    error_log("[ResumeIQ-X][LLM][Groq] API key found: " . substr($apiKey, 0, 10) . "...");

    // Try multiple Groq models in case one is rate-limited
    // NOTE: llama-3.1-70b-versatile was decommissioned, removed from list
    $models = ['llama-3.3-70b-versatile', 'llama3-70b-8192', 'llama3-8b-8192', 'gemma2-9b-it'];

    foreach ($models as $model) {
        error_log("[ResumeIQ-X][LLM][Groq] Trying model: {$model}");
        
        $data = [
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => 'You are an expert resume analyzer. Respond with valid JSON only.'],
                ['role' => 'user',   'content' => _generateAnalysisPrompt($resumeText, $jobDescription)]
            ],
            'temperature' => 0.2,
            'max_tokens'  => 2000,
        ];

        $res = _httpPost('https://api.groq.com/openai/v1/chat/completions', $data, [
            "Authorization: Bearer {$apiKey}"
        ]);

        error_log("[ResumeIQ-X][LLM][Groq] Response code: {$res['code']}, OK: " . ($res['ok'] ? 'yes' : 'no'));

        if (!$res['ok']) {
            // 429 = rate limit — try next model
            if ($res['code'] === 429) {
                error_log("[ResumeIQ-X][LLM][Groq] Model {$model} rate-limited, trying next...");
                continue;
            }
            error_log("[ResumeIQ-X][LLM][Groq] HTTP error {$res['code']}: " . substr($res['body'], 0, 500));
            return _err('groq', "HTTP {$res['code']}: " . substr($res['body'], 0, 200));
        }

        $body = json_decode($res['body'], true);
        $text = $body['choices'][0]['message']['content'] ?? null;
        
        if (!$text) {
            error_log("[ResumeIQ-X][LLM][Groq] No content in response");
            continue;
        }

        error_log("[ResumeIQ-X][LLM][Groq] Got response, parsing...");
        $analysis = _parseLLMResponse($text);
        if ($analysis) {
            error_log("[ResumeIQ-X][LLM][Groq] ✓ Analysis successful with model: {$model}");
            return _ok('groq', $analysis);
        }
        error_log("[ResumeIQ-X][LLM][Groq] Failed to parse response");
    }

    return _err('groq', 'All Groq models rate-limited or failed');
}

function _callGemini(string $resumeText, string $jobDescription): array
{
    $apiKey = env('GEMINI_API_KEY', '');
    if (!$apiKey) return _err('gemini', 'API key not configured');

    // Try multiple Gemini models — ordered by availability
    $models = ['gemini-2.0-flash-lite', 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-flash-latest'];

    foreach ($models as $model) {
        $data = [
            'contents'         => [['parts' => [['text' => _generateAnalysisPrompt($resumeText, $jobDescription)]]]],
            'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 2000],
        ];

        $res = _httpPost(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            $data, []
        );

        if (!$res['ok']) {
            if (in_array($res['code'], [404, 429])) {
                error_log("[ResumeIQ-X][LLM] Gemini model {$model} unavailable ({$res['code']}), trying next...");
                continue;
            }
            return _err('gemini', "HTTP {$res['code']}: " . substr($res['body'], 0, 200));
        }

        $body = json_decode($res['body'], true);
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) continue;

        $analysis = _parseLLMResponse($text);
        if ($analysis) return _ok('gemini', $analysis);
    }

    return _err('gemini', 'All Gemini models unavailable or rate-limited');
}

function _callAnthropic(string $resumeText, string $jobDescription): array
{
    $apiKey = env('ANTHROPIC_API_KEY', '');
    if (!$apiKey) return _err('anthropic', 'API key not configured');

    $data = [
        'model'      => 'claude-3-5-haiku-20241022',
        'max_tokens' => 2000,
        'messages'   => [
            ['role' => 'user', 'content' => _generateAnalysisPrompt($resumeText, $jobDescription)]
        ],
    ];

    $res = _httpPost('https://api.anthropic.com/v1/messages', $data, [
        "x-api-key: {$apiKey}",
        'anthropic-version: 2023-06-01',
    ]);

    if (!$res['ok']) return _err('anthropic', "HTTP {$res['code']}: " . substr($res['body'], 0, 200));

    $body = json_decode($res['body'], true);
    $text = $body['content'][0]['text'] ?? null;
    if (!$text) return _err('anthropic', 'Empty response');

    $analysis = _parseLLMResponse($text);
    return $analysis ? _ok('anthropic', $analysis) : _err('anthropic', 'JSON parse failed');
}

function _callDeepSeek(string $resumeText, string $jobDescription): array
{
    $apiKey = env('DEEPSEEK_API_KEY', '');
    if (!$apiKey) return _err('deepseek', 'API key not configured');

    $data = [
        'model'       => 'deepseek-chat',
        'messages'    => [
            ['role' => 'system', 'content' => 'You are an expert resume analyzer. Respond with valid JSON only.'],
            ['role' => 'user',   'content' => _generateAnalysisPrompt($resumeText, $jobDescription)]
        ],
        'temperature' => 0.2,
        'max_tokens'  => 2000,
    ];

    $res = _httpPost('https://api.deepseek.com/v1/chat/completions', $data, [
        "Authorization: Bearer {$apiKey}"
    ]);

    if (!$res['ok']) return _err('deepseek', "HTTP {$res['code']}: " . substr($res['body'], 0, 200));

    $body = json_decode($res['body'], true);
    $text = $body['choices'][0]['message']['content'] ?? null;
    if (!$text) return _err('deepseek', 'Empty response');

    $analysis = _parseLLMResponse($text);
    return $analysis ? _ok('deepseek', $analysis) : _err('deepseek', 'JSON parse failed');
}

// ── LOCAL OLLAMA FALLBACK ─────────────────────────────────────────────────────

function _callOllama(string $resumeText, string $jobDescription): array
{
    $host  = OLLAMA_HOST;
    $model = OLLAMA_MODEL;

    // Check if Ollama is running
    $ping = @file_get_contents("{$host}/api/tags");
    if ($ping === false) {
        return _err('ollama', "Ollama not running at {$host}. Start with: ollama serve");
    }

    $prompt = _generateAnalysisPrompt($resumeText, $jobDescription);

    $data = [
        'model'  => $model,
        'prompt' => $prompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.2,
            'num_predict' => 2000,
        ],
    ];

    $ch = curl_init("{$host}/api/generate");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,   // local model can be slow
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) return _err('ollama', "cURL error: {$curlErr}");
    if ($httpCode !== 200) return _err('ollama', "HTTP {$httpCode}: " . substr($response, 0, 200));

    $body = json_decode($response, true);
    $text = $body['response'] ?? null;
    if (!$text) return _err('ollama', 'Empty response from Ollama');

    $analysis = _parseLLMResponse($text);
    if (!$analysis) {
        // Ollama sometimes wraps JSON in extra text — try harder
        if (preg_match('/\{[\s\S]*"overall_score"[\s\S]*\}/m', $text, $m)) {
            $analysis = json_decode($m[0], true);
        }
    }

    return $analysis ? _ok('ollama', $analysis) : _err('ollama', 'JSON parse failed. Raw: ' . substr($text, 0, 200));
}

// ── HTTP helper ───────────────────────────────────────────────────────────────

function _httpPost(string $url, array $data, array $extraHeaders = []): array
{
    $headers = array_merge(['Content-Type: application/json'], $extraHeaders);

    // Ensure JSON encodes cleanly — handle special chars
    $jsonBody = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonBody === false) {
        // Fallback: sanitize strings in data
        array_walk_recursive($data, function(&$val) {
            if (is_string($val)) {
                $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
                $val = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $val);
            }
        });
        $jsonBody = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    if (!$jsonBody) {
        return ['ok' => false, 'code' => 0, 'body' => 'JSON encode failed'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $jsonBody,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $body    = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['ok' => false, 'code' => 0, 'body' => "cURL: {$curlErr}"];
    }

    return ['ok' => ($code >= 200 && $code < 300), 'code' => $code, 'body' => $body];
}

// ── Result helpers ────────────────────────────────────────────────────────────

function _ok(string $provider, array $analysis): array
{
    return ['success' => true, 'analysis' => $analysis, 'provider' => $provider, 'error' => ''];
}

function _err(string $provider, string $error): array
{
    return ['success' => false, 'analysis' => null, 'provider' => $provider, 'error' => $error];
}
