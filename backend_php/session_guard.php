<?php

/*
==================================================
ResumeIQ-X Session Guard Middleware
Enterprise Multi-Role Authentication Layer
Production Ready Version
Supports:
admin | user | recruiter
JSON-safe + API-safe + dashboard-safe
==================================================
*/


/*
==================================================
SAFE SESSION START
==================================================
*/

if(session_status() === PHP_SESSION_NONE){

session_start();

}


/*
==================================================
JSON RESPONSE HELPER
(API SAFE ERROR OUTPUT)
==================================================
*/

function sessionError($message="Unauthorized",$code=401){

http_response_code($code);

header("Content-Type: application/json");

echo json_encode([
"status"=>false,
"message"=>$message
]);

exit;

}


/*
==================================================
SESSION INITIALIZATION HARDENING
Prevents Session Fixation Attacks
==================================================
*/

if(!isset($_SESSION["session_initialized"])){

session_regenerate_id(true);

$_SESSION["session_initialized"]=true;

}


/*
==================================================
CHECK LOGIN EXISTS
CRITICAL: Now checks role-specific session IDs
Supports:
user_id (candidate) | admin_id | recruiter_id
==================================================
*/

function requireLogin(){

if(

!isset($_SESSION["user_id"]) &&
!isset($_SESSION["admin_id"]) &&
!isset($_SESSION["recruiter_id"])

){

sessionError("Login required");

}

}


/*
==================================================
VALIDATE ROLE EXISTS AND MATCHES SESSION ID
CRITICAL: Prevents cross-role authentication
==================================================
*/

function requireRoleExists(){

requireLogin();

if(!isset($_SESSION["user_role"])){

sessionError("User role missing");

}

// CRITICAL: Validate role matches the correct session ID
$role = $_SESSION["user_role"];

if ($role === "candidate" && !isset($_SESSION["user_id"])) {
    sessionError("Invalid candidate session");
}

if ($role === "admin" && !isset($_SESSION["admin_id"])) {
    sessionError("Invalid admin session");
}

if ($role === "recruiter" && !isset($_SESSION["recruiter_id"])) {
    sessionError("Invalid recruiter session");
}

// CRITICAL: Ensure ONLY the correct session ID exists
if ($role === "candidate" && (isset($_SESSION["admin_id"]) || isset($_SESSION["recruiter_id"]))) {
    sessionError("Session contamination detected");
}

if ($role === "admin" && (isset($_SESSION["user_id"]) || isset($_SESSION["recruiter_id"]))) {
    sessionError("Session contamination detected");
}

if ($role === "recruiter" && (isset($_SESSION["user_id"]) || isset($_SESSION["admin_id"]))) {
    sessionError("Session contamination detected");
}

}


/*
==================================================
CHECK SINGLE ROLE ACCESS
Example:
requireRole("admin");
==================================================
*/

function requireRole($role){

requireRoleExists();

if($_SESSION["user_role"] !== $role){

sessionError("Access denied");

}

}


/*
==================================================
CHECK MULTIPLE ROLE ACCESS
Example:
requireAnyRole(["admin","recruiter"]);
==================================================
*/

function requireAnyRole($roles=[]){

requireRoleExists();

if(empty($roles)){

sessionError("Role configuration error",500);

}

if(!in_array($_SESSION["user_role"],$roles,true)){

sessionError("Access denied");

}

}


/*
==================================================
ROLE SHORTCUT HELPERS
Cleaner Enterprise Usage
==================================================
*/

function requireAdmin(){

requireRole("admin");

}


function requireUser(){
    // Accept both "candidate" and legacy "user" role
    requireAnyRole(["candidate","user"]);
}


function requireCandidate(){
    requireAnyRole(["candidate","user"]);
}


function requireRecruiter(){

requireRole("recruiter");

}


/*
==================================================
ROLE GROUP HELPERS
Enterprise Pipeline Access Control
==================================================
*/

function allowUserAndRecruiter(){

requireAnyRole([

"candidate",
"recruiter"

]);

}


function allowAdminAndRecruiter(){

requireAnyRole([

"admin",
"recruiter"

]);

}


function allowAllRoles(){

requireAnyRole([

"admin",
"candidate",
"recruiter"

]);

}

// ──────────────────────────────────────────────────────────────────────────────
// EXTENDED SESSION HELPERS (added for recruiter dashboard)
// ──────────────────────────────────────────────────────────────────────────────

/**
 * Verify session and return user data, or null if not authenticated
 */
function verifySession(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check session timeout (24 hours)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 86400)) {
        session_unset();
        session_destroy();
        return null;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'name' => $_SESSION['name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'],
        'company_name' => $_SESSION['company_name'] ?? null
    ];
}

/*
==================================================
VERIFY JOB POSTING OWNERSHIP
Returns true if recruiter owns the job posting
==================================================
*/

function verifyJobOwnership(int $jobId, int $recruiterId): bool
{
    require_once __DIR__ . '/db.php';
    $db = getDatabaseConnection();
    
    $stmt = $db->prepare("SELECT id FROM job_postings WHERE id = :job_id AND recruiter_id = :recruiter_id LIMIT 1");
    $stmt->execute([':job_id' => $jobId, ':recruiter_id' => $recruiterId]);
    
    return (bool) $stmt->fetch();
}

/*
==================================================
VERIFY RECRUITER CAN ACCESS CANDIDATE
Returns true if recruiter owns a job the candidate applied to
==================================================
*/

function verifyRecruiterCandidateAccess(int $candidateId, int $recruiterId): bool
{
    require_once __DIR__ . '/db.php';
    $db = getDatabaseConnection();
    
    // Check if recruiter has any job postings that this candidate applied to
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM candidate_applications ca
        JOIN job_postings jp ON ca.job_posting_id = jp.id
        WHERE ca.candidate_id = :candidate_id 
        AND jp.recruiter_id = :recruiter_id
    ");
    $stmt->execute([':candidate_id' => $candidateId, ':recruiter_id' => $recruiterId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

/*
==================================================
GET CURRENT USER ID
Returns current user ID or null
==================================================
*/

function getCurrentUserId(): ?int
{
    $session = verifySession();
    return $session ? $session['user_id'] : null;
}

/*
==================================================
GET CURRENT USER ROLE
Returns current user role or null
==================================================
*/

function getCurrentUserRole(): ?string
{
    $session = verifySession();
    return $session ? $session['role'] : null;
}
