<?php
// Router for PHP built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Health check
if ($uri === '/health.php') {
    require 'health.php';
    return true;
}

// Backend PHP files
if (preg_match('/^\/backend_php\//', $uri)) {
    $file = __DIR__ . $uri;
    if (file_exists($file) && is_file($file)) {
        require $file;
        return true;
    }
    http_response_code(404);
    echo "Backend file not found: $uri";
    return true;
}

// Root homepage
if ($uri === '/' || $uri === '') {
    $file = __DIR__ . '/frontend/index.html';
    if (file_exists($file)) {
        readfile($file);
        return true;
    }
}

// Try frontend directory for all other requests
$frontendFile = __DIR__ . '/frontend' . $uri;
if (file_exists($frontendFile) && is_file($frontendFile)) {
    // Serve the file directly
    $ext = pathinfo($frontendFile, PATHINFO_EXTENSION);
    $mimeTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'php' => 'text/html'
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    
    if ($ext === 'php') {
        require $frontendFile;
    } else {
        readfile($frontendFile);
    }
    return true;
}

// Try root directory
$rootFile = __DIR__ . $uri;
if (file_exists($rootFile) && is_file($rootFile)) {
    readfile($rootFile);
    return true;
}

// 404
http_response_code(404);
echo "File not found: $uri";
return true;
