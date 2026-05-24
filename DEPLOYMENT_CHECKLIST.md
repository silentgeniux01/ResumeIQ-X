# 🚀 Deployment Checklist - UI/UX Fixes

## 📋 Pre-Deployment Verification

### ✅ Files Modified
- [x] `frontend/index.html` - Removed admin/recruiter portal buttons
- [x] `frontend/register.html` - Removed recruiter role option and unused CSS
- [x] `backend_php/test_email_config.php` - Created for testing

### ✅ Files Verified (No Changes Needed)
- [x] `backend_php/forgot_password.php` - Logic correct
- [x] `backend_php/email_helper.php` - Multi-method SMTP working
- [x] `backend_php/reset_password.php` - Token validation correct
- [x] `backend_php/config.php` - Environment loading correct

---

## 🔧 Railway Deployment Steps

### Step 1: Commit Changes to Git
```bash
git add frontend/index.html
git add frontend/register.html
git add backend_php/test_email_config.php
git add UI_UX_FIXES_SUMMARY.md
git add CHANGES_VISUAL_GUIDE.md
git add DEPLOYMENT_CHECKLIST.md
git commit -m "fix: Remove public access to admin/recruiter portals and restrict registration to candidates only"
git push origin main
```

### Step 2: Verify Railway Auto-Deploy
1. Go to Railway dashboard: https://railway.app/dashboard
2. Select your project: `resumeiq-x-production`
3. Wait for automatic deployment to complete
4. Check deployment logs for any errors

### Step 3: Verify Environment Variables
Ensure these are set in Railway > Variables:

**Email Configuration** (CRITICAL for password reset):
```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mayurkove428@gmail.com
MAIL_PASSWORD=yrfomdszuixayykn
MAIL_FROM_NAME=ResumeIQ-X
MAIL_FROM_ADDRESS=mayurkove428@gmail.com
```

**Application Configuration**:
```
APP_URL=https://resumeiq-x-production.up.railway.app
APP_NAME=ResumeIQ-X
APP_ENV=production
```

---

## 🧪 Post-Deployment Testing

### Test 1: Email Configuration ⚡ PRIORITY
**URL**: `https://resumeiq-x-production.up.railway.app/backend_php/test_email_config.php`

**Expected Output**:
```json
{
  "status": true,
  "message": "Email configuration loaded",
  "config": {
    "MAIL_HOST": "smtp.gmail.com",
    "MAIL_PORT": "587",
    "MAIL_USERNAME": "mayurkove428@gmail.com",
    "MAIL_PASSWORD": "***SET***",
    "MAIL_FROM_NAME": "ResumeIQ-X",
    "MAIL_FROM_ADDRESS": "mayurkove428@gmail.com"
  }
}
```

**If MAIL_PASSWORD shows "NOT SET"**:
1. Go to Railway dashboard
2. Add/update `MAIL_PASSWORD` variable
3. Redeploy

---

### Test 2: Homepage UI Changes
**URL**: `https://resumeiq-x-production.up.railway.app/`

**Checklist**:
- [ ] "Recruiter Portal" button is NOT visible
- [ ] "Admin Portal" button is NOT visible
- [ ] "Create Free Account" button IS visible
- [ ] "Learn More" button IS visible
- [ ] All other homepage elements work correctly

**Screenshot**: Take a screenshot of the CTA section for verification

---

### Test 3: Registration Page Changes
**URL**: `https://resumeiq-x-production.up.railway.app/frontend/register.html`

**Checklist**:
- [ ] Role toggle (Candidate/Recruiter) is NOT visible
- [ ] Registration form displays correctly
- [ ] All fields are functional (name, email, mobile, password)
- [ ] OTP verification works for email
- [ ] OTP verification works for mobile
- [ ] Account creation succeeds
- [ ] New account has "candidate" role (verify in database)

**Test Registration**:
1. Use a test email
2. Complete registration
3. Verify role in database: `SELECT role FROM users WHERE email='test@example.com'`
4. Expected: `role = 'candidate'`

---

### Test 4: Password Reset Email ⚡ CRITICAL
**URL**: `https://resumeiq-x-production.up.railway.app/frontend/forgot_password.html`

**Test Steps**:
1. Enter email: `mayurkove428@gmail.com`
2. Click "Send Reset Link"
3. Wait 1-2 minutes
4. Check email inbox (and spam folder)
5. Verify email received with subject: "ResumeIQ-X — Reset Your Password"
6. Click reset link in email
7. Enter new password
8. Verify password reset successful
9. Login with new password

**If Email Not Received**:
1. Check Railway logs for `[ResumeIQ-X][EMAIL]` entries
2. Verify Gmail App Password is correct
3. Check Gmail security alerts: https://myaccount.google.com/notifications
4. Try sending to a different email address

---

### Test 5: Admin/Recruiter Access (Direct URL)
**Admin Login**: `https://resumeiq-x-production.up.railway.app/frontend/admin_login.html`
**Recruiter Login**: `https://resumeiq-x-production.up.railway.app/frontend/recruiter_login.html`

**Checklist**:
- [ ] Admin login page loads correctly
- [ ] Recruiter login page loads correctly
- [ ] Both pages are NOT linked from homepage
- [ ] Login functionality works for existing admin/recruiter accounts

---

## 🐛 Troubleshooting Guide

### Issue: Email Configuration Test Fails

**Symptom**: `MAIL_PASSWORD` shows "NOT SET"

**Solution**:
1. Railway Dashboard > Your Project > Variables
2. Click "New Variable"
3. Name: `MAIL_PASSWORD`
4. Value: `yrfomdszuixayykn`
5. Click "Add"
6. Redeploy

---

### Issue: Password Reset Email Not Received

**Check 1: Railway Logs**
```
Railway Dashboard > Deployments > Latest > Logs
Search for: "[ResumeIQ-X][EMAIL]"
```

**Check 2: Gmail App Password**
- Verify: `yrfomdszuixayykn` is correct
- Regenerate if needed: https://myaccount.google.com/apppasswords

**Check 3: Gmail Security**
- Check: https://myaccount.google.com/notifications
- Look for blocked sign-in attempts

**Check 4: Test with Different Email**
- Try sending to Gmail, Outlook, Yahoo
- Check if it's a recipient-side issue

---

### Issue: Homepage Still Shows Admin/Recruiter Buttons

**Solution**:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Try incognito/private mode
4. Verify deployment completed successfully

---

### Issue: Registration Still Shows Recruiter Option

**Solution**:
1. Clear browser cache
2. Hard refresh
3. Check Railway deployment logs
4. Verify `frontend/register.html` was deployed

---

## 📊 Success Criteria

All tests must pass:
- ✅ Email configuration test returns all values
- ✅ Homepage shows only public buttons
- ✅ Registration only allows candidate role
- ✅ Password reset email received and works
- ✅ Admin/recruiter pages accessible via direct URL only

---

## 🎯 Rollback Plan (If Needed)

If critical issues occur:

```bash
# Revert to previous commit
git log --oneline  # Find previous commit hash
git revert <commit-hash>
git push origin main

# Railway will auto-deploy the reverted version
```

---

## 📝 Post-Deployment Notes

**Date**: _____________  
**Deployed By**: _____________  
**Deployment Time**: _____________  
**All Tests Passed**: ☐ Yes ☐ No  

**Issues Found**:
- _____________________________________________
- _____________________________________________

**Resolution**:
- _____________________________________________
- _____________________________________________

---

## 🎉 Deployment Complete

Once all tests pass:
1. ✅ Mark this checklist as complete
2. ✅ Archive test files (optional)
3. ✅ Update project documentation
4. ✅ Notify team/stakeholders

---

**Created**: 2026-05-03  
**Version**: 1.0  
**Status**: Ready for Deployment
