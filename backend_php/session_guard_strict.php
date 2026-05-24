<?php
/*
==================================================
ResumeIQ-X Strict Session Guard
Role-Based Access Control with Session Isolation
Prevents cross-role authentication attacks
==================================================
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Validates that ONLY the specified role is logged in
 * Rejects any other role or missing session
 * 
 * @param string $requiredRole 'candidate', 'admin', or 'recruiter'
 * @param string $redirectUrl Where to redirect if unauthorized
 */
function requireStrictRole(string $requiredRole, string $redirectUrl = '../frontend/user_login.html'): void
{
    $requiredRole = strtolower(trim($requiredRole));
    
    // Define role-specific session ID keys
    $roleSessionKeys = [
        'candidate' => 'user_id',
        'admin'     => 'admin_id',
        'recruiter' => 'recruiter_id',
    ];
    
    if (!isset($roleSessionKeys[$requiredRole])) {
        error_log("[ResumeIQ-X][SessionGuard] Invalid role specified: {$requiredRole}");
        http_response_code(500);
        header("Location: {$redirectUrl}");
        exit;
    }
    
    $requiredSessionKey = $roleSessionKeys[$requiredRole];
    $currentRole = $_SESSION['user_role'] ?? null;
    
    // CRITICAL CHECK 1: Required session ID must exist
    if (!isset($_SESSION[$requiredSessionKey])) {
        error_log("[ResumeIQ-X][SessionGuard] Missing {$requiredRole} session ID");
        http_response_code(401);
        header("Location: {$redirectUrl}");
        exit;
    }
    
    // CRITICAL CHECK 2: Role must match exactly
    if ($currentRole !== $requiredRole) {
        error_log("[ResumeIQ-X][SessionGuard] Role mismatch. Expected: {$requiredRole}, Got: " . ($currentRole ?? 'none'));
        http_response_code(403);
        header("Location: {$redirectUrl}");
        exit;
    }
    
    // CRITICAL CHECK 3: Other role session IDs must NOT exist
    foreach ($roleSessionKeys as $role => $sessionKey) {
        if ($role !== $requiredRole && isset($_SESSION[$sessionKey])) {
            error_log("[ResumeIQ-X][SessionGuard] Session contamination detected. Found {$role} session in {$requiredRole} context");
            
            // Clear the contaminated session
            unset($_SESSION[$sessionKey]);
            
            // Reject access
            http_response_code(403);
            header("Location: {$redirectUrl}");
            exit;
        }
    }
    
    // CRITICAL CHECK 4: Session activity timeout (optional but recommended)
    $sessionLifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 7200); // 2 hours default
    $lastActivity = $_SESSION['last_activity'] ?? 0;
    
    if ($lastActivity > 0 && (time() - $lastActivity) > $sessionLifetime) {
        error_log("[ResumeIQ-X][SessionGuard] Session expired for {$requiredRole}");
        session_destroy();
        http_response_code(401);
        header("Location: {$redirectUrl}");
        exit;
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
    // All checks passed - access granted
    return;
}

/**
 * Get current authenticated user ID (role-specific)
 * 
 * @param string $role 'candidate', 'admin', or 'recruiter'
 * @return int|null User ID or null if not authenticated
 */
function getCurrentUserId(string $role): ?int
{
    $roleSessionKeys = [
        'candidate' => 'user_id',
        'admin'     => 'admin_id',
        'recruiter' => 'recruiter_id',
    ];
    
    $sessionKey = $roleSessionKeys[$role] ?? null;
    
    if (!$sessionKey || !isset($_SESSION[$sessionKey])) {
        return null;
    }
    
    return (int) $_SESSION[$sessionKey];
}

/**
 * Check if user is authenticated for specific role
 * 
 * @param string $role 'candidate', 'admin', or 'recruiter'
 * @return bool True if authenticated for that role
 */
function isAuthenticated(string $role): bool
{
    return getCurrentUserId($role) !== null;
}

/**
 * Destroy session and logout
 */
function destroySession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }
}
