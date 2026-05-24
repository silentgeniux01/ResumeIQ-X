<?php
/*
==================================================
Test Creator Information in AI Chat
==================================================
*/

require_once 'backend_php/config.php';
require_once 'backend_php/ai_chat.php';

echo "==============================================\n";
echo "Testing Creator Information\n";
echo "==============================================\n\n";

$testQuestions = [
    "Who created ResumeIQ-X?",
    "Who made this platform?",
    "Tell me about the creator",
    "Who is the developer of this system?"
];

foreach ($testQuestions as $question) {
    echo "Question: {$question}\n";
    echo "----------------------------------------------\n";
    
    $result = getAIChatResponse($question, []);
    
    if ($result['success']) {
        echo "Provider: " . strtoupper($result['provider']) . "\n";
        echo "Response:\n";
        echo wordwrap($result['message'], 70) . "\n\n";
    } else {
        echo "Error: " . $result['message'] . "\n\n";
    }
    
    echo "==============================================\n\n";
    
    // Small delay to avoid rate limiting
    sleep(2);
}

echo "Test complete!\n";
?>
