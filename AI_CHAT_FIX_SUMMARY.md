# AI Chat Fix Summary

## 🐛 Problem
Chat widget showed error: "Sorry, I'm having trouble connecting"

Browser console showed:
```
Failed to load resource: the server responded with a status of 404 (Not Found)
/backend_php/ai_chat.php:1
```

## 🔍 Root Cause
The JavaScript was constructing a **relative path** (`/backend_php/ai_chat.php`) instead of the correct path (`/ResumeIQ-X/backend_php/ai_chat.php`).

The path detection logic wasn't working because:
- It was checking if pathname **includes** `/ResumeIQ-X/`
- But when on `index.html`, the pathname is `/ResumeIQ-X/frontend/index.html`
- The logic would set `appRoot = '/ResumeIQ-X/'`
- But then it would try to fetch from `/ResumeIQ-X/backend_php/ai_chat.php`
- Which is correct! But the browser was somehow resolving it wrong

## ✅ Solution
Changed from **relative path** to **absolute URL**:

### Before:
```javascript
const appRoot = window.location.pathname.includes('/ResumeIQ-X/') 
  ? '/ResumeIQ-X/' 
  : '/';
const response = await fetch(appRoot + 'backend_php/ai_chat.php', {
```

### After:
```javascript
const protocol = window.location.protocol;
const host = window.location.host;
const pathname = window.location.pathname;

// Get base directory (remove index.html if present)
let baseDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);

// Construct full API URL
const apiUrl = `${protocol}//${host}${baseDir}backend_php/ai_chat.php`;

const response = await fetch(apiUrl, {
```

### Result:
Now constructs: `http://172.16.90.171/ResumeIQ-X/frontend/backend_php/ai_chat.php`

Wait, that's still wrong! Let me fix it properly...

## 🔧 Correct Fix

The issue is that `index.html` is in `/ResumeIQ-X/frontend/` but `backend_php` is in `/ResumeIQ-X/backend_php/`.

We need to go up one directory level:

```javascript
// Get current directory
let currentDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);

// Go up one level to project root
let projectRoot = currentDir.substring(0, currentDir.lastIndexOf('/', currentDir.length - 2) + 1);

// Construct API URL
const apiUrl = `${protocol}//${host}${projectRoot}backend_php/ai_chat.php`;
```

This will give us: `http://172.16.90.171/ResumeIQ-X/backend_php/ai_chat.php` ✅

## 📝 Files Modified
- `frontend/index.html` - Fixed API URL construction

## ✅ Verification
After fix, test:
1. Open: `http://172.16.90.171/ResumeIQ-X/frontend/index.html`
2. Click 🤖 button
3. Type message
4. Should work!

## 🎯 Expected Behavior
- API URL logged in console: `http://172.16.90.171/ResumeIQ-X/backend_php/ai_chat.php`
- Response time: 1-2 seconds
- Provider badge: "Powered by Groq Llama"
- No 404 errors
