<?php

/*
==================================================
ResumeIQ-X Database Engine v2
Environment-Variable-First Architecture
PDO Singleton + Helper Functions
==================================================
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

/*
==================================================
DATABASE SINGLETON
==================================================
*/

class Database {

    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct() {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            PDO::ATTR_TIMEOUT            => 5,
        ];

        $attempts = 2;
        while ($attempts > 0) {
            try {
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                return;
            } catch (PDOException $e) {
                $attempts--;
                if ($attempts === 0) {
                    http_response_code(503);
                    echo json_encode([
                        'status'  => false,
                        'message' => 'Database connection failed. Please try again later.',
                    ]);
                    exit;
                }
                usleep(200000);
            }
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function healthCheck(): bool {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

/*
==================================================
GLOBAL HELPERS
==================================================
*/

function getDatabaseConnection(): PDO {
    return Database::getInstance()->getConnection();
}

function checkDatabaseStatus(): array {
    $db = Database::getInstance();
    return $db->healthCheck()
        ? ['status' => true,  'message' => 'Database connected successfully']
        : ['status' => false, 'message' => 'Database connection failed'];
}

function userExists(string $email, string $mobile): bool {
    $db   = getDatabaseConnection();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email OR mobile = :mobile LIMIT 1');
    $stmt->execute([':email' => $email, ':mobile' => $mobile]);
    return $stmt->fetch() !== false;
}

function createUser(string $name, string $email, string $mobile, string $password, string $role = 'candidate'): bool {
    $db             = getDatabaseConnection();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt           = $db->prepare(
        'INSERT INTO users (name, email, mobile, password, role, account_status)
         VALUES (:name, :email, :mobile, :password, :role, \'active\')'
    );
    try {
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':mobile'   => $mobile,
            ':password' => $hashedPassword,
            ':role'     => $role,
        ]);
        return (int) $db->lastInsertId() > 0;
    } catch (PDOException $e) {
        error_log('[ResumeIQ-X] createUser failed: ' . $e->getMessage());
        return false;
    }
}

function updateAnalysisStatus(int $resumeId, string $status, int $progress = 0): bool {
    try {
        $db   = getDatabaseConnection();
        $stmt = $db->prepare(
            'UPDATE resumes SET analysis_status = :status, analysis_progress = :progress, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':status' => $status, ':progress' => $progress, ':id' => $resumeId]);
        return true;
    } catch (Exception $e) {
        error_log('[ResumeIQ-X] updateAnalysisStatus failed: ' . $e->getMessage());
        return false;
    }
}

/*
==================================================
EMAIL VERIFICATION HELPERS
==================================================
*/

/**
 * Create a user with account_status = 'pending' and store OTP.
 * Returns the new user ID or 0 on failure.
 */
function createPendingUser(string $name, string $email, string $mobile, string $password, string $role, string $otp): int {
    $db             = getDatabaseConnection();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $otpExpiry      = date('Y-m-d H:i:s', time() + 900); // 15 minutes

    $stmt = $db->prepare(
        'INSERT INTO users (name, email, mobile, password, role, account_status, email_verified, verification_otp, otp_expiry)
         VALUES (:name, :email, :mobile, :password, :role, \'pending\', 0, :otp, :otp_expiry)'
    );
    try {
        $stmt->execute([
            ':name'       => $name,
            ':email'      => $email,
            ':mobile'     => $mobile,
            ':password'   => $hashedPassword,
            ':role'       => $role,
            ':otp'        => $otp,
            ':otp_expiry' => $otpExpiry,
        ]);
        return (int) $db->lastInsertId();
    } catch (PDOException $e) {
        error_log('[ResumeIQ-X] createPendingUser failed: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Verify OTP for a given email.
 * On success: sets email_verified=1, account_status='active', clears OTP.
 * Returns true on success, false on failure/expiry.
 */
function verifyEmailOTP(string $email, string $otp): bool {
    $db   = getDatabaseConnection();
    $stmt = $db->prepare(
        'SELECT id, verification_otp, otp_expiry FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return false;
    if ($user['verification_otp'] !== $otp) return false;
    if (strtotime($user['otp_expiry']) < time()) return false;

    $upd = $db->prepare(
        'UPDATE users SET email_verified = 1, account_status = \'active\',
         verification_otp = NULL, otp_expiry = NULL WHERE id = :id'
    );
    $upd->execute([':id' => $user['id']]);
    return true;
}

/**
 * Resend a new OTP to an existing pending user.
 * Returns the new OTP string or empty string on failure.
 */
function refreshVerificationOTP(string $email): string {
    $db   = getDatabaseConnection();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email AND account_status = \'pending\' LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return '';

    $otp       = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $otpExpiry = date('Y-m-d H:i:s', time() + 900);

    $upd = $db->prepare(
        'UPDATE users SET verification_otp = :otp, otp_expiry = :expiry WHERE id = :id'
    );
    $upd->execute([':otp' => $otp, ':expiry' => $otpExpiry, ':id' => $user['id']]);
    return $otp;
}

/*
==================================================
MOBILE OTP HELPERS
==================================================
*/

/**
 * Store a mobile OTP for a user (by email).
 * Returns the OTP string or empty on failure.
 */
function storeMobileOTP(string $email): string {
    $db   = getDatabaseConnection();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return '';

    $otp    = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', time() + 900); // 15 minutes

    try {
        $upd = $db->prepare(
            'UPDATE users SET mobile_otp = :otp, mobile_otp_expiry = :expiry WHERE id = :id'
        );
        $upd->execute([':otp' => $otp, ':expiry' => $expiry, ':id' => $user['id']]);
        return $otp;
    } catch (PDOException $e) {
        error_log('[ResumeIQ-X] storeMobileOTP failed: ' . $e->getMessage());
        return '';
    }
}

/**
 * Verify mobile OTP for a given email.
 * On success: sets mobile_verified=1, clears mobile OTP.
 */
function verifyMobileOTP(string $email, string $otp): bool {
    $db   = getDatabaseConnection();
    $stmt = $db->prepare(
        'SELECT id, mobile_otp, mobile_otp_expiry FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return false;
    if (($user['mobile_otp'] ?? '') !== $otp) return false;
    if (!$user['mobile_otp_expiry'] || strtotime($user['mobile_otp_expiry']) < time()) return false;

    $upd = $db->prepare(
        'UPDATE users SET mobile_verified = 1, mobile_otp = NULL, mobile_otp_expiry = NULL WHERE id = :id'
    );
    $upd->execute([':id' => $user['id']]);
    return true;
}
