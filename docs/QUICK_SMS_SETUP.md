# Quick SMS Setup Guide

Get mobile OTP working via SMS in 5 minutes!

## Step 1: Choose Your SMS Provider

### For India 🇮🇳
- **Fast2SMS** (Easiest, fastest setup)
- **MSG91** (More reliable, requires DLT)

### For International 🌍
- **Twilio** (Best for global use)

---

## Step 2: Get API Credentials

### Option A: Fast2SMS (India - Quickest)

1. Sign up: https://www.fast2sms.com/register
2. Go to: https://www.fast2sms.com/dashboard/dev-api
3. Copy your **API Key**

### Option B: MSG91 (India - Production)

1. Sign up: https://msg91.com/signup
2. Complete KYC verification
3. Go to: https://control.msg91.com/app/
4. Copy your **Auth Key**
5. Create Sender ID (takes 1-2 days approval)

### Option C: Twilio (International)

1. Sign up: https://www.twilio.com/try-twilio
2. Go to: https://www.twilio.com/console
3. Copy **Account SID** and **Auth Token**
4. Get a phone number: https://www.twilio.com/console/phone-numbers

---

## Step 3: Update Your .env File

### For Fast2SMS:
```env
SMS_GATEWAY=fast2sms
FAST2SMS_API_KEY=your_api_key_here
FAST2SMS_SENDER_ID=RSMIQX
```

### For MSG91:
```env
SMS_GATEWAY=msg91
MSG91_AUTH_KEY=your_auth_key_here
MSG91_SENDER_ID=RSMIQX
MSG91_TEMPLATE_ID=your_template_id_here
```

### For Twilio:
```env
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_FROM_NUMBER=+1234567890
```

---

## Step 4: Test It!

Run the test script:

```bash
php backend_php/test_sms.php +919876543210
```

Replace `+919876543210` with your mobile number (include country code).

**Expected output:**
```
✓ SMS sent successfully!
Check your mobile phone for the OTP: 123456
```

---

## Step 5: Try Registration

1. Go to your registration page
2. Enter your mobile number
3. Click "Send OTP"
4. You should receive SMS on your mobile! 📱

---

## Troubleshooting

### "SMS gateway not configured"
→ Make sure `SMS_GATEWAY` is set in `.env` (not `none`)

### "Invalid mobile number"
→ Include country code: `+919876543210` (not `9876543210`)

### "Credentials missing"
→ Check your `.env` file has the correct API keys

### Still not working?
→ Check `docs/sms_setup_guide.md` for detailed troubleshooting

---

## Email Fallback

Don't worry! If SMS fails, the system automatically sends OTP via email as a backup. Your users can still verify their mobile number.

---

## Need Help?

- **Detailed Guide:** `docs/sms_setup_guide.md`
- **Test Script:** `php backend_php/test_sms.php`
- **Provider Support:**
  - Twilio: https://support.twilio.com/
  - MSG91: https://msg91.com/help
  - Fast2SMS: https://www.fast2sms.com/contact
