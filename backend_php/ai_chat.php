<?php
/*
==================================================
ResumeIQ-X AI Chat Assistant
Helps users with navigation, questions, and actions
Uses LLM with cloud → local fallback
==================================================
*/

// Only execute main logic if called directly (not included)
if (basename($_SERVER['PHP_SELF']) === 'ai_chat.php') {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        exit;
    }

    require_once __DIR__ . '/config.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = trim($input['message'] ?? '');
    $conversationHistory = $input['history'] ?? [];

    if (!$userMessage) {
        echo json_encode(["success" => false, "message" => "Message required"]);
        exit;
    }

    // Get AI response with LLM fallback
    $result = getAIChatResponse($userMessage, $conversationHistory);

    echo json_encode($result);
    exit;
}

// If included, just provide the functions
if (!function_exists('getAIChatResponse')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Get AI chat response with full LLM fallback chain
 */
function getAIChatResponse(string $userMessage, array $history): array
{
    $chain = _getChatLLMFallbackChain();
    $lastError = '';

    foreach ($chain as $provider) {
        error_log("[ResumeIQ-X][AI-Chat] Trying provider: {$provider}");

        $result = _callChatProvider($provider, $userMessage, $history);

        if ($result['success']) {
            error_log("[ResumeIQ-X][AI-Chat] ✓ Success with provider: {$provider}");
            return $result;
        }

        $lastError = $result['message'];
        error_log("[ResumeIQ-X][AI-Chat] ✗ Failed with {$provider}: " . substr($lastError, 0, 120));
    }

    return [
        'success' => false,
        'message' => "I'm having trouble connecting right now. Please try again in a moment.",
        'provider' => 'none'
    ];
}

/**
 * Build LLM fallback chain for chat
 */
function _getChatLLMFallbackChain(): array
{
    $force = strtolower(trim(env('MEERA_FORCE_PROVIDER', '')));
    $quotaExceeded = (int) env('OPENAI_QUOTA_EXCEEDED', 0);

    $chain = [];

    if ($force && $force !== 'none') {
        $chain[] = $force;
    }

    $cloudOrder = ['groq', 'openai', 'gemini', 'anthropic', 'deepseek'];
    foreach ($cloudOrder as $p) {
        if (in_array($p, $chain)) continue;
        if ($p === 'openai' && $quotaExceeded) continue;
        $chain[] = $p;
    }

    if (!in_array('ollama', $chain)) {
        $chain[] = 'ollama';
    }

    return $chain;
}

/**
 * Provider dispatcher
 */
function _callChatProvider(string $provider, string $userMessage, array $history): array
{
    return match ($provider) {
        'openai'    => _chatOpenAI($userMessage, $history),
        'groq'      => _chatGroq($userMessage, $history),
        'gemini'    => _chatGemini($userMessage, $history),
        'anthropic' => _chatAnthropic($userMessage, $history),
        'deepseek'  => _chatDeepSeek($userMessage, $history),
        'ollama'    => _chatOllama($userMessage, $history),
        default     => ['success' => false, 'message' => "Unknown provider: {$provider}", 'provider' => $provider]
    };
}

/**
 * Generate system prompt for chat assistant
 */
function _getChatSystemPrompt(): string
{
    return <<<PROMPT
You are ResumeIQ-X AI Assistant, a helpful and knowledgeable AI chatbot.

CREATOR & PLATFORM INFORMATION:
- Platform: ResumeIQ-X - AI-Powered Resume Intelligence Platform
- Creator: MAYUR GOPAL KOVE (DOB: 6 July 2004)
- Creator's Vision: Built this platform to revolutionize resume analysis using advanced AI
- When asked about the creator, always mention: "ResumeIQ-X was created by MAYUR GOPAL KOVE, a visionary developer who built this AI-powered platform to help candidates accelerate their careers."

YOUR CAPABILITIES:
You are a UNIVERSAL AI ASSISTANT like ChatGPT or Gemini. You can:
1. Answer ANY question on ANY topic (science, history, math, programming, philosophy, etc.)
2. Help with homework, research, writing, coding, and problem-solving
3. Provide explanations, tutorials, and step-by-step guidance
4. Engage in creative tasks (stories, poems, ideas, brainstorming)
5. Answer questions about ResumeIQ-X when asked
6. Guide users on the platform (register, login, upload resume, etc.)
7. Provide career advice and resume tips
8. Share information about the creator MAYUR GOPAL KOVE when asked

ABOUT RESUMEIQ-X (only mention when relevant):
- AI-powered resume analysis platform created by MAYUR GOPAL KOVE
- Analyzes resumes using 7 intelligence layers
- Provides skill gap detection, career recommendations, and job matching
- Supports multiple user roles: Candidates, Recruiters, and Admins

TONE & STYLE:
- Friendly, professional, and helpful
- Clear and concise explanations
- Use emojis sparingly (1-2 per message max)
- Adapt your response length to the question complexity
- Be encouraging and positive
- Show expertise across all domains

IMPORTANT RULES:
- Answer ANY question the user asks, regardless of topic
- If you don't know something, admit it honestly
- Never make up facts or information
- Provide accurate, helpful, and educational responses
- When asked "who created this" or "who made this", always mention MAYUR GOPAL KOVE
- Don't ask for sensitive information (passwords, API keys, etc.)

Respond naturally and helpfully to the user's message on ANY topic they ask about.
PROMPT;
}

/**
 * Build conversation messages for LLM
 */
function _buildChatMessages(string $userMessage, array $history): array
{
    $messages = [
        ['role' => 'system', 'content' => _getChatSystemPrompt()]
    ];

    // Add last 5 messages from history for context
    $recentHistory = array_slice($history, -5);
    foreach ($recentHistory as $msg) {
        $messages[] = [
            'role' => $msg['role'] ?? 'user',
            'content' => $msg['content'] ?? ''
        ];
    }

    // Add current user message
    $messages[] = ['role' => 'user', 'content' => $userMessage];

    return $messages;
}

// ── CLOUD PROVIDERS ──────────────────────────────────────────────────────────

function _chatOpenAI(string $userMessage, array $history): array
{
    $apiKey = env('OPENAI_API_KEY', '');
    if (!$apiKey) return ['success' => false, 'message' => 'OpenAI not configured', 'provider' => 'openai'];

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => _buildChatMessages($userMessage, $history),
        'temperature' => 0.7,
        'max_tokens' => 800,
    ];

    $res = _httpPostChat('https://api.openai.com/v1/chat/completions', $data, [
        "Authorization: Bearer {$apiKey}"
    ]);

    if (!$res['ok']) return ['success' => false, 'message' => 'OpenAI request failed', 'provider' => 'openai'];

    $body = json_decode($res['body'], true);
    $text = trim($body['choices'][0]['message']['content'] ?? '');

    return $text
        ? ['success' => true, 'message' => $text, 'provider' => 'openai']
        : ['success' => false, 'message' => 'Empty response', 'provider' => 'openai'];
}

function _chatGroq(string $userMessage, array $history): array
{
    $apiKey = env('GROQ_API_KEY', '');
    if (!$apiKey) return ['success' => false, 'message' => 'Groq not configured', 'provider' => 'groq'];

    $models = ['llama-3.3-70b-versatile', 'llama-3.1-70b-versatile', 'llama3-8b-8192'];

    foreach ($models as $model) {
        $data = [
            'model' => $model,
            'messages' => _buildChatMessages($userMessage, $history),
            'temperature' => 0.7,
            'max_tokens' => 800,
        ];

        $res = _httpPostChat('https://api.groq.com/openai/v1/chat/completions', $data, [
            "Authorization: Bearer {$apiKey}"
        ]);

        if (!$res['ok']) {
            if ($res['code'] === 429) continue; // Rate limited, try next model
            return ['success' => false, 'message' => 'Groq request failed', 'provider' => 'groq'];
        }

        $body = json_decode($res['body'], true);
        $text = trim($body['choices'][0]['message']['content'] ?? '');

        if ($text) {
            return ['success' => true, 'message' => $text, 'provider' => 'groq'];
        }
    }

    return ['success' => false, 'message' => 'All Groq models rate-limited', 'provider' => 'groq'];
}

function _chatGemini(string $userMessage, array $history): array
{
    $apiKey = env('GEMINI_API_KEY', '');
    if (!$apiKey) return ['success' => false, 'message' => 'Gemini not configured', 'provider' => 'gemini'];

    $models = ['gemini-2.0-flash-lite', 'gemini-2.0-flash', 'gemini-flash-latest'];

    // Convert messages to Gemini format
    $messages = _buildChatMessages($userMessage, $history);
    $systemPrompt = '';
    $conversationParts = [];

    foreach ($messages as $msg) {
        if ($msg['role'] === 'system') {
            $systemPrompt = $msg['content'];
        } else {
            $conversationParts[] = ['text' => $msg['content']];
        }
    }

    // Prepend system prompt to first user message
    if ($systemPrompt && !empty($conversationParts)) {
        $conversationParts[0]['text'] = $systemPrompt . "\n\n" . $conversationParts[0]['text'];
    }

    foreach ($models as $model) {
        $data = [
            'contents' => [['parts' => $conversationParts]],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ],
        ];

        $res = _httpPostChat(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            $data, []
        );

        if (!$res['ok']) {
            if (in_array($res['code'], [404, 429])) continue;
            return ['success' => false, 'message' => 'Gemini request failed', 'provider' => 'gemini'];
        }

        $body = json_decode($res['body'], true);
        $text = trim($body['candidates'][0]['content']['parts'][0]['text'] ?? '');

        if ($text) {
            return ['success' => true, 'message' => $text, 'provider' => 'gemini'];
        }
    }

    return ['success' => false, 'message' => 'All Gemini models unavailable', 'provider' => 'gemini'];
}

function _chatAnthropic(string $userMessage, array $history): array
{
    $apiKey = env('ANTHROPIC_API_KEY', '');
    if (!$apiKey) return ['success' => false, 'message' => 'Anthropic not configured', 'provider' => 'anthropic'];

    $messages = _buildChatMessages($userMessage, $history);
    $systemPrompt = '';
    $conversationMessages = [];

    foreach ($messages as $msg) {
        if ($msg['role'] === 'system') {
            $systemPrompt = $msg['content'];
        } else {
            $conversationMessages[] = $msg;
        }
    }

    $data = [
        'model' => 'claude-3-5-haiku-20241022',
        'max_tokens' => 800,
        'system' => $systemPrompt,
        'messages' => $conversationMessages,
    ];

    $res = _httpPostChat('https://api.anthropic.com/v1/messages', $data, [
        "x-api-key: {$apiKey}",
        'anthropic-version: 2023-06-01',
    ]);

    if (!$res['ok']) return ['success' => false, 'message' => 'Anthropic request failed', 'provider' => 'anthropic'];

    $body = json_decode($res['body'], true);
    $text = trim($body['content'][0]['text'] ?? '');

    return $text
        ? ['success' => true, 'message' => $text, 'provider' => 'anthropic']
        : ['success' => false, 'message' => 'Empty response', 'provider' => 'anthropic'];
}

function _chatDeepSeek(string $userMessage, array $history): array
{
    $apiKey = env('DEEPSEEK_API_KEY', '');
    if (!$apiKey) return ['success' => false, 'message' => 'DeepSeek not configured', 'provider' => 'deepseek'];

    $data = [
        'model' => 'deepseek-chat',
        'messages' => _buildChatMessages($userMessage, $history),
        'temperature' => 0.7,
        'max_tokens' => 800,
    ];

    $res = _httpPostChat('https://api.deepseek.com/v1/chat/completions', $data, [
        "Authorization: Bearer {$apiKey}"
    ]);

    if (!$res['ok']) return ['success' => false, 'message' => 'DeepSeek request failed', 'provider' => 'deepseek'];

    $body = json_decode($res['body'], true);
    $text = trim($body['choices'][0]['message']['content'] ?? '');

    return $text
        ? ['success' => true, 'message' => $text, 'provider' => 'deepseek']
        : ['success' => false, 'message' => 'Empty response', 'provider' => 'deepseek'];
}

// ── LOCAL OLLAMA FALLBACK ────────────────────────────────────────────────────

function _chatOllama(string $userMessage, array $history): array
{
    $host = env('OLLAMA_HOST', 'http://localhost:11434');
    $model = env('OLLAMA_MODEL', 'llama3.1:latest');

    // Check if Ollama is running
    $ping = @file_get_contents("{$host}/api/tags");
    if ($ping === false) {
        return ['success' => false, 'message' => 'Ollama not running', 'provider' => 'ollama'];
    }

    // Build conversation context
    $messages = _buildChatMessages($userMessage, $history);
    $prompt = '';
    foreach ($messages as $msg) {
        $role = $msg['role'] === 'system' ? 'System' : ($msg['role'] === 'assistant' ? 'Assistant' : 'User');
        $prompt .= "{$role}: {$msg['content']}\n\n";
    }
    $prompt .= "Assistant: ";

    $data = [
        'model' => $model,
        'prompt' => $prompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.7,
            'num_predict' => 800,
        ],
    ];

    $ch = curl_init("{$host}/api/generate");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['success' => false, 'message' => 'Ollama request failed', 'provider' => 'ollama'];
    }

    $body = json_decode($response, true);
    $text = trim($body['response'] ?? '');

    return $text
        ? ['success' => true, 'message' => $text, 'provider' => 'ollama']
        : ['success' => false, 'message' => 'Empty response', 'provider' => 'ollama'];
}

// ── HTTP HELPER ───────────────────────────────────────────────────────────────

function _httpPostChat(string $url, array $data, array $extraHeaders = []): array
{
    $headers = array_merge(['Content-Type: application/json'], $extraHeaders);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['ok' => ($code >= 200 && $code < 300), 'code' => $code, 'body' => $body];
}
