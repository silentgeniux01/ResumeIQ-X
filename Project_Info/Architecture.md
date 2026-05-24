# ResumeIQ-X — System Architecture

## 1. High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                              │
│  Browser (Chrome/Edge/Firefox)                                   │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐   │
│  │Candidate │ │  Admin   │ │Recruiter │ │  Analysis Viewer │   │
│  │  Pages   │ │Dashboard │ │ Portal   │ │   (Chart.js)     │   │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────────┬─────────┘   │
└───────┼────────────┼────────────┼─────────────────┼─────────────┘
        │            │            │                 │
        ▼            ▼            ▼                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                      WEB SERVER LAYER                            │
│  Apache (XAMPP local) / Railway (production)                     │
│  .htaccess routing rules                                         │
└─────────────────────────────────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────────────────────────┐
│                      PHP BACKEND LAYER                           │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │
│  │   Auth      │  │  Analysis   │  │    Recruiter System     │ │
│  │  Module     │  │   Engine    │  │                         │ │
│  │             │  │             │  │  Job Postings CRUD      │ │
│  │ register    │  │ start_      │  │  Candidate Filtering    │ │
│  │ login       │  │ analysis    │  │  Shortlisting           │ │
│  │ logout      │  │ .php        │  │  Email Communication    │ │
│  │ OTP verify  │  │             │  │  Dashboard Stats        │ │
│  └──────┬──────┘  └──────┬──────┘  └──────────┬──────────────┘ │
│         │                │                     │                │
│  ┌──────▼──────────────────────────────────────▼──────────────┐ │
│  │                    CORE HELPERS                             │ │
│  │  config.php  │  db.php  │  session_guard.php               │ │
│  │  email_helper.php  │  sms_helper.php  │  llm_helper.php    │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
        │                    │                    │
        ▼                    ▼                    ▼
┌──────────────┐  ┌──────────────────┐  ┌────────────────────────┐
│   DATABASE   │  │   LLM PROVIDERS  │  │   EXTERNAL SERVICES    │
│              │  │                  │  │                        │
│ Railway MySQL│  │ 1. Groq          │  │ Cloudinary (files)     │
│              │  │ 2. OpenAI        │  │ Twilio (SMS)           │
│ users        │  │ 3. Gemini        │  │ Gmail SMTP (email)     │
│ resumes      │  │ 4. Anthropic     │  │                        │
│ analysis_    │  │ 5. DeepSeek      │  │                        │
│ results      │  │ 6. Ollama LOCAL  │  │                        │
│ job_postings │  │                  │  │                        │
│ shortlist_   │  │  Fallback Chain  │  │                        │
│ actions      │  │  (auto-switch)   │  │                        │
└──────────────┘  └──────────────────┘  └────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────────────────────────┐
│                    PYTHON LAYER                                  │
│  ai_engine_python/utils/pdf_reader.py                           │
│                                                                  │
│  Engine 1: PyMuPDF (fitz) — fast, handles most PDFs            │
│  Engine 2: pdfplumber — layout-aware extraction                 │
│  Engine 3: OCR (pytesseract) — scanned/image PDFs              │
│  Engine 4: Image OCR (PIL + tesseract) — image resumes         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. LLM Fallback Chain Architecture

```
Resume Text Ready
       │
       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LLM FALLBACK CHAIN                           │
│                    (llm_helper.php)                             │
│                                                                  │
│  MEERA_FORCE_PROVIDER set? ──YES──► Try forced provider first  │
│         │                                                        │
│         NO                                                       │
│         │                                                        │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  1. GROQ (llama-3.3-70b-versatile)                      │   │
│  │     Rate limited? → try llama3-8b-8192 → gemma2-9b-it  │   │
│  │     ✓ Success → Return result                           │   │
│  │     ✗ All fail → Next provider                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│         │ FAIL                                                   │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  2. OPENAI (gpt-4o-mini)                                │   │
│  │     OPENAI_QUOTA_EXCEEDED=1? → Skip                     │   │
│  │     ✓ Success → Return result                           │   │
│  │     ✗ Fail → Next provider                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│         │ FAIL                                                   │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  3. GEMINI                                              │   │
│  │     Try: gemini-2.0-flash-lite → gemini-2.0-flash      │   │
│  │          → gemini-2.5-flash → gemini-flash-latest      │   │
│  │     ✓ Success → Return result                           │   │
│  │     ✗ All fail → Next provider                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│         │ FAIL                                                   │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  4. ANTHROPIC (claude-3-5-haiku)                        │   │
│  │     ✓ Success → Return result                           │   │
│  │     ✗ Fail → Next provider                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│         │ FAIL                                                   │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  5. DEEPSEEK (deepseek-chat)                            │   │
│  │     ✓ Success → Return result                           │   │
│  │     ✗ Fail → Next provider                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│         │ FAIL                                                   │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  6. OLLAMA LOCAL (llama3.1:latest)                      │   │
│  │     Runs on localhost:11434                             │   │
│  │     Works OFFLINE — no internet needed                  │   │
│  │     ✓ Success → Return result                           │   │
│  │     ✗ Fail → Return error (all providers failed)        │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Database Schema Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE SCHEMA                            │
│                                                                  │
│  users (core)                                                    │
│  ├── id, name, email, mobile, password                          │
│  ├── role: candidate | admin | recruiter                        │
│  ├── email_verified, mobile_verified                            │
│  ├── verification_otp, mobile_otp (with expiry)                 │
│  └── company_name (for recruiters)                              │
│                                                                  │
│  resumes                                                         │
│  ├── id, user_id (FK → users)                                   │
│  ├── file_name, file_path (Cloudinary URL)                      │
│  ├── file_type (pdf/docx/txt)                                   │
│  ├── analysis_status: pending|processing|completed|failed       │
│  └── analysis_progress (0-100)                                  │
│                                                                  │
│  analysis_results (72 columns)                                   │
│  ├── Core: resume_id, user_id, analysis_status                  │
│  ├── Legacy scores: resume_strength_score, confidence_score     │
│  ├── LLM scores: overall_score, match_percentage                │
│  ├── Candidate info: name, email, phone, experience_years       │
│  ├── Arrays (JSON): skills, education, strengths, weaknesses    │
│  ├── Arrays (JSON): recommendations, suitable_job_titles        │
│  ├── Intelligence: detected_sector, candidate_summary           │
│  ├── Vectors: semantic_role_scores, domain_distribution         │
│  ├── Vectors: skill_maturity, capability_vector                 │
│  └── Meta: llm_provider_used, analysis_timestamp               │
│                                                                  │
│  job_postings                                                    │
│  ├── id, recruiter_id (FK → users)                              │
│  ├── title, description, required_skills (JSON)                 │
│  ├── experience_required, salary_range, location                │
│  └── employment_type, status                                    │
│                                                                  │
│  candidate_applications                                          │
│  ├── job_posting_id (FK), candidate_id (FK)                     │
│  └── status: pending|reviewed|shortlisted|rejected              │
│                                                                  │
│  shortlist_actions                                               │
│  ├── recruiter_id, candidate_id, job_posting_id                 │
│  └── action_type: accepted|rejected                             │
│                                                                  │
│  recruiter_communications                                        │
│  ├── recruiter_id, candidate_id                                 │
│  └── email_subject, email_body, template_used                   │
│                                                                  │
│  otp_temp                                                        │
│  ├── email, otp_type (email|mobile)                             │
│  └── otp_code, expiry, verified                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. Authentication Flow Architecture

```
Registration Flow:
User fills form → POST /register_user.php
    │
    ├── Validate inputs (email format, 10-digit mobile, 6+ char password)
    ├── Strip country code from mobile (+91XXXXXXXXXX → XXXXXXXXXX)
    ├── Check email uniqueness
    ├── bcrypt hash password (cost=10)
    ├── Generate 6-digit email OTP (random_int, cryptographically secure)
    ├── Generate 6-digit mobile OTP
    ├── Store in users table (account_status='pending')
    ├── Send email OTP via Gmail SMTP
    └── Send mobile OTP via Twilio SMS (fallback to email if SMS fails)

Verification Flow:
User enters OTP → POST /send_otp.php (type=verify_email)
    │
    ├── Lookup OTP in otp_temp table
    ├── Check expiry (10 minutes)
    ├── Compare OTP (constant-time comparison)
    ├── Mark email_verified=1 in users table
    └── Return success

Login Flow:
User submits credentials → POST /login_user.php
    │
    ├── Fetch user by email
    ├── password_verify() against bcrypt hash
    ├── Check email_verified=1
    ├── session_regenerate_id(true) — prevent session fixation
    ├── Set $_SESSION[user_id, name, email, user_role]
    └── Redirect to role-specific dashboard
```

---

## 5. Analysis Pipeline Architecture

```
Admin clicks Analyze → POST /start_analysis.php
    │
    ├── STEP 1: Authenticate (admin or recruiter role)
    ├── STEP 2: Fetch resume from DB (get Cloudinary URL)
    ├── STEP 3: Download PDF from Cloudinary to temp file
    │
    ├── STEP 4: Extract Text (extractResumeTextLLM)
    │   ├── Try Python pdf_reader.py (PyMuPDF → pdfplumber → OCR)
    │   ├── Try pdftotext (if installed)
    │   └── Raw PDF extraction (BT/ET markers, parentheses)
    │
    ├── STEP 5: Sanitize text (UTF-8 clean, remove control chars)
    ├── STEP 6: Update DB status → 'processing' (progress=10%)
    │
    ├── STEP 7: LLM Analysis (analyzeResumeWithLLM)
    │   └── [See LLM Fallback Chain above]
    │
    ├── STEP 8: Map LLM output to 72 DB columns
    │   ├── New LLM columns: overall_score, skills, strengths, etc.
    │   └── Legacy columns: resume_strength_score, talent_category, etc.
    │
    ├── STEP 9: Upsert into analysis_results (INSERT ... ON DUPLICATE KEY UPDATE)
    ├── STEP 10: Update resume status → 'completed' (progress=100%)
    └── STEP 11: Return JSON response to admin dashboard
```

---

## 6. Deployment Architecture

```
Local Development:
┌─────────────────────────────────────────────────────────────────┐
│  XAMPP (Windows)                                                │
│  ├── Apache → serves PHP files                                  │
│  ├── PHP 8+ → processes backend                                 │
│  ├── Python 3.14 → PDF extraction                               │
│  └── Ollama → local LLM fallback                               │
│                                                                  │
│  External Services:                                             │
│  ├── Railway MySQL (cloud DB)                                   │
│  ├── Cloudinary (file storage)                                  │
│  ├── Twilio (SMS)                                               │
│  └── Gmail SMTP (email)                                         │
└─────────────────────────────────────────────────────────────────┘

Production (Railway):
┌─────────────────────────────────────────────────────────────────┐
│  Docker Container                                               │
│  ├── supervisord manages processes                              │
│  ├── Apache + PHP                                               │
│  ├── Python 3                                                   │
│  └── Environment variables from Railway dashboard              │
│                                                                  │
│  Railway Services:                                              │
│  ├── Web Service (Docker container)                             │
│  └── MySQL Service (managed database)                           │
└─────────────────────────────────────────────────────────────────┘
```
