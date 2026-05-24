# ResumeIQ-X — Requirements

## 1. Functional Requirements

### 1.1 User Authentication System

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-01 | System shall allow candidates to register with name, email, mobile, password | High |
| FR-02 | System shall send email OTP for email verification | High |
| FR-03 | System shall send SMS OTP via Twilio for mobile verification | High |
| FR-04 | System shall support three roles: candidate, admin, recruiter | High |
| FR-05 | System shall implement bcrypt password hashing (cost factor 10) | High |
| FR-06 | System shall support password reset via email link | High |
| FR-07 | System shall maintain sessions for 24 hours | Medium |
| FR-08 | System shall regenerate session ID after login (session fixation prevention) | High |
| FR-09 | System shall support OTP expiry after 10 minutes | High |
| FR-10 | System shall fall back to email OTP if SMS fails | Medium |

### 1.2 Resume Upload System

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-11 | System shall accept PDF, DOCX, TXT, PNG, JPG resume formats | High |
| FR-12 | System shall upload resumes to Cloudinary cloud storage | High |
| FR-13 | System shall validate file size (max 10MB) | High |
| FR-14 | System shall validate file type by MIME type | High |
| FR-15 | System shall store Cloudinary URL in database | High |
| FR-16 | System shall save local copy for Python processing | Medium |

### 1.3 LLM Analysis Engine

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-17 | System shall extract text from PDF using PyMuPDF | High |
| FR-18 | System shall fall back to pdfplumber if PyMuPDF fails | Medium |
| FR-19 | System shall analyze resume using LLM with structured JSON prompt | High |
| FR-20 | System shall try providers in order: Groq → OpenAI → Gemini → Anthropic → DeepSeek → Ollama | High |
| FR-21 | System shall extract: name, email, phone, experience, education, skills | High |
| FR-22 | System shall generate: overall_score (0-100), match_percentage (0-100) | High |
| FR-23 | System shall detect sector automatically (engineering, medical, finance, etc.) | High |
| FR-24 | System shall generate strengths, weaknesses, recommendations | High |
| FR-25 | System shall suggest suitable job titles | Medium |
| FR-26 | System shall store results in 72-column analysis_results table | High |
| FR-27 | System shall work for ANY resume sector without configuration | High |

### 1.4 Admin Dashboard

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-28 | Admin shall view all uploaded resumes in a queue | High |
| FR-29 | Admin shall trigger LLM analysis for any resume | High |
| FR-30 | Admin shall see real-time progress during analysis | High |
| FR-31 | Admin shall see stats: total, pending, processing, completed | High |
| FR-32 | Admin shall download original resume files | Medium |
| FR-33 | Admin shall delete resumes | Medium |
| FR-34 | Admin shall preview analysis results | High |

### 1.5 Recruiter Dashboard

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-35 | Recruiter shall create job postings with title, description, skills, experience | High |
| FR-36 | Recruiter shall view candidates with AI-generated scores | High |
| FR-37 | Recruiter shall filter candidates by score, skills, experience, sector | High |
| FR-38 | Recruiter shall accept or reject candidates (single and bulk) | High |
| FR-39 | Recruiter shall view detailed analysis reports | High |
| FR-40 | Recruiter shall send emails to candidates with templates | High |
| FR-41 | Recruiter shall view dashboard with statistics and charts | High |
| FR-42 | Recruiter shall download candidate reports as PDF | Medium |

### 1.6 Candidate Dashboard

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-43 | Candidate shall see analysis status (pending/processing/completed) | High |
| FR-44 | Candidate shall view full AI intelligence report | High |
| FR-45 | Candidate shall see scores, skills, strengths, weaknesses | High |
| FR-46 | Candidate shall see career trajectory predictions | Medium |
| FR-47 | Candidate shall see learning recommendations | Medium |

---

## 2. Non-Functional Requirements

### 2.1 Performance

| ID | Requirement | Target |
|----|-------------|--------|
| NFR-01 | Dashboard load time | < 2 seconds |
| NFR-02 | Candidate filtering response | < 1 second for 1000 candidates |
| NFR-03 | LLM analysis completion | < 30 seconds |
| NFR-04 | File upload to Cloudinary | < 10 seconds |
| NFR-05 | API response time | < 500ms for DB queries |

### 2.2 Security

| ID | Requirement |
|----|-------------|
| NFR-06 | All passwords hashed with bcrypt (cost 10) |
| NFR-07 | All DB queries use prepared statements |
| NFR-08 | All user input sanitized (XSS prevention) |
| NFR-09 | Session cookies use HttpOnly flag |
| NFR-10 | File uploads validated by MIME type |
| NFR-11 | API keys stored in .env, never in code |
| NFR-12 | Role-based access control on all endpoints |

### 2.3 Reliability

| ID | Requirement |
|----|-------------|
| NFR-13 | LLM analysis must never permanently fail (Ollama local fallback) |
| NFR-14 | SMS must fall back to email if Twilio fails |
| NFR-15 | System must handle concurrent users |
| NFR-16 | Database connections must be persistent and pooled |

### 2.4 Scalability

| ID | Requirement |
|----|-------------|
| NFR-17 | System must be deployable to Railway cloud |
| NFR-18 | File storage must use cloud (Cloudinary) not local disk |
| NFR-19 | Database must be cloud MySQL (Railway) |
| NFR-20 | Docker containerization for horizontal scaling |

### 2.5 Usability

| ID | Requirement |
|----|-------------|
| NFR-21 | Dark theme UI consistent across all pages |
| NFR-22 | Responsive design for mobile and desktop |
| NFR-23 | Real-time feedback during long operations |
| NFR-24 | Clear error messages for all failure scenarios |

---

## 3. Technical Requirements

### 3.1 Server Requirements
- PHP 8.0+ with extensions: PDO, cURL, OpenSSL, ZipArchive
- Python 3.10+ with: PyMuPDF (fitz), pdfplumber
- Apache/Nginx web server
- MySQL 5.7+ or 8.0+

### 3.2 Client Requirements
- Modern browser (Chrome, Firefox, Edge, Safari)
- JavaScript enabled
- Minimum screen width: 320px

### 3.3 External Service Requirements
- Cloudinary account (free tier sufficient for development)
- At least one LLM API key (Groq recommended — free)
- Twilio account for SMS (trial account works)
- Gmail account with App Password for SMTP

### 3.4 Development Environment
- XAMPP (Windows) or LAMP (Linux)
- Python 3.14 with virtual environment
- Ollama installed with llama3.1 model

---

## 4. Database Requirements

### 4.1 Core Tables
- `users` — All user accounts (candidates, admins, recruiters)
- `resumes` — Uploaded resume files and analysis status
- `analysis_results` — 72-column AI analysis data

### 4.2 Recruiter Tables
- `job_postings` — Job advertisements
- `candidate_applications` — Candidate-to-job links
- `shortlist_actions` — Accept/reject decisions
- `recruiter_communications` — Email history
- `recruiter_activity` — Audit log

### 4.3 OTP Tables
- `otp_temp` — Temporary OTP storage with expiry
- `password_resets` — Password reset tokens

---

## 5. Compliance Requirements

| Requirement | Implementation |
|-------------|----------------|
| Data Privacy | Passwords never stored in plain text |
| Secure Transmission | HTTPS in production (Railway provides SSL) |
| Session Security | HttpOnly cookies, session regeneration |
| Input Validation | All inputs sanitized before DB storage |
| File Security | MIME type validation, size limits |
