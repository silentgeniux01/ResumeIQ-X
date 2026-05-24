# 🔧 Railway OTP Fix - Step by Step Guide

## समस्या
- ✅ Local server वर OTP येतो
- ❌ Railway वर OTP येत नाही

## कारण
Railway वर email आणि SMS environment variables set नाहीत.

---

## ✅ Solution: Railway Variables Add करा

### Step 1: Railway Dashboard उघडा
1. Browser मध्ये जा: https://railway.app
2. Login करा
3. **ResumeIQ-X** project उघडा

### Step 2: Variables Tab उघडा
1. Left sidebar मध्ये **Variables** वर क्लिक करा
2. किंवा project settings मध्ये **Variables** tab शोधा

### Step 3: Raw Editor उघडा
1. Variables page वर **Raw Editor** button दिसेल
2. त्यावर क्लिक करा
3. एक text editor उघडेल

### Step 4: Variables Paste करा

**खालील सगळे variables copy करा आणि Raw Editor मध्ये paste करा:**

```env
# EMAIL CONFIGURATION
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mayurkove428@gmail.com
MAIL_PASSWORD=yrfomdszuixayykn
MAIL_FROM_NAME=ResumeIQ-X
MAIL_FROM_ADDRESS=mayurkove428@gmail.com

# SMS CONFIGURATION
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=AC0df40af6364bf6af7563ae67f1d935b5
TWILIO_AUTH_TOKEN=80d4b15f3d8c97666771f24608f21557
TWILIO_FROM_NUMBER=+15075965425

# APP CONFIGURATION
APP_NAME=ResumeIQ-X
APP_URL=https://resumeiq-x-production.up.railway.app
```

### Step 5: Save करा
1. **Save** किंवा **Update Variables** button वर क्लिक करा
2. Railway automatically redeploy करेल (~2-3 minutes)

### Step 6: Deployment Complete होण्याची Wait करा
1. **Deployments** tab वर जा
2. Latest deployment **"Success"** होईपर्यंत wait करा
3. Green checkmark (✓) दिसला की deployment complete

---

## 🧪 Testing After Deployment

### Test 1: Email OTP
1. जा: https://resumeiq-x-production.up.railway.app/frontend/register.html
2. Email enter करा: `mayurkove428@gmail.com`
3. **Send Email OTP** क्लिक करा
4. Email check करा (inbox + spam folder)
5. 6-digit OTP मिळायला हवा

### Test 2: Mobile OTP
1. Mobile number enter करा: `+919876543210`
2. **Send Mobile OTP** क्लिक करा
3. SMS check करा (Twilio वरून येईल)
4. किंवा email check करा (SMS fail झाल्यास email fallback)

---

## 🔍 Troubleshooting

### अजूनही OTP येत नाही?

#### Check 1: Railway Logs
1. Railway Dashboard > **Deployments** tab
2. Latest deployment वर क्लिक करा
3. **View Logs** क्लिक करा
4. Search for: `[ResumeIQ-X][EMAIL]` किंवा `[ResumeIQ-X][SMS]`

**Success logs दिसायला हवेत:**
```
[ResumeIQ-X][EMAIL] Email sent successfully to mayurkove428@gmail.com
```

**Error logs दिसले तर:**
```
[ResumeIQ-X][EMAIL] MAIL_USERNAME or MAIL_PASSWORD not set in .env
```
→ Variables properly add झाले नाहीत, पुन्हा add करा

#### Check 2: Variables Properly Set आहेत का?
1. Railway Dashboard > **Variables** tab
2. Check करा की हे variables exist आहेत:
   - `MAIL_HOST`
   - `MAIL_USERNAME`
   - `MAIL_PASSWORD`
   - `TWILIO_ACCOUNT_SID`
   - `TWILIO_AUTH_TOKEN`

#### Check 3: Gmail App Password Valid आहे का?
1. जा: https://myaccount.google.com/apppasswords
2. Check करा की `yrfomdszuixayykn` valid आहे
3. Invalid असेल तर new App Password generate करा
4. Railway variables update करा

#### Check 4: Twilio Account Active आहे का?
1. जा: https://www.twilio.com/console
2. Check account balance (trial accounts: $15 credit)
3. Check phone number verified आहे का
4. Check API credentials valid आहेत का

---

## 📊 Expected Behavior After Fix

### Email OTP:
- ✅ Click "Send Email OTP"
- ✅ Message: "OTP sent to your email"
- ✅ Email received within 10-30 seconds
- ✅ Subject: "ResumeIQ-X — Email Verification OTP"
- ✅ 6-digit code visible

### Mobile OTP:
- ✅ Click "Send Mobile OTP"
- ✅ Message: "OTP sent to your mobile number"
- ✅ SMS received within 10-30 seconds (Twilio)
- ✅ OR email received (if SMS fails, fallback)
- ✅ 6-digit code visible

### Registration:
- ✅ Both OTPs verified
- ✅ Submit registration
- ✅ Account created successfully
- ✅ Redirect to login page

---

## 🎯 Quick Checklist

- [ ] Railway Dashboard उघडले
- [ ] Variables tab उघडले
- [ ] Raw Editor उघडले
- [ ] Email variables paste केले
- [ ] SMS variables paste केले
- [ ] Save/Update केले
- [ ] Deployment complete होण्याची wait केली
- [ ] Registration page test केले
- [ ] Email OTP received
- [ ] Mobile OTP received
- [ ] Registration successful

---

## 📞 Support

अजूनही problem असेल तर:
1. Railway logs screenshot घ्या
2. Browser console errors check करा (F12 > Console)
3. Network tab check करा (F12 > Network)
4. Error messages note करा

---

**Status**: ⏳ WAITING FOR RAILWAY VARIABLES SETUP

**Next Step**: Railway Dashboard > Variables > Add Email + SMS config

**ETA**: 5 minutes (2 min setup + 3 min deployment)
