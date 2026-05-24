-- Fix resumes table to ensure created_at column exists with proper default

-- Check if created_at column exists and has default value
-- If not, this will add it

ALTER TABLE resumes 
MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update any existing rows that have NULL created_at
UPDATE resumes 
SET created_at = NOW() 
WHERE created_at IS NULL;

-- Verify the table structure
DESCRIBE resumes;

-- Show sample data to verify
SELECT id, user_id, file_name, analysis_status, analysis_progress, created_at 
FROM resumes 
ORDER BY id DESC 
LIMIT 5;
