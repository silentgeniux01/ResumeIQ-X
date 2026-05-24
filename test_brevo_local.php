<?php
/*
Test Brevo SMTP locally
*/

$host = 'smtp-relay.brevo.com';
$port = 587;
$username = 'aa1349001@smtp-brevo.com';
$password = 'xsmtpsib-269fea00d3f34e8b7f5d494f9ed09e91a1d464b0b97c403cbb083c3d00034ea9-NhwzSm2MRL5Y9ZCR';
$from = 'mayurkove428@gmail.com';
$to = 'mayurkove428@gmail.com';

echo "Testing Brevo SMTP connection...\n\n";

$socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 10);

if (!$socket) {
    die("❌ Connection failed: {$errstr} (errno {$errno})\n");
}

echo "✅ Connected to {$host}:{$port}\n";

$response = fgets($socket);
echo "Server: {$response}";

fputs($socket, "EHLO test\r\n");
while ($line = fgets($socket)) {
    echo "Server: {$line}";
    if ($line[3] === ' ') break;
}

fputs($socket, "STARTTLS\r\n");
$response = fgets($socket);
echo "Server: {$response}";

if (strpos($response, '220') === false) {
    die("❌ STARTTLS not supported\n");
}

if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    die("❌ TLS upgrade failed\n");
}

echo "✅ TLS enabled\n";

fputs($socket, "EHLO test\r\n");
while ($line = fgets($socket)) {
    echo "Server: {$line}";
    if ($line[3] === ' ') break;
}

fputs($socket, "AUTH LOGIN\r\n");
$response = fgets($socket);
echo "Server: {$response}";

fputs($socket, base64_encode($username) . "\r\n");
$response = fgets($socket);
echo "Server: {$response}";

fputs($socket, base64_encode($password) . "\r\n");
$response = fgets($socket);
echo "Server: {$response}";

if (strpos($response, '235') !== false) {
    echo "✅ Authentication successful!\n";
    echo "\nBrevo SMTP is working correctly.\n";
    echo "The issue must be in the email_helper.php code.\n";
} else {
    echo "❌ Authentication failed!\n";
    echo "Check Brevo credentials.\n";
}

fputs($socket, "QUIT\r\n");
fclose($socket);
