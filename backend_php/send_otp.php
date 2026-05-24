<?php
/*
==================================================
ResumeIQ-X Unified OTP Engine v2
Stores OTPs in DB temp table (not session)
Handles: email OTP + mobile OTP send/verify
==================================================
*/

if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/email_helper.php';
require_once __DIR__ . '/sms_helper.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
    exit;
}

$type   = trim($_POST["type"]   ?? "");
$email  = trim($_POST["email"]  ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$otp    = trim($_POST["otp"]    ?? "");

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "message" => "Valid email required"]);
    exit;
}

/* ── DB connection ── */
require_once __DIR__ . '/db.php';
$db = getDatabaseConnection();

/* ── Ensure otp_temp table exists ── */
$db->exec("CREATE TABLE IF NOT EXISTS otp_temp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_type VARCHAR(20) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    mobile VARCHAR(20) NULL,
    expiry DATETIME NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_type (email, otp_type)
)");

/* ── SEND EMAIL OTP ── */
if ($type === "email") {
    $newOtp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', time() + 900);

    // Delete old OTPs for this email+type
    $db->prepare("DELETE FROM otp_temp WHERE email=:e AND otp_type='email'")->execute([':e'=>$email]);
    $db->prepare("INSERT INTO otp_temp (email,otp_type,otp_code,expiry) VALUES (:e,'email',:o,:x)")
       ->execute([':e'=>$email,':o'=>$newOtp,':x'=>$expiry]);

    $appName = env('APP_NAME','ResumeIQ-X');
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0f172a;color:#e2e8f0;border-radius:16px;overflow:hidden;'>
      <div style='background:linear-gradient(135deg,#6366f1,#4f46e5);padding:28px;text-align:center;'>
        <h1 style='margin:0;font-size:24px;color:#fff;'>⚡ {$appName}</h1>
        <p style='margin:6px 0 0;color:#c7d2fe;font-size:13px;'>Email Verification</p>
      </div>
      <div style='padding:28px;'>
        <h2 style='color:#a5b4fc;margin-top:0;font-size:18px;'>Verify Your Email Address</h2>
        <p style='color:#cbd5e1;'>Use the OTP below to verify your email. Expires in <strong>15 minutes</strong>.</p>
        <div style='background:#1e293b;border:2px solid #6366f1;border-radius:12px;padding:24px;text-align:center;margin:20px 0;'>
          <div style='font-size:12px;color:#94a3b8;margin-bottom:6px;letter-spacing:2px;text-transform:uppercase;'>Email Verification Code</div>
          <div style='font-size:44px;font-weight:700;letter-spacing:12px;color:#a5b4fc;font-family:monospace;'>{$newOtp}</div>
        </div>
        <p style='color:#94a3b8;font-size:12px;'>Do not share this code with anyone.</p>
      </div>
      <div style='background:#1e293b;padding:14px;text-align:center;color:#64748b;font-size:11px;'>&copy; " . date('Y') . " {$appName}</div>
    </div></body></html>";

    $sent = sendEmail($email, 'User', "{$appName} — Email Verification OTP", $html);
    echo json_encode(["status" => true, "message" => "OTP sent to your email", "email_sent" => $sent]);
    exit;
}

/* ── VERIFY EMAIL OTP ── */
if ($type === "verify_email") {
    if (!$otp) { echo json_encode(["status"=>false,"message"=>"OTP required"]); exit; }

    $stmt = $db->prepare("SELECT id,otp_code,expiry FROM otp_temp WHERE email=:e AND otp_type='email' AND verified=0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([':e'=>$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) { echo json_encode(["status"=>false,"message"=>"No OTP sent. Click Send OTP first."]); exit; }
    if (strtotime($row['expiry']) < time()) { echo json_encode(["status"=>false,"message"=>"OTP expired. Please resend."]); exit; }
    if ($row['otp_code'] !== $otp) { echo json_encode(["status"=>false,"message"=>"Invalid OTP. Try again."]); exit; }

    $db->prepare("UPDATE otp_temp SET verified=1 WHERE id=:id")->execute([':id'=>$row['id']]);
    echo json_encode(["status"=>true,"message"=>"Email verified ✓"]);
    exit;
}

/* ── SEND MOBILE OTP ── */
if ($type === "mobile") {
    if (!$mobile) { echo json_encode(["status"=>false,"message"=>"Mobile number required"]); exit; }

    // Validate mobile number
    if (!isValidMobileNumber($mobile)) {
        echo json_encode(["status"=>false,"message"=>"Enter valid mobile number"]);
        exit;
    }

    // Format mobile with country code
    $mobileFormatted = formatMobileNumber($mobile);
    
    $newOtp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', time() + 900);

    $db->prepare("DELETE FROM otp_temp WHERE email=:e AND otp_type='mobile'")->execute([':e'=>$email]);
    $db->prepare("INSERT INTO otp_temp (email,otp_type,otp_code,mobile,expiry) VALUES (:e,'mobile',:o,:m,:x)")
       ->execute([':e'=>$email,':o'=>$newOtp,':m'=>$mobileFormatted,':x'=>$expiry]);

    // Try to send via SMS first
    $smsResult = sendOTPSMS($mobileFormatted, $newOtp);
    
    if ($smsResult['success']) {
        echo json_encode([
            "status" => true,
            "message" => "OTP sent to your mobile number",
            "delivery_method" => "sms",
            "mobile" => $mobileFormatted
        ]);
        exit;
    }
    
    // Fallback to email if SMS fails
    error_log("[ResumeIQ-X][OTP] SMS failed, falling back to email: " . $smsResult['message']);
    
    $appName = env('APP_NAME','ResumeIQ-X');
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0f172a;color:#e2e8f0;border-radius:16px;overflow:hidden;'>
      <div style='background:linear-gradient(135deg,#6366f1,#4f46e5);padding:28px;text-align:center;'>
        <h1 style='margin:0;font-size:24px;color:#fff;'>📱 {$appName}</h1>
        <p style='margin:6px 0 0;color:#c7d2fe;font-size:13px;'>Mobile Verification</p>
      </div>
      <div style='padding:28px;'>
        <h2 style='color:#a5b4fc;margin-top:0;font-size:18px;'>Verify Your Mobile Number</h2>
        <p style='color:#cbd5e1;'>Use the OTP below to verify mobile number <strong>{$mobileFormatted}</strong>. Expires in <strong>15 minutes</strong>.</p>
        <div style='background:#1e293b;border:2px solid #6366f1;border-radius:12px;padding:24px;text-align:center;margin:20px 0;'>
          <div style='font-size:12px;color:#94a3b8;margin-bottom:6px;letter-spacing:2px;text-transform:uppercase;'>Mobile Verification Code</div>
          <div style='font-size:44px;font-weight:700;letter-spacing:12px;color:#a5b4fc;font-family:monospace;'>{$newOtp}</div>
        </div>
        <p style='color:#94a3b8;font-size:12px;'>Do not share this code with anyone.</p>
        <p style='color:#f59e0b;font-size:11px;'>⚠️ SMS delivery failed. OTP sent to your email as fallback.</p>
      </div>
      <div style='background:#1e293b;padding:14px;text-align:center;color:#64748b;font-size:11px;'>&copy; " . date('Y') . " {$appName}</div>
    </div></body></html>";

    $sent = sendEmail($email, 'User', "{$appName} — Mobile Verification OTP", $html);
    echo json_encode([
        "status" => true,
        "message" => "SMS failed. OTP sent to your email ({$email})",
        "delivery_method" => "email_fallback",
        "email_sent" => $sent,
        "sms_error" => $smsResult['message']
    ]);
    exit;
}

/* ── VERIFY MOBILE OTP ── */
if ($type === "verify_mobile") {
    if (!$otp) { echo json_encode(["status"=>false,"message"=>"OTP required"]); exit; }

    $stmt = $db->prepare("SELECT id,otp_code,expiry FROM otp_temp WHERE email=:e AND otp_type='mobile' AND verified=0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([':e'=>$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) { echo json_encode(["status"=>false,"message"=>"No OTP sent. Click Send OTP first."]); exit; }
    if (strtotime($row['expiry']) < time()) { echo json_encode(["status"=>false,"message"=>"OTP expired. Please resend."]); exit; }
    if ($row['otp_code'] !== $otp) { echo json_encode(["status"=>false,"message"=>"Invalid OTP. Try again."]); exit; }

    $db->prepare("UPDATE otp_temp SET verified=1 WHERE id=:id")->execute([':id'=>$row['id']]);
    echo json_encode(["status"=>true,"message"=>"Mobile verified ✓"]);
    exit;
}

echo json_encode(["status"=>false,"message"=>"Unknown type"]);
exit;
