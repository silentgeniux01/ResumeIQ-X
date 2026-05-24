# 🚀 Railway Database Setup - Simple Instructions

## You have 2 options:

---

## ✅ OPTION 1: Copy-Paste in Database Client (EASIEST)

I can see you already have a database client open! Follow these steps:

### Step 1: Open the SQL file
1. In your database client, open the file: `COPY_PASTE_THIS_SQL.sql`
2. Or manually open it in any text editor

### Step 2: Select all and execute
1. Select ALL the SQL code (Ctrl+A)
2. Click the "Execute" or "Run" button
3. Wait for completion (should take 5-10 seconds)

### Step 3: Verify
You should see:
- ✓ 9 tables created
- ✓ 1 admin user inserted
- ✓ Success message

---

## ✅ OPTION 2: Use Railway Web Console

### Step 1: Go to Railway Dashboard
1. Open: https://railway.app/dashboard
2. Login if needed
3. Select project: **zestful-hope**

### Step 2: Open MySQL Query Editor
1. Click on **MySQL** service
2. Click **Data** tab
3. Click **Query** button (top right)

### Step 3: Execute SQL
1. Open `COPY_PASTE_THIS_SQL.sql` in a text editor
2. Copy ALL the content (Ctrl+A, Ctrl+C)
3. Paste into Railway query editor (Ctrl+V)
4. Click **Execute** or **Run**
5. Wait for completion

### Step 4: Verify
Check the **Tables** section - you should see 9 tables:
- users
- admins
- recruiters
- resumes
- job_postings
- shortlisted_candidates
- communication_history
- otp_verification
- ai_chat_history

---

## 🔐 Default Admin Credentials

After setup, login with:

```
Email: admin@resumeiqx.ai
Password: admin123
```

⚠️ **IMPORTANT**: Change this password immediately!

---

## ✅ Verification Steps

1. Go to your Railway app URL
2. Navigate to: `/admin_dashboard.php`
3. Login with default credentials
4. You should see the admin dashboard

---

## 🐛 Troubleshooting

### Issue: "Table already exists"
**Solution**: This is fine! It means some tables were already created. The script uses `CREATE TABLE IF NOT EXISTS` so it won't break.

### Issue: "Foreign key constraint fails"
**Solution**: Make sure you execute the ENTIRE SQL file, not just parts of it. The tables must be created in order.

### Issue: "Access denied"
**Solution**: Verify your database credentials in `.env` match Railway dashboard.

---

## 📊 What Gets Created

### Tables (9 total):
1. **users** - User accounts
2. **admins** - Admin accounts  
3. **recruiters** - Recruiter accounts
4. **resumes** - Uploaded resumes
5. **job_postings** - Job listings
6. **shortlisted_candidates** - Shortlisted candidates
7. **communication_history** - Email/SMS logs
8. **otp_verification** - OTP records
9. **ai_chat_history** - AI chat logs

### Default Data:
- 1 admin user (admin@resumeiqx.ai / admin123)

---

## 🎯 Next Steps

After database setup:

1. ✅ Test admin login
2. ✅ Create a test user account
3. ✅ Upload a test resume
4. ✅ Test AI chat assistant
5. ✅ Test recruiter features

---

**Need Help?**

If you're stuck, just tell me which option you want to use and I'll guide you through it step by step!

---

**Created by**: Mayur Gopal Kove  
**Date**: May 3, 2026
