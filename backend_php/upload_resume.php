<?php

/*
==================================================
ResumeIQ-X Resume Upload Controller
Cloudinary Cloud Storage Version
Uploads resume to Cloudinary and stores URL in DB
==================================================
*/

require_once __DIR__ . "/session_guard.php";
requireUser();

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/config.php";

header("Content-Type: application/json");


/*
==================================================
VALIDATE FILE PRESENT
==================================================
*/

if (!isset($_FILES["resume"]) || $_FILES["resume"]["error"] !== UPLOAD_ERR_OK) {
    $errCode = $_FILES["resume"]["error"] ?? -1;
    echo json_encode([
        "status"  => false,
        "message" => "Resume file missing or upload error (code: {$errCode})",
    ]);
    exit;
}


$user_id  = $_SESSION["user_id"];
$file     = $_FILES["resume"];
$fileName = $file["name"];
$tmpPath  = $file["tmp_name"];
$fileSize = $file["size"];
$fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));


/*
==================================================
VALIDATE FILE TYPE
==================================================
*/

$allowedTypes = unserialize(ALLOWED_FILE_TYPES);
if (!in_array($fileExt, $allowedTypes)) {
    echo json_encode([
        "status"  => false,
        "message" => "File type not allowed. Allowed: " . implode(", ", $allowedTypes),
    ]);
    exit;
}


/*
==================================================
VALIDATE FILE SIZE
==================================================
*/

if ($fileSize > MAX_UPLOAD_SIZE) {
    $maxMb = MAX_UPLOAD_SIZE / (1024 * 1024);
    echo json_encode([
        "status"  => false,
        "message" => "File too large. Maximum size: {$maxMb}MB",
    ]);
    exit;
}


/*
==================================================
CLOUDINARY UPLOAD (no Composer — uses REST API)
==================================================
*/

$cloudName = CLOUDINARY_CLOUD_NAME;
$apiKey    = CLOUDINARY_API_KEY;
$apiSecret = CLOUDINARY_API_SECRET;

if (!$cloudName || !$apiKey || !$apiSecret) {
    echo json_encode([
        "status"  => false,
        "message" => "Cloudinary not configured. Check environment variables.",
    ]);
    exit;
}

// Build Cloudinary signed upload parameters
// public_id already contains the folder path — do NOT include folder separately
$timestamp = time();
$publicId  = "resumeiqx/resumes/resume_{$user_id}_" . $timestamp;

// Only sign the params you actually send (must match exactly)
$sigParams = [
    "public_id" => $publicId,
    "timestamp" => $timestamp,
];
ksort($sigParams);

$sigString = "";
foreach ($sigParams as $k => $v) {
    $sigString .= "{$k}={$v}&";
}
$sigString = rtrim($sigString, "&");
$signature = sha1($sigString . $apiSecret);

// Upload via cURL multipart POST
$uploadUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/raw/upload";

$postFields = [
    "file"      => new CURLFile($tmpPath, mime_content_type($tmpPath), $fileName),
    "api_key"   => $apiKey,
    "timestamp" => $timestamp,
    "signature" => $signature,
    "public_id" => $publicId,
    // NOTE: no "folder" param — it's already embedded in public_id
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $uploadUrl,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response   = curl_exec($ch);
$curlError  = curl_error($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError || $httpCode !== 200) {
    error_log("[ResumeIQ-X] Cloudinary upload failed: HTTP {$httpCode} | cURL: {$curlError} | Response: {$response}");
    echo json_encode([
        "status"  => false,
        "message" => "Cloud upload failed. Please try again.",
    ]);
    exit;
}

$cloudResult = json_decode($response, true);

if (empty($cloudResult["secure_url"])) {
    error_log("[ResumeIQ-X] Cloudinary response missing secure_url: " . $response);
    echo json_encode([
        "status"  => false,
        "message" => "Cloud upload response invalid.",
    ]);
    exit;
}

$cloudUrl      = $cloudResult["secure_url"];
$cloudPublicId = $cloudResult["public_id"] ?? $publicId;


/*
==================================================
ALSO SAVE LOCAL COPY (for Python AI engine access)
==================================================
*/

$uploadDirectory = dirname(__DIR__) . "/uploads/resumes/";
if (!file_exists($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}

$secureFileName = $timestamp . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $fileName);
$localPath      = $uploadDirectory . $secureFileName;
move_uploaded_file($tmpPath, $localPath);   // best-effort; non-fatal if it fails


/*
==================================================
DATABASE INSERT
Store Cloudinary URL as file_path
==================================================
*/

$db   = getDatabaseConnection();
$stmt = $db->prepare("
    INSERT INTO resumes
        (user_id, file_name, file_path, file_type, analysis_status, analysis_progress)
    VALUES
        (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $secureFileName,
    $cloudUrl,          // Cloudinary HTTPS URL stored as file_path
    $fileExt,
    "pending",
    0,
]);

$resume_id = $db->lastInsertId();


/*
==================================================
SUCCESS RESPONSE
==================================================
*/

echo json_encode([
    "status"    => true,
    "resume_id" => $resume_id,
    "cloud_url" => $cloudUrl,
    "message"   => "Resume uploaded successfully to cloud storage",
]);

exit;
