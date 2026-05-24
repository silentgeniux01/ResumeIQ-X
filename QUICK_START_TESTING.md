# 🚀 Quick Start Testing Guide

## ⚡ 3-Minute Test Plan

### Test 1: Email Config (30 seconds)
**URL**: https://resumeiq-x-production.up.railway.app/backend_php/test_email_config.php

**What to look for**:
- `MAIL_PASSWORD`: Should show `***SET***` (not `NOT SET`)
- All other MAIL_* fields should have values

**If it fails**: Go to Railway > Variables > Add `MAIL_PASSWORD` = `yrfomdszuixayykn`

---

### Test 2: Homepage (30 seconds)
**URL**: https://resumeiq-x-production.up.railway.app/

**What to look for**:
- ❌ NO "Recruiter Portal" button
- ❌ NO "Admin Portal" button
- ✅ YES "Create Free Account" button
- ✅ YES "Learn More" button

**If buttons still show**: Clear cache (Ctrl+Shift+Delete) and hard refresh (Ctrl+F5)

---

### Test 3: Registration (30 seconds)
**URL**: https://resumeiq-x-production.up.railway.app/frontend/register.html

**What to look for**:
- ❌ NO role toggle (Candidate/Recruiter buttons)
- ✅ Form should work normally

**If role toggle shows**: Clear cache and hard refresh

---

### Test 4: Password Reset Email (90 seconds)
**URL**: https://resumeiq-x-production.up.railway.app/frontend/forgot_password.html

**Steps**:
1. Enter: `mayurkove428@gmail.com`
2. Click "Send Reset Link"
3. Check email (inbox + spam)
4. Should receive email within 1-2 minutes

**If no email**:
1. Check Railway logs for `[ResumeIQ-X][EMAIL]`
2. Verify Gmail App Password: `yrfomdszuixayykn`
3. Check Gmail security: https://myaccount.google.com/notifications

---

## 🎯 Expected Results Summary

| Test | Expected Result | Status |
|------|----------------|--------|
| Email Config | All MAIL_* variables set | ☐ |
| Homepage | No admin/recruiter buttons | ☐ |
| Registration | No role toggle visible | ☐ |
| Password Reset | Email received | ☐ |

---

## 🔧 Quick Fixes

### Fix 1: Email Not Working
```
Railway Dashboard > Variables > Add:
MAIL_PASSWORD = yrfomdszuixayykn
```

### Fix 2: Old UI Still Showing
```
Browser: Ctrl+Shift+Delete > Clear cache
Then: Ctrl+F5 (hard refresh)
```

### Fix 3: Check Deployment Status
```
Railway Dashboard > Deployments > Latest
Status should be: "Active"
```

---

## 📞 Need Help?

**Check Railway Logs**:
1. Railway Dashboard
2. Click your deployment
3. View logs
4. Search for errors

**Common Log Searches**:
- `[ResumeIQ-X][EMAIL]` - Email sending
- `error` - Any errors
- `Password reset` - Reset link generation

---

## ✅ All Tests Passed?

If all 4 tests pass:
- ✅ Deployment successful
- ✅ UI/UX fixes working
- ✅ Email system operational
- ✅ Security improvements active

**You're done!** 🎉

---

**Quick Reference**:
- Test Email Config: `/backend_php/test_email_config.php`
- Homepage: `/`
- Registration: `/frontend/register.html`
- Password Reset: `/frontend/forgot_password.html`
- Admin Login (direct): `/frontend/admin_login.html`
- Recruiter Login (direct): `/frontend/recruiter_login.html`
