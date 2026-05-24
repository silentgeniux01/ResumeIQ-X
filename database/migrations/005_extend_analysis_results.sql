-- Migration: Extend analysis_results table for LLM analysis
-- Description: Adds columns for LLM-powered resume analysis data

-- Check if analysis_results table exists, if not create it
CREATE TABLE IF NOT EXISTS analysis_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    user_id INT NOT NULL,
    analysis_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_resume (resume_id),
    INDEX idx_user (user_id),
    INDEX idx_status (analysis_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add new columns for LLM analysis (use IF NOT EXISTS equivalent)
ALTER TABLE analysis_results
    ADD COLUMN IF NOT EXISTS overall_score INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS match_percentage INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS candidate_name VARCHAR(255),
    ADD COLUMN IF NOT EXISTS candidate_email VARCHAR(255),
    ADD COLUMN IF NOT EXISTS candidate_phone VARCHAR(20),
    ADD COLUMN IF NOT EXISTS experience_years INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS education JSON,
    ADD COLUMN IF NOT EXISTS skills JSON,
    ADD COLUMN IF NOT EXISTS strengths JSON,
    ADD COLUMN IF NOT EXISTS weaknesses JSON,
    ADD COLUMN IF NOT EXISTS recommendations JSON,
    ADD COLUMN IF NOT EXISTS detected_sector VARCHAR(100),
    ADD COLUMN IF NOT EXISTS suitable_job_titles JSON,
    ADD COLUMN IF NOT EXISTS candidate_summary TEXT,
    ADD COLUMN IF NOT EXISTS llm_provider_used VARCHAR(50),
    ADD COLUMN IF NOT EXISTS analysis_timestamp TIMESTAMP NULL;

-- Add indexes for filtering
ALTER TABLE analysis_results
    ADD INDEX IF NOT EXISTS idx_overall_score (overall_score),
    ADD INDEX IF NOT EXISTS idx_match_percentage (match_percentage),
    ADD INDEX IF NOT EXISTS idx_detected_sector (detected_sector);

-- Rollback script
-- ALTER TABLE analysis_results
--     DROP COLUMN overall_score,
--     DROP COLUMN match_percentage,
--     DROP COLUMN candidate_name,
--     DROP COLUMN candidate_email,
--     DROP COLUMN candidate_phone,
--     DROP COLUMN experience_years,
--     DROP COLUMN education,
--     DROP COLUMN skills,
--     DROP COLUMN strengths,
--     DROP COLUMN weaknesses,
--     DROP COLUMN recommendations,
--     DROP COLUMN detected_sector,
--     DROP COLUMN suitable_job_titles,
--     DROP COLUMN candidate_summary,
--     DROP COLUMN llm_provider_used,
--     DROP COLUMN analysis_timestamp;
