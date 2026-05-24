# Railway Database Setup Guide

## Quick Setup (Automated)

Run this PowerShell command in your project directory:

```powershell
.\setup_railway_db.ps1
```

This script will:
1. ✓ Check Railway CLI installation
2. ✓ Check MySQL client (XAMPP or system MySQL)
3. ✓ Read credentials from `.env`
4. ✓ Execute SQL file on Railway database
5. ✓ Create all 9 tables
6. ✓ Insert default admin user

---

## Manual Setup (Alternative)

If the automated script doesn't work, use Railway CLI directly:

### Option 1: Using Railway CLI + MySQL

```powershell
# Make sure you're linked to your Railway project
railway link

# Execute SQL file
Get-Content setup_database_railway.sql | railway run -s MySQL mysql -u root railway
```

### Option 2: Using XAMPP MySQL directly

```powershell
# Navigate to XAMPP MySQL bin directory
cd C:\xampp\mysql\bin

# Execute SQL file
Get-Content C:\xampp\htdocs\ResumeIQ-X\setup_database_railway.sql | .\mysql.exe -h monorail.proxy.rlwy.net -P 33459 -u root -pFzOAGAJqKTQAyTjMoNszrzFHQEvXAlVr railway
```

### Option 3: Using Railway Web Console

1. Go to Railway dashboard: https://railway.app/dashboard
2. Select your project: **zestful-hope**
3. Click on **MySQL** service
4. Click **Data** tab
5. Click **Query** button
6. Copy and paste contents of `setup_database_railway.sql`
7. Click **Execute**

---

## Database Credentials

From your `.env` file:

```
Host: monorail.proxy.rlwy.net
Port: 33459
Database: railway
User: root
Password: FzOAGAJqKTQAyTjMoNszrzFHQEvXAlVr
```

---

## Tables Created

1. ✓ **users** - User accounts with email/mobile verification
2. ✓ **admins** - Admin accounts
3. ✓ **recruiters** - Recruiter accounts with company info
4. ✓ **resumes** - Uploaded resumes with analysis results
5. ✓ **job_postings** - Job postings by recruiters
6. ✓ **shortlisted_candidates** - Shortlisted candidates for jobs
7. ✓ **communication_history** - Email/SMS communication logs
8. ✓ **otp_verification** - OTP verification records
9. ✓ **ai_chat_history** - AI chat conversation history

---

## Default Admin Account

After setup, you can login with:

```
Email: admin@resumeiqx.ai
Password: admin123
```

⚠️ **IMPORTANT**: Change this password immediately after first login!

---

## Verification

To verify the setup worked:

1. Go to your Railway app URL
2. Navigate to admin login page
3. Login with default credentials
4. You should see the admin dashboard

---

## Troubleshooting

### Error: "mysql is not recognized"

**Solution**: Install MySQL client or use XAMPP MySQL:

```powershell
# Add XAMPP MySQL to PATH temporarily
$env:Path += ";C:\xampp\mysql\bin"

# Then run the script again
.\setup_railway_db.ps1
```

### Error: "Can't connect to MySQL server"

**Solution**: Check your Railway database is running:

```powershell
railway status
```

### Error: "Access denied for user"

**Solution**: Verify credentials in `.env` match Railway dashboard.

---

## Next Steps

After database setup:

1. ✓ Test admin login
2. ✓ Create a test user account
3. ✓ Upload a test resume
4. ✓ Test AI chat assistant
5. ✓ Test recruiter dashboard

---

## Support

If you encounter issues:

1. Check Railway logs: `railway logs -s MySQL`
2. Check app logs: `railway logs -s ResumeIQ-X`
3. Verify environment variables: `railway variables`

---

**Created by**: Mayur Gopal Kove  
**Date**: May 3, 2026
