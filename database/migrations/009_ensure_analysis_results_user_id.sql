-- Migration: Ensure analysis_results has user_id column
-- The original schema.sql didn't include user_id, this adds it safely

ALTER TABLE analysis_results
    ADD COLUMN IF NOT EXISTS user_id INT NULL,
    ADD COLUMN IF NOT EXISTS analysis_status VARCHAR(20) DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS analysis_progress INT DEFAULT 0;

-- Add index on user_id if not exists
ALTER TABLE analysis_results
    ADD INDEX IF NOT EXISTS idx_user_id (user_id);

-- Rollback:
-- ALTER TABLE analysis_results DROP COLUMN IF EXISTS user_id;
-- ALTER TABLE analysis_results DROP COLUMN IF EXISTS analysis_progress;
