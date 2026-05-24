# SMS Setup Guide - Send OTP to Real Mobile Numbers

## 🔍 Current Behavior

**Right now:**
- Mobile OTP is sent to **email** (fallback method)
- This happens because no SMS gateway is configured

**After SMS setup:**
- Mobile OTP will be sent to **actual mobile number via SMS**
- Email is only used as fallback if SMS fails

---

## 📱 SMS Gateway Options

The system supports 3 SMS gateways:

### 1. **Twilio** (Recommended for International)
- ✅ Works worldwide
- ✅ Reliable delivery
- ✅ Easy setup
- 💰 Cost: ~$0.0075 per SMS
- 🌐 Website: https://www.twilio.com

### 2. **MSG91** (Best for India)
- ✅ India-focused
- ✅ Cheaper than Twilio
- ✅ Good delivery rates
- 💰 Cost: ~₹0.15 per SMS
- 🌐 Website: https://msg91.com

### 3. **Fast2SMS** (Budget Option for India)
- ✅ Very cheap
- ✅ India only
- ⚠️ Lower reliability
- 💰 Cost: ~₹0.10 per SMS
- 🌐 Website: https://www.fast2sms.com

---

## 🚀 Quick Setup (Choose One)

### Option A: Twilio Setup (International)

#### Step 1: Create Twilio Account
1. Go to https://www.twilio.com/try-twilio
2. Sign up for free trial ($15 credit)
3. Verify your email and phone

#### Step 2: Get Credentials
1. Go to Twilio Console: https://console.twilio.com
2. Copy these values:
   - **Account SID**: `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
   - **Auth Token**: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
3. Get a phone number:
   - Click "Get a Trial Number"
   - Copy the number: `+1234567890`

#### Step 3: Configure .env File
Add these lines to your `.env` file:

```env
# SMS Configuration - Twilio
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_FROM_NUMBER=+1234567890
```

#### Step 4: Test
```bash
# Restart your web server
# Then try registering with a real mobile number
```

---

### Option B: MSG91 Setup (India)

#### Step 1: Create MSG91 Account
1. Go to https://msg91.com/signup
2. Sign up (free trial available)
3. Verify your email

#### Step 2: Get API Key
1. Login to MSG91 Dashboard
2. Go to "API" section
3. Copy your **Auth Key**: `xxxxxxxxxxxxxxxxxxxxxxxx`
4. Note your **Sender ID** (default: `MSGIND`)

#### Step 3: Configure .env File
Add these lines to your `.env` file:

```env
# SMS Configuration - MSG91
SMS_GATEWAY=msg91
MSG91_AUTH_KEY=xxxxxxxxxxxxxxxxxxxxxxxx
MSG91_SENDER_ID=RSMIQX
MSG91_TEMPLATE_ID=
```

#### Step 4: Test
```bash
# Restart your web server
# Try registering with Indian mobile number
```

---

### Option C: Fast2SMS Setup (India - Budget)

#### Step 1: Create Fast2SMS Account
1. Go to https://www.fast2sms.com/register
2. Sign up (free credits available)
3. Verify your email

#### Step 2: Get API Key
1. Login to Fast2SMS Dashboard
2. Go to "API & Developers" section
3. Copy your **API Key**: `xxxxxxxxxxxxxxxxxxxxxxxx`

#### Step 3: Configure .env File
Add these lines to your `.env` file:

```env
# SMS Configuration - Fast2SMS
SMS_GATEWAY=fast2sms
FAST2SMS_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxx
FAST2SMS_SENDER_ID=RSMIQX
```

#### Step 4: Test
```bash
# Restart your web server
# Try registering with Indian mobile number
```

---

## 🧪 Testing SMS Delivery

### Test 1: Check Configuration
Create a test file `test_sms.php`:

```php
<?php
require_once 'backend_php/config.php';
require_once 'backend_php/sms_helper.php';

// Test with your mobile number
$mobile = '+919876543210'; // Replace with your number
$otp = '123456';

$result = sendOTPSMS($mobile, $otp);

echo "SMS Test Result:\n";
echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "Message: " . $result['message'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
?>
```

Run it:
```bash
php test_sms.php
```

### Test 2: Register with Real Mobile
1. Go to registration page
2. Enter your real mobile number
3. Click "Send OTP"
4. Check your phone for SMS

**Expected:**
- ✅ SMS received on your phone
- ✅ 6-digit OTP visible
- ✅ Message says "ResumeIQ-X"

---

## 🔧 Troubleshooting

### Problem: "SMS gateway not configured"

**Solution:**
1. Check `.env` file has SMS settings
2. Verify `SMS_GATEWAY` is set to `twilio`, `msg91`, or `fast2sms`
3. Restart web server

### Problem: "Twilio credentials missing"

**Solution:**
1. Verify all 3 Twilio variables are in `.env`:
   - `TWILIO_ACCOUNT_SID`
   - `TWILIO_AUTH_TOKEN`
   - `TWILIO_FROM_NUMBER`
2. Check for typos in variable names
3. Ensure no extra spaces

### Problem: "SMS failed: Authentication failed"

**Solution:**
1. Double-check API credentials
2. Verify account is active
3. Check account balance/credits
4. For Twilio: Verify phone number is verified in trial mode

### Problem: "SMS failed: Invalid phone number"

**Solution:**
1. Ensure mobile number includes country code
2. Format: `+919876543210` (with +)
3. Remove spaces and dashes
4. For India: Should be 10 digits after +91

### Problem: Still receiving OTP via email

**Solution:**
1. Check error logs: `tail -f /var/log/apache2/error.log`
2. Look for `[ResumeIQ-X][SMS]` messages
3. SMS is failing, check the error message
4. Verify SMS gateway configuration

---

## 💰 Cost Comparison

### Twilio (International)
```
Cost per SMS: $0.0075
100 OTPs: $0.75
1,000 OTPs: $7.50
10,000 OTPs: $75.00
```

### MSG91 (India)
```
Cost per SMS: ₹0.15
100 OTPs: ₹15
1,000 OTPs: ₹150
10,000 OTPs: ₹1,500
```

### Fast2SMS (India)
```
Cost per SMS: ₹0.10
100 OTPs: ₹10
1,000 OTPs: ₹100
10,000 OTPs: ₹1,000
```

---

## 📊 How It Works

### Current Flow (Email Fallback)
```
User enters mobile → System tries SMS → SMS fails (no config) 
→ Falls back to email → OTP sent to email ✉️
```

### After SMS Setup
```
User enters mobile → System tries SMS → SMS succeeds 
→ OTP sent to mobile 📱
```

### With Fallback (If SMS fails)
```
User enters mobile → System tries SMS → SMS fails (network issue) 
→ Falls back to email → OTP sent to email ✉️
```

---

## 🔐 Security Features

The SMS system includes:
- ✅ OTP expires in 15 minutes
- ✅ One-time use only
- ✅ Stored securely in database
- ✅ Automatic cleanup of old OTPs
- ✅ Rate limiting (via cooldown timer)
- ✅ Email fallback if SMS fails

---

## 📝 .env File Example

Complete SMS configuration in `.env`:

```env
# ============================================
# SMS CONFIGURATION
# ============================================

# Choose one: twilio, msg91, fast2sms, none
SMS_GATEWAY=twilio

# Twilio (International)
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_FROM_NUMBER=+1234567890

# MSG91 (India)
MSG91_AUTH_KEY=xxxxxxxxxxxxxxxxxxxxxxxx
MSG91_SENDER_ID=RSMIQX
MSG91_TEMPLATE_ID=

# Fast2SMS (India)
FAST2SMS_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxx
FAST2SMS_SENDER_ID=RSMIQX
```

---

## 🎯 Recommended Setup

### For Production (India)
```env
SMS_GATEWAY=msg91
MSG91_AUTH_KEY=your_key_here
MSG91_SENDER_ID=RSMIQX
```

### For Production (International)
```env
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=your_sid_here
TWILIO_AUTH_TOKEN=your_token_here
TWILIO_FROM_NUMBER=+1234567890
```

### For Testing/Development
```env
SMS_GATEWAY=none
# OTPs will be sent via email
```

---

## 🚨 Important Notes

1. **Trial Accounts**: Most SMS services have trial accounts with limited credits
2. **Verified Numbers**: In trial mode, you can only send to verified numbers
3. **Production**: Upgrade to paid account for unlimited sending
4. **Costs**: Monitor your SMS usage to avoid unexpected charges
5. **Fallback**: Email fallback ensures users can always register even if SMS fails

---

## ✅ Verification Checklist

After setup, verify:
- [ ] `.env` file has SMS configuration
- [ ] SMS gateway credentials are correct
- [ ] Web server restarted
- [ ] Test SMS sent successfully
- [ ] Registration page sends SMS to mobile
- [ ] OTP received on phone
- [ ] OTP verification works
- [ ] Email fallback works if SMS fails

---

## 📞 Support

### Twilio Support
- Docs: https://www.twilio.com/docs/sms
- Support: https://support.twilio.com

### MSG91 Support
- Docs: https://docs.msg91.com
- Support: support@msg91.com

### Fast2SMS Support
- Docs: https://docs.fast2sms.com
- Support: support@fast2sms.com

---

## 🎉 Success!

Once configured, your users will receive:
- **Email OTP** → Sent to their email address ✉️
- **Mobile OTP** → Sent to their phone via SMS 📱

Both must be verified before account creation!

---

**Last Updated**: May 3, 2026
**Version**: 1.0.0
**Status**: Ready to Configure
