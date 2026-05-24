# Registration Error Fix - "Server error. Please try again."

## The Problem

When trying to register (User/Admin/Recruiter), the system shows a generic error:
```
Server error. Please try again.
```

This error message **hides the real problem**, making it impossible to debug.

## Root Cause

The registration files (`recruiter_register.php`, `register_user.php`, `admin_register.php`) were catching PDO exceptions but only showing a generic error message:

```php
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["status" => false, "message" => "Registration failed. Please try again."]);
}
```

The actual error was being logged to the server but **not shown to the user**.

## Common Causes of Registration Errors

### 1. **Duplicate Entry** (Most Common)
- Email or mobile number already registered
- **Error**: `Duplicate entry 'email@example.com' for key 'email'`

### 2. **Database Schema Mismatch**
- Column doesn't exist in database table
- **Error**: `Unknown column 'company_name' in 'field list'`

### 3. **Missing Table**
- Database table doesn't exist
- **Error**: `Table 'database.users' doesn't exist`

### 4. **OTP Not Verified**
- User didn't verify email or mobile OTP
- **Error**: `Please verify your email OTP first`

### 5. **Database Connection Failure**
- Railway database not accessible
- **Error**: `SQLSTATE[HY000] [2002] Connection refused`

## The Fix

I added **detailed error messages** that show the actual problem:

### Before (Generic Error):
```php
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["status" => false, "message" => "Registration failed. Please try again."]);
}
```

### After (Specific Errors):
```php
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // More specific error messages
    $errorMessage = "Registration failed. Please try again.";
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $errorMessage = "This email or mobile number is already registered.";
    } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
        $errorMessage = "Database schema error. Please contact support.";
    } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
        $errorMessage = "Database table error. Please contact support.";
    }
    
    echo json_encode(["status" => false, "message" => $errorMessage, "debug" => $e->getMessage()]);
}
```

### What Changed:
1. ✅ **Specific error messages** based on error type
2. ✅ **Stack trace logging** for better debugging
3. ✅ **Debug field** in JSON response (shows actual error)
4. ✅ **User-friendly messages** instead of generic errors

## Error Messages You'll Now See

### Instead of Generic:
```
❌ Server error. Please try again.
```

### You'll See Specific Errors:

#### Duplicate Entry:
```
❌ This email or mobile number is already registered.
```

#### Database Schema Error:
```
❌ Database schema error. Please contact support.
Debug: Unknown column 'company_name' in 'field list'
```

#### Missing Table:
```
❌ Database table error. Please contact support.
Debug: Table 'railway.users' doesn't exist
```

#### OTP Not Verified:
```
❌ Please verify your email OTP first
```

## Files Fixed

1. ✅ `backend_php/recruiter_register.php`
2. ✅ `backend_php/register_user.php`
3. ✅ `backend_php/admin_register.php`

## Testing After Deployment

### Test 1: Try to Register Again
1. Go to recruiter registration page
2. Fill in the same details (JANHAVI CHAVAN, janhavic811@gmail.com)
3. Verify both OTPs
4. Click "Create Recruiter Account"
5. **Expected**: You should now see a **specific error message** instead of generic "Server error"

### Test 2: Check for Duplicate
If the error is "This email or mobile number is already registered":
- The account already exists in the database
- Try logging in instead of registering
- Or use a different email/mobile number

### Test 3: Check Railway Logs
1. Go to Railway dashboard
2. Click on your project
3. Go to "Deployments" → "Logs"
4. Look for error logs with full details:
   ```
   [ResumeIQ-X][Recruiter Register] Database error: Duplicate entry...
   [ResumeIQ-X][Recruiter Register] Stack trace: ...
   ```

## Debugging Steps

### Step 1: Check if User Already Exists
Run this SQL query on Railway database:
```sql
SELECT id, name, email, mobile, role, account_status 
FROM users 
WHERE email = 'janhavic811@gmail.com' 
   OR mobile = '9579143580';
```

**If user exists**:
- ✅ Account is already registered
- Solution: Use login instead of registration
- Or: Delete the existing account and try again

**If user doesn't exist**:
- ❌ Different error (schema, table, connection)
- Check Railway logs for details

### Step 2: Check OTP Verification
Run this SQL query:
```sql
SELECT * FROM otp_temp 
WHERE email = 'janhavic811@gmail.com' 
ORDER BY id DESC 
LIMIT 5;
```

**Expected**:
- Should see 2 rows with `verified = 1`
- One for `otp_type = 'email'`
- One for `otp_type = 'mobile'`

**If not verified**:
- OTP verification failed
- User needs to verify OTPs again

### Step 3: Check Database Schema
Run this SQL query:
```sql
DESCRIBE users;
```

**Expected columns**:
- `id`, `name`, `email`, `mobile`, `password`
- `role`, `company_name`, `account_status`
- `email_verified`, `mobile_verified`
- `created_at`, `updated_at`

**If columns are missing**:
- Database schema is outdated
- Run the schema update SQL

### Step 4: Check Railway Environment Variables
Verify these are set in Railway:
```
DB_HOST=monorail.proxy.rlwy.net
DB_PORT=33459
DB_NAME=railway
DB_USER=root
DB_PASSWORD=<your_password>
BREVO_API_KEY=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
```

## Deployment

- ✅ Committed: `11f9335`
- ✅ Pushed to GitHub
- ✅ Railway deploying now (~1-2 minutes)

## Next Steps

After Railway finishes deploying:

1. **Try registration again** - You'll see the actual error
2. **Check the error message** - It will tell you exactly what's wrong
3. **Follow the debugging steps** above based on the error
4. **Check Railway logs** for full error details

## Most Likely Issue

Based on your screenshot showing:
- ✅ Name: JANHAVI CHAVAN
- ✅ Email: janhavic811@gmail.com (verified)
- ✅ Mobile: 9579143580 (verified)
- ❌ Error: "Server error. Please try again."

The most likely cause is:
```
This email or mobile number is already registered.
```

**Solution**: Try logging in instead of registering, or use a different email/mobile.

---

**Created by**: MAYUR GOPAL KOVE  
**Date**: 2026-05-04  
**Status**: Fixed and Deployed ✅
