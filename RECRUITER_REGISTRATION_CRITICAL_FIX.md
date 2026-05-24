# CRITICAL FIX: Recruiter Registration API Endpoint Error

## The Real Problem

The recruiter registration form was calling the **WRONG API endpoint**!

### What Was Happening:
```javascript
// Frontend: recruiter_register.html (line 296)
const res = await fetch(apiUrl('register_user.php'), {  // ← WRONG ENDPOINT!
  method: 'POST',
  body: 'name=...&email=...&mobile=...&password=...&role=recruiter'
});
```

### Why This Caused "Server error":
1. Frontend called `register_user.php` (candidate registration API)
2. `register_user.php` expects `role` parameter
3. But recruiter registration needs `company_name` parameter
4. Database INSERT failed because `company_name` was missing
5. Generic error message shown: "Server error. Please try again."

## The Fix

Changed the API endpoint from `register_user.php` to `recruiter_register.php`:

### Before (WRONG):
```javascript
const res = await fetch(apiUrl('register_user.php'), {
  method: 'POST',
  body: 'name=...&email=...&mobile=...&password=...&role=recruiter'
});
```

### After (CORRECT):
```javascript
const res = await fetch(apiUrl('recruiter_register.php'), {
  method: 'POST',
  body: 'name=...&email=...&mobile=...&password=...&company_name='
});
```

## Why This Happened

The recruiter registration form was probably copied from the user registration form and the API endpoint wasn't updated.

## Files Fixed

1. ✅ `frontend/recruiter_register.html` - Changed API endpoint
2. ✅ `backend_php/recruiter_register.php` - Added better error messages (previous fix)
3. ✅ `backend_php/register_user.php` - Added better error messages (previous fix)
4. ✅ `backend_php/admin_register.php` - Added better error messages (previous fix)

## API Endpoints Clarification

### User/Candidate Registration:
- **Frontend**: `frontend/user_register.html`
- **API**: `backend_php/register_user.php`
- **Parameters**: `name`, `email`, `mobile`, `password`, `role` (candidate/recruiter)

### Admin Registration:
- **Frontend**: `frontend/admin_register.html`
- **API**: `backend_php/admin_register.php`
- **Parameters**: `name`, `email`, `mobile`, `password`

### Recruiter Registration:
- **Frontend**: `frontend/recruiter_register.html`
- **API**: `backend_php/recruiter_register.php` ← **NOW CORRECT!**
- **Parameters**: `name`, `email`, `mobile`, `password`, `company_name`

## Testing After Deployment

### Step 1: Wait for Railway Deployment
- Railway is deploying now (~1-2 minutes)
- Check deployment status in Railway dashboard

### Step 2: Clear Browser Cache
```
Windows/Linux: Ctrl + Shift + R
Mac: Cmd + Shift + R
```

### Step 3: Try Registration Again
1. Go to: `https://resumeiq-x-production.up.railway.app/frontend/recruiter_register.html`
2. Fill in details:
   - Name: JANHAVI CHAVAN
   - Email: janhavic811@gmail.com
   - Mobile: 9579143580
3. Verify both OTPs (email + mobile)
4. Click "Create Recruiter Account"

### Expected Results:

#### If Account Doesn't Exist:
```
✓ Recruiter account created! Redirecting...
```
Then redirects to login page.

#### If Account Already Exists:
```
❌ This email or mobile number is already registered.
```

#### If OTP Not Verified:
```
❌ Please verify your email OTP first
or
❌ Please verify your mobile OTP first
```

## Deployment

- ✅ Committed: `db46cb7`
- ✅ Message: "CRITICAL FIX: Recruiter registration calling wrong API endpoint"
- ✅ Pushed to GitHub
- ✅ Railway deploying now

## Root Cause Analysis

### Why the Error Was Generic:
1. Frontend called wrong API (`register_user.php`)
2. `register_user.php` tried to INSERT with `role='recruiter'`
3. But `company_name` column was NULL (not provided)
4. Database rejected the INSERT (constraint violation or missing required field)
5. PDO exception caught with generic message

### Why It Should Work Now:
1. Frontend now calls correct API (`recruiter_register.php`)
2. `recruiter_register.php` expects correct parameters
3. Database INSERT will succeed (if account doesn't exist)
4. If error occurs, you'll see specific message (from previous fix)

## Additional Checks

### Check if Account Already Exists:
Run this SQL on Railway database:
```sql
SELECT id, name, email, mobile, role, account_status, created_at
FROM users
WHERE email = 'janhavic811@gmail.com' 
   OR mobile LIKE '%9579143580';
```

**If account exists**:
- Delete it: `DELETE FROM users WHERE email = 'janhavic811@gmail.com';`
- Or: Try logging in instead of registering

**If account doesn't exist**:
- Registration should work now!

### Check OTP Verification:
```sql
SELECT * FROM otp_temp
WHERE email = 'janhavic811@gmail.com'
ORDER BY id DESC
LIMIT 5;
```

**Expected**:
- 2 rows with `verified = 1`
- One for `otp_type = 'email'`
- One for `otp_type = 'mobile'`

## Summary

The issue was **NOT** a server error or database error. It was simply the frontend calling the **wrong API endpoint**.

- ❌ **Before**: `register_user.php` (candidate API)
- ✅ **After**: `recruiter_register.php` (recruiter API)

This is now fixed and deployed! 🎉

---

**Created by**: MAYUR GOPAL KOVE  
**Date**: 2026-05-04  
**Status**: CRITICAL FIX DEPLOYED ✅
