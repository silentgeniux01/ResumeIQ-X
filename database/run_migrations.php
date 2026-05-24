<?php
/*
==================================================
ResumeIQ-X Database Migration Runner
Runs all migration files in order
==================================================
*/

require_once __DIR__ . '/../backend_php/config.php';
require_once __DIR__ . '/../backend_php/db.php';

echo "==============================================\n";
echo "ResumeIQ-X Database Migration Runner\n";
echo "==============================================\n\n";

$db = getDatabaseConnection();

// Get all migration files
$migrationFiles = glob(__DIR__ . '/migrations/*.sql');
sort($migrationFiles);

if (empty($migrationFiles)) {
    echo "No migration files found.\n";
    exit(0);
}

echo "Found " . count($migrationFiles) . " migration files.\n\n";

$successCount = 0;
$failCount = 0;

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    echo "Running migration: {$filename}... ";
    
    try {
        $sql = file_get_contents($file);
        
        // Remove comments and split by semicolon
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && 
                       !preg_match('/^--/', $stmt) &&
                       !preg_match('/^DROP TABLE/', $stmt); // Skip rollback statements
            }
        );
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $db->exec($statement);
            }
        }
        
        echo "✓ SUCCESS\n";
        $successCount++;
        
    } catch (PDOException $e) {
        echo "✗ FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

echo "\n==============================================\n";
echo "Migration Summary:\n";
echo "  Success: {$successCount}\n";
echo "  Failed:  {$failCount}\n";
echo "==============================================\n";

if ($failCount > 0) {
    echo "\n⚠️  Some migrations failed. Please check the errors above.\n";
    exit(1);
} else {
    echo "\n✓ All migrations completed successfully!\n";
    exit(0);
}
