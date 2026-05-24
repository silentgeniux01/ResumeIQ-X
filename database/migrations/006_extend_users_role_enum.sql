-- Migration: Extend users table role enum to include 'recruiter'
-- Description: Adds 'recruiter' as a valid role in the users table

-- Modify the role enum to include 'recruiter'
ALTER TABLE users 
    MODIFY COLUMN role ENUM('candidate', 'admin', 'recruiter') DEFAULT 'candidate';

-- Add company_name column for recruiters
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) AFTER mobile;

-- Rollback script
-- ALTER TABLE users 
--     MODIFY COLUMN role ENUM('candidate', 'admin') DEFAULT 'candidate';
-- ALTER TABLE users DROP COLUMN IF EXISTS company_name;
