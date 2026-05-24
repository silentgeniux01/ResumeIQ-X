<?php
/*
==================================================
Test Company Name Column Existence
==================================================
*/

header("Content-Type: application/json");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

try {
    $db = getDatabaseConnection();
    
    // Check database name
    $stmt = $db->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    
    // Check if company_name column exists
    $stmt = $db->prepare("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = :db 
          AND TABLE_NAME = 'users' 
          AND COLUMN_NAME = 'company_name'
    ");
    $stmt->execute([':db' => $dbName]);
    $columnInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all columns in users table
    $stmt = $db->query("SHOW COLUMNS FROM users");
    $allColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Try a test insert (will rollback)
    $db->beginTransaction();
    try {
        $testStmt = $db->prepare("
            INSERT INTO users (
                name, email, mobile, password, role, company_name,
                account_status, email_verified, mobile_verified
            ) VALUES (
                'TEST', 'test@test.com', '1234567890', 'test', 'recruiter', 'TEST_COMPANY',
                'active', 1, 1
            )
        ");
        $testStmt->execute();
        $db->rollback(); // Rollback the test insert
        $insertTest = "SUCCESS - Column exists and accepts data";
    } catch (PDOException $e) {
        $db->rollback();
        $insertTest = "FAILED - " . $e->getMessage();
    }
    
    echo json_encode([
        "status" => true,
        "database_name" => $dbName,
        "database_host" => DB_HOST,
        "database_port" => DB_PORT,
        "company_name_column_exists" => $columnInfo ? true : false,
        "company_name_column_info" => $columnInfo,
        "insert_test" => $insertTest,
        "all_columns" => array_column($allColumns, 'Field'),
        "total_columns" => count($allColumns)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
