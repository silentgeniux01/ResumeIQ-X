-- ResumeIQ-X Database Setup Script for Railway
-- Database name: railway (already created by Railway)

USE railway;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    mobile_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_mobile (mobile),
    INDEX idx_verification_token (verification_token),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    mobile_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recruiters table
CREATE TABLE IF NOT EXISTS recruiters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20) UNIQUE NOT NULL,
    company VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    mobile_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resumes table
CREATE TABLE IF NOT EXISTS resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    analysis_result LONGTEXT,
    analysis_preview TEXT,
    talent_score DECIMAL(5,2),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    analyzed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_uploaded_at (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job postings table
CREATE TABLE IF NOT EXISTS job_postings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    location VARCHAR(255),
    salary_range VARCHAR(100),
    job_type ENUM('full-time', 'part-time', 'contract', 'internship') DEFAULT 'full-time',
    status ENUM('active', 'closed', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES recruiters(id) ON DELETE CASCADE,
    INDEX idx_recruiter_id (recruiter_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shortlisted candidates table
CREATE TABLE IF NOT EXISTS shortlisted_candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    resume_id INT NOT NULL,
    recruiter_id INT NOT NULL,
    notes TEXT,
    status ENUM('shortlisted', 'interviewed', 'offered', 'rejected') DEFAULT 'shortlisted',
    shortlisted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_postings(id) ON DELETE CASCADE,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    FOREIGN KEY (recruiter_id) REFERENCES recruiters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_job_resume (job_id, resume_id),
    INDEX idx_job_id (job_id),
    INDEX idx_resume_id (resume_id),
    INDEX idx_recruiter_id (recruiter_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Communication history table
CREATE TABLE IF NOT EXISTS communication_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    recruiter_id INT NOT NULL,
    type ENUM('email', 'sms', 'call', 'note') NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    FOREIGN KEY (recruiter_id) REFERENCES recruiters(id) ON DELETE CASCADE,
    INDEX idx_resume_id (resume_id),
    INDEX idx_recruiter_id (recruiter_id),
    INDEX idx_type (type),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP verification table
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    type ENUM('email', 'mobile') NOT NULL,
    purpose ENUM('registration', 'login', 'password_reset') NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_identifier (identifier),
    INDEX idx_otp (otp),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI chat history table
CREATE TABLE IF NOT EXISTS ai_chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    user_id INT,
    user_type ENUM('user', 'admin', 'recruiter', 'guest') DEFAULT 'guest',
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    provider VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin123 - CHANGE THIS!)
INSERT INTO admins (name, email, mobile, password, email_verified, mobile_verified) 
VALUES (
    'Admin',
    'admin@resumeiqx.ai',
    '+1234567890',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    1,
    1
) ON DUPLICATE KEY UPDATE id=id;

-- Create views for dashboard statistics
CREATE OR REPLACE VIEW admin_dashboard_stats AS
SELECT 
    COUNT(*) as total_resumes,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_resumes,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_resumes,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_resumes,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_resumes,
    AVG(CASE WHEN talent_score IS NOT NULL THEN talent_score ELSE NULL END) as avg_talent_score
FROM resumes;

CREATE OR REPLACE VIEW recruiter_dashboard_stats AS
SELECT 
    r.id as recruiter_id,
    COUNT(DISTINCT jp.id) as total_jobs,
    COUNT(DISTINCT sc.id) as total_shortlisted,
    COUNT(DISTINCT CASE WHEN jp.status = 'active' THEN jp.id END) as active_jobs
FROM recruiters r
LEFT JOIN job_postings jp ON r.id = jp.recruiter_id
LEFT JOIN shortlisted_candidates sc ON r.id = sc.recruiter_id
GROUP BY r.id;

SELECT 'Database setup completed successfully!' as message;
