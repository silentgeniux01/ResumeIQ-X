<?php
/*
==================================================
Check Twilio Account Status
Shows if account is in trial mode and verified numbers
==================================================
*/

require_once 'backend_php/config.php';

$accountSid = env('TWILIO_ACCOUNT_SID', '');
$authToken  = env('TWILIO_AUTH_TOKEN', '');

if (!$accountSid || !$authToken) {
    echo "❌ Twilio credentials not configured\n";
    exit(1);
}

echo "==============================================\n";
echo "Twilio Account Status Check\n";
echo "==============================================\n\n";

// Check account info
$url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}.json";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Failed to fetch account info\n";
    echo "HTTP Code: {$httpCode}\n";
    echo "Response: {$response}\n";
    exit(1);
}

$account = json_decode($response, true);

echo "Account SID: {$account['sid']}\n";
echo "Friendly Name: {$account['friendly_name']}\n";
echo "Status: {$account['status']}\n";
echo "Type: {$account['type']}\n\n";

if ($account['type'] === 'Trial') {
    echo "⚠️  TRIAL ACCOUNT DETECTED\n";
    echo "==============================================\n";
    echo "Limitations:\n";
    echo "  • Can only send SMS to verified numbers\n";
    echo "  • Limited credits (~$15)\n";
    echo "  • SMS includes trial message prefix\n\n";
    
    echo "To send SMS to ANY number:\n";
    echo "  1. Upgrade to paid account\n";
    echo "  2. OR verify each recipient number\n\n";
    
    echo "Verify numbers at:\n";
    echo "  https://console.twilio.com/us1/develop/phone-numbers/manage/verified\n\n";
} else {
    echo "✅ PAID ACCOUNT - Can send to any number!\n\n";
}

// Check verified numbers
echo "==============================================\n";
echo "Fetching Verified Numbers...\n";
echo "==============================================\n\n";

$url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/OutgoingCallerIds.json";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $numbers = $data['outgoing_caller_ids'] ?? [];
    
    if (empty($numbers)) {
        echo "No verified numbers found.\n";
    } else {
        echo "Verified Numbers (" . count($numbers) . "):\n";
        foreach ($numbers as $num) {
            echo "  ✅ {$num['phone_number']} - {$num['friendly_name']}\n";
        }
    }
} else {
    echo "❌ Failed to fetch verified numbers\n";
}

echo "\n==============================================\n";
echo "Summary\n";
echo "==============================================\n";

if ($account['type'] === 'Trial') {
    echo "Your Twilio account is in TRIAL mode.\n";
    echo "SMS will ONLY work for verified numbers.\n\n";
    echo "Next Steps:\n";
    echo "1. Verify your friend's number in Twilio console\n";
    echo "2. OR upgrade to paid account ($20 minimum)\n";
} else {
    echo "Your Twilio account is PAID.\n";
    echo "SMS should work for any valid number.\n";
}

echo "\n";
?>
