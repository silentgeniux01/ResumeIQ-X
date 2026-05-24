-- Fix users table schema for recruiter registration
-- Add company_name column if it doesn't exist

-- Check if column exists and add it if missing
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) NULL AFTER role;

-- Verify the change
DESCRIBE users;

-- Expected columns after this fix:
-- id, name, email, mobile, password, role, company_name, account_status, 
-- email_verified, mobile_verified, created_at, updated_at
