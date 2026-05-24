-- Migration: Create recruiter_activity table
-- Description: Logs all recruiter actions for audit trail and activity feed

CREATE TABLE IF NOT EXISTS recruiter_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_description TEXT,
    related_entity_type VARCHAR(50),
    related_entity_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recruiter (recruiter_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback script
-- DROP TABLE IF EXISTS recruiter_activity;
