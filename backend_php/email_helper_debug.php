<?php
/*
==================================================
Debug version of email helper - shows all SMTP communication
==================================================
*/

require_once __DIR__ . '/config.php';

function sendEmailDebug(string $toEmail, string $toName, string $subject, string $htmlBody): array
{
    $host     = env('MAIL_HOST',         'smtp.gmail.com');
    $port     = (int) env('MAIL_PORT',   587);
    $username = env('MAIL_USERNAME',     '');
    $password = env('MAIL_PASSWORD',     '');
    $fromName = env('MAIL_FROM_NAME',    'ResumeIQ-X');
    $fromAddr = env('MAIL_FROM_ADDRESS', $username);

    $log = [];
    $log[] = "=== SMTP DEBUG LOG ===";
    $log[] = "Host: {$host}:{$port}";
    $log[] = "Username: {$username}";
    $log[] = "From: {$fromAddr}";
    $log[] = "To: {$toEmail}";
    $log[] = "";

    if (!$username || !$password) {
        $log[] = "❌ ERROR: MAIL_USERNAME or MAIL_PASSWORD not set";
        return ['success' => false, 'log' => $log];
    }

    $log[] = "Connecting to {$host}:{$port}...";
    $socket = @stream_socket_client(
        "tcp://{$host}:{$port}", $errno, $errstr, 20, STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        $log[] = "❌ Connection failed: {$errstr} (errno {$errno})";
        return ['success' => false, 'log' => $log];
    }

    $log[] = "✅ Connected";
    stream_set_timeout($socket, 20);

    $banner = fgets($socket, 1024);
    $log[] = "< " . trim($banner);

    // EHLO
    fputs($socket, "EHLO resumeiqx.local\r\n");
    $log[] = "> EHLO resumeiqx.local";
    while ($line = fgets($socket, 1024)) {
        $log[] = "< " . trim($line);
        if (isset($line[3]) && $line[3] === ' ') break;
    }

    // STARTTLS
    fputs($socket, "STARTTLS\r\n");
    $log[] = "> STARTTLS";
    $tlsResp = fgets($socket, 1024);
    $log[] = "< " . trim($tlsResp);

    if (strpos($tlsResp, '220') === false) {
        $log[] = "❌ STARTTLS not accepted";
        fclose($socket);
        return ['success' => false, 'log' => $log];
    }

    // Upgrade to TLS
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        $log[] = "❌ TLS upgrade failed";
        fclose($socket);
        return ['success' => false, 'log' => $log];
    }

    $log[] = "✅ TLS enabled";

    // Re-EHLO
    fputs($socket, "EHLO resumeiqx.local\r\n");
    $log[] = "> EHLO resumeiqx.local";
    while ($line = fgets($socket, 1024)) {
        $log[] = "< " . trim($line);
        if (isset($line[3]) && $line[3] === ' ') break;
    }

    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $log[] = "> AUTH LOGIN";
    $r1 = fgets($socket, 1024);
    $log[] = "< " . trim($r1);

    fputs($socket, base64_encode($username) . "\r\n");
    $log[] = "> [username]";
    $r2 = fgets($socket, 1024);
    $log[] = "< " . trim($r2);

    fputs($socket, base64_encode($password) . "\r\n");
    $log[] = "> [password]";
    $r3 = fgets($socket, 1024);
    $log[] = "< " . trim($r3);

    if (strpos($r3, '235') === false) {
        $log[] = "❌ Authentication failed";
        fclose($socket);
        return ['success' => false, 'log' => $log];
    }

    $log[] = "✅ Authenticated";

    // MAIL FROM
    fputs($socket, "MAIL FROM:<{$fromAddr}>\r\n");
    $log[] = "> MAIL FROM:<{$fromAddr}>";
    $r = fgets($socket, 1024);
    $log[] = "< " . trim($r);

    // RCPT TO
    fputs($socket, "RCPT TO:<{$toEmail}>\r\n");
    $log[] = "> RCPT TO:<{$toEmail}>";
    $r = fgets($socket, 1024);
    $log[] = "< " . trim($r);

    // DATA
    fputs($socket, "DATA\r\n");
    $log[] = "> DATA";
    $r = fgets($socket, 1024);
    $log[] = "< " . trim($r);

    // Send email content
    $boundary = md5(uniqid('', true));
    $date = date('r');
    $msgId = '<' . uniqid('resumeiqx', true) . '@resumeiqx.ai>';

    $plainText = strip_tags($htmlBody);

    $message = "Date: {$date}\r\n"
             . "Message-ID: {$msgId}\r\n"
             . "From: {$fromName} <{$fromAddr}>\r\n"
             . "To: {$toName} <{$toEmail}>\r\n"
             . "Subject: {$subject}\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n"
             . "\r\n"
             . "--{$boundary}\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
             . $plainText . "\r\n"
             . "--{$boundary}\r\n"
             . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
             . $htmlBody . "\r\n"
             . "--{$boundary}--\r\n"
             . "\r\n.\r\n";

    fputs($socket, $message);
    $log[] = "> [email content]";
    $r = fgets($socket, 1024);
    $log[] = "< " . trim($r);

    if (strpos($r, '250') === false) {
        $log[] = "❌ Email send failed";
        fclose($socket);
        return ['success' => false, 'log' => $log];
    }

    $log[] = "✅ Email sent successfully!";

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return ['success' => true, 'log' => $log];
}

// Test
header("Content-Type: text/plain");

$result = sendEmailDebug(
    'mayurkove428@gmail.com',
    'Test User',
    'ResumeIQ-X — Debug Test',
    '<h1>Test Email</h1><p>This is a debug test.</p>'
);

echo implode("\n", $result['log']);
echo "\n\n";
echo $result['success'] ? "✅ SUCCESS" : "❌ FAILED";
