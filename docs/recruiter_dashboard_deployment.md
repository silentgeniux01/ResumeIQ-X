# Recruiter Dashboard System — Deployment Guide

## Overview

The Recruiter Dashboard System adds a third user role (Recruiter) to ResumeIQ-X with:
- Job posting management
- LLM-powered resume analysis (OpenAI → Groq → Gemini → Anthropic → DeepSeek fallback)
- Candidate viewing, filtering, and shortlisting
- Email communication with templates
- Dashboard with statistics and charts

---

## Step 1: Environment Variables

Add these to your `.env` file:

```env
# LLM Providers (at least one required)
OPENAI_API_KEY=sk-...
GROQ_API_KEY=gsk_...
GEMINI_API_KEY=...
ANTHROPIC_API_KEY=sk-ant-...
DEEPSEEK_API_KEY=sk-...

# LLM Configuration
OPENAI_QUOTA_EXCEEDED=0        # Set to 1 to skip OpenAI
MEERA_FORCE_PROVIDER=groq      # Optional: force a specific provider first
```

---

## Step 2: Database Migrations

Run all migrations in order:

```bash
php database/run_migrations.php
```

This creates:
- `job_postings` — recruiter job listings
- `candidate_applications` — candidate-to-job links
- `shortlist_actions` — accept/reject decisions
- `recruiter_communications` — email history
- `recruiter_activity` — audit log
- Extends `analysis_results` with LLM analysis fields
- Extends `users.role` enum to include `'recruiter'`
- Adds performance indexes

**Rollback:** Each migration file contains rollback SQL in comments at the bottom.

---

## Step 3: File Structure

```
backend_php/
├── recruiter_register.php      ← Recruiter registration
├── recruiter_login.php         ← Recruiter login
├── create_job_posting.php      ← Create job
├── get_job_postings.php        ← List jobs
├── update_job_posting.php      ← Edit job
├── delete_job_posting.php      ← Delete job
├── get_job_details.php         ← Single job details
├── get_candidates.php          ← Filtered candidate list
├── get_candidate_details.php   ← Full analysis report
├── shortlist_candidate.php     ← Accept/reject candidate
├── bulk_shortlist.php          ← Bulk accept/reject
├── get_shortlisted_candidates.php
├── send_candidate_email.php    ← Send email to candidate
├── get_email_templates.php     ← Email templates
├── get_communication_history.php
├── get_recruiter_dashboard.php ← Dashboard metrics
├── get_dashboard_charts.php    ← Chart data
├── trigger_resume_analysis.php ← LLM analysis trigger
├── get_analysis_status.php     ← Analysis status check
├── llm_helper.php              ← LLM fallback chain
├── error_handler.php           ← Centralized error handling
└── input_validator.php         ← Input sanitization

frontend/
├── recruiter_login.html        ← Recruiter login page
├── recruiter_register.html     ← Recruiter registration page
└── recruiter/
    ├── dashboard.php           ← Main dashboard
    ├── job_postings.php        ← Job management
    ├── candidates.php          ← Candidate browser
    ├── candidate_details.php   ← Full analysis report
    ├── shortlist.php           ← Shortlisted candidates
    └── communications.php      ← Email composer

frontend/assets/
├── css/recruiter.css           ← Dashboard styles
└── templates/email_templates.json ← Email templates
```

---

## Step 4: Access URLs

| Page | URL |
|------|-----|
| Recruiter Login | `/frontend/recruiter_login.html` |
| Recruiter Register | `/frontend/recruiter_register.html` |
| Dashboard | `/frontend/recruiter/dashboard.php` |
| Job Postings | `/frontend/recruiter/job_postings.php` |
| Candidates | `/frontend/recruiter/candidates.php` |
| Shortlisted | `/frontend/recruiter/shortlist.php` |
| Communications | `/frontend/recruiter/communications.php` |

---

## Step 5: LLM Fallback Chain

The system tries providers in this order:

```
1. MEERA_FORCE_PROVIDER (if set)
2. OpenAI (if OPENAI_QUOTA_EXCEEDED != 1)
3. Groq
4. Gemini
5. Anthropic
6. DeepSeek
```

Each provider has a 30-second timeout. If one fails, the next is tried automatically.

---

## Step 6: User Flow

```
1. Recruiter registers at /frontend/recruiter_register.html
2. Verifies email OTP
3. Logs in at /frontend/recruiter_login.html
4. Creates job postings at /frontend/recruiter/job_postings.php
5. Admin analyzes resumes via trigger_resume_analysis.php
6. Recruiter views candidates at /frontend/recruiter/candidates.php
7. Recruiter reviews full reports at candidate_details.php
8. Recruiter accepts/rejects candidates
9. Recruiter sends emails via /frontend/recruiter/communications.php
10. Recruiter tracks progress on dashboard
```

---

## Troubleshooting

### "Authentication required" errors
- Check session is active
- Verify `session_guard.php` is included
- Check `APP_URL` in `.env` matches your deployment URL

### LLM analysis fails
- Check at least one LLM API key is set in `.env`
- Check `OPENAI_QUOTA_EXCEEDED` is `0` if using OpenAI
- Check PHP `curl` extension is enabled

### Database errors
- Run `php database/run_migrations.php` to ensure all tables exist
- Check `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` in `.env`

### Email not sending
- Check `MAIL_USERNAME` and `MAIL_PASSWORD` in `.env`
- For Gmail, use an App Password (not your regular password)
- Check PHP `openssl` extension is enabled for STARTTLS

---

## Security Notes

- All endpoints verify session and role before processing
- All database queries use prepared statements (SQL injection safe)
- Passwords hashed with bcrypt (cost factor 10)
- OTPs expire after 10 minutes
- Sessions expire after 24 hours of inactivity
- File uploads validated by type and size (10MB max)
- Input sanitized to prevent XSS
