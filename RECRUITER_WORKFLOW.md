# Recruiter System Workflow Guide

## 🎯 Complete User Journey

### 1. Registration Flow
```
┌─────────────────────────────────────────────────────────────┐
│  START: Visit recruiter_register.html                       │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 1: Fill Registration Form                             │
│  • Full Name                                                 │
│  • Email Address                                             │
│  • Mobile Number (with country code)                         │
│  • Password (min 6 chars)                                    │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 2: Email Verification                                  │
│  • Click "Send OTP" button                                   │
│  • Receive 6-digit OTP via email                             │
│  • Enter OTP and click "Verify"                              │
│  • ✅ Email Verified badge appears                           │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 3: Mobile Verification                                 │
│  • Click "Send OTP" button                                   │
│  • Receive 6-digit OTP via email (for mobile verification)   │
│  • Enter OTP and click "Verify"                              │
│  • ✅ Mobile Verified badge appears                          │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 4: Create Account                                      │
│  • Click "Create Recruiter Account" button                   │
│  • Account created with role='recruiter'                     │
│  • Redirect to recruiter_login.html                          │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  SUCCESS: Registration Complete! 🎉                          │
└─────────────────────────────────────────────────────────────┘
```

### 2. Login Flow
```
┌─────────────────────────────────────────────────────────────┐
│  START: Visit recruiter_login.html                           │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 1: Enter Credentials                                   │
│  • Email Address                                             │
│  • Password                                                  │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 2: Authentication                                      │
│  • System validates credentials                              │
│  • Checks account_status = 'active'                          │
│  • Verifies role = 'recruiter'                               │
│  • Creates session with recruiter_id                         │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  SUCCESS: Redirect to recruiter_dashboard.php 🎉            │
└─────────────────────────────────────────────────────────────┘
```

### 3. Dashboard Usage Flow
```
┌─────────────────────────────────────────────────────────────┐
│  RECRUITER DASHBOARD                                         │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Statistics Overview                                 │   │
│  │  • Total Candidates: XX                              │   │
│  │  • Analyzed: XX                                      │   │
│  │  • Pending Analysis: XX                              │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Candidate Database Table                            │   │
│  │  ┌──────┬───────┬────────┬────────┬───────┬────────┐│   │
│  │  │ Name │ Email │ Resume │ Status │ Score │ Actions││   │
│  │  ├──────┼───────┼────────┼────────┼───────┼────────┤│   │
│  │  │ John │ john@ │ res.pdf│ ✓ Done │  85   │ [Btns] ││   │
│  │  │ Jane │ jane@ │ cv.pdf │ ⏳ Pend│  —    │ [Btns] ││   │
│  │  └──────┴───────┴────────┴────────┴───────┴────────┘│   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### 4. Email Sending Flow
```
┌─────────────────────────────────────────────────────────────┐
│  STEP 1: Click "Email" Button on Candidate Row              │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 2: Email Modal Opens                                   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  📧 Send Job Recommendation                          │   │
│  │  ─────────────────────────────────────────────────   │   │
│  │  Candidate Name: [John Doe] (readonly)              │   │
│  │  Candidate Email: [john@example.com] (readonly)     │   │
│  │  ─────────────────────────────────────────────────   │   │
│  │  Email Subject:                                      │   │
│  │  [Congratulations! Job Opportunities...]            │   │
│  │  ─────────────────────────────────────────────────   │   │
│  │  Job Recommendations:                                │   │
│  │  [                                                   │   │
│  │   • Senior Software Engineer at TechCorp            │   │
│  │   • Full Stack Developer at StartupXYZ              │   │
│  │   • Backend Engineer at Enterprise Inc.             │   │
│  │  ]                                                   │   │
│  │  ─────────────────────────────────────────────────   │   │
│  │  Personal Message: (optional)                        │   │
│  │  [                                                   │   │
│  │   Your profile stood out to us because of your      │   │
│  │   strong background in Python and cloud tech...     │   │
│  │  ]                                                   │   │
│  │  ─────────────────────────────────────────────────   │   │
│  │  [✉️ Send Email]                                     │   │
│  └─────────────────────────────────────────────────────┘   │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  STEP 3: Email Processing                                    │
│  • Validate all required fields                              │
│  • Fetch recruiter information                               │
│  • Build professional HTML email                             │
│  • Send via SMTP                                             │
│  • Log activity in database                                  │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│  SUCCESS: ✓ Email sent successfully! 🎉                     │
│  Modal closes automatically after 2 seconds                  │
└─────────────────────────────────────────────────────────────┘
```

### 5. Email Template Structure
```
┌─────────────────────────────────────────────────────────────┐
│  📧 CANDIDATE RECEIVES EMAIL                                 │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  ┌───────────────────────────────────────────────┐ │   │
│  │  │  🎉 Congratulations!                          │ │   │
│  │  │  Exciting Career Opportunities Await          │ │   │
│  │  │  (Gradient Purple Header)                     │ │   │
│  │  └───────────────────────────────────────────────┘ │   │
│  │                                                     │   │
│  │  Hi John Doe,                                       │   │
│  │                                                     │   │
│  │  We've reviewed your profile on ResumeIQ-X and     │   │
│  │  are impressed with your skills and experience!    │   │
│  │                                                     │   │
│  │  ┌─────────────────────────────────────────────┐  │   │
│  │  │ 💬 Personal Message (if provided)           │  │   │
│  │  │ Your profile stood out to us because...     │  │   │
│  │  └─────────────────────────────────────────────┘  │   │
│  │                                                     │   │
│  │  ┌─────────────────────────────────────────────┐  │   │
│  │  │ 📋 Recommended Positions                    │  │   │
│  │  │                                             │  │   │
│  │  │ • Senior Software Engineer at TechCorp      │  │   │
│  │  │ • Full Stack Developer at StartupXYZ        │  │   │
│  │  │ • Backend Engineer at Enterprise Inc.       │  │   │
│  │  └─────────────────────────────────────────────┘  │   │
│  │                                                     │   │
│  │  These positions align well with your expertise... │   │
│  │                                                     │   │
│  │  Best regards,                                      │   │
│  │  Sarah Johnson                                      │   │
│  │  Talent Acquisition Specialist                      │   │
│  │  sarah@company.com                                  │   │
│  │                                                     │   │
│  │  ───────────────────────────────────────────────   │   │
│  │  Powered by ResumeIQ-X — AI Career Intelligence    │   │
│  │  © 2026 ResumeIQ-X. All rights reserved.           │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## 🔐 Security Features

### Session Management
```
Login → Session Created
├── recruiter_id: 123
├── user_name: "Sarah Johnson"
├── user_email: "sarah@company.com"
└── user_role: "recruiter"

Dashboard Access → Session Check
├── ✅ recruiter_id exists → Allow access
└── ❌ recruiter_id missing → Redirect to login

Logout → Session Destroyed
└── Redirect to recruiter_login.html
```

### Input Validation
```
Registration:
├── Name: Required, non-empty
├── Email: Valid email format
├── Mobile: 10 digits (after country code strip)
├── Password: Minimum 6 characters
├── Email OTP: 6 digits, verified
└── Mobile OTP: 6 digits, verified

Email Sending:
├── Subject: Required, non-empty
├── Job Recommendations: Required, non-empty
├── Personal Message: Optional
├── Candidate Email: Valid email format
└── Candidate ID: Valid integer
```

## 📊 Database Operations

### Registration
```sql
INSERT INTO users (
  name, email, mobile, password, 
  role, account_status, 
  email_verified, mobile_verified
) VALUES (
  'Sarah Johnson', 'sarah@company.com', '1234567890', 
  '$2y$10$...', 'recruiter', 'active', 1, 1
);
```

### Login
```sql
SELECT id, name, email, password, role, account_status
FROM users
WHERE email = 'sarah@company.com'
LIMIT 1;
```

### Email Activity Logging
```sql
INSERT INTO recruiter_activity (
  recruiter_id, candidate_id, action_type
) VALUES (
  123, 456, 'email_sent'
);
```

## 🎨 UI Color Scheme

### Recruiter Portal Theme
```
Primary Colors:
├── Cyan: #0ea5e9 (rgb(14, 165, 233))
├── Teal: #06b6d4 (rgb(6, 182, 212))
└── Sky Blue: #38bdf8 (rgb(56, 189, 248))

Background:
├── Dark: #030712
├── Dark2: #0f172a
└── Dark3: #1e293b

Accents:
├── Success: #10b981 (Green)
├── Warning: #f59e0b (Orange)
├── Error: #ef4444 (Red)
└── Info: #6366f1 (Indigo)
```

## 🚀 Quick Start Guide

### For Recruiters
1. **Register**: Visit `/frontend/recruiter_register.html`
2. **Verify**: Complete email and mobile verification
3. **Login**: Visit `/frontend/recruiter_login.html`
4. **Dashboard**: Access candidate database
5. **Email**: Send job recommendations to candidates

### For Developers
1. **Test Registration**: Create test recruiter account
2. **Test Login**: Verify authentication flow
3. **Test Dashboard**: Check candidate data display
4. **Test Email**: Send test email to yourself
5. **Check Logs**: Verify recruiter_activity table

## 📝 API Endpoints

### Authentication
- `POST /backend_php/register_user.php` - Register recruiter
- `POST /backend_php/login_user.php` - Login recruiter
- `GET /backend_php/logout.php` - Logout recruiter

### Dashboard
- `GET /backend_php/get_admin_dashboard_resumes.php` - Get candidates
- `GET /backend_php/download_resume.php?resume_id=X` - Download resume
- `GET /frontend/analysis_result_viewer.php?resume_id=X` - View analysis

### Email
- `POST /backend_php/send_candidate_email.php` - Send job recommendation

### OTP
- `POST /backend_php/send_otp.php` - Send/verify OTP

---

**Last Updated**: May 3, 2026
**Version**: 1.0.0
**Status**: ✅ Production Ready
