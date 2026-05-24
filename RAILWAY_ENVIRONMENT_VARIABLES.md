# 🚀 Complete Railway Environment Variables List

**For ResumeIQ-X Deployment**  
**Creator**: MAYUR GOPAL KOVE

---

## 📋 Copy-Paste Ready Variables for Railway

### **CRITICAL - Required for App to Work**

```env
DB_NAME=railway
APP_ENV=production
APP_NAME=ResumeIQ-X
APP_SECRET_KEY=AbCdEfGh1234567890XyZaBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890AbCd
NODE_API_PORT=5000
```

---

### **EMAIL CONFIGURATION** (Required for OTP)

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mayurkove428@gmail.com
MAIL_PASSWORD=yrfomdszuixayykn
MAIL_FROM_NAME=ResumeIQ-X
MAIL_FROM_ADDRESS=mayurkove428@gmail.com
```

---

### **CLOUDINARY** (File Storage)

```env
CLOUDINARY_CLOUD_NAME=dw7e4hyty
CLOUDINARY_API_KEY=733153665547652
CLOUDINARY_API_SECRET=uk5XjPQwdV9BhE3WNBjzJ3vNMpQ
CLOUDINARY_URL=cloudinary://733153665547652:uk5XjPQwdV9BhE3WNBjzJ3vNMpQ@dw7e4hyty
```

---

### **AI CHAT - LLM FALLBACK CHAIN** ⭐

**Primary Provider (Fastest, Free):**
```env
MEERA_FORCE_PROVIDER=groq
GROQ_API_KEY=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
```

**Fallback Chain (Cloud → Local):**

**1. Groq (Primary - Fastest)**
```env
GROQ_API_KEY=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
```

**2. OpenAI (Fallback 1)**
```env
OPENAI_API_KEY=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
OPENAI_QUOTA_EXCEEDED=0
```

**3. Gemini (Fallback 2)**
```env
GEMINI_API_KEY=AIzaSyBiKp2NVekn8Lv5ozVvS5QPcxIzWJbK6bg
```

**4. Anthropic Claude (Fallback 3)**
```env
ANTHROPIC_API_KEY=s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
```

**5. DeepSeek (Fallback 4)**
```env
DEEPSEEK_API_KEY=sk-401fbd42cf00493b8c28db07f3027460
```

**6. Ollama (Local Fallback - Final)**
```env
OLLAMA_HOST=http://localhost:11434
OLLAMA_MODEL=llama3.1:latest
```

---

### **SMS GATEWAY** (Optional - for Mobile OTP)

**Twilio (International):**
```env
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=AC0df40af6364bf6af7563ae67f1d935b5
TWILIO_AUTH_TOKEN=80d4b15f3d8c97666771f24608f21557
TWILIO_FROM_NUMBER=+15075965425
```

**OR MSG91 (India):**
```env
SMS_GATEWAY=msg91
MSG91_AUTH_KEY=your_auth_key
MSG91_SENDER_ID=RSMIQX
MSG91_TEMPLATE_ID=your_template_id
```

**OR Fast2SMS (India):**
```env
SMS_GATEWAY=fast2sms
FAST2SMS_API_KEY=your_api_key
FAST2SMS_SENDER_ID=RSMIQX
```

---

### **SECURITY & MISC**

```env
SESSION_LIFETIME=7200
MAX_UPLOAD_SIZE_MB=10
ALLOWED_FILE_TYPES=pdf,txt,doc,docx,png,jpg,jpeg
JWT_SECRET_KEY=change_this_to_a_strong_random_secret_key_in_production
ENCRYPTION_KEY=change_this_to_a_32_byte_hex_key_in_production
```

---

### **CREATOR IDENTITY** (Optional but Recommended)

```env
MAYURX__CREATOR=Mayur Gopal Kove
MAYURX__CREATOR_ID=MAYUR_GOPAL_KOVE_20040706
MAYURX__CREATOR_DOB=2004-07-06
MAYURX__ENVIRONMENT=production
```

---

### **RESEARCH & DATA** (Optional)

```env
TAVILY_API_KEY=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
GITHUB_TOKEN=your_github_personal_access_token_here
SEMANTIC_SCHOLAR_API_KEY=your_semantic_scholar_key_here
```

---

### **VOICE & AVATAR** (Optional - for Meera Voice)

```env
ELEVENLABS_API_KEY=sk_8fe8dcf84c90355ed32de27e4cc59232c85f3193b65871c9
ELEVENLABS_VOICE_ID=Ms9OTvWb99V6DwRHZn6q
```

---

## 🎯 Priority Order for Railway Setup

### **MUST HAVE** (App won't work without these):
1. ✅ `DB_NAME=railway`
2. ✅ `APP_ENV=production`
3. ✅ `APP_SECRET_KEY=<64-char-string>`
4. ✅ `NODE_API_PORT=5000`
5. ✅ Email variables (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD)

### **HIGHLY RECOMMENDED** (For full functionality):
6. ✅ `GROQ_API_KEY` (AI Chat - fastest, free)
7. ✅ `MEERA_FORCE_PROVIDER=groq`
8. ✅ Cloudinary variables (file storage)
9. ✅ Twilio variables (SMS OTP)

### **OPTIONAL** (Nice to have):
10. Other LLM API keys (OpenAI, Gemini, Anthropic, DeepSeek)
11. Creator identity variables
12. Research API keys (Tavily, GitHub)
13. Voice API keys (ElevenLabs)

---

## 📊 LLM Fallback Chain Explained

Your AI Chat uses this fallback order:

```
1. Groq (Primary)          → Fastest, free, works great
   ↓ (if fails)
2. OpenAI (Fallback 1)     → Paid, high quality
   ↓ (if fails)
3. Gemini (Fallback 2)     → Google's LLM, free tier
   ↓ (if fails)
4. Anthropic (Fallback 3)  → Claude, high quality
   ↓ (if fails)
5. DeepSeek (Fallback 4)   → Chinese LLM, cheap
   ↓ (if fails)
6. Ollama (Final Fallback) → Local LLM, always available
```

**Recommendation**: Set `MEERA_FORCE_PROVIDER=groq` to use Groq as primary (fastest and free).

---

## 🔐 Security Notes

### **NEVER Commit These to Git:**
- ❌ API Keys
- ❌ Database passwords
- ❌ SMTP passwords
- ❌ Secret keys

### **Generate Secure Keys:**

**APP_SECRET_KEY (64 characters):**
```powershell
# PowerShell
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 64 | % {[char]$_})
```

**JWT_SECRET_KEY (32+ characters):**
```powershell
# PowerShell
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 32 | % {[char]$_})
```

---

## 📝 How to Add Variables in Railway

### **Method 1: One by One**
1. Go to ResumeIQ-X service
2. Click **"Variables"** tab
3. Click **"+ New Variable"**
4. Enter name and value
5. Click **"Add"**
6. Repeat for each variable

### **Method 2: Bulk Import** (Faster!)
1. Go to ResumeIQ-X service
2. Click **"Variables"** tab
3. Click **"Raw Editor"** button
4. Paste all variables at once
5. Click **"Save"**

**Format for Raw Editor:**
```
DB_NAME=railway
APP_ENV=production
APP_SECRET_KEY=your_key_here
MAIL_HOST=smtp.gmail.com
GROQ_API_KEY=your_groq_key
```

---

## ✅ Verification Checklist

After adding variables, verify:

- [ ] Database variables set (DB_NAME, etc.)
- [ ] App variables set (APP_ENV, APP_SECRET_KEY)
- [ ] Email variables set (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD)
- [ ] At least one LLM API key set (GROQ_API_KEY recommended)
- [ ] MEERA_FORCE_PROVIDER=groq set
- [ ] Cloudinary variables set (optional but recommended)
- [ ] SMS gateway variables set (optional)

---

## 🚀 After Adding Variables

1. **Redeploy** (Railway auto-redeploys when variables change)
2. **Wait 2-3 minutes** for deployment
3. **Test your app** at the Railway URL
4. **Check logs** if anything fails

---

## 🆘 Troubleshooting

**If AI Chat doesn't work:**
- Check `GROQ_API_KEY` is set correctly
- Check `MEERA_FORCE_PROVIDER=groq` is set
- Check Railway logs for API errors

**If Email OTP doesn't work:**
- Verify Gmail App Password (not regular password)
- Check `MAIL_USERNAME` and `MAIL_PASSWORD`
- Test with: https://myaccount.google.com/apppasswords

**If SMS OTP doesn't work:**
- Verify Twilio credentials
- Check `SMS_GATEWAY=twilio` is set
- Verify phone number format: `+15075965425`

---

**Created by**: MAYUR GOPAL KOVE  
**Date**: 2026-05-03  
**Purpose**: Complete Railway environment configuration
