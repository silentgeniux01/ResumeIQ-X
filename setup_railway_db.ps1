# Railway Database Setup Script for PowerShell
# This script executes the SQL file on Railway MySQL database

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  ResumeIQ-X Railway Database Setup" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Check if Railway CLI is installed
Write-Host "Checking Railway CLI..." -ForegroundColor Yellow
$railwayCheck = Get-Command railway -ErrorAction SilentlyContinue
if (-not $railwayCheck) {
    Write-Host "ERROR: Railway CLI not found!" -ForegroundColor Red
    Write-Host "Install it with: npm install -g @railway/cli" -ForegroundColor Yellow
    exit 1
}
Write-Host "✓ Railway CLI found" -ForegroundColor Green
Write-Host ""

# Check if MySQL client is available
Write-Host "Checking MySQL client..." -ForegroundColor Yellow
$mysqlCheck = Get-Command mysql -ErrorAction SilentlyContinue
if (-not $mysqlCheck) {
    Write-Host "WARNING: MySQL client not found in PATH" -ForegroundColor Yellow
    Write-Host "Trying XAMPP MySQL..." -ForegroundColor Yellow
    
    # Try common XAMPP paths
    $xamppPaths = @(
        "C:\xampp\mysql\bin\mysql.exe",
        "C:\XAMPP\mysql\bin\mysql.exe"
    )
    
    $mysqlPath = $null
    foreach ($path in $xamppPaths) {
        if (Test-Path $path) {
            $mysqlPath = $path
            Write-Host "✓ Found MySQL at: $mysqlPath" -ForegroundColor Green
            break
        }
    }
    
    if (-not $mysqlPath) {
        Write-Host "ERROR: MySQL client not found!" -ForegroundColor Red
        Write-Host "Please install MySQL or XAMPP" -ForegroundColor Yellow
        exit 1
    }
} else {
    $mysqlPath = "mysql"
    Write-Host "✓ MySQL client found" -ForegroundColor Green
}
Write-Host ""

# Read database credentials from .env
Write-Host "Reading database credentials from .env..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Write-Host "ERROR: .env file not found!" -ForegroundColor Red
    exit 1
}

$envContent = Get-Content ".env"
$dbHost = ($envContent | Select-String "^DB_HOST=(.+)$").Matches.Groups[1].Value
$dbPort = ($envContent | Select-String "^DB_PORT=(.+)$").Matches.Groups[1].Value
$dbName = ($envContent | Select-String "^DB_NAME=(.+)$").Matches.Groups[1].Value
$dbUser = ($envContent | Select-String "^DB_USER=(.+)$").Matches.Groups[1].Value
$dbPass = ($envContent | Select-String "^DB_PASS=(.+)$").Matches.Groups[1].Value

Write-Host "✓ Credentials loaded" -ForegroundColor Green
Write-Host "  Host: $dbHost" -ForegroundColor Gray
Write-Host "  Port: $dbPort" -ForegroundColor Gray
Write-Host "  Database: $dbName" -ForegroundColor Gray
Write-Host "  User: $dbUser" -ForegroundColor Gray
Write-Host ""

# Execute SQL file
Write-Host "Executing SQL file on Railway database..." -ForegroundColor Yellow
Write-Host "This may take a minute..." -ForegroundColor Gray
Write-Host ""

$sqlFile = "setup_database_railway.sql"
if (-not (Test-Path $sqlFile)) {
    Write-Host "ERROR: $sqlFile not found!" -ForegroundColor Red
    exit 1
}

# Build MySQL command
$mysqlCmd = "& `"$mysqlPath`" -h $dbHost -P $dbPort -u $dbUser -p$dbPass $dbName"

# Execute SQL file
try {
    Get-Content $sqlFile | & $mysqlPath -h $dbHost -P $dbPort -u $dbUser "-p$dbPass" $dbName
    
    Write-Host ""
    Write-Host "==================================================" -ForegroundColor Green
    Write-Host "  ✓ Database setup completed successfully!" -ForegroundColor Green
    Write-Host "==================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Default Admin Credentials:" -ForegroundColor Cyan
    Write-Host "  Email: admin@resumeiqx.ai" -ForegroundColor White
    Write-Host "  Password: admin123" -ForegroundColor White
    Write-Host ""
    Write-Host "⚠️  IMPORTANT: Change the admin password immediately!" -ForegroundColor Yellow
    Write-Host ""
    
} catch {
    Write-Host ""
    Write-Host "ERROR: Failed to execute SQL file" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
