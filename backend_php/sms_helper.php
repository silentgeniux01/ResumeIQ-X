<?php
/*
==================================================
ResumeIQ-X SMS Helper
Supports multiple SMS gateways:
  - Twilio (International)
  - MSG91 (India)
  - Fast2SMS (India)
==================================================
*/

require_once __DIR__ . '/config.php';

/**
 * Send SMS using configured gateway
 * 
 * @param string $mobile Mobile number with country code (e.g., +919876543210)
 * @param string $message SMS message content
 * @return array ['success' => bool, 'message' => string, 'response' => mixed]
 */
function sendSMS(string $mobile, string $message): array
{
    $gateway = strtolower(env('SMS_GATEWAY', 'none'));
    
    switch ($gateway) {
        case 'twilio':
            return _sendViaTwilio($mobile, $message);
        case 'msg91':
            return _sendViaMsg91($mobile, $message);
        case 'fast2sms':
            return _sendViaFast2SMS($mobile, $message);
        case 'none':
        default:
            error_log('[ResumeIQ-X][SMS] No SMS gateway configured. Set SMS_GATEWAY in .env');
            return [
                'success' => false,
                'message' => 'SMS gateway not configured',
                'response' => null
            ];
    }
}

/**
 * Send OTP SMS
 * 
 * @param string $mobile Mobile number with country code
 * @param string $otp 6-digit OTP
 * @return array ['success' => bool, 'message' => string]
 */
function sendOTPSMS(string $mobile, string $otp): array
{
    $appName = env('APP_NAME', 'ResumeIQ-X');
    $message = "{$otp} is your OTP for {$appName}. Valid for 15 minutes. Do not share with anyone.";
    
    return sendSMS($mobile, $message);
}

/* ──────────────────────────────────────────────
   TWILIO SMS GATEWAY (International)
   ────────────────────────────────────────────── */
function _sendViaTwilio(string $mobile, string $message): array
{
    $accountSid = env('TWILIO_ACCOUNT_SID', '');
    $authToken  = env('TWILIO_AUTH_TOKEN', '');
    $fromNumber = env('TWILIO_FROM_NUMBER', '');
    
    if (!$accountSid || !$authToken || !$fromNumber) {
        error_log('[ResumeIQ-X][SMS] Twilio credentials missing in .env');
        return [
            'success' => false,
            'message' => 'Twilio credentials not configured',
            'response' => null
        ];
    }
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
    
    $data = [
        'From' => $fromNumber,
        'To'   => $mobile,
        'Body' => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 201 && isset($result['sid'])) {
        error_log("[ResumeIQ-X][SMS] Twilio SMS sent successfully to {$mobile}");
        return [
            'success' => true,
            'message' => 'SMS sent successfully',
            'response' => $result
        ];
    }
    
    $errorMsg = $result['message'] ?? 'Unknown error';
    error_log("[ResumeIQ-X][SMS] Twilio failed: {$errorMsg}");
    return [
        'success' => false,
        'message' => "SMS failed: {$errorMsg}",
        'response' => $result
    ];
}

/* ──────────────────────────────────────────────
   MSG91 SMS GATEWAY (India)
   ────────────────────────────────────────────── */
function _sendViaMsg91(string $mobile, string $message): array
{
    $authKey    = env('MSG91_AUTH_KEY', '');
    $senderId   = env('MSG91_SENDER_ID', '');
    $templateId = env('MSG91_TEMPLATE_ID', ''); // Optional for transactional
    
    if (!$authKey) {
        error_log('[ResumeIQ-X][SMS] MSG91 auth key missing in .env');
        return [
            'success' => false,
            'message' => 'MSG91 credentials not configured',
            'response' => null
        ];
    }
    
    // Remove country code if present (MSG91 expects 10-digit for India)
    $mobileClean = preg_replace('/^\+91/', '', $mobile);
    $mobileClean = preg_replace('/[^0-9]/', '', $mobileClean);
    
    $url = 'https://api.msg91.com/api/v5/flow/';
    
    $data = [
        'template_id' => $templateId ?: 'default',
        'sender'      => $senderId ?: 'RSMIQX',
        'short_url'   => '0',
        'mobiles'     => $mobileClean,
        'message'     => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "authkey: {$authKey}"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['type']) && $result['type'] === 'success') {
        error_log("[ResumeIQ-X][SMS] MSG91 SMS sent successfully to {$mobile}");
        return [
            'success' => true,
            'message' => 'SMS sent successfully',
            'response' => $result
        ];
    }
    
    $errorMsg = $result['message'] ?? 'Unknown error';
    error_log("[ResumeIQ-X][SMS] MSG91 failed: {$errorMsg}");
    return [
        'success' => false,
        'message' => "SMS failed: {$errorMsg}",
        'response' => $result
    ];
}

/* ──────────────────────────────────────────────
   FAST2SMS GATEWAY (India)
   ────────────────────────────────────────────── */
function _sendViaFast2SMS(string $mobile, string $message): array
{
    $apiKey = env('FAST2SMS_API_KEY', '');
    
    if (!$apiKey) {
        error_log('[ResumeIQ-X][SMS] Fast2SMS API key missing in .env');
        return [
            'success' => false,
            'message' => 'Fast2SMS credentials not configured',
            'response' => null
        ];
    }
    
    // Remove country code if present
    $mobileClean = preg_replace('/^\+91/', '', $mobile);
    $mobileClean = preg_replace('/[^0-9]/', '', $mobileClean);
    
    $url = 'https://www.fast2sms.com/dev/bulkV2';
    
    $data = [
        'route'    => 'v3',
        'sender_id' => env('FAST2SMS_SENDER_ID', 'RSMIQX'),
        'message'  => $message,
        'language' => 'english',
        'flash'    => 0,
        'numbers'  => $mobileClean
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        "authorization: {$apiKey}"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['return']) && $result['return'] === true) {
        error_log("[ResumeIQ-X][SMS] Fast2SMS sent successfully to {$mobile}");
        return [
            'success' => true,
            'message' => 'SMS sent successfully',
            'response' => $result
        ];
    }
    
    $errorMsg = $result['message'] ?? 'Unknown error';
    error_log("[ResumeIQ-X][SMS] Fast2SMS failed: {$errorMsg}");
    return [
        'success' => false,
        'message' => "SMS failed: {$errorMsg}",
        'response' => $result
    ];
}

/**
 * Format mobile number with country code
 * 
 * @param string $mobile Raw mobile number
 * @param string $defaultCountryCode Default country code (e.g., '+91' for India)
 * @return string Formatted mobile with country code
 */
function formatMobileNumber(string $mobile, string $defaultCountryCode = '+91'): string
{
    // Remove all non-numeric characters
    $clean = preg_replace('/[^0-9]/', '', $mobile);
    
    // If already has country code, return with +
    if (strlen($clean) > 10) {
        return '+' . $clean;
    }
    
    // Add default country code
    return $defaultCountryCode . $clean;
}

/**
 * Validate mobile number format
 * 
 * @param string $mobile Mobile number to validate
 * @return bool True if valid
 */
function isValidMobileNumber(string $mobile): bool
{
    $clean = preg_replace('/[^0-9]/', '', $mobile);
    
    // Should be at least 10 digits
    return strlen($clean) >= 10 && strlen($clean) <= 15;
}
