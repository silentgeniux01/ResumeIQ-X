<?php
/*
==================================================
ResumeIQ-X Input Validator
SQL injection, XSS, and email injection prevention
All queries use prepared statements — this adds
an extra layer of output sanitization
==================================================
*/

/**
 * Sanitize a string for safe output (XSS prevention)
 */
function sanitizeOutput(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitize all string values in an array recursively
 */
function sanitizeArray(array $data): array
{
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $data[$key] = sanitizeOutput($value);
        } elseif (is_array($value)) {
            $data[$key] = sanitizeArray($value);
        }
    }
    return $data;
}

/**
 * Validate and sanitize email (prevents email injection)
 */
function sanitizeEmail(string $email): ?string
{
    $email = trim($email);
    // Remove newlines to prevent email header injection
    $email = str_replace(["\r", "\n", "\t", "%0a", "%0d", "%09"], '', $email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

/**
 * Validate integer within range
 */
function validateInt($value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): ?int
{
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int === false) return null;
    if ($int < $min || $int > $max) return null;
    return $int;
}

/**
 * Validate that a value is in an allowed list
 */
function validateEnum($value, array $allowed): ?string
{
    return in_array($value, $allowed, true) ? $value : null;
}

/**
 * Validate JSON string and return decoded array
 */
function validateJsonArray(string $json): ?array
{
    $decoded = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) return null;
    if (!is_array($decoded)) return null;
    return $decoded;
}

/**
 * Validate file upload
 * Returns error message or null if valid
 */
function validateFileUpload(array $file, array $allowedTypes = ['pdf','txt','doc','docx'], int $maxSizeMb = 10): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'File upload failed (error code: ' . ($file['error'] ?? 'unknown') . ')';
    }

    $maxBytes = $maxSizeMb * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return "File size exceeds {$maxSizeMb}MB limit";
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes, true)) {
        return 'File type not allowed. Allowed: ' . implode(', ', $allowedTypes);
    }

    // Check MIME type
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'pdf'  => 'application/pdf',
        'txt'  => 'text/plain',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];

    if (isset($allowedMimes[$ext]) && $mimeType !== $allowedMimes[$ext]) {
        // Allow text/plain for txt files even if detected differently
        if (!($ext === 'txt' && str_starts_with($mimeType, 'text/'))) {
            return 'File content does not match its extension';
        }
    }

    return null; // Valid
}

/**
 * Generate a CSRF token and store in session
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from request
 */
function validateCsrfToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $stored = $_SESSION['csrf_token'] ?? '';
    return hash_equals($stored, $token);
}

/**
 * Sanitize a URL to prevent open redirect
 */
function sanitizeRedirectUrl(string $url, string $defaultUrl = '/'): string
{
    // Only allow relative URLs or same-origin URLs
    if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
        return $url;
    }
    return $defaultUrl;
}
