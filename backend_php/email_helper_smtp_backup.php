<?php
/*
==================================================
ResumeIQ-X Email Helper v3
Multi-method SMTP Mailer — no Composer required
Methods tried in order:
  1. stream_socket_client STARTTLS (port 587)
  2. stream_socket_client SSL      (port 465)
  3. PHP mail() fallback
Detailed error logging for every failure.
==================================================
*/

require_once __DIR__ . '/config.php';

/* ──────────────────────────────────────────────
   CORE SMTP SEND — tries 587 STARTTLS then 465 SSL
   ────────────────────────────────────────────── */
function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): bool
{
    $host     = env('MAIL_HOST',         'smtp.gmail.com');
    $port587  = (int) env('MAIL_PORT',   587);
    $username = env('MAIL_USERNAME',     '');
    $password = env('MAIL_PASSWORD',     '');
    $fromName = env('MAIL_FROM_NAME',    'ResumeIQ-X');
    $fromAddr = env('MAIL_FROM_ADDRESS', $username);

    if (!$username || !$password) {
        error_log('[ResumeIQ-X][EMAIL] MAIL_USERNAME or MAIL_PASSWORD not set in .env');
        return _mailFallback($toEmail, $toName, $subject, $htmlBody, $fromAddr, $fromName);
    }

    // Try STARTTLS on port 587 first
    $result = _smtpStartTLS($host, $port587, $username, $password, $fromAddr, $fromName, $toEmail, $toName, $subject, $htmlBody);
    if ($result) return true;

    // Try SSL on port 465
    $result = _smtpSSL($host, 465, $username, $password, $fromAddr, $fromName, $toEmail, $toName, $subject, $htmlBody);
    if ($result) return true;

    // Last resort: PHP mail()
    return _mailFallback($toEmail, $toName, $subject, $htmlBody, $fromAddr, $fromName);
}

/* ──────────────────────────────────────────────
   METHOD 1: STARTTLS on port 587
   ────────────────────────────────────────────── */
function _smtpStartTLS(
    string $host, int $port,
    string $username, string $password,
    string $fromAddr, string $fromName,
    string $toEmail, string $toName,
    string $subject, string $htmlBody
): bool {
    error_log("[ResumeIQ-X][EMAIL] Trying STARTTLS {$host}:{$port}");

    $socket = @stream_socket_client(
        "tcp://{$host}:{$port}", $errno, $errstr, 20, STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        error_log("[ResumeIQ-X][EMAIL] STARTTLS connect failed: {$errstr} (errno {$errno})");
        return false;
    }

    stream_set_timeout($socket, 20);

    $banner = _smtpRead($socket);
    if (!$banner) { fclose($socket); return false; }

    // EHLO
    _smtpWrite($socket, "EHLO resumeiqx.local");
    _smtpReadMulti($socket);

    // STARTTLS
    _smtpWrite($socket, "STARTTLS");
    $tlsResp = _smtpRead($socket);
    if (strpos($tlsResp, '220') === false) {
        error_log("[ResumeIQ-X][EMAIL] STARTTLS not accepted: {$tlsResp}");
        fclose($socket);
        return false;
    }

    // Upgrade to TLS
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        error_log("[ResumeIQ-X][EMAIL] TLS upgrade failed");
        fclose($socket);
        return false;
    }

    // Re-EHLO
    _smtpWrite($socket, "EHLO resumeiqx.local");
    _smtpReadMulti($socket);

    // AUTH
    if (!_smtpAuth($socket, $username, $password)) {
        fclose($socket);
        return false;
    }

    $ok = _smtpSendData($socket, $fromAddr, $fromName, $toEmail, $toName, $subject, $htmlBody);
    fclose($socket);
    return $ok;
}

/* ──────────────────────────────────────────────
   METHOD 2: SSL on port 465
   ────────────────────────────────────────────── */
function _smtpSSL(
    string $host, int $port,
    string $username, string $password,
    string $fromAddr, string $fromName,
    string $toEmail, string $toName,
    string $subject, string $htmlBody
): bool {
    error_log("[ResumeIQ-X][EMAIL] Trying SSL {$host}:{$port}");

    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ]);

    $socket = @stream_socket_client(
        "ssl://{$host}:{$port}", $errno, $errstr, 20,
        STREAM_CLIENT_CONNECT, $context
    );

    if (!$socket) {
        error_log("[ResumeIQ-X][EMAIL] SSL connect failed: {$errstr} (errno {$errno})");
        return false;
    }

    stream_set_timeout($socket, 20);

    _smtpRead($socket); // banner

    _smtpWrite($socket, "EHLO resumeiqx.local");
    _smtpReadMulti($socket);

    if (!_smtpAuth($socket, $username, $password)) {
        fclose($socket);
        return false;
    }

    $ok = _smtpSendData($socket, $fromAddr, $fromName, $toEmail, $toName, $subject, $htmlBody);
    fclose($socket);
    return $ok;
}

/* ──────────────────────────────────────────────
   AUTH LOGIN helper
   ────────────────────────────────────────────── */
function _smtpAuth($socket, string $username, string $password): bool
{
    _smtpWrite($socket, "AUTH LOGIN");
    $r1 = _smtpRead($socket); // 334 Username:
    if (strpos($r1, '334') === false) {
        error_log("[ResumeIQ-X][EMAIL] AUTH LOGIN not accepted: {$r1}");
        return false;
    }

    _smtpWrite($socket, base64_encode($username));
    $r2 = _smtpRead($socket); // 334 Password:
    if (strpos($r2, '334') === false) {
        error_log("[ResumeIQ-X][EMAIL] AUTH username not accepted: {$r2}");
        return false;
    }

    _smtpWrite($socket, base64_encode($password));
    $r3 = _smtpRead($socket); // 235 Authenticated
    if (strpos($r3, '235') === false) {
        error_log("[ResumeIQ-X][EMAIL] AUTH failed (wrong password / not an App Password?): {$r3}");
        return false;
    }

    return true;
}

/* ──────────────────────────────────────────────
   Send MAIL FROM / RCPT TO / DATA
   ────────────────────────────────────────────── */
function _smtpSendData(
    $socket,
    string $fromAddr, string $fromName,
    string $toEmail, string $toName,
    string $subject, string $htmlBody
): bool {
    _smtpWrite($socket, "MAIL FROM:<{$fromAddr}>");
    $r = _smtpRead($socket);
    if (strpos($r, '250') === false) {
        error_log("[ResumeIQ-X][EMAIL] MAIL FROM rejected: {$r}");
        return false;
    }

    _smtpWrite($socket, "RCPT TO:<{$toEmail}>");
    $r = _smtpRead($socket);
    if (strpos($r, '250') === false && strpos($r, '251') === false) {
        error_log("[ResumeIQ-X][EMAIL] RCPT TO rejected: {$r}");
        return false;
    }

    _smtpWrite($socket, "DATA");
    $r = _smtpRead($socket);
    if (strpos($r, '354') === false) {
        error_log("[ResumeIQ-X][EMAIL] DATA not accepted: {$r}");
        return false;
    }

    $boundary = md5(uniqid('', true));
    $date     = date('r');
    $msgId    = '<' . uniqid('resumeiqx', true) . '@resumeiqx.ai>';

    $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

    $message = "Date: {$date}\r\n"
             . "Message-ID: {$msgId}\r\n"
             . "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromAddr}>\r\n"
             . "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n"
             . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n"
             . "X-Mailer: ResumeIQ-X\r\n"
             . "\r\n"
             . "--{$boundary}\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: base64\r\n\r\n"
             . chunk_split(base64_encode($plainText)) . "\r\n"
             . "--{$boundary}\r\n"
             . "Content-Type: text/html; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: base64\r\n\r\n"
             . chunk_split(base64_encode($htmlBody)) . "\r\n"
             . "--{$boundary}--\r\n"
             . "\r\n.\r\n";

    fputs($socket, $message);
    $r = _smtpRead($socket);

    if (strpos($r, '250') === false) {
        error_log("[ResumeIQ-X][EMAIL] DATA send failed: {$r}");
        return false;
    }

    _smtpWrite($socket, "QUIT");
    error_log("[ResumeIQ-X][EMAIL] Email sent successfully to {$toEmail}");
    return true;
}

/* ──────────────────────────────────────────────
   METHOD 3: PHP mail() fallback
   ────────────────────────────────────────────── */
function _mailFallback(
    string $toEmail, string $toName,
    string $subject, string $htmlBody,
    string $fromAddr, string $fromName
): bool {
    error_log("[ResumeIQ-X][EMAIL] Trying PHP mail() fallback");

    $headers  = "From: {$fromName} <{$fromAddr}>\r\n";
    $headers .= "Reply-To: {$fromAddr}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: ResumeIQ-X\r\n";

    $result = @mail($toEmail, $subject, $htmlBody, $headers);
    if ($result) {
        error_log("[ResumeIQ-X][EMAIL] mail() fallback succeeded for {$toEmail}");
    } else {
        error_log("[ResumeIQ-X][EMAIL] mail() fallback also failed for {$toEmail}");
    }
    return $result;
}

/* ──────────────────────────────────────────────
   SMTP I/O helpers
   ────────────────────────────────────────────── */
function _smtpWrite($socket, string $cmd): void
{
    fputs($socket, $cmd . "\r\n");
}

function _smtpRead($socket): string
{
    $line = fgets($socket, 1024);
    return $line !== false ? trim($line) : '';
}

function _smtpReadMulti($socket): string
{
    $response = '';
    while ($line = fgets($socket, 1024)) {
        $response .= $line;
        // Multi-line responses have '-' after the code; single/last line has ' '
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    return $response;
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

    // If no pre-built link passed, build from APP_URL
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
