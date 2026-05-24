# "Resume ID Missing" Error - Complete Troubleshooting Guide

## What You're Seeing

```
⚠️ Analysis Not Available
Resume ID missing
```

## Why This Happens

This error occurs in **3 specific scenarios**:

### Scenario 1: Direct URL Access (Most Common)
**What happened**: You accessed `analysis_result_viewer.php` directly without the `resume_id` parameter

**Examples**:
- ❌ `https://resumeiq-x.railway.app/frontend/analysis_result_viewer.php`
- ✅ `https://resumeiq-x.railway.app/frontend/analysis_result_viewer.php?resume_id=123`

**How it happens**:
- Typing URL directly in browser
- Clicking bookmarked URL without parameter
- Browser history entry without parameter
- Shared link without parameter

**Solution**: Always access the report through the "View Report" button on `candidate_my_status.php`

### Scenario 2: Invalid Resume ID
**What happened**: The `resume_id` parameter exists but is invalid (0, empty, or non-numeric)

**Examples**:
- ❌ `?resume_id=` (empty)
- ❌ `?resume_id=0` (zero)
- ❌ `?resume_id=abc` (non-numeric, becomes 0 after intval())

**How it happens**:
- Database didn't save resume ID properly
- Session lost resume ID
- URL was manually edited incorrectly

**Solution**: Check database to ensure resume has valid ID

### Scenario 3: API Call Failure
**What happened**: The page loaded with valid `resume_id`, but the API call to `get_analysis_preview.php` failed

**How it happens**:
- Network error
- API endpoint not found (404)
- Session expired
- Database connection failed

**Solution**: Check browser console for network errors

## How to Debug

### Step 1: Check the URL
Open the page and look at the URL in the address bar:

```
https://resumeiq-x.railway.app/frontend/analysis_result_viewer.php?resume_id=XXX
                                                                     ↑
                                                    This parameter MUST exist!
```

- ✅ If `?resume_id=XXX` exists → Go to Step 2
- ❌ If missing → You accessed the page directly (Scenario 1)

### Step 2: Check Browser Console
Press **F12** → **Console** tab

Look for these logs:
```javascript
Fetching analysis for resume_id: 123  // ← Should show a number, not 0
```

- ✅ If you see a valid number (1, 2, 3, etc.) → Go to Step 3
- ❌ If you see 0 or nothing → Invalid resume ID (Scenario 2)

### Step 3: Check Network Tab
Press **F12** → **Network** tab → Reload page

Look for the API call:
```
get_analysis_preview.php?resume_id=123
```

Click on it and check:
- **Status**: Should be `200 OK`
  - ❌ If `404 Not Found` → API endpoint issue
  - ❌ If `500 Internal Server Error` → Server/database issue
  
- **Response**: Should be JSON like:
  ```json
  {
    "status": true,
    "data": { ... }
  }
  ```
  - ❌ If `{"status": false, "message": "Resume ID missing"}` → API didn't receive resume_id
  - ❌ If `{"status": false, "message": "Analysis not ready"}` → Analysis not completed yet

### Step 4: Check Railway Logs (For Developers)
If you have access to Railway dashboard:

1. Go to Railway project
2. Click on your service
3. Go to "Deployments" → "Logs"
4. Look for these logs:
   ```
   analysis_result_viewer.php accessed
   resume_id from GET: 123
   resume_id after intval: 123
   ```

This will show exactly what the server received.

## Solutions

### Solution 1: Use the Correct Flow
**Always follow this flow**:

```
1. Upload resume (upload_resume.php)
   ↓
2. View status (candidate_my_status.php)
   ↓
3. Wait for "Analysis Complete" status
   ↓
4. Click "View Report" button
   ↓
5. See analysis report (analysis_result_viewer.php?resume_id=XXX)
```

**Never**:
- Bookmark the analysis_result_viewer.php URL
- Type the URL directly
- Share the URL without the resume_id parameter

### Solution 2: Clear Browser Cache
Sometimes old JavaScript is cached:

1. Press **Ctrl + Shift + Delete** (Windows) or **Cmd + Shift + Delete** (Mac)
2. Select "Cached images and files"
3. Click "Clear data"
4. Reload the page

### Solution 3: Check Database
Run this SQL query to verify resume IDs:

```sql
SELECT 
    r.id as resume_id,
    r.user_id,
    r.analysis_status,
    ar.id as analysis_result_id,
    ar.candidate_name
FROM resumes r
LEFT JOIN analysis_results ar ON ar.resume_id = r.id
WHERE r.user_id = YOUR_USER_ID
ORDER BY r.id DESC
LIMIT 5;
```

Expected output:
- `resume_id` should be a positive integer (1, 2, 3, etc.)
- `analysis_status` should be "completed"
- `analysis_result_id` should exist (not NULL)

### Solution 4: Force Redirect
If you keep seeing the error, the redirect might not be working. Try accessing:

```
https://resumeiq-x.railway.app/frontend/candidate_my_status.php
```

This will show your status and provide the correct "View Report" link.

## Prevention

### For Users:
1. ✅ Always use the "View Report" button
2. ❌ Don't bookmark the analysis_result_viewer.php URL
3. ❌ Don't type the URL manually
4. ✅ If you want to bookmark, bookmark `candidate_my_status.php` instead

### For Developers:
1. ✅ Always validate `resume_id` parameter
2. ✅ Use absolute URLs for API calls
3. ✅ Add proper error handling
4. ✅ Log important parameters for debugging
5. ✅ Redirect to status page when parameter is missing

## Technical Details

### PHP Validation (Lines 3-18):
```php
// Check if resume_id exists and is not empty
if(!isset($_GET["resume_id"]) || empty($_GET["resume_id"])){
    header("Location: candidate_my_status.php");
    exit();
}

// Convert to integer (non-numeric becomes 0)
$resumeId = intval($_GET["resume_id"]);

// Check if resume_id is 0 (invalid)
if($resumeId === 0){
    header("Location: candidate_my_status.php");
    exit();
}
```

### JavaScript API Call (Line 283):
```javascript
const response = await fetch(
  getApiUrl("get_analysis_preview.php") + "?resume_id=<?php echo $resumeId;?>",
  {credentials:"include"}
);
```

### API Validation (backend_php/get_analysis_preview.php):
```php
$resumeId = intval($_GET["resume_id"] ?? 0);
if (!$resumeId) {
    echo json_encode(["status" => false, "message" => "Resume ID missing"]);
    exit;
}
```

## Quick Reference

| Error Message | Cause | Solution |
|--------------|-------|----------|
| "Resume ID missing" | No `?resume_id=` in URL | Use "View Report" button |
| "Analysis not ready" | Analysis not completed | Wait for completion |
| "Login required" | Session expired | Log in again |
| 404 Not Found | API endpoint issue | Check deployment |
| Network error | Connection issue | Check internet |

## Still Having Issues?

If none of the above solutions work:

1. **Take screenshots** of:
   - The URL in address bar
   - Browser console (F12 → Console)
   - Network tab (F12 → Network → get_analysis_preview.php)

2. **Check Railway logs** for server-side errors

3. **Verify database** has valid resume IDs

4. **Clear all browser data** and try again

5. **Try a different browser** to rule out browser-specific issues

---

**Created by**: MAYUR GOPAL KOVE  
**Date**: 2026-05-04  
**Status**: Deployed with Enhanced Debugging ✅
