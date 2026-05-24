<?php

/*
==================================================
ResumeIQ-X Enterprise Configuration Engine v2
Environment-Variable-First Architecture
Zero Hardcoded Credentials
Cross-Platform Compatible
==================================================
*/

/*
==================================================
ENV FILE LOADER
Parses .env file from project root
==================================================
*/

function loadEnvFile() {
    $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!file_exists($envPath)) {
        return;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Strip surrounding quotes
        if (preg_match('/^(["\'])(.*)\\1$/', $value, $m)) {
            $value = $m[2];
        }
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

loadEnvFile();

/*
==================================================
HELPER: Read env with fallback
==================================================
*/

function env($key, $default = '') {
    $value = $_ENV[$key] ?? getenv($key);
    return ($value !== false && $value !== '') ? $value : $default;
}

/*
==================================================
ENVIRONMENT MODE
==================================================
*/

define('APP_ENV',  env('APP_ENV',  'production'));
define('APP_NAME', env('APP_NAME', 'ResumeIQ-X'));
define('APP_URL',  env('APP_URL',  'http://localhost'));

/*
==================================================
DATABASE CONFIGURATION
==================================================
*/

define('DB_HOST',    env('DB_HOST',    'monorail.proxy.rlwy.net'));
define('DB_PORT',    (int) env('DB_PORT', 33459));
define('DB_NAME',    env('DB_NAME',    'railway'));
define('DB_USER',    env('DB_USER',    'root'));
define('DB_PASS',    env('DB_PASS',    ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

/*
==================================================
PYTHON ENGINE — CROSS-PLATFORM PATH RESOLUTION
==================================================
*/

$projectRoot = dirname(__DIR__);

// Detect OS for venv binary path
$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
$venvBin   = $isWindows ? 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe'
                        : 'bin'     . DIRECTORY_SEPARATOR . 'python3';

$defaultPythonPath = $projectRoot
    . DIRECTORY_SEPARATOR . 'ai_engine_python'
    . DIRECTORY_SEPARATOR . 'venv'
    . DIRECTORY_SEPARATOR . $venvBin;

$defaultPipelinePath = $projectRoot
    . DIRECTORY_SEPARATOR . 'ai_engine_python'
    . DIRECTORY_SEPARATOR . 'pipelines'
    . DIRECTORY_SEPARATOR . 'run_analysis.py';

$rawPython   = env('PYTHON_EXECUTABLE',    '');
$rawPipeline = env('PYTHON_PIPELINE_SCRIPT', '');

// Resolve relative paths against project root
$pythonPath   = $rawPython   ? (file_exists($rawPython)   ? $rawPython   : $projectRoot . DIRECTORY_SEPARATOR . $rawPython)   : $defaultPythonPath;
$pipelinePath = $rawPipeline ? (file_exists($rawPipeline) ? $rawPipeline : $projectRoot . DIRECTORY_SEPARATOR . $rawPipeline) : $defaultPipelinePath;

define('PYTHON_EXECUTABLE',      $pythonPath);
define('PYTHON_PIPELINE_SCRIPT', $pipelinePath);

/*
==================================================
NODE API ENDPOINT
==================================================
*/

define('NODE_API_URL',     env('NODE_API_URL',  'http://127.0.0.1:5000'));
define('NODE_AI_ENDPOINT', NODE_API_URL . '/api/upload');

/*
==================================================
CLOUDINARY CONFIGURATION
==================================================
*/

define('CLOUDINARY_CLOUD_NAME', env('CLOUDINARY_CLOUD_NAME', ''));
define('CLOUDINARY_API_KEY',    env('CLOUDINARY_API_KEY',    ''));
define('CLOUDINARY_API_SECRET', env('CLOUDINARY_API_SECRET', ''));

/*
==================================================
FILE UPLOAD SETTINGS
==================================================
*/

$maxMb = (int) env('MAX_UPLOAD_SIZE_MB', 10);
define('MAX_UPLOAD_SIZE', $maxMb * 1024 * 1024);

$allowedTypes = env('ALLOWED_FILE_TYPES', 'pdf,txt,doc,docx,png,jpg,jpeg');
define('ALLOWED_FILE_TYPES', serialize(array_map('trim', explode(',', $allowedTypes))));

/*
==================================================
UPLOAD DIRECTORY
==================================================
*/

$uploadDir = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'resumes' . DIRECTORY_SEPARATOR;
define('UPLOAD_DIRECTORY', $uploadDir);

/*
==================================================
TIMEZONE
==================================================
*/

date_default_timezone_set('Asia/Kolkata');

/*
==================================================
SYSTEM HEALTH CHECK
==================================================
*/

function configHealthCheck() {
    return [
        'environment'     => APP_ENV,
        'database_host'   => DB_HOST,
        'node_api'        => NODE_AI_ENDPOINT,
        'python_pipeline' => file_exists(PYTHON_PIPELINE_SCRIPT) ? 'found' : 'missing',
        'cloudinary'      => CLOUDINARY_CLOUD_NAME ? 'configured' : 'not_configured',
    ];
}
