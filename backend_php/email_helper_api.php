<?php
/*
==================================================
ResumeIQ-X Email Helper - Brevo API Version
Uses HTTP API instead of SMTP (Railway blocks SMTP ports)
==================================================
*/

require_once __DIR__ . '/config.php';

/**
 * Send email using Brevo API (v3)
 * Railway blocks SMTP ports, so we use HTTP API instead
 */
function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): bool
{
    $apiKey = env('BREVO_API_KEY', '');
    
    if (!$apiKey) {
        error_log('[ResumeIQ-X][EMAIL] BREVO_API_KEY not set in environment');
        return false;
    }
    
    $fromName = env('MAIL_FROM_NAME', 'ResumeIQ-X');
    $fromEmail = env('MAIL_FROM_ADDRESS', 'mayurkove428@gmail.com');
    
    $url = 'https://api.brevo.com/v3/smtp/email';
    
    $data = [
        'sender' => [
            'name' => $fromName,
            'email' => $fromEmail
        ],
        'to' => [
            [
                'email' => $toEmail,
                'name' => $toName
            ]
        ],
        'subject' => $subject,
        'htmlContent' => $htmlBody
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201) {
        error_log("[ResumeIQ-X][EMAIL] Email sent successfully to {$toEmail} via Brevo API");
        return true;
    }
    
    $result = json_decode($response, true);
    $errorMsg = $result['message'] ?? 'Unknown error';
    error_log("[ResumeIQ-X][EMAIL] Brevo API failed: HTTP {$httpCode} - {$errorMsg}");
    return false;
}

/* ──────────────────────────────────────────────
   PUBLIC HELPERS
   ────────────────────────────────────────────── */

function generateOTP(): string
{
    return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

function sendVerificationEmail(string $email, string $name, string $otp): bool
{
    $appName = env('APP_NAME', 'ResumeIQ-X');
    $subject = "{$appName} — Your Email Verification OTP";

    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0f172a;color:#e2e8f0;border-radius:16px;overflow:hidden;'>
      <div style='background:linear-gradient(135deg,#6366f1,#4f46e5);padding:32px;text-align:center;'>
        <h1 style='margin:0;font-size:26px;color:#fff;'>&#9889; {$appName}</h1>
        <p style='margin:8px 0 0;color:#c7d2fe;font-size:14px;'>AI Resume Intelligence Platform</p>
      </div>
      <div style='padding:32px;'>
        <h2 style='color:#a5b4fc;margin-top:0;font-size:20px;'>Verify Your Email Address</h2>
        <p style='color:#e2e8f0;'>Hi <strong>{$name}</strong>,</p>
        <p style='color:#cbd5e1;'>Thanks for registering! Use the OTP below to verify your email address. This code expires in <strong>15 minutes</strong>.</p>
        <div style='background:#1e293b;border:2px solid #6366f1;border-radius:12px;padding:28px;text-align:center;margin:28px 0;'>
          <div style='font-size:14px;color:#94a3b8;margin-bottom:8px;letter-spacing:2px;text-transform:uppercase;'>Your Verification Code</div>
          <div style='font-size:48px;font-weight:700;letter-spacing:14px;color:#a5b4fc;font-family:monospace;'>{$otp}</div>
        </div>
        <p style='color:#94a3b8;font-size:13px;'>&#128274; Do not share this code with anyone. ResumeIQ-X will never ask for your OTP.</p>
        <p style='color:#94a3b8;font-size:13px;'>If you did not register, please ignore this email.</p>
      </div>
      <div style='background:#1e293b;padding:16px;text-align:center;color:#64748b;font-size:12px;'>
        &copy; " . date('Y') . " {$appName}. All rights reserved.
      </div>
    </div></body></html>";

    return sendEmail($email, $name, $subject, $html);
}

function sendPasswordResetEmail(string $email, string $name, string $token, string $resetLink = ''): bool
{
    $appName = env('APP_NAME', 'ResumeIQ-X');
    $subject = "{$appName} — Reset Your Password";

    if (!$resetLink) {
        $appUrl    = rtrim(env('APP_URL', 'http://localhost'), '/');
        $resetLink = "{$appUrl}/frontend/reset_password.html?token=" . urlencode($token);
    }

    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0f172a;color:#e2e8f0;border-radius:16px;overflow:hidden;'>
      <div style='background:linear-gradient(135deg,#6366f1,#4f46e5);padding:32px;text-align:center;'>
        <h1 style='margin:0;font-size:26px;color:#fff;'>&#128272; {$appName}</h1>
        <p style='margin:8px 0 0;color:#c7d2fe;font-size:14px;'>Password Reset Request</p>
      </div>
      <div style='padding:32px;'>
        <h2 style='color:#a5b4fc;margin-top:0;font-size:20px;'>Reset Your Password</h2>
        <p style='color:#e2e8f0;'>Hi <strong>{$name}</strong>,</p>
        <p style='color:#cbd5e1;'>We received a request to reset your password. Click the button below to set a new password. This link expires in <strong>30 minutes</strong>.</p>
        <div style='text-align:center;margin:32px 0;'>
          <a href='{$resetLink}'
             style='background:linear-gradient(95deg,#6366f1,#4f46e5);color:#fff;text-decoration:none;
                    padding:16px 36px;border-radius:60px;font-weight:700;font-size:16px;display:inline-block;'>
            &#128273; Reset My Password
          </a>
        </div>
        <p style='color:#94a3b8;font-size:13px;'>If the button does not work, copy and paste this link into your browser:</p>
        <p style='color:#6366f1;font-size:12px;word-break:break-all;background:#1e293b;padding:10px;border-radius:8px;'>{$resetLink}</p>
        <p style='color:#94a3b8;font-size:13px;margin-top:16px;'>If you did not request a password reset, please ignore this email. Your password will not change.</p>
      </div>
      <div style='background:#1e293b;padding:16px;text-align:center;color:#64748b;font-size:12px;'>
        &copy; " . date('Y') . " {$appName}. All rights reserved.
      </div>
    </div></body></html>";

    return sendEmail($email, $name, $subject, $html);
}
