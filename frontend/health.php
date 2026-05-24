<?php
// Simple healthcheck endpoint for Railway
header('Content-Type: application/json');
http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'service' => 'ResumeIQ-X',
    'timestamp' => time()
]);
