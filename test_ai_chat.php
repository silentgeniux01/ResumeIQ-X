<?php
/*
==================================================
Test AI Chat Assistant
Quick test to verify LLM fallback chain works
==================================================
*/

require_once 'backend_php/config.php';

echo "==============================================\n";
echo "AI Chat Assistant Test\n";
echo "==============================================\n\n";

// Test message
$testMessage = "What is ResumeIQ-X and how does it work?";

echo "Test Message: {$testMessage}\n\n";
echo "Testing LLM fallback chain...\n\n";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = json_encode([
    'message' => $testMessage,
    'history' => []
]);

// Capture output
ob_start();
file_put_contents('php://input', $input);
require 'backend_php/ai_chat.php';
$output = ob_get_clean();

$result = json_decode($output, true);

echo "==============================================\n";
echo "Result:\n";
echo "==============================================\n\n";

if ($result['success']) {
    echo "✅ SUCCESS!\n\n";
    echo "Provider: " . strtoupper($result['provider']) . "\n\n";
    echo "Response:\n";
    echo wordwrap($result['message'], 70) . "\n\n";
} else {
    echo "❌ FAILED\n\n";
    echo "Error: " . $result['message'] . "\n\n";
}

echo "==============================================\n";
echo "Available Providers Check:\n";
echo "==============================================\n\n";

$providers = [
    'GROQ' => env('GROQ_API_KEY', ''),
    'OpenAI' => env('OPENAI_API_KEY', ''),
    'Gemini' => env('GEMINI_API_KEY', ''),
    'Anthropic' => env('ANTHROPIC_API_KEY', ''),
    'DeepSeek' => env('DEEPSEEK_API_KEY', ''),
    'Ollama' => env('OLLAMA_HOST', 'http://localhost:11434'),
];

foreach ($providers as $name => $key) {
    $status = $key ? '✅ Configured' : '❌ Not configured';
    echo "{$name}: {$status}\n";
}

echo "\n==============================================\n";
echo "Forced Provider: " . (env('MEERA_FORCE_PROVIDER', 'none') ?: 'none') . "\n";
echo "OpenAI Quota Exceeded: " . (env('OPENAI_QUOTA_EXCEEDED', 0) ? 'YES' : 'NO') . "\n";
echo "==============================================\n\n";

echo "Test complete!\n\n";
echo "To test in browser:\n";
echo "1. Open: http://your-domain/ResumeIQ-X/index.html\n";
echo "2. Click the 🤖 button in bottom-right corner\n";
echo "3. Type a message and press Enter\n\n";
?>
