-- Migration: Add performance indexes for recruiter dashboard queries
-- These indexes optimize the most common query patterns

-- analysis_results: filtering by score and sector
ALTER TABLE analysis_results
    ADD INDEX IF NOT EXISTS idx_overall_score (overall_score),
    ADD INDEX IF NOT EXISTS idx_detected_sector (detected_sector),
    ADD INDEX IF NOT EXISTS idx_experience_years (experience_years),
    ADD INDEX IF NOT EXISTS idx_analysis_status (analysis_status);

-- shortlist_actions: recruiter + action_type lookups
ALTER TABLE shortlist_actions
    ADD INDEX IF NOT EXISTS idx_recruiter_action (recruiter_id, action_type);

-- recruiter_activity: recent activity feed
ALTER TABLE recruiter_activity
    ADD INDEX IF NOT EXISTS idx_recruiter_created (recruiter_id, created_at);

-- candidate_applications: status filtering
ALTER TABLE candidate_applications
    ADD INDEX IF NOT EXISTS idx_status (status);

-- job_postings: active jobs by recruiter
ALTER TABLE job_postings
    ADD INDEX IF NOT EXISTS idx_recruiter_status (recruiter_id, status);

-- Rollback:
-- ALTER TABLE analysis_results DROP INDEX idx_overall_score, DROP INDEX idx_detected_sector, DROP INDEX idx_experience_years, DROP INDEX idx_analysis_status;
-- ALTER TABLE shortlist_actions DROP INDEX idx_recruiter_action;
-- ALTER TABLE recruiter_activity DROP INDEX idx_recruiter_created;
-- ALTER TABLE candidate_applications DROP INDEX idx_status;
-- ALTER TABLE job_postings DROP INDEX idx_recruiter_status;
