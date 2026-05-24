# ResumeIQ-X — Project Details

## 1. Technology Stack (Complete)

### Backend Technologies

| Technology | Version | Role |
|-----------|---------|------|
| PHP | 8.0+ | Core backend language — all API endpoints |
| MySQL | 9.4 (Railway) | Primary database |
| Python | 3.14 | PDF text extraction |
| PyMuPDF (fitz) | Latest | Primary PDF reader |
| pdfplumber | Latest | Secondary PDF reader |
| pytesseract | Latest | OCR for scanned PDFs |

### Frontend Technologies

| Technology | Version | Role |
|-----------|---------|------|
| HTML5 | — | Page structure |
| CSS3 | — | Styling, animations, dark theme |
| Vanilla JavaScript | ES2022 | All interactivity |
| Chart.js | 4.4.0 | Data visualizations |
| Font Awesome | 6.4.0 | Icons |
| Google Fonts (Inter, Space Grotesk) | — | Typography |

### AI/LLM Providers

| Provider | Model | Role |
|---------|-------|------|
| Groq | llama-3.3-70b-versatile | Primary (fastest, free) |
| OpenAI | gpt-4o-mini | Fallback #2 |
| Google Gemini | gemini-2.0-flash-lite | Fallback #3 |
| Anthropic | claude-3-5-haiku | Fallback #4 |
| DeepSeek | deepseek-chat | Fallback #5 |
| Ollama (local) | llama3.1:latest (8B) | Final fallback (offline) |

### Cloud Services

| Service | Purpose | Free Tier |
|---------|---------|-----------|
| Cloudinary | Resume file storage | 25GB storage |
| Railway | Hosting + MySQL | $5/month |
| Twilio | SMS OTP | Trial credits |
| Gmail SMTP | Email delivery | Free |

### Infrastructure

| Tool | Purpose |
|------|---------|
| Docker | Containerization |
| Supervisord | Process management in Docker |
| Apache | Web server |
| .htaccess | URL routing |
| Railway | Cloud deployment platform |

---

## 2. Project File Count

| Category | Count |
|----------|-------|
| PHP Backend files | ~45 files |
| Frontend HTML/PHP pages | ~25 files |
| JavaScript files | ~8 files |
| CSS files | ~5 files |
| Python files | ~15 files |
| SQL migration files | 9 files |
| Configuration files | ~8 files |
| Documentation files | 8 files |
| **Total** | **~123 files** |

---

## 3. Database Details

### Table: users
```sql
id, name, email, mobile, password (bcrypt),
role (candidate|admin|recruiter),
company_name, account_status, email_verified, mobile_verified,
verification_otp, otp_expiry, mobile_otp, mobile_otp_expiry,
created_at
```

### Table: resumes
```sql
id, user_id, file_name, file_path (Cloudinary URL),
file_type, analysis_status, analysis_progress,
created_at
```

### Table: analysis_results (72 columns)
```sql
-- Core
id, resume_id, user_id, analysis_status, analysis_progress

-- Legacy Python pipeline scores
resume_strength_score, confidence_score, career_readiness_score,
talent_category, score, resume_strength, career_readiness,
hire_confidence_score, ats_compatibility_score, rank_score

-- LLM Analysis scores
overall_score, match_percentage

-- Candidate information
candidate_name, candidate_email, candidate_phone, experience_years

-- JSON arrays
education, skills, strengths, weaknesses, recommendations,
suitable_job_titles, missing_skills, latent_skills, recommended_roles

-- Intelligence vectors (JSON)
semantic_role_scores, domain_distribution, skill_maturity,
missing_dependencies, learning_recommendations, similar_candidates,
trajectory_prediction, reasoning_signals, capability_vector,
candidate_signal_profile, latent_skill_report, career_direction_vector

-- Career predictions
career_prediction_short, career_prediction_mid, career_prediction_long

-- Cluster analysis
similar_candidate_cluster, cluster_strength_score,
cluster_similarity_percent, similarity_band

-- Advanced metrics
learning_velocity_score, skill_entropy_score, career_entropy_score,
technical_depth_score, toolchain_complexity_score, domain_focus_score,
career_signal_consistency, semantic_alignment_score,
capability_alignment_score, career_projection_score,
trajectory_confidence_score, embedding_density_score,
vector_norm_score, knowledge_graph_expansion_score

-- Metadata
detected_sector, candidate_summary, llm_provider_used,
analysis_timestamp, summary, execution_time, created_at
```

### Recruiter Tables
```sql
job_postings (8 columns)
candidate_applications (6 columns)
shortlist_actions (7 columns)
recruiter_communications (8 columns)
recruiter_activity (7 columns)
```

### OTP Tables
```sql
otp_temp (8 columns)
password_resets (5 columns)
```

---

## 4. API Endpoints (Complete List)

### Authentication
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/register_user.php` | POST | Register candidate/recruiter |
| `/admin_register.php` | POST | Register admin |
| `/recruiter_register.php` | POST | Register recruiter |
| `/login_user.php` | POST | Multi-role login |
| `/admin_login.php` | POST | Admin login |
| `/recruiter_login.php` | POST | Recruiter login |
| `/logout.php` | POST | Logout |
| `/admin_logout.php` | POST | Admin logout |
| `/forgot_password.php` | POST | Request password reset |
| `/reset_password.php` | POST | Reset with token |
| `/send_otp.php` | POST | Send/verify email+mobile OTP |
| `/verify_email.php` | POST | Verify email OTP |
| `/verify_mobile.php` | POST | Verify mobile OTP |

### Resume Management
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/upload_resume.php` | POST | Upload to Cloudinary |
| `/download_resume.php` | GET | Download original file |
| `/delete_resume.php` | POST | Delete resume |
| `/check_status.php` | GET | Get analysis status |

### Analysis
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/start_analysis.php` | POST | Trigger LLM analysis |
| `/get_analysis_preview.php` | GET | Get full analysis data |
| `/get_admin_dashboard_resumes.php` | GET | Admin queue data |

### Recruiter
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/create_job_posting.php` | POST | Create job |
| `/get_job_postings.php` | GET | List recruiter's jobs |
| `/update_job_posting.php` | POST | Edit job |
| `/delete_job_posting.php` | POST | Delete job |
| `/get_job_details.php` | GET | Single job details |
| `/get_candidates.php` | GET | Filtered candidate list |
| `/get_candidate_details.php` | GET | Full analysis report |
| `/shortlist_candidate.php` | POST | Accept/reject |
| `/bulk_shortlist.php` | POST | Bulk accept/reject |
| `/get_shortlisted_candidates.php` | GET | Shortlisted list |
| `/send_candidate_email.php` | POST | Send email |
| `/get_email_templates.php` | GET | Email templates |
| `/get_communication_history.php` | GET | Email history |
| `/get_recruiter_dashboard.php` | GET | Dashboard metrics |
| `/get_dashboard_charts.php` | GET | Chart data |
| `/generate_candidate_pdf.php` | GET | PDF report |

---

## 5. Frontend Pages (Complete List)

### Candidate Pages
| Page | Description |
|------|-------------|
| `index.html` | Landing page |
| `register.html` | Registration with OTP |
| `user_login.html` | Login |
| `upload_resume.php` | Resume upload |
| `candidate_my_status.php` | Analysis status tracker |
| `analysis_result_viewer.php` | Full AI report |
| `dashboard.php` | Candidate dashboard |
| `forgot_password.html` | Password reset request |
| `reset_password.html` | New password form |
| `verify_email.html` | Email verification |

### Admin Pages
| Page | Description |
|------|-------------|
| `admin_login.html` | Admin login |
| `admin_register.html` | Admin registration |
| `admin_dashboard.php` | Resume queue + analysis trigger |

### Recruiter Pages
| Page | Description |
|------|-------------|
| `recruiter_login.html` | Recruiter login |
| `recruiter_register.html` | Recruiter registration |
| `recruiter/dashboard.php` | Stats + charts |
| `recruiter/job_postings.php` | Job CRUD |
| `recruiter/candidates.php` | Candidate browser |
| `recruiter/candidate_details.php` | Full report view |
| `recruiter/shortlist.php` | Accepted/rejected |
| `recruiter/communications.php` | Email composer |

---

## 6. Email Templates

The system includes 5 pre-built email templates:

1. **Interview Invitation** — Invites candidate for interview with date/time/location
2. **Acceptance Notification** — Congratulates selected candidate
3. **Rejection Notification** — Politely informs rejected candidate
4. **Follow-Up** — Status update for pending candidates
5. **Custom Message** — Free-form email

All templates support placeholders:
`{{candidate_name}}`, `{{job_title}}`, `{{recruiter_name}}`, `{{company_name}}`, `{{interview_date}}`, `{{interview_time}}`, `{{interview_location}}`

---

## 7. Security Implementation Details

### Password Security
- bcrypt with cost factor 10
- Never stored in plain text
- `password_verify()` for comparison (timing-safe)

### Session Security
- `session_regenerate_id(true)` after login
- 24-hour session timeout
- HttpOnly cookie flag
- Session destroyed on logout

### Input Validation
- All inputs sanitized with `htmlspecialchars()`
- Email validated with `FILTER_VALIDATE_EMAIL`
- Mobile number: strip country code, validate 10 digits
- File uploads: MIME type + size validation

### SQL Security
- All queries use PDO prepared statements
- No string concatenation in SQL
- Parameterized queries throughout

### API Security
- Every endpoint checks session + role
- Ownership verification for recruiter operations
- `ini_set('display_errors', 0)` on all API files
- `ob_start()` + `ob_clean()` to prevent stray output
