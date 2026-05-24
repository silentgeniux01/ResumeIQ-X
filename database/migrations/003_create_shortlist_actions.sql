-- Migration: Create shortlist_actions table
-- Description: Stores recruiter accept/reject decisions for candidates

CREATE TABLE IF NOT EXISTS shortlist_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    candidate_id INT NOT NULL,
    job_posting_id INT NOT NULL,
    action_type ENUM('accepted', 'rejected') NOT NULL,
    action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE CASCADE,
    
    INDEX idx_recruiter (recruiter_id),
    INDEX idx_candidate (candidate_id),
    INDEX idx_job (job_posting_id),
    INDEX idx_action_type (action_type),
    UNIQUE KEY unique_shortlist (recruiter_id, candidate_id, job_posting_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback script
-- DROP TABLE IF EXISTS shortlist_actions;
