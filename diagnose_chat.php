<?php
/*
==================================================
AI Chat Diagnostic Tool
Checks all components and identifies issues
==================================================
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "==============================================\n";
echo "AI Chat Diagnostic Tool\n";
echo "==============================================\n\n";

// Test 1: Check if config loads
echo "Test 1: Configuration Loading\n";
echo "----------------------------------------------\n";
try {
    require_once 'backend_php/config.php';
    echo "✅ config.php loaded successfully\n\n";
} catch (Exception $e) {
    echo "❌ Failed to load config.php: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check environment variables
echo "Test 2: Environment Variables\n";
echo "----------------------------------------------\n";
$requiredVars = [
    'GROQ_API_KEY',
    'OPENAI_API_KEY',
    'GEMINI_API_KEY',
    'ANTHROPIC_API_KEY',
    'DEEPSEEK_API_KEY',
    'OLLAMA_HOST',
    'MEERA_FORCE_PROVIDER'
];

foreach ($requiredVars as $var) {
    $value = env($var, '');
    $status = $value ? '✅' : '❌';
    $display = $value ? (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value) : 'NOT SET';
    echo "{$status} {$var}: {$display}\n";
}
echo "\n";

// Test 3: Check if ai_chat.php exists
echo "Test 3: File Existence\n";
echo "----------------------------------------------\n";
$files = [
    'backend_php/ai_chat.php',
    'backend_php/llm_helper.php',
    'backend_php/config.php'
];

foreach ($files as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅' : '❌';
    echo "{$status} {$file}\n";
}
echo "\n";

// Test 4: Simulate API call
echo "Test 4: Simulated API Call\n";
echo "----------------------------------------------\n";

// Create test input
$testData = [
    'message' => 'Hello, test message',
    'history' => []
];

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture output
ob_start();

// Simulate php://input
$tempFile = tmpfile();
fwrite($tempFile, json_encode($testData));
rewind($tempFile);
stream_filter_register('test.input', 'TestInputStream');
stream_filter_append($tempFile, 'test.input');

try {
    // Include the chat API
    include 'backend_php/ai_chat.php';
    $output = ob_get_clean();
    
    echo "API Output:\n";
    echo $output . "\n\n";
    
    $result = json_decode($output, true);
    if ($result && isset($result['success'])) {
        if ($result['success']) {
            echo "✅ API call successful!\n";
            echo "Provider: " . ($result['provider'] ?? 'unknown') . "\n";
            echo "Response: " . substr($result['message'] ?? '', 0, 100) . "...\n";
        } else {
            echo "❌ API returned error: " . ($result['message'] ?? 'unknown') . "\n";
        }
    } else {
        echo "❌ Invalid JSON response\n";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

fclose($tempFile);
echo "\n";

// Test 5: Direct LLM test
echo "Test 5: Direct LLM Provider Test\n";
echo "----------------------------------------------\n";

// Test Groq directly
$groqKey = env('GROQ_API_KEY', '');
if ($groqKey) {
    echo "Testing Groq API...\n";
    
    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            ['role' => 'user', 'content' => 'Say "test successful" if you can read this.']
        ],
        'max_tokens' => 50
    ];
    
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer {$groqKey}"
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        echo "❌ cURL Error: {$curlError}\n";
    } elseif ($httpCode !== 200) {
        echo "❌ HTTP {$httpCode}: " . substr($response, 0, 200) . "\n";
    } else {
        $result = json_decode($response, true);
        $text = $result['choices'][0]['message']['content'] ?? '';
        if ($text) {
            echo "✅ Groq API working! Response: {$text}\n";
        } else {
            echo "❌ Empty response from Groq\n";
        }
    }
} else {
    echo "⚠️  Groq API key not configured\n";
}

echo "\n";

// Test 6: Check Ollama
echo "Test 6: Ollama Local Fallback\n";
echo "----------------------------------------------\n";
$ollamaHost = env('OLLAMA_HOST', 'http://localhost:11434');
$ping = @file_get_contents("{$ollamaHost}/api/tags", false, stream_context_create([
    'http' => ['timeout' => 5]
]));

if ($ping !== false) {
    echo "✅ Ollama is running at {$ollamaHost}\n";
    $tags = json_decode($ping, true);
    if (isset($tags['models'])) {
        echo "Available models: " . count($tags['models']) . "\n";
    }
} else {
    echo "❌ Ollama not running at {$ollamaHost}\n";
    echo "   Start with: ollama serve\n";
}

echo "\n";

// Summary
echo "==============================================\n";
echo "Diagnostic Summary\n";
echo "==============================================\n\n";

$groqConfigured = env('GROQ_API_KEY', '') !== '';
$ollamaRunning = $ping !== false;

if ($groqConfigured || $ollamaRunning) {
    echo "✅ At least one LLM provider is available\n";
    if ($groqConfigured) echo "   - Groq (cloud)\n";
    if ($ollamaRunning) echo "   - Ollama (local)\n";
} else {
    echo "❌ No LLM providers available!\n";
    echo "   Configure at least one provider in .env\n";
}

echo "\n";
echo "Next Steps:\n";
echo "1. Open: http://your-domain/ResumeIQ-X/test_chat_api.html\n";
echo "2. Click 'Run Test' to test the API\n";
echo "3. Check browser console for errors\n";
echo "\n";

// Helper class for stream filter (not actually used, just for compatibility)
class TestInputStream extends php_user_filter {
    function filter($in, $out, &$consumed, $closing) {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}
?>
