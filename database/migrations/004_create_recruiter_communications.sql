-- Migration: Create recruiter_communications table
-- Description: Logs all emails sent by recruiters to candidates

CREATE TABLE IF NOT EXISTS recruiter_communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    candidate_id INT NOT NULL,
    job_posting_id INT,
    email_subject VARCHAR(255) NOT NULL,
    email_body TEXT NOT NULL,
    template_used VARCHAR(100),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE SET NULL,
    
    INDEX idx_recruiter (recruiter_id),
    INDEX idx_candidate (candidate_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback script
-- DROP TABLE IF EXISTS recruiter_communications;
