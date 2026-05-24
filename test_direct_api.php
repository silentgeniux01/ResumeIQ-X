<?php
/*
==================================================
Direct API Test - Bypasses all headers
==================================================
*/

// Simulate a real POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = []; // Clear POST
$_GET = [];  // Clear GET

// Create test data
$testMessage = 'Hello, can you help me understand ResumeIQ-X?';
$testData = json_encode([
    'message' => $testMessage,
    'history' => []
]);

// Write to php://input simulation
file_put_contents('php://memory', $testData);

// Now test the actual function
require_once 'backend_php/config.php';

// Manually call the chat function
$input = json_decode($testData, true);
$userMessage = trim($input['message'] ?? '');
$conversationHistory = $input['history'] ?? [];

echo "==============================================\n";
echo "Direct API Function Test\n";
echo "==============================================\n\n";

echo "Input Message: {$userMessage}\n";
echo "History Count: " . count($conversationHistory) . "\n\n";

if (!$userMessage) {
    echo "❌ Message is empty!\n";
    exit(1);
}

echo "Calling AI chat function...\n\n";

// Load the chat functions
require_once 'backend_php/ai_chat.php';

// Call the function directly
$result = getAIChatResponse($userMessage, $conversationHistory);

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
?>
