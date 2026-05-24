<?php
/*
==================================================
ResumeIQ-X Mobile OTP Verification
Sends OTP to email (since no SMS gateway)
and verifies it for mobile number confirmation
==================================================
*/

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

require_once "db.php";
require_once "email_helper.php";
require_once "sms_helper.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
    exit;
}

$action = trim($_POST["action"] ?? "send"); // send | verify
$email  = trim($_POST["email"]  ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$otp    = trim($_POST["otp"]    ?? "");

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "message" => "Valid email required"]);
    exit;
}

/* ── SEND mobile OTP ── */
if ($action === "send") {
    $db   = getDatabaseConnection();
    $stmt = $db->prepare("SELECT id, name FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([":email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => false, "message" => "Account not found"]);
        exit;
    }

    // Validate and format mobile number
    if (!isValidMobileNumber($mobile)) {
        echo json_encode(["status" => false, "message" => "Invalid mobile number"]);
        exit;
    }
    
    $mobileFormatted = formatMobileNumber($mobile);

    $newOtp = storeMobileOTP($email);
    if (!$newOtp) {
        echo json_encode(["status" => false, "message" => "Failed to generate OTP"]);
        exit;
    }

    // Try to send via SMS first
    $smsResult = sendOTPSMS($mobileFormatted, $newOtp);
    
    if ($smsResult['success']) {
        echo json_encode([
            "status"     => true,
            "message"    => "OTP sent to your mobile number",
            "delivery_method" => "sms",
            "mobile" => $mobileFormatted
        ]);
        exit;
    }
    
    // Fallback to email if SMS fails
    error_log("[ResumeIQ-X][OTP] SMS failed, falling back to email: " . $smsResult['message']);

    // Send via email (SMS gateway not configured)
    $appName = env('APP_NAME', 'ResumeIQ-X');
    $subject = "{$appName} — Mobile Number Verification OTP";
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0f172a;color:#e2e8f0;border-radius:16px;overflow:hidden;'>
      <div style='background:linear-gradient(135deg,#6366f1,#4f46e5);padding:28px;text-align:center;'>
        <h1 style='margin:0;font-size:24px;color:#fff;'>📱 {$appName}</h1>
        <p style='margin:6px 0 0;color:#c7d2fe;font-size:13px;'>Mobile Number Verification</p>
      </div>
      <div style='padding:28px;'>
        <h2 style='color:#a5b4fc;margin-top:0;font-size:18px;'>Verify Your Mobile Number</h2>
        <p style='color:#e2e8f0;'>Hi <strong>{$user['name']}</strong>,</p>
        <p style='color:#cbd5e1;'>Use the OTP below to verify your mobile number <strong>{$mobileFormatted}</strong>. Expires in <strong>15 minutes</strong>.</p>
        <div style='background:#1e293b;border:2px solid #6366f1;border-radius:12px;padding:24px;text-align:center;margin:24px 0;'>
          <div style='font-size:13px;color:#94a3b8;margin-bottom:6px;letter-spacing:2px;text-transform:uppercase;'>Mobile Verification Code</div>
          <div style='font-size:44px;font-weight:700;letter-spacing:12px;color:#a5b4fc;font-family:monospace;'>{$newOtp}</div>
        </div>
        <p style='color:#94a3b8;font-size:12px;'>Do not share this code with anyone.</p>
        <p style='color:#f59e0b;font-size:11px;'>⚠️ SMS delivery failed. OTP sent to your email as fallback.</p>
      </div>
      <div style='background:#1e293b;padding:14px;text-align:center;color:#64748b;font-size:11px;'>
        &copy; " . date('Y') . " {$appName}. All rights reserved.
      </div>
    </div></body></html>";

    $sent = sendEmail($email, $user['name'], $subject, $html);

    echo json_encode([
        "status"     => true,
        "message"    => "SMS failed. OTP sent to your email",
        "delivery_method" => "email_fallback",
        "email_sent" => $sent,
        "sms_error" => $smsResult['message']
    ]);
    exit;
}

/* ── VERIFY mobile OTP ── */
if ($action === "verify") {
    if (!$otp) {
        echo json_encode(["status" => false, "message" => "OTP is required"]);
        exit;
    }

    $verified = verifyMobileOTP($email, $otp);

    if (!$verified) {
        echo json_encode(["status" => false, "message" => "Invalid or expired OTP"]);
        exit;
    }

    echo json_encode(["status" => true, "message" => "Mobile number verified ✓"]);
    exit;
}

echo json_encode(["status" => false, "message" => "Unknown action"]);
exit;
