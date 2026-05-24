# Database Schema Error Fix - Missing company_name Column

## The Error

```
❌ Database schema error. Please contact support.
```

**Console shows**:
```
Failed to load resource: the server responded with a status of 400 ()
backend_php/recruiter_register.php
```

## Root Cause

The `users` table is **missing the `company_name` column** that recruiter registration requires.

### What Happened:
1. Frontend calls `recruiter_register.php` ✅ (now correct)
2. Backend tries to INSERT with `company_name` field
3. Database rejects: `Unknown column 'company_name' in 'field list'`
4. Error message shown: "Database schema error. Please contact support."

## The Fix

Add the missing `company_name` column to the `users` table.

### SQL Fix:
```sql
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) NULL AFTER role;
```

### How to Apply:

#### Option 1: Railway Dashboard (Recommended)
1. Go to Railway dashboard
2. Click on your MySQL database service
3. Click "Data" tab
4. Click "Query" button
5. Paste this SQL:
   ```sql
   ALTER TABLE users 
   ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) NULL AFTER role;
   ```
6. Click "Run Query"
7. Verify with:
   ```sql
   DESCRIBE users;
   ```

#### Option 2: Database Client
1. Connect to Railway MySQL:
   ```
   Host: monorail.proxy.rlwy.net
   Port: 33459
   Database: railway
   User: root
   Password: <your_password>
   ```
2. Run the ALTER TABLE command above

#### Option 3: Command Line
```bash
mysql -h monorail.proxy.rlwy.net -P 33459 -u root -p railway
```
Then run the SQL command.

## Expected Table Schema

After applying the fix, the `users` table should have these columns:

```sql
DESCRIBE users;
```

**Expected output**:
```
+------------------+--------------+------+-----+-------------------+
| Field            | Type         | Null | Key | Default           |
+------------------+--------------+------+-----+-------------------+
| id               | int          | NO   | PRI | NULL              |
| name             | varchar(255) | NO   |     | NULL              |
| email            | varchar(255) | NO   | UNI | NULL              |
| mobile           | varchar(20)  | NO   | UNI | NULL              |
| password         | varchar(255) | NO   |     | NULL              |
| role             | varchar(50)  | NO   |     | candidate         |
| company_name     | varchar(255) | YES  |     | NULL              | ← NEW!
| account_status   | varchar(50)  | NO   |     | active            |
| email_verified   | tinyint(1)   | NO   |     | 0                 |
| mobile_verified  | tinyint(1)   | NO   |     | 0                 |
| created_at       | timestamp    | NO   |     | CURRENT_TIMESTAMP |
| updated_at       | timestamp    | NO   |     | CURRENT_TIMESTAMP |
+------------------+--------------+------+-----+-------------------+
```

## Testing After Fix

### Step 1: Apply the SQL Fix
Run the ALTER TABLE command on Railway database.

### Step 2: Try Registration Again
1. Go to: `https://resumeiq-x-production.up.railway.app/frontend/recruiter_register.html`
2. Fill in details:
   - Name: SAKSHI PATIL
   - Email: sakshispatil4106@gmail.com
   - Mobile: 9579388941
3. Verify both OTPs
4. Click "Create Recruiter Account"

### Expected Results:

#### If Schema is Fixed:
```
✓ Recruiter account created! Redirecting...
```

#### If Account Already Exists:
```
❌ This email or mobile number is already registered.
```

## Why This Column Was Missing

The `company_name` column is specific to recruiter accounts and might have been:
1. Not included in the initial database schema
2. Removed during a migration
3. Never added when recruiter functionality was implemented

## Alternative: Make company_name Optional

If you don't want to add the column, you can modify the backend to make `company_name` optional:

### Option A: Remove company_name from INSERT
Edit `backend_php/recruiter_register.php` line 95-100:

**Before**:
```php
$stmt = $db->prepare("
    INSERT INTO users (
        name, email, mobile, password, role, company_name,
        account_status, email_verified, mobile_verified
    ) VALUES (
        :name, :email, :mobile, :password, 'recruiter', :company_name,
        'active', 1, 1
    )
");
```

**After**:
```php
$stmt = $db->prepare("
    INSERT INTO users (
        name, email, mobile, password, role,
        account_status, email_verified, mobile_verified
    ) VALUES (
        :name, :email, :mobile, :password, 'recruiter',
        'active', 1, 1
    )
");
```

And remove `:company_name` from execute:
```php
$stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':mobile' => $mobile,
    ':password' => $hashedPassword,
    // Remove: ':company_name' => $companyName
]);
```

## Recommended Solution

**Add the column** (Option 1) because:
1. ✅ Recruiters should have company information
2. ✅ Maintains data integrity
3. ✅ Allows future features (company-based filtering, etc.)
4. ✅ Matches the intended design

## Quick Check: Does Column Exist?

Run this SQL to check:
```sql
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'railway' 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'company_name';
```

**If result is empty**: Column doesn't exist → Apply the fix  
**If result shows company_name**: Column exists → Different issue

## Summary

1. ✅ **Error identified**: Missing `company_name` column
2. ✅ **Fix created**: SQL ALTER TABLE command
3. ⏳ **Action required**: Run SQL on Railway database
4. ✅ **After fix**: Registration will work

---

**Created by**: MAYUR GOPAL KOVE  
**Date**: 2026-05-04  
**Status**: Fix Ready - Awaiting Database Update ⏳
