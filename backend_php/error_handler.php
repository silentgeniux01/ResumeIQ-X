<?php
/*
==================================================
ResumeIQ-X Centralized Error Handler
Standardized error responses with HTTP status codes
==================================================
*/

/**
 * Send a standardized error response and exit
 */
function sendError(string $message, int $httpCode = 400, string $errorCode = '', array $details = []): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json');

    $response = [
        'status'     => false,
        'message'    => $message,
        'error_code' => $errorCode ?: _httpCodeToErrorCode($httpCode),
    ];

    // Only include details in development mode
    if (!empty($details) && env('APP_ENV', 'production') === 'development') {
        $response['details'] = $details;
    }

    echo json_encode($response);
    exit;
}

/**
 * Send a standardized success response
 */
function sendSuccess(string $message, array $data = [], int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json');

    $response = ['status' => true, 'message' => $message];
    if (!empty($data)) $response['data'] = $data;

    echo json_encode($response);
    exit;
}

/**
 * Map HTTP code to error code string
 */
function _httpCodeToErrorCode(int $code): string
{
    return match($code) {
        400 => 'VALIDATION_ERROR',
        401 => 'AUTH_REQUIRED',
        403 => 'ACCESS_DENIED',
        404 => 'NOT_FOUND',
        405 => 'METHOD_NOT_ALLOWED',
        409 => 'CONFLICT',
        500 => 'SERVER_ERROR',
        default => 'UNKNOWN_ERROR'
    };
}

/**
 * Log an error with context
 */
function logError(string $context, string $message, array $extra = []): void
{
    $timestamp = date('Y-m-d H:i:s');
    $extraStr  = !empty($extra) ? ' | ' . json_encode($extra) : '';
    error_log("[ResumeIQ-X][{$context}][{$timestamp}] {$message}{$extraStr}");
}

/**
 * Log a recruiter action to the activity table
 */
function logRecruiterActivity(int $recruiterId, string $actionType, string $description, string $entityType = '', int $entityId = 0): void
{
    try {
        require_once __DIR__ . '/db.php';
        $db   = getDatabaseConnection();
        $stmt = $db->prepare("INSERT INTO recruiter_activity (recruiter_id, action_type, action_description, related_entity_type, related_entity_id) VALUES (:rid, :action, :desc, :etype, :eid)");
        $stmt->execute([
            ':rid'    => $recruiterId,
            ':action' => $actionType,
            ':desc'   => $description,
            ':etype'  => $entityType ?: null,
            ':eid'    => $entityId   ?: null,
        ]);
    } catch (Exception $e) {
        logError('ActivityLog', 'Failed to log activity: ' . $e->getMessage());
    }
}

/**
 * Validate required POST fields
 * Returns array of missing field names, or empty array if all present
 */
function validateRequired(array $fields, array $source = null): array
{
    $source  = $source ?? $_POST;
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($source[$field]) || trim((string)$source[$field]) === '') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Sanitize string input to prevent XSS
 */
function sanitizeString(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Require POST method or send error
 */
function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Method not allowed', 405);
    }
}

/**
 * Require GET method or send error
 */
function requireGet(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Method not allowed', 405);
    }
}

/**
 * Parse JSON body from request
 */
function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Get POST or JSON body param
 */
function getParam(string $key, $default = null)
{
    // Try POST first
    if (isset($_POST[$key])) return $_POST[$key];
    // Try JSON body
    static $jsonBody = null;
    if ($jsonBody === null) $jsonBody = getJsonBody();
    return $jsonBody[$key] ?? $default;
}
