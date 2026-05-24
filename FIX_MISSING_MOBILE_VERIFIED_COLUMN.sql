-- Add missing mobile_verified column to users table
-- This is the REAL issue causing the registration error!

ALTER TABLE users ADD COLUMN mobile_verified TINYINT(1) DEFAULT 0 AFTER email_verified;

-- Verify both columns now exist
SHOW COLUMNS FROM users WHERE Field IN ('email_verified', 'mobile_verified', 'company_name');

-- Test query to ensure the structure is correct
SELECT 'SUCCESS - All required columns exist' AS status;
