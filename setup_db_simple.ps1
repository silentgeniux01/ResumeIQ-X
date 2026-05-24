# Simple Railway Database Setup - Execute SQL commands one by one

Write-Host "Setting up Railway database..." -ForegroundColor Cyan
Write-Host ""

# Read SQL file
$sqlContent = Get-Content "setup_database_railway.sql" -Raw

# Split into individual statements (rough split by semicolon)
$statements = $sqlContent -split ";" | Where-Object { $_.Trim() -ne "" -and $_.Trim() -notmatch "^--" }

Write-Host "Found $($statements.Count) SQL statements to execute" -ForegroundColor Yellow
Write-Host ""

# Database credentials
$host = "monorail.proxy.rlwy.net"
$port = "33459"
$dbname = "railway"
$user = "root"
$pass = "FzOAGAJqKTQAyTjMoNszrzFHQEvXAlVr"

# Try to find mysql.exe
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
if (-not (Test-Path $mysqlPath)) {
    Write-Host "ERROR: MySQL client not found at $mysqlPath" -ForegroundColor Red
    exit 1
}

Write-Host "Using MySQL client: $mysqlPath" -ForegroundColor Green
Write-Host ""

# Execute each statement
$successCount = 0
$errorCount = 0

foreach ($stmt in $statements) {
    $cleanStmt = $stmt.Trim()
    if ($cleanStmt -eq "" -or $cleanStmt.StartsWith("--")) {
        continue
    }
    
    # Show first 50 chars of statement
    $preview = if ($cleanStmt.Length -gt 50) { $cleanStmt.Substring(0, 50) + "..." } else { $cleanStmt }
    Write-Host "Executing: $preview" -ForegroundColor Gray
    
    try {
        $cleanStmt | & $mysqlPath -h $host -P $port -u $user "-p$pass" $dbname 2>&1 | Out-Null
        $successCount++
        Write-Host "  ✓ Success" -ForegroundColor Green
    } catch {
        $errorCount++
        Write-Host "  ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  Execution Summary" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  Success: $successCount" -ForegroundColor Green
Write-Host "  Errors: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
Write-Host ""

if ($errorCount -eq 0) {
    Write-Host "✓ Database setup completed!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Default Admin Credentials:" -ForegroundColor Cyan
    Write-Host "  Email: admin@resumeiqx.ai" -ForegroundColor White
    Write-Host "  Password: admin123" -ForegroundColor White
    Write-Host ""
}
