-- Add company_name column to users table
-- Run this in your Railway database

ALTER TABLE users ADD COLUMN company_name VARCHAR(255) NULL AFTER role;

-- Verify the column was added
DESCRIBE users;

-- Check if there are any existing recruiter accounts that need the column
SELECT id, name, email, role FROM users WHERE role = 'recruiter' LIMIT 5;
