# 🔍 Email OTP Debug Guide - Railway

## समस्या
- ✅ Mobile SMS काम करत आहे (Twilio)
- ❌ Email OTP येत नाही

---

## Debug Steps

### Step 1: Railway Logs तपासा

1. **Railway Dashboard उघडा**: https://railway.app
2. **ResumeIQ-X** project उघडा
3. **Deployments** tab क्लिक करा
4. Latest deployment वर क्लिक करा
5. **View Logs** क्लिक करा
6. Search करा: `[ResumeIQ-X][EMAIL]`

**काय शोधायचे:**

✅ **Success log** (email sent):
```
[ResumeIQ-X][EMAIL] Email sent successfully to mayurkove428@gmail.com
```

❌ **Error logs** (email failed):
```
[ResumeIQ-X][EMAIL] MAIL_USERNAME or MAIL_PASSWORD not set in .env
[ResumeIQ-X][EMAIL] STARTTLS connect failed
[ResumeIQ-X][EMAIL] AUTH failed (wrong password / not an App Password?)
[ResumeIQ-X][EMAIL] TLS upgrade failed
```

---

## Common Issues & Solutions

### Issue 1: Gmail App Password Invalid

**Symptoms:**
```
[ResumeIQ-X][EMAIL] AUTH failed (wrong password / not an App Password?)
```

**Solution:**
1. जा: https://myaccount.google.com/apppasswords
2. Check करा की `yrfomdszuixayykn` valid आहे
3. Invalid असेल तर:
   - New App Password generate करा
   - Railway Variables मध्ये update करा:
     ```
     MAIL_PASSWORD=your_new_16_char_app_password
     ```
   - Save करा आणि redeploy wait करा

---

### Issue 2: Gmail SMTP Blocked

**Symptoms:**
```
[ResumeIQ-X][EMAIL] STARTTLS connect failed
[ResumeIQ-X][EMAIL] SSL connect failed
```

**Solution:**
1. Check Gmail account security:
   - जा: https://myaccount.google.com/security
   - "Less secure app access" OFF असेल तर App Password use करा (already done)
   - "2-Step Verification" ON असायला हवे (App Password साठी required)

2. Check if Gmail blocked Railway IP:
   - Gmail inbox check करा
   - "Suspicious activity" email आले का?
   - "Allow access" क्लिक करा

---

### Issue 3: Railway Environment Variables Not Set

**Symptoms:**
```
[ResumeIQ-X][EMAIL] MAIL_USERNAME or MAIL_PASSWORD not set in .env
```

**Solution:**
1. Railway Dashboard > Variables tab
2. Check करा की हे variables exist आहेत:
   - `MAIL_HOST=smtp.gmail.com`
   - `MAIL_PORT=587`
   - `MAIL_USERNAME=mayurkove428@gmail.com`
   - `MAIL_PASSWORD=yrfomdszuixayykn`
   - `MAIL_FROM_NAME=ResumeIQ-X`
   - `MAIL_FROM_ADDRESS=mayurkove428@gmail.com`

3. Missing असतील तर पुन्हा add करा आणि Save करा

---

### Issue 4: PHP mail() Fallback Not Working

**Symptoms:**
```
[ResumeIQ-X][EMAIL] Trying PHP mail() fallback
[ResumeIQ-X][EMAIL] mail() fallback also failed
```

**Solution:**
Railway वर PHP mail() function disabled असू शकतो. SMTP method (port 587/465) use करायला हवा.

Check करा:
- SMTP credentials correct आहेत का?
- Gmail App Password valid आहे का?

---

## Testing Email OTP

### Test 1: Local Server (Localhost)
```
URL: http://localhost/ResumeIQ-X/frontend/register.html
Expected: Email OTP येतो ✅
```

### Test 2: Railway Deployment
```
URL: https://resumeiq-x-production.up.railway.app/frontend/register.html
Expected: Email OTP यायला हवा
```

**Test करताना:**
1. Email enter करा: `mayurkove428@gmail.com`
2. **Send Email OTP** क्लिक करा
3. Browser Console check करा (F12 > Console)
4. Network tab check करा (F12 > Network)
5. Response message काय आहे ते बघा

**Expected Response:**
```json
{
  "status": true,
  "message": "OTP sent to your email",
  "email_sent": true
}
```

**Error Response:**
```json
{
  "status": true,
  "message": "OTP sent to your email",
  "email_sent": false
}
```
→ email_sent: false म्हणजे email send failed

---

## Quick Fix: Alternative Email Provider

Gmail काम करत नसेल तर alternative SMTP provider use करा:

### Option 1: SendGrid (Free 100 emails/day)
```
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
```

### Option 2: Mailgun (Free 5000 emails/month)
```
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@your-domain.mailgun.org
MAIL_PASSWORD=your_mailgun_password
```

### Option 3: Brevo (Free 300 emails/day)
```
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email
MAIL_PASSWORD=your_brevo_smtp_key
```

---

## Immediate Action Required

### 1️⃣ Check Railway Logs
```
Railway Dashboard > Deployments > Latest > View Logs
Search: [ResumeIQ-X][EMAIL]
```

### 2️⃣ Verify Gmail App Password
```
https://myaccount.google.com/apppasswords
Current: yrfomdszuixayykn
Status: Valid? ✅ / Invalid? ❌
```

### 3️⃣ Test Email OTP
```
https://resumeiq-x-production.up.railway.app/frontend/register.html
Click: Send Email OTP
Check: Email inbox (+ spam folder)
```

### 4️⃣ Check Browser Console
```
F12 > Console tab
Look for: API response from send_otp.php
Check: email_sent: true or false?
```

---

## Expected Behavior After Fix

✅ Click "Send Email OTP"  
✅ Message: "OTP sent to your email"  
✅ Email received within 10-30 seconds  
✅ Subject: "ResumeIQ-X — Email Verification OTP"  
✅ 6-digit OTP visible in email  

---

## Contact Support

अजूनही काम करत नसेल तर:
1. Railway logs screenshot share करा
2. Browser console errors share करा
3. Network tab response share करा

---

**Status**: 🔍 DEBUGGING EMAIL OTP

**Next Step**: Railway logs check करा आणि error message शोधा

**ETA**: 5-10 minutes (debug + fix)
