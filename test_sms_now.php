<?php
/*
==================================================
SMS Test Script - Verify Twilio Configuration
Run this to test if SMS is working
==================================================
*/

require_once 'backend_php/config.php';
require_once 'backend_php/sms_helper.php';

echo "==============================================\n";
echo "SMS Configuration Test\n";
echo "==============================================\n\n";

// Check configuration
echo "1. Checking SMS Gateway Configuration...\n";
$gateway = env('SMS_GATEWAY', 'none');
echo "   SMS_GATEWAY: {$gateway}\n";

if ($gateway === 'none') {
    echo "   ❌ ERROR: SMS_GATEWAY is set to 'none'\n";
    echo "   Fix: Set SMS_GATEWAY=twilio in .env file\n";
    exit(1);
}

if ($gateway === 'twilio') {
    echo "   ✅ Twilio selected\n\n";
    
    echo "2. Checking Twilio Credentials...\n";
    $sid = env('TWILIO_ACCOUNT_SID', '');
    $token = env('TWILIO_AUTH_TOKEN', '');
    $from = env('TWILIO_FROM_NUMBER', '');
    
    echo "   Account SID: " . ($sid ? substr($sid, 0, 10) . "..." : "❌ MISSING") . "\n";
    echo "   Auth Token: " . ($token ? substr($token, 0, 10) . "..." : "❌ MISSING") . "\n";
    echo "   From Number: " . ($from ? $from : "❌ MISSING") . "\n";
    
    if (!$sid || !$token || !$from) {
        echo "\n   ❌ ERROR: Twilio credentials incomplete\n";
        exit(1);
    }
    echo "   ✅ All credentials present\n\n";
}

// Test SMS sending
echo "3. Testing SMS Delivery...\n";
echo "   Enter your mobile number (with country code, e.g., +919876543210): ";
$mobile = trim(fgets(STDIN));

if (empty($mobile)) {
    echo "   ❌ No mobile number provided\n";
    exit(1);
}

echo "   Sending test OTP to: {$mobile}\n";
$testOtp = '123456';

$result = sendOTPSMS($mobile, $testOtp);

echo "\n4. Result:\n";
echo "   Success: " . ($result['success'] ? '✅ YES' : '❌ NO') . "\n";
echo "   Message: {$result['message']}\n";

if ($result['response']) {
    echo "   Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}

if ($result['success']) {
    echo "\n==============================================\n";
    echo "✅ SUCCESS! SMS sent to your phone!\n";
    echo "Check your phone for OTP: 123456\n";
    echo "==============================================\n";
} else {
    echo "\n==============================================\n";
    echo "❌ FAILED! SMS not sent\n";
    echo "Check the error message above\n";
    echo "==============================================\n";
    
    // Common error solutions
    echo "\nCommon Solutions:\n";
    echo "1. Verify Twilio credentials are correct\n";
    echo "2. Check Twilio account balance/credits\n";
    echo "3. For trial accounts: Verify the mobile number in Twilio console\n";
    echo "4. Ensure mobile number format is correct (+countrycode + number)\n";
}

echo "\n";
?>
