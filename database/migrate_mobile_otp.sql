-- ResumeIQ-X Migration: Add mobile OTP verification columns
-- Run this on your Railway MySQL database

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS mobile_otp VARCHAR(10) NULL,
  ADD COLUMN IF NOT EXISTS mobile_otp_expiry DATETIME NULL,
  ADD COLUMN IF NOT EXISTS mobile_verified TINYINT(1) NOT NULL DEFAULT 0;

-- Mark existing users as mobile verified so they are not locked out
UPDATE users SET mobile_verified = 1 WHERE mobile_verified = 0 AND account_status = 'active';
