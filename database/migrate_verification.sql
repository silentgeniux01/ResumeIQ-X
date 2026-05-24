/*
==================================================
ResumeIQ-X Migration: Email Verification + Cloud
Run this on your Railway MySQL database to add
the new columns required for email OTP verification.
==================================================
*/

-- Add email verification columns to users table
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS verification_otp VARCHAR(10) NULL,
  ADD COLUMN IF NOT EXISTS otp_expiry DATETIME NULL;

-- Add reset token columns if not already present
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS token_expiry DATETIME NULL;

-- Mark all existing users as email-verified so they are not locked out
UPDATE users SET email_verified = 1 WHERE email_verified = 0;

-- Add missing resume columns
ALTER TABLE resumes ADD COLUMN IF NOT EXISTS analysis_progress INT DEFAULT 0;
ALTER TABLE resumes ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Ensure password_resets table exists
CREATE TABLE IF NOT EXISTS password_resets (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(255) NOT NULL,
  reset_token VARCHAR(255) NOT NULL,
  expiry_time DATETIME     NOT NULL,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_token (reset_token),
  INDEX idx_email (email)
);
