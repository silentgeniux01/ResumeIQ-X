# ResumeIQ-X — Files & Their Purpose

## Backend PHP Files (`backend_php/`)

### Core Infrastructure
| File | Purpose |
|------|---------|
| `config.php` | Loads `.env` file, defines all constants (DB, Cloudinary, file limits) |
| `db.php` | PDO database connection, OTP helper functions |
| `session_guard.php` | Authentication middleware — `requireLogin()`, `requireAdmin()`, `requireRecruiter()`, `verifySession()`, ownership verification |
| `email_helper.php` | SMTP email engine — STARTTLS + SSL fallback, OTP emails, password reset emails |
| `sms_helper.php` | SMS gateway — Twilio, MSG91, Fast2SMS support with fallback |
| `llm_helper.php` | LLM fallback chain — 6 providers, prompt generation, response parsing, field normalization |
| `input_validator.php` | Input sanitization, XSS prevention, file upload validation, CSRF tokens |
| `error_handler.php` | Centralized error responses, HTTP status codes, activity logging |

### Authentication
| File | Purpose |
|------|---------|
| `register_user.php` | Candidate/recruiter registration — validates mobile (strips +91), bcrypt hash, sends OTPs |
| `admin_register.php` | Admin registration with same validation |
| `recruiter_register.php` | Recruiter registration with company_name field |
| `login_user.php` | Multi-role login — sets session variables based on role |
| `admin_login.php` | Admin-specific login (sets admin_id in session) |
| `recruiter_login.php` | Recruiter login |
| `logout.php` | Destroys session, redirects to login |
| `admin_logout.php` | Admin logout |
| `forgot_password.php` | Generates reset token, sends email with APP_URL link |
| `reset_password.php` | Validates token, updates password with bcrypt |
| `send_otp.php` | Unified OTP engine — send/verify email OTP and mobile OTP |
| `verify_email.php` | Email OTP verification |
| `verify_mobile.php` | Mobile OTP verification with SMS/email fallback |

### Resume Management
| File | Purpose |
|------|---------|
| `upload_resume.php` | Cloudinary upload — validates file, signs request, uploads, stores URL in DB |
| `download_resume.php` | Serves resume file for download |
| `delete_resume.php` | Deletes resume from DB (Cloudinary cleanup optional) |
| `check_status.php` | Returns analysis status + full analysis data for candidate status page |

### Analysis Engine
| File | Purpose |
|------|---------|
| `start_analysis.php` | Main analysis trigger — downloads PDF, extracts text, calls LLM chain, saves 72 columns |
| `get_analysis_preview.php` | Returns complete analysis data for the result viewer — handles both legacy and LLM columns |
| `get_admin_dashboard_resumes.php` | Returns resume queue for admin dashboard with status normalization |

### Recruiter System
| File | Purpose |
|------|---------|
| `create_job_posting.php` | Creates job with validation (title, description, skills JSON, employment type) |
| `get_job_postings.php` | Lists recruiter's jobs with application counts |
| `update_job_posting.php` | Updates job (ownership verified) |
| `delete_job_posting.php` | Deletes job with CASCADE (ownership verified) |
| `get_job_details.php` | Single job with accepted/rejected counts |
| `get_candidates.php` | Filtered, paginated candidate list with shortlist status |
| `get_candidate_details.php` | Full analysis report for a specific candidate |
| `shortlist_candidate.php` | Accept/reject with UPSERT (last operation wins) |
| `bulk_shortlist.php` | Bulk accept/reject with transaction |
| `get_shortlisted_candidates.php` | Lists accepted/rejected candidates |
| `send_candidate_email.php` | Sends email with template placeholder replacement |
| `get_email_templates.php` | Returns email templates from JSON file |
| `get_communication_history.php` | Email history for a candidate |
| `get_recruiter_dashboard.php` | Dashboard metrics with 5-minute session cache |
| `get_dashboard_charts.php` | Chart.js compatible data for bar/pie/funnel charts |
| `generate_candidate_pdf.php` | HTML report for browser print-to-PDF |

### Candidate Dashboard
| File | Purpose |
|------|---------|
| `get_candidate_dashboard.php` | Candidate's own dashboard data |

---

## Frontend Pages (`frontend/`)

### Candidate Flow
| File | Purpose |
|------|---------|
| `index.html` | Landing page with features, CTA buttons |
| `register.html` | Registration form — email OTP + mobile OTP inline verification |
| `user_login.html` | Login with animated background |
| `upload_resume.php` | Drag-and-drop resume upload with progress bar |
| `candidate_my_status.php` | Real-time analysis status tracker (polls every 10 seconds) |
| `analysis_result_viewer.php` | Full AI intelligence report with 8 Chart.js visualizations |
| `dashboard.php` | Candidate dashboard |
| `forgot_password.html` | Password reset request form |
| `reset_password.html` | New password form (uses token from URL) |
| `verify_email.html` | Email verification page |
| `about.html` | About page |
| `help.html` | Help/FAQ page |

### Admin Flow
| File | Purpose |
|------|---------|
| `admin_login.html` | Admin login with animated background |
| `admin_register.html` | Admin registration |
| `admin_dashboard.php` | Resume queue — real-time stats, progress bars, analyze/download/preview/delete |

### Recruiter Flow
| File | Purpose |
|------|---------|
| `recruiter_login.html` | Recruiter login |
| `recruiter_register.html` | Two-step registration with inline email OTP |
| `recruiter/dashboard.php` | Stats cards + 3 Chart.js charts + activity feed |
| `recruiter/job_postings.php` | Job CRUD with modal form |
| `recruiter/candidates.php` | Filtered table with bulk actions |
| `recruiter/candidate_details.php` | Full report with accept/reject/email/PDF buttons |
| `recruiter/shortlist.php` | Tabbed accepted/rejected view |
| `recruiter/communications.php` | Two-panel email composer with template auto-fill |

---

## JavaScript Files (`frontend/js/`)

| File | Purpose |
|------|---------|
| `api.js` | `apiUrl()` helper — resolves backend paths for any deployment |
| `app.js` | Global app utilities |
| `dashboard.js` | Candidate dashboard logic |
| `index.js` | Landing page interactions |
| `upload.js` | Resume upload with drag-and-drop |

---

## CSS Files (`frontend/css/` and `frontend/assets/css/`)

| File | Purpose |
|------|---------|
| `style.css` | Global styles |
| `dashboard.css` | Dashboard-specific styles |
| `upload.css` | Upload page styles |
| `recruiter.css` | Complete recruiter portal styles (sidebar, cards, tables, modals) |
| `fontawesome.min.css` | Font Awesome icons (local copy) |

---

## Python Files (`ai_engine_python/`)

| File | Purpose |
|------|---------|
| `utils/pdf_reader.py` | Multi-engine PDF extraction — PyMuPDF → pdfplumber → OCR → Image OCR |
| `pipelines/resume_pipeline.py` | Legacy pipeline (kept for reference) |
| `pipelines/run_analysis.py` | Legacy pipeline runner |

---

## Database Files (`database/`)

| File | Purpose |
|------|---------|
| `schema.sql` | Complete database schema |
| `run_migrations.php` | Migration runner — executes all SQL files in order |
| `migrations/001_create_job_postings.sql` | job_postings table |
| `migrations/002_create_candidate_applications.sql` | candidate_applications table |
| `migrations/003_create_shortlist_actions.sql` | shortlist_actions table |
| `migrations/004_create_recruiter_communications.sql` | recruiter_communications table |
| `migrations/005_extend_analysis_results.sql` | Adds LLM columns to analysis_results |
| `migrations/006_extend_users_role_enum.sql` | Adds 'recruiter' to role enum |
| `migrations/007_create_recruiter_activity.sql` | recruiter_activity audit table |
| `migrations/008_add_performance_indexes.sql` | Performance indexes |
| `migrations/009_ensure_analysis_results_user_id.sql` | Ensures user_id column exists |

---

## Configuration Files (Root)

| File | Purpose |
|------|---------|
| `.env` | All credentials — DB, LLM APIs, Cloudinary, Twilio, SMTP |
| `.env.example` | Template for .env (safe to commit) |
| `.htaccess` | Apache URL routing, security headers |
| `Dockerfile` | Docker container definition for Railway |
| `railway.toml` | Railway deployment configuration |
| `supervisord.conf` | Process management in Docker (Apache + PHP) |
| `.gitignore` | Excludes .env, node_modules, uploads, __pycache__ |

---

## Template Files

| File | Purpose |
|------|---------|
| `frontend/assets/templates/email_templates.json` | 5 email templates with placeholders |
| `frontend/assets/js/chart.min.js` | Chart.js 4.4.0 (local copy, no CDN) |
