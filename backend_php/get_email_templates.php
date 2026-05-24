<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session_guard.php';

$session = verifySession();
if (!$session) { http_response_code(401); echo json_encode(["status"=>false,"message"=>"Authentication required"]); exit; }
if ($session['role'] !== 'recruiter') { http_response_code(403); echo json_encode(["status"=>false,"message"=>"Recruiter role required"]); exit; }

$templateFile = __DIR__ . '/../frontend/assets/templates/email_templates.json';
if (!file_exists($templateFile)) { http_response_code(500); echo json_encode(["status"=>false,"message"=>"Templates not found"]); exit; }

$templates = json_decode(file_get_contents($templateFile), true);
echo json_encode(["status"=>true,"data"=>["templates"=>$templates]]);
