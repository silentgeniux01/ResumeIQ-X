# SMS Gateway Setup Guide

This guide explains how to configure SMS delivery for mobile OTP verification in ResumeIQ-X.

## Overview

ResumeIQ-X supports multiple SMS gateway providers:
- **Twilio** - International SMS (recommended for global use)
- **MSG91** - India-focused SMS provider
- **Fast2SMS** - India-focused SMS provider

The system will automatically fall back to email delivery if SMS fails.

---

## Quick Start

1. Choose an SMS provider based on your location and requirements
2. Sign up and get API credentials
3. Add credentials to your `.env` file
4. Set `SMS_GATEWAY` to your chosen provider
5. Test the integration

---

## Provider Setup Instructions

### Option 1: Twilio (International)

**Best for:** Global SMS delivery, high reliability, professional use

**Setup Steps:**

1. **Sign up for Twilio**
   - Visit: https://www.twilio.com/try-twilio
   - Create a free account (includes trial credits)

2. **Get your credentials**
   - Go to: https://www.twilio.com/console
   - Copy your **Account SID** and **Auth Token**
   - Get a phone number from: https://www.twilio.com/console/phone-numbers

3. **Configure .env**
   ```env
   SMS_GATEWAY=twilio
   TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   TWILIO_AUTH_TOKEN=your_auth_token_here
   TWILIO_FROM_NUMBER=+1234567890
   ```

4. **Pricing**
   - Trial: Free credits included
   - Production: ~$0.0075 per SMS (varies by country)
   - See: https://www.twilio.com/sms/pricing

---

### Option 2: MSG91 (India)

**Best for:** India-based applications, cost-effective for Indian numbers

**Setup Steps:**

1. **Sign up for MSG91**
   - Visit: https://msg91.com/signup
   - Complete registration and KYC verification

2. **Get your credentials**
   - Go to: https://control.msg91.com/app/
   - Navigate to **API** section
   - Copy your **Auth Key**
   - Create a **Sender ID** (e.g., RSMIQX) - requires approval
   - Create a **Template** for OTP messages (DLT registration required)

3. **Configure .env**
   ```env
   SMS_GATEWAY=msg91
   MSG91_AUTH_KEY=your_auth_key_here
   MSG91_SENDER_ID=RSMIQX
   MSG91_TEMPLATE_ID=your_template_id_here
   ```

4. **Important Notes**
   - Sender ID approval takes 1-2 business days
   - DLT (Distributed Ledger Technology) registration required for India
   - Template must be pre-approved by telecom operators

5. **Pricing**
   - Transactional SMS: ₹0.15 - ₹0.25 per SMS
   - See: https://msg91.com/pricing

---

### Option 3: Fast2SMS (India)

**Best for:** Quick setup for Indian numbers, testing, small projects

**Setup Steps:**

1. **Sign up for Fast2SMS**
   - Visit: https://www.fast2sms.com/register
   - Complete registration

2. **Get your API key**
   - Go to: https://www.fast2sms.com/dashboard/dev-api
   - Copy your **API Key**

3. **Configure .env**
   ```env
   SMS_GATEWAY=fast2sms
   FAST2SMS_API_KEY=your_api_key_here
   FAST2SMS_SENDER_ID=RSMIQX
   ```

4. **Important Notes**
   - Sender ID may not be customizable on free tier
   - DLT registration required for production use
   - Test credits provided for new accounts

5. **Pricing**
   - Test: Free credits included
   - Production: ₹0.15 - ₹0.30 per SMS
   - See: https://www.fast2sms.com/pricing

---

## Testing Your Setup

### 1. Test via PHP Script

Create a test file `test_sms.php`:

```php
<?php
require_once 'backend_php/config.php';
require_once 'backend_php/sms_helper.php';

$mobile = '+919876543210'; // Replace with your mobile number
$otp = '123456';

$result = sendOTPSMS($mobile, $otp);

echo "Status: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";
if (isset($result['response'])) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
}
```

Run: `php test_sms.php`

### 2. Test via Registration Flow

1. Go to your registration page
2. Enter your mobile number
3. Click "Send OTP"
4. Check if you receive SMS on your mobile
5. If SMS fails, check email for fallback OTP

### 3. Check Logs

Monitor your error logs for SMS delivery status:
- Windows: Check PHP error log location
- Linux: `tail -f /var/log/apache2/error.log`

Look for entries like:
```
[ResumeIQ-X][SMS] Twilio SMS sent successfully to +919876543210
```

---

## Troubleshooting

### SMS Not Received

**Check 1: Verify credentials**
```bash
# Check if SMS_GATEWAY is set
grep SMS_GATEWAY .env

# Verify credentials are not empty
grep TWILIO_ACCOUNT_SID .env  # or MSG91_AUTH_KEY, FAST2SMS_API_KEY
```

**Check 2: Mobile number format**
- Must include country code (e.g., +91 for India)
- Remove spaces and special characters
- Example: `+919876543210` (correct) vs `9876543210` (incorrect)

**Check 3: Provider account status**
- Twilio: Check trial account limits
- MSG91: Verify Sender ID is approved
- Fast2SMS: Check credit balance

**Check 4: Error logs**
```bash
# Check PHP error log
tail -f /var/log/php_errors.log

# Look for SMS-related errors
grep "ResumeIQ-X.*SMS" /var/log/php_errors.log
```

### Common Errors

#### "SMS gateway not configured"
- **Cause:** `SMS_GATEWAY` not set or set to `none`
- **Fix:** Set `SMS_GATEWAY=twilio` (or msg91/fast2sms) in `.env`

#### "Twilio credentials missing"
- **Cause:** Missing or empty Twilio credentials
- **Fix:** Add `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER` to `.env`

#### "Invalid mobile number"
- **Cause:** Mobile number format incorrect
- **Fix:** Ensure number includes country code (e.g., +919876543210)

#### "Insufficient credits"
- **Cause:** Provider account has no credits
- **Fix:** Add credits to your provider account

#### "Sender ID not approved" (MSG91/Fast2SMS)
- **Cause:** Sender ID pending approval
- **Fix:** Wait for approval or use default sender ID

---

## Email Fallback

If SMS delivery fails, the system automatically falls back to email delivery:

1. OTP is sent to user's registered email
2. Email includes a warning that SMS failed
3. User can still complete verification using email OTP

**Response when fallback occurs:**
```json
{
  "status": true,
  "message": "SMS failed. OTP sent to your email (user@example.com)",
  "delivery_method": "email_fallback",
  "email_sent": true,
  "sms_error": "SMS gateway not configured"
}
```

---

## Production Recommendations

### Security
- Never commit `.env` file to version control
- Use environment variables on production servers
- Rotate API keys regularly
- Monitor SMS usage for unusual patterns

### Reliability
- Set up monitoring for SMS delivery failures
- Configure alerts for high failure rates
- Keep backup credits in provider account
- Test SMS delivery regularly

### Cost Optimization
- Use transactional SMS routes (cheaper than promotional)
- Implement rate limiting to prevent abuse
- Set OTP expiry to 15 minutes (current default)
- Monitor and block suspicious phone numbers

### Compliance (India)
- Complete DLT registration for MSG91/Fast2SMS
- Register all SMS templates with telecom operators
- Include opt-out instructions in SMS (if required)
- Maintain SMS delivery logs for audit

---

## API Reference

### sendSMS()
```php
/**
 * Send SMS using configured gateway
 * 
 * @param string $mobile Mobile number with country code (e.g., +919876543210)
 * @param string $message SMS message content
 * @return array ['success' => bool, 'message' => string, 'response' => mixed]
 */
function sendSMS(string $mobile, string $message): array
```

### sendOTPSMS()
```php
/**
 * Send OTP SMS
 * 
 * @param string $mobile Mobile number with country code
 * @param string $otp 6-digit OTP
 * @return array ['success' => bool, 'message' => string]
 */
function sendOTPSMS(string $mobile, string $otp): array
```

### formatMobileNumber()
```php
/**
 * Format mobile number with country code
 * 
 * @param string $mobile Raw mobile number
 * @param string $defaultCountryCode Default country code (e.g., '+91' for India)
 * @return string Formatted mobile with country code
 */
function formatMobileNumber(string $mobile, string $defaultCountryCode = '+91'): string
```

### isValidMobileNumber()
```php
/**
 * Validate mobile number format
 * 
 * @param string $mobile Mobile number to validate
 * @return bool True if valid
 */
function isValidMobileNumber(string $mobile): bool
```

---

## Support

For issues or questions:
1. Check error logs first
2. Verify provider account status
3. Test with provider's API documentation
4. Contact provider support if needed

**Provider Support:**
- Twilio: https://support.twilio.com/
- MSG91: https://msg91.com/help
- Fast2SMS: https://www.fast2sms.com/contact

---

## Changelog

### v1.0.0 (Current)
- Initial SMS gateway integration
- Support for Twilio, MSG91, Fast2SMS
- Automatic email fallback
- Mobile number validation and formatting
- Comprehensive error logging
