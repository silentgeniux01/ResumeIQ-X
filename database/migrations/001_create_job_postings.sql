-- Migration: Create job_postings table
-- Description: Stores job advertisements created by recruiters

CREATE TABLE IF NOT EXISTS job_postings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    required_skills JSON NOT NULL,
    experience_required INT DEFAULT 0,
    salary_range VARCHAR(100),
    location VARCHAR(255),
    employment_type ENUM('full-time', 'part-time', 'contract') DEFAULT 'full-time',
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recruiter (recruiter_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback script
-- DROP TABLE IF EXISTS job_postings;
