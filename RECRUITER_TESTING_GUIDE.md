# Recruiter System Testing Guide

## 🧪 Complete Testing Checklist

### Prerequisites
- [ ] Database is running and accessible
- [ ] SMTP email configuration is set in .env file
- [ ] Web server (Apache/Nginx) is running
- [ ] PHP 8+ is installed
- [ ] All files are uploaded to server

---

## Test Suite 1: Registration Flow

### Test 1.1: Access Registration Page
**Steps:**
1. Navigate to `/frontend/recruiter_register.html`
2. Verify page loads without errors
3. Check that "🏢 Recruiter Portal" badge is visible
4. Verify all form fields are present

**Expected Result:**
- ✅ Page loads successfully
- ✅ Cyan/blue theme is applied
- ✅ All fields visible: Name, Email, Mobile, Password
- ✅ Country code selector shows default country

**Status:** [ ] Pass [ ] Fail

---

### Test 1.2: Email Verification
**Steps:**
1. Enter valid email address
2. Click "Send OTP" button
3. Check email inbox for OTP
4. Enter OTP in the field
5. Click "Verify" button

**Expected Result:**
- ✅ "Send OTP" button changes to "Sending..."
- ✅ OTP input field appears
- ✅ Email received with 6-digit OTP
- ✅ After verification, ✅ badge appears
- ✅ "Email verified" message shows

**Status:** [ ] Pass [ ] Fail

**Notes:**
```
OTP Received: ______
Time Taken: ______ seconds
```

---

### Test 1.3: Mobile Verification
**Steps:**
1. Select country code from dropdown
2. Enter 10-digit mobile number
3. Click "Send OTP" button
4. Check email for mobile OTP
5. Enter OTP and verify

**Expected Result:**
- ✅ Country code selector works
- ✅ Mobile OTP sent to email
- ✅ OTP verification successful
- ✅ "Mobile verified" badge appears

**Status:** [ ] Pass [ ] Fail

---

### Test 1.4: Complete Registration
**Steps:**
1. Fill all fields:
   - Name: "Test Recruiter"
   - Email: (verified)
   - Mobile: (verified)
   - Password: "test123"
2. Click "Create Recruiter Account"

**Expected Result:**
- ✅ Success message appears
- ✅ Redirects to recruiter_login.html
- ✅ Database record created with role='recruiter'

**Status:** [ ] Pass [ ] Fail

**Database Verification:**
```sql
SELECT id, name, email, role, account_status, email_verified, mobile_verified
FROM users
WHERE email = 'your-test-email@example.com';
```

Expected Output:
```
role: recruiter
account_status: active
email_verified: 1
mobile_verified: 1
```

---

## Test Suite 2: Login Flow

### Test 2.1: Valid Login
**Steps:**
1. Navigate to `/frontend/recruiter_login.html`
2. Enter registered email
3. Enter correct password
4. Click "Sign In"

**Expected Result:**
- ✅ "Signing in..." message appears
- ✅ Success message shows
- ✅ Redirects to recruiter_dashboard.php
- ✅ Session created with recruiter_id

**Status:** [ ] Pass [ ] Fail

---

### Test 2.2: Invalid Credentials
**Steps:**
1. Enter valid email
2. Enter wrong password
3. Click "Sign In"

**Expected Result:**
- ✅ Error message: "Invalid password"
- ✅ No redirect occurs
- ✅ Button re-enabled

**Status:** [ ] Pass [ ] Fail

---

### Test 2.3: Non-Recruiter Account
**Steps:**
1. Try logging in with user/admin account
2. Click "Sign In"

**Expected Result:**
- ✅ Error: "Access denied. Recruiter account required."
- ✅ No redirect occurs

**Status:** [ ] Pass [ ] Fail

---

### Test 2.4: Unverified Account
**Steps:**
1. Create account without email verification
2. Try to login

**Expected Result:**
- ✅ Error: "Email not verified. Please check your email for the OTP."
- ✅ Redirect to verify_email.html

**Status:** [ ] Pass [ ] Fail

---

## Test Suite 3: Dashboard Functionality

### Test 3.1: Dashboard Access
**Steps:**
1. Login as recruiter
2. Verify dashboard loads

**Expected Result:**
- ✅ Dashboard displays recruiter name
- ✅ Statistics show correct counts
- ✅ Candidate table loads
- ✅ "LIVE DATA" badge visible

**Status:** [ ] Pass [ ] Fail

**Screenshot Location:** `_____________________`

---

### Test 3.2: Statistics Display
**Steps:**
1. Check statistics cards
2. Verify counts match database

**Expected Result:**
- ✅ Total Candidates: matches DB count
- ✅ Analyzed: matches completed count
- ✅ Pending Analysis: matches pending count

**Status:** [ ] Pass [ ] Fail

**Database Verification:**
```sql
-- Total Candidates
SELECT COUNT(*) FROM resumes;

-- Analyzed
SELECT COUNT(*) FROM resumes WHERE analysis_status = 'completed';

-- Pending
SELECT COUNT(*) FROM resumes WHERE analysis_status = 'pending';
```

---

### Test 3.3: Candidate Table Display
**Steps:**
1. Verify table shows all candidates
2. Check each column displays correctly

**Expected Result:**
- ✅ Candidate name visible
- ✅ Email address visible
- ✅ Resume filename visible
- ✅ Status badge shows correct status
- ✅ Score displays (or "—" if not analyzed)
- ✅ Action buttons visible

**Status:** [ ] Pass [ ] Fail

---

### Test 3.4: View Analysis
**Steps:**
1. Click "View" button on analyzed candidate
2. Verify analysis page loads

**Expected Result:**
- ✅ Redirects to analysis_result_viewer.php
- ✅ Analysis data displays correctly

**Status:** [ ] Pass [ ] Fail

---

### Test 3.5: Download Resume
**Steps:**
1. Click download button
2. Verify file downloads

**Expected Result:**
- ✅ File download starts
- ✅ Correct filename
- ✅ File opens successfully

**Status:** [ ] Pass [ ] Fail

---

## Test Suite 4: Email Functionality

### Test 4.1: Open Email Modal
**Steps:**
1. Click "Email" button on any candidate
2. Verify modal opens

**Expected Result:**
- ✅ Modal appears with animation
- ✅ Candidate name auto-filled
- ✅ Candidate email auto-filled
- ✅ Default subject line present
- ✅ All fields editable (except name/email)

**Status:** [ ] Pass [ ] Fail

---

### Test 4.2: Send Email - All Fields
**Steps:**
1. Open email modal
2. Keep default subject
3. Enter job recommendations:
   ```
   • Senior Software Engineer at TechCorp
   • Full Stack Developer at StartupXYZ
   • Backend Engineer at Enterprise Inc.
   ```
4. Enter personal message:
   ```
   Your profile stood out to us because of your 
   strong background in Python and cloud technologies.
   ```
5. Click "Send Email"

**Expected Result:**
- ✅ Button shows "Sending..."
- ✅ Success message appears
- ✅ Modal closes after 2 seconds
- ✅ Email received by candidate

**Status:** [ ] Pass [ ] Fail

**Email Verification:**
- [ ] Email received in inbox
- [ ] Subject line correct
- [ ] Candidate name in greeting
- [ ] Personal message visible (in blue box)
- [ ] Job recommendations formatted correctly
- [ ] Recruiter signature present
- [ ] Recruiter name and email correct
- [ ] Footer branding present

---

### Test 4.3: Send Email - Required Fields Only
**Steps:**
1. Open email modal
2. Enter subject and job recommendations only
3. Leave personal message empty
4. Send email

**Expected Result:**
- ✅ Email sends successfully
- ✅ No personal message section in email
- ✅ All other sections present

**Status:** [ ] Pass [ ] Fail

---

### Test 4.4: Email Validation
**Steps:**
1. Open email modal
2. Clear subject field
3. Try to send

**Expected Result:**
- ✅ Error: "Subject and job recommendations are required"
- ✅ Email not sent

**Status:** [ ] Pass [ ] Fail

---

### Test 4.5: Email Activity Logging
**Steps:**
1. Send email to candidate
2. Check database

**Expected Result:**
- ✅ Record in recruiter_activity table

**Database Verification:**
```sql
SELECT * FROM recruiter_activity
WHERE recruiter_id = YOUR_RECRUITER_ID
ORDER BY action_timestamp DESC
LIMIT 1;
```

Expected Output:
```
action_type: email_sent
candidate_id: [correct ID]
action_timestamp: [recent timestamp]
```

**Status:** [ ] Pass [ ] Fail

---

## Test Suite 5: Session & Security

### Test 5.1: Session Protection
**Steps:**
1. Logout from recruiter account
2. Try to access `/frontend/recruiter_dashboard.php` directly

**Expected Result:**
- ✅ Redirects to recruiter_login.html
- ✅ No dashboard content visible

**Status:** [ ] Pass [ ] Fail

---

### Test 5.2: Role-Based Access
**Steps:**
1. Login as regular user
2. Try to access recruiter dashboard

**Expected Result:**
- ✅ Access denied
- ✅ Redirect to appropriate page

**Status:** [ ] Pass [ ] Fail

---

### Test 5.3: Logout Functionality
**Steps:**
1. Login as recruiter
2. Click "Sign out" button
3. Verify redirect

**Expected Result:**
- ✅ Session destroyed
- ✅ Redirects to recruiter_login.html
- ✅ Cannot access dashboard without re-login

**Status:** [ ] Pass [ ] Fail

---

### Test 5.4: SQL Injection Prevention
**Steps:**
1. Try SQL injection in email field:
   ```
   test@example.com' OR '1'='1
   ```
2. Try in password field

**Expected Result:**
- ✅ No SQL error
- ✅ Login fails with "User not found"
- ✅ No database compromise

**Status:** [ ] Pass [ ] Fail

---

### Test 5.5: XSS Prevention
**Steps:**
1. Try XSS in personal message:
   ```
   <script>alert('XSS')</script>
   ```
2. Send email

**Expected Result:**
- ✅ Script tags escaped in email
- ✅ No JavaScript execution
- ✅ Text displays as plain text

**Status:** [ ] Pass [ ] Fail

---

## Test Suite 6: UI/UX Testing

### Test 6.1: Responsive Design
**Steps:**
1. Test on desktop (1920x1080)
2. Test on tablet (768x1024)
3. Test on mobile (375x667)

**Expected Result:**
- ✅ All elements visible on all screens
- ✅ No horizontal scrolling
- ✅ Buttons accessible
- ✅ Modal fits screen

**Status:** [ ] Pass [ ] Fail

---

### Test 6.2: Loading States
**Steps:**
1. Observe all button loading states
2. Check spinner animations

**Expected Result:**
- ✅ Buttons show loading text
- ✅ Spinners animate smoothly
- ✅ Buttons disabled during loading

**Status:** [ ] Pass [ ] Fail

---

### Test 6.3: Error Messages
**Steps:**
1. Trigger various errors
2. Verify error messages display

**Expected Result:**
- ✅ Error messages visible
- ✅ Red color scheme
- ✅ Clear error text
- ✅ Messages disappear after action

**Status:** [ ] Pass [ ] Fail

---

### Test 6.4: Success Messages
**Steps:**
1. Complete successful actions
2. Verify success messages

**Expected Result:**
- ✅ Success messages visible
- ✅ Green color scheme
- ✅ Checkmark icon present
- ✅ Auto-dismiss after delay

**Status:** [ ] Pass [ ] Fail

---

## Test Suite 7: Integration Testing

### Test 7.1: End-to-End Flow
**Steps:**
1. Register new recruiter account
2. Verify email and mobile
3. Login to dashboard
4. View candidate analysis
5. Send job recommendation email
6. Logout

**Expected Result:**
- ✅ All steps complete without errors
- ✅ Data persists correctly
- ✅ Email received successfully

**Status:** [ ] Pass [ ] Fail

**Time Taken:** ______ minutes

---

### Test 7.2: Multiple Recruiters
**Steps:**
1. Create 2 recruiter accounts
2. Login with both (different browsers)
3. Send emails from both
4. Verify activity logs

**Expected Result:**
- ✅ Both can access dashboard
- ✅ Both can send emails
- ✅ Activity logged separately

**Status:** [ ] Pass [ ] Fail

---

### Test 7.3: Concurrent Operations
**Steps:**
1. Open dashboard in 2 tabs
2. Send email from tab 1
3. Refresh tab 2
4. Verify data consistency

**Expected Result:**
- ✅ No conflicts
- ✅ Data syncs correctly
- ✅ No duplicate emails

**Status:** [ ] Pass [ ] Fail

---

## Test Suite 8: Performance Testing

### Test 8.1: Page Load Time
**Steps:**
1. Measure dashboard load time
2. Use browser DevTools

**Expected Result:**
- ✅ Dashboard loads in < 2 seconds
- ✅ No console errors

**Status:** [ ] Pass [ ] Fail

**Load Time:** ______ ms

---

### Test 8.2: Email Send Time
**Steps:**
1. Measure time from click to success
2. Test with different email sizes

**Expected Result:**
- ✅ Email sends in < 5 seconds
- ✅ No timeout errors

**Status:** [ ] Pass [ ] Fail

**Send Time:** ______ seconds

---

### Test 8.3: Large Dataset
**Steps:**
1. Test with 100+ candidates
2. Verify table performance

**Expected Result:**
- ✅ Table loads smoothly
- ✅ Scrolling is smooth
- ✅ No lag or freezing

**Status:** [ ] Pass [ ] Fail

---

## Test Suite 9: Browser Compatibility

### Test 9.1: Chrome
**Version:** __________
**Status:** [ ] Pass [ ] Fail
**Issues:** ___________________________

### Test 9.2: Firefox
**Version:** __________
**Status:** [ ] Pass [ ] Fail
**Issues:** ___________________________

### Test 9.3: Safari
**Version:** __________
**Status:** [ ] Pass [ ] Fail
**Issues:** ___________________________

### Test 9.4: Edge
**Version:** __________
**Status:** [ ] Pass [ ] Fail
**Issues:** ___________________________

---

## Test Suite 10: Error Handling

### Test 10.1: Network Error
**Steps:**
1. Disconnect internet
2. Try to send email

**Expected Result:**
- ✅ Error message: "Server error. Please try again."
- ✅ Button re-enabled

**Status:** [ ] Pass [ ] Fail

---

### Test 10.2: Database Error
**Steps:**
1. Stop database server
2. Try to access dashboard

**Expected Result:**
- ✅ Graceful error handling
- ✅ No PHP errors exposed

**Status:** [ ] Pass [ ] Fail

---

### Test 10.3: SMTP Error
**Steps:**
1. Configure invalid SMTP settings
2. Try to send email

**Expected Result:**
- ✅ Error message displayed
- ✅ Activity not logged
- ✅ User notified of failure

**Status:** [ ] Pass [ ] Fail

---

## Bug Report Template

### Bug #____
**Severity:** [ ] Critical [ ] High [ ] Medium [ ] Low
**Test Suite:** _______________
**Test Case:** _______________

**Description:**
```
[Describe the bug]
```

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Result:**
```
[What should happen]
```

**Actual Result:**
```
[What actually happened]
```

**Screenshots:**
```
[Attach screenshots]
```

**Environment:**
- Browser: _______________
- OS: _______________
- PHP Version: _______________
- Database: _______________

**Console Errors:**
```
[Paste console errors]
```

**Status:** [ ] Open [ ] In Progress [ ] Fixed [ ] Closed

---

## Final Checklist

### Pre-Production
- [ ] All test suites passed
- [ ] No critical bugs
- [ ] Performance acceptable
- [ ] Security verified
- [ ] Documentation complete
- [ ] Backup created

### Production Deployment
- [ ] Files uploaded
- [ ] Database migrated
- [ ] SMTP configured
- [ ] SSL certificate active
- [ ] Monitoring enabled
- [ ] Backup scheduled

### Post-Deployment
- [ ] Smoke test passed
- [ ] User acceptance test
- [ ] Performance monitoring
- [ ] Error logging active
- [ ] Support team trained

---

## Test Summary

**Total Tests:** 50+
**Passed:** ______
**Failed:** ______
**Skipped:** ______
**Pass Rate:** ______%

**Tested By:** _______________
**Date:** _______________
**Sign-off:** _______________

---

**Version:** 1.0.0
**Last Updated:** May 3, 2026
**Status:** Ready for Testing
