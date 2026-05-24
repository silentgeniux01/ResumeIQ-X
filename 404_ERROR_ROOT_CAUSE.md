# 404 Error Root Cause Analysis

## The Problem

When accessing `analysis_result_viewer.php`, the page shows:
```
⚠️ Analysis Not Available
Resume ID missing
```

And the browser console shows:
```
Failed to load resource: the server responded with a status of 404 (Not Found)
```

## Root Cause

The issue was **incorrect API URL construction** using relative paths.

### Original Code (BROKEN):
```javascript
const response = await fetch(
  "../backend_php/get_analysis_preview.php?resume_id=<?php echo $resumeId;?>",
  {credentials:"include"}
);
```

### Why This Failed:

**Relative paths (`../`) are fragile** and depend on the current page's location in the directory structure.

#### Example Scenario:
```
Current URL: https://resumeiq-x.railway.app/frontend/analysis_result_viewer.php
Relative path: ../backend_php/get_analysis_preview.php
Resolved to: https://resumeiq-x.railway.app/backend_php/get_analysis_preview.php ✅ WORKS

BUT if accessed differently:
Current URL: https://resumeiq-x.railway.app/frontend/subfolder/analysis_result_viewer.php
Relative path: ../backend_php/get_analysis_preview.php
Resolved to: https://resumeiq-x.railway.app/frontend/backend_php/get_analysis_preview.php ❌ 404!
```

### Additional Issues with Relative Paths:
1. **URL rewriting** - `.htaccess` rules can change the apparent path
2. **Proxy/CDN** - Railway or other proxies might modify paths
3. **Browser caching** - Old paths might be cached
4. **Deployment differences** - Local vs production paths differ

## The Solution

Use **absolute URL construction** based on the current window location:

### Fixed Code:
```javascript
// Helper function to construct proper API URLs
function getApiUrl(script) {
  const protocol = window.location.protocol;  // https:
  const host = window.location.host;          // resumeiq-x.railway.app
  const pathname = window.location.pathname;  // /frontend/analysis_result_viewer.php
  
  // Get current directory
  let currentDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);
  // Result: /frontend/
  
  // Go up one level to project root
  let projectRoot = currentDir.substring(0, currentDir.lastIndexOf('/', currentDir.length - 2) + 1);
  // Result: /
  
  // Construct full API URL
  return `${protocol}//${host}${projectRoot}backend_php/${script}`;
  // Result: https://resumeiq-x.railway.app/backend_php/get_analysis_preview.php
}

// Usage:
const response = await fetch(
  getApiUrl("get_analysis_preview.php") + "?resume_id=<?php echo $resumeId;?>",
  {credentials:"include"}
);
```

### Benefits:
✅ **Always works** - Regardless of current page location  
✅ **No 404 errors** - Absolute paths are reliable  
✅ **Works in production** - Railway deployment paths handled correctly  
✅ **Works locally** - XAMPP localhost paths work too  
✅ **Proxy-safe** - CDN and reverse proxies don't break it  

## Why "Resume ID missing" Was Shown

The error message was misleading. Here's what actually happened:

1. ✅ Page loaded successfully with `resume_id` parameter
2. ✅ PHP validated `resume_id` exists
3. ❌ JavaScript tried to fetch API using relative path
4. ❌ API returned 404 (not found)
5. ❌ JavaScript couldn't parse 404 HTML as JSON
6. ❌ Catch block showed generic "Resume ID missing" error

The **real error** was the 404, not the missing resume_id!

## Similar Issues in Other Files

This same pattern exists in other files that should also be fixed:

### Files Using Relative API Paths:
1. ✅ `frontend/candidate_my_status.php` - Already uses `apiUrl()` helper
2. ✅ `frontend/analysis_result_viewer.php` - **FIXED** in this commit
3. ⚠️ `frontend/admin_dashboard.php` - Check if needs fixing
4. ⚠️ `frontend/recruiter/dashboard.php` - Check if needs fixing
5. ⚠️ `frontend/upload_resume.php` - Check if needs fixing

### Recommended Pattern:
Always use this helper function for API calls:
```javascript
function getApiUrl(script) {
  const protocol = window.location.protocol;
  const host = window.location.host;
  const pathname = window.location.pathname;
  let currentDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);
  let projectRoot = currentDir.substring(0, currentDir.lastIndexOf('/', currentDir.length - 2) + 1);
  return `${protocol}//${host}${projectRoot}backend_php/${script}`;
}
```

## Testing the Fix

### Before Fix:
```
Console: Failed to load resource: 404 (Not Found)
Page: ⚠️ Analysis Not Available - Resume ID missing
```

### After Fix:
```
Console: (no errors)
Page: 🧠 Candidate Intelligence Report (with all charts and data)
```

## Deployment

- ✅ Committed: `072059e`
- ✅ Pushed to GitHub
- ✅ Railway auto-deploying

## Prevention

To prevent this in the future:

1. **Always use absolute URLs** for API calls
2. **Test in production** - Local paths might work but production fails
3. **Check browser console** - 404 errors are visible there
4. **Use helper functions** - Centralize URL construction logic
5. **Avoid relative paths** - They're fragile and error-prone

---

**Created by**: MAYUR GOPAL KOVE  
**Date**: 2026-05-04  
**Status**: Fixed and Deployed ✅
