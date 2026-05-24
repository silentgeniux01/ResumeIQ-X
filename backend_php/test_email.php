<?php
/*
==================================================
Email Test Script - Railway Debugging v2
Shows detailed SMTP errors
==================================================
*/

header("Content-Type: text/plain");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/email_helper.php';

echo "=== EMAIL CONFIGURATION TEST ===\n\n";

// Test email configuration
$testEmail = 'mayurkove428@gmail.com';
$testOTP = '123456';

echo "Environment Variables:\n";
echo "  MAIL_HOST: " . env('MAIL_HOST', 'NOT SET') . "\n";
echo "  MAIL_PORT: " . env('MAIL_PORT', 'NOT SET') . "\n";
echo "  MAIL_USERNAME: " . env('MAIL_USERNAME', 'NOT SET') . "\n";
echo "  MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? 'SET (length: ' . strlen(env('MAIL_PASSWORD')) . ')' : 'NOT SET') . "\n";
echo "  MAIL_FROM_NAME: " . env('MAIL_FROM_NAME', 'NOT SET') . "\n";
echo "  MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'NOT SET') . "\n";
echo "\n";

echo "Test Email: {$testEmail}\n";
echo "Test OTP: {$testOTP}\n\n";

echo "Attempting to send email...\n\n";

// Try to send test email
$appName = env('APP_NAME', 'ResumeIQ-X');
$html = "<!DOCTYPE html><html><body>
<div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0f172a;color:#e2e8f0;border-radius:16px;padding:2rem;'>
  <h1 style='color:#6366f1;'>Test Email from Railway</h1>
  <p>This is a test email to verify SMTP configuration.</p>
  <p>Test OTP: <strong style='font-size:24px;color:#a5b4fc;'>{$testOTP}</strong></p>
  <p style='color:#94a3b8;font-size:12px;'>Sent from: {$appName}</p>
  <p style='color:#94a3b8;font-size:12px;'>Time: " . date('Y-m-d H:i:s') . "</p>
</div>
</body></html>";

$sent = sendEmail($testEmail, 'Test User', "{$appName} — Email Test", $html);

echo "\n=== RESULT ===\n";
if ($sent) {
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox: {$testEmail}\n";
    echo "Also check spam folder if not in inbox.\n";
} else {
    echo "❌ Email failed to send\n";
    echo "\nPossible issues:\n";
    echo "1. Brevo sender email not verified\n";
    echo "2. SMTP credentials incorrect\n";
    echo "3. Railway IP blocked by Brevo\n";
    echo "\nCheck Railway logs for detailed error:\n";
    echo "Railway Dashboard > Deployments > Latest > View Logs\n";
    echo "Search for: [ResumeIQ-X][EMAIL]\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Verify sender email in Brevo: https://app.brevo.com/senders\n";
echo "2. Check Brevo logs: https://app.brevo.com/log\n";
echo "3. Check Railway logs for SMTP errors\n";
