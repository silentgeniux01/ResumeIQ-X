# 🔧 OTP 404 Error Fix

## समस्या
Browser console मध्ये दिसत आहे:
```
Failed to load resource: the server responded with a status of 404 ()
```

## कारण
`send_otp.php` file Railway वर properly deploy झाली आहे, पण browser wrong URL call करत आहे.

---

## Solution: API URL Fix

### Problem
Screenshot मध्ये दिसत आहे की browser `https://www.cloudflare.com/cdn-cgi/trace` call करत आहे instead of `send_otp.php`.

This means `apiUrl()` function काम करत नाही.

### Fix Required

Register pages मध्ये `apiUrl()` function properly define करायला हवा:

```javascript
function apiUrl(script) {
  // Railway deployment
  if (window.location.hostname.includes('railway.app')) {
    return 'https://resumeiq-x-production.up.railway.app/backend_php/' + script;
  }
  
  // Local development
  const parts = window.location.pathname.split('/');
  parts.pop(); // Remove current file
  parts.pop(); // Remove 'frontend' folder
  return window.location.origin + parts.join('/') + '/backend_php/' + script;
}
```

---

## Files to Update

1. `frontend/register.html`
2. `frontend/admin_register.html`
3. `frontend/recruiter_register.html`

---

## Testing After Fix

1. Deploy to Railway
2. Open: https://resumeiq-x-production.up.railway.app/frontend/register.html
3. F12 > Network tab
4. Click "Send Email OTP"
5. Check URL being called:
   - ✅ Should be: `https://resumeiq-x-production.up.railway.app/backend_php/send_otp.php`
   - ❌ Wrong: `https://www.cloudflare.com/...`

---

## Immediate Action

मी आता register pages update करतो with proper `apiUrl()` function.
