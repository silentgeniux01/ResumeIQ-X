<?php

/*
==================================================
ResumeIQ-X Recruiter Email Engine
Send Professional Job Recommendations to Candidates
==================================================
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once "db.php";
require_once "email_helper.php";


/*
==================================================
RECRUITER AUTHENTICATION CHECK
==================================================
*/

if (!isset($_SESSION['recruiter_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized. Recruiter login required."
    ]);
    exit;
}


/*
==================================================
ALLOW ONLY POST REQUESTS
==================================================
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}


/*
==================================================
FETCH INPUT DATA
==================================================
*/

$candidateId      = intval($_POST["candidate_id"] ?? 0);
$candidateName    = trim($_POST["candidate_name"] ?? "");
$candidateEmail   = trim($_POST["candidate_email"] ?? "");
$subject          = trim($_POST["subject"] ?? "");
$jobRecommendations = trim($_POST["job_recommendations"] ?? "");
$personalMessage  = trim($_POST["personal_message"] ?? "");


/*
==================================================
VALIDATE INPUT
==================================================
*/

if (!$candidateId || !$candidateName || !$candidateEmail || !$subject || !$jobRecommendations) {
    echo json_encode([
        "status" => false,
        "message" => "All required fields must be provided"
    ]);
    exit;
}

if (!filter_var($candidateEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email address"
    ]);
    exit;
}


/*
==================================================
GET RECRUITER INFO
==================================================
*/

$db = getDatabaseConnection();
$recruiterId = $_SESSION['recruiter_id'];

$stmt = $db->prepare("SELECT name, email FROM users WHERE id = :id AND role = 'recruiter' LIMIT 1");
$stmt->execute([":id" => $recruiterId]);
$recruiter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recruiter) {
    echo json_encode([
        "status" => false,
        "message" => "Recruiter account not found"
    ]);
    exit;
}

$recruiterName = $recruiter['name'];
$recruiterEmail = $recruiter['email'];


/*
==================================================
BUILD EMAIL HTML
==================================================
*/

$appName = env('APP_NAME', 'ResumeIQ-X');

// Escape HTML entities
$candidateNameSafe = htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8');
$recruiterNameSafe = htmlspecialchars($recruiterName, ENT_QUOTES, 'UTF-8');
$jobRecommendationsSafe = nl2br(htmlspecialchars($jobRecommendations, ENT_QUOTES, 'UTF-8'));
$personalMessageSafe = $personalMessage ? nl2br(htmlspecialchars($personalMessage, ENT_QUOTES, 'UTF-8')) : '';

$personalMessageBlock = $personalMessage 
    ? "<div style='background:rgba(99,102,241,.08);border-left:3px solid #6366f1;padding:1rem 1.2rem;border-radius:8px;margin:1.5rem 0;'>
         <p style='color:#e2e8f0;margin:0;font-size:15px;line-height:1.6;'>{$personalMessageSafe}</p>
       </div>"
    : '';

$htmlBody = "<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin:0;padding:0;background:#0f172a;font-family:Arial,sans-serif;'>
  <div style='max-width:600px;margin:0 auto;background:#0f172a;'>
    
    <!-- Header -->
    <div style='background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:2rem;text-align:center;'>
      <h1 style='margin:0;font-size:28px;color:#fff;'>🎉 Congratulations!</h1>
      <p style='margin:8px 0 0;color:#e0e7ff;font-size:15px;'>Exciting Career Opportunities Await</p>
    </div>
    
    <!-- Body -->
    <div style='padding:2rem;background:#1e293b;'>
      <p style='color:#e2e8f0;font-size:16px;margin:0 0 1rem;'>Hi <strong style='color:#a5b4fc;'>{$candidateNameSafe}</strong>,</p>
      
      <p style='color:#cbd5e1;font-size:15px;line-height:1.7;margin:0 0 1.5rem;'>
        We've reviewed your profile on <strong>{$appName}</strong> and are impressed with your skills and experience! 
        Based on your background, we'd like to share some exciting job opportunities that match your profile.
      </p>
      
      {$personalMessageBlock}
      
      <div style='background:#0f172a;border:1px solid rgba(99,102,241,.3);border-radius:12px;padding:1.5rem;margin:1.5rem 0;'>
        <h2 style='color:#a5b4fc;margin:0 0 1rem;font-size:18px;'>📋 Recommended Positions</h2>
        <div style='color:#e2e8f0;font-size:15px;line-height:1.8;'>
          {$jobRecommendationsSafe}
        </div>
      </div>
      
      <p style='color:#cbd5e1;font-size:15px;line-height:1.7;margin:1.5rem 0 0;'>
        These positions align well with your expertise and career trajectory. We believe you would be an excellent fit 
        and encourage you to explore these opportunities.
      </p>
      
      <p style='color:#cbd5e1;font-size:15px;line-height:1.7;margin:1rem 0 0;'>
        If you're interested in learning more about any of these roles, please reply to this email or reach out to us directly. 
        We're here to support your career growth!
      </p>
      
      <div style='margin:2rem 0 1rem;padding-top:1.5rem;border-top:1px solid rgba(255,255,255,.1);'>
        <p style='color:#94a3b8;font-size:14px;margin:0 0 .5rem;'>Best regards,</p>
        <p style='color:#e2e8f0;font-size:15px;font-weight:600;margin:0;'>{$recruiterNameSafe}</p>
        <p style='color:#94a3b8;font-size:14px;margin:.3rem 0 0;'>Talent Acquisition Specialist</p>
        <p style='color:#6366f1;font-size:14px;margin:.3rem 0 0;'>{$recruiterEmail}</p>
      </div>
    </div>
    
    <!-- Footer -->
    <div style='background:#0a0f1e;padding:1.5rem;text-align:center;'>
      <p style='color:#64748b;font-size:13px;margin:0 0 .5rem;'>
        Powered by <strong style='color:#a5b4fc;'>{$appName}</strong> — AI-Powered Career Intelligence
      </p>
      <p style='color:#475569;font-size:12px;margin:0;'>
        &copy; " . date('Y') . " {$appName}. All rights reserved.
      </p>
    </div>
    
  </div>
</body>
</html>";


/*
==================================================
SEND EMAIL
==================================================
*/

$emailSent = sendEmail($candidateEmail, $candidateName, $subject, $htmlBody);

if (!$emailSent) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to send email. Please check email configuration."
    ]);
    exit;
}


/*
==================================================
LOG RECRUITER ACTIVITY
==================================================
*/

try {
    $stmt = $db->prepare(
        "INSERT INTO recruiter_activity (recruiter_id, candidate_id, action_type) 
         VALUES (:recruiter_id, :candidate_id, 'email_sent')"
    );
    $stmt->execute([
        ":recruiter_id" => $recruiterId,
        ":candidate_id" => $candidateId
    ]);
} catch (Exception $e) {
    error_log("[ResumeIQ-X] Failed to log recruiter activity: " . $e->getMessage());
    // Non-fatal — email was sent successfully
}


/*
==================================================
SUCCESS RESPONSE
==================================================
*/

echo json_encode([
    "status" => true,
    "message" => "Email sent successfully to {$candidateName}",
    "recipient" => $candidateEmail
]);

exit;

