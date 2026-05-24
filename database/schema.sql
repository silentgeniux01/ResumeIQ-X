/*
==================================================
ResumeIQ-X Database Schema
AI Based Resume Analyzer & Job Recommender Intelligence
Production Grade Relational Design
Enhanced Multi-User + Admin Workflow Version
==================================================
*/


/*
==================================================
USERS TABLE
Stores candidates / recruiters / admins
Duplicate Prevention Enabled
==================================================
*/

CREATE TABLE users (

id INT AUTO_INCREMENT PRIMARY KEY,

name VARCHAR(255) NOT NULL,

email VARCHAR(255) UNIQUE NOT NULL,

mobile VARCHAR(15) UNIQUE,

password VARCHAR(255) NOT NULL,

role ENUM('candidate','recruiter','admin') DEFAULT 'candidate',

account_status ENUM('active','pending','blocked') DEFAULT 'active',

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);



/*
==================================================
RESUMES TABLE
Tracks uploaded resumes
Admin Controlled Analysis Workflow Enabled
==================================================
*/

CREATE TABLE resumes (

id INT AUTO_INCREMENT PRIMARY KEY,

user_id INT,

file_name VARCHAR(255),

file_path VARCHAR(500),

file_type VARCHAR(50),

analysis_status ENUM(
'pending',
'processing',
'completed'
) DEFAULT 'pending',

analyzed_by_admin BOOLEAN DEFAULT FALSE,

upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (user_id)

REFERENCES users(id)

ON DELETE SET NULL

);



/*
==================================================
ANALYSIS RESULTS TABLE
Core AI pipeline output storage
Extended Cognitive Intelligence Compatible
==================================================
*/

CREATE TABLE analysis_results (

id INT AUTO_INCREMENT PRIMARY KEY,

resume_id INT,

resume_strength_score FLOAT,

confidence_score FLOAT,

career_readiness_score FLOAT,

talent_category VARCHAR(255),

semantic_role_scores JSON,

domain_distribution JSON,

skill_maturity JSON,

missing_dependencies JSON,

learning_recommendations JSON,

similar_candidates JSON,

trajectory_prediction JSON,

reasoning_signals JSON,

capability_vector JSON,

candidate_signal_profile JSON,

latent_skill_report JSON,

career_direction_vector JSON,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (resume_id)

REFERENCES resumes(id)

ON DELETE CASCADE

);



/*
==================================================
JOB ROLES TABLE
Semantic role intelligence dataset
==================================================
*/

CREATE TABLE job_roles (

id INT AUTO_INCREMENT PRIMARY KEY,

role_name VARCHAR(255),

required_skills JSON,

description TEXT,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);



/*
==================================================
SKILL GRAPH TABLE
Future knowledge graph expansion
==================================================
*/

CREATE TABLE skill_graph (

id INT AUTO_INCREMENT PRIMARY KEY,

skill_name VARCHAR(255),

domain VARCHAR(255),

difficulty_level VARCHAR(50),

related_skills JSON,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);



/*
==================================================
RECRUITER ACTIVITY TABLE
Tracks recruiter interactions
==================================================
*/

CREATE TABLE recruiter_activity (

id INT AUTO_INCREMENT PRIMARY KEY,

recruiter_id INT,

candidate_id INT,

action_type VARCHAR(100),

action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (recruiter_id)

REFERENCES users(id)

ON DELETE CASCADE

);



/*
==================================================
VECTOR STORE TABLE
Stores semantic embeddings
Future Retrieval Engine Compatible
==================================================
*/

CREATE TABLE candidate_vectors (

id INT AUTO_INCREMENT PRIMARY KEY,

resume_id INT,

embedding JSON,

vector_dimension INT DEFAULT 384,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (resume_id)

REFERENCES resumes(id)

ON DELETE CASCADE

);



/*
==================================================
ADMIN ACTION LOG TABLE
Tracks Admin Resume Analysis Events
==================================================
*/

CREATE TABLE admin_activity_log (

id INT AUTO_INCREMENT PRIMARY KEY,

admin_id INT,

resume_id INT,

action_type VARCHAR(100),

action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (admin_id)

REFERENCES users(id)

ON DELETE CASCADE

);



/*
==================================================
SYSTEM PIPELINE STATUS TABLE
Tracks runtime execution states
==================================================
*/

CREATE TABLE pipeline_status (

id INT AUTO_INCREMENT PRIMARY KEY,

resume_id INT,

pipeline_state ENUM(
'queued',
'running',
'completed',
'failed'
) DEFAULT 'queued',

execution_time FLOAT,

updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);
/*
==================================================
PASSWORD RESET TOKEN TABLE
Secure Token Based Reset Engine
==================================================
*/

CREATE TABLE password_resets (

id INT AUTO_INCREMENT PRIMARY KEY,

email VARCHAR(255) NOT NULL,

reset_token VARCHAR(255) NOT NULL,

expiry_time DATETIME NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);
ALTER TABLE users

ADD reset_token VARCHAR(255) NULL,

ADD token_expiry DATETIME NULL;


-- ============================================
-- Email & Mobile Verification Columns
-- ============================================
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS verification_otp VARCHAR(10) NULL,
  ADD COLUMN IF NOT EXISTS otp_expiry DATETIME NULL;

-- Set existing users as verified so they are not locked out
UPDATE users SET email_verified = 1 WHERE email_verified = 0;

-- ============================================
-- Add missing columns used by the application
-- ============================================
ALTER TABLE resumes ADD COLUMN IF NOT EXISTS analysis_progress INT DEFAULT 0;
ALTER TABLE resumes ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
