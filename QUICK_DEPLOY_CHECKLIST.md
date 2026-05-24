# 🚀 Quick Deploy Checklist for ResumeIQ-X

## ✅ Pre-Deployment Checklist

### 1. GitHub Repository
- [x] Code pushed to GitHub
- [x] README.md created
- [x] DEPLOYMENT_GUIDE.md created
- [x] .gitignore configured (`.env` excluded)
- [x] .env.example provided

### 2. Required API Keys (Get These First!)

#### LLM API Keys (At least ONE required)
- [ ] **Groq API Key** (Recommended - Free & Fast)
  - Get from: https://console.groq.com/keys
  - Free tier: 30 requests/minute
  
- [ ] **OpenAI API Key** (Optional backup)
  - Get from: https://platform.openai.com/api-keys
  - Paid service
  
- [ ] **Google Gemini API Key** (Optional backup)
  - Get from: https://makersuite.google.com/app/apikey
  - Free tier available

#### Email Service (REQUIRED)
- [ ] **Gmail App Password**
  - Enable 2FA on Gmail
  - Generate app password: https://myaccount.google.com/apppasswords
  - Use this instead of regular password

#### SMS Service (Optional but recommended)
- [ ] **Twilio Account**
  - Sign up: https://www.twilio.com/try-twilio
  - Get: Account SID, Auth Token, Phone Number
  - Note: Trial accounts only send to verified numbers

---

## 🚂 Railway Deployment (Recommended - 5 Minutes)

### Step 1: Create Railway Account
1. Go to https://railway.app
2. Sign up with GitHub
3. Authorize Railway

### Step 2: Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose "ResumeIQ-X" repository
4. Click "Deploy Now"

### Step 3: Add MySQL Database
1. Click "New" → "Database" → "MySQL"
2. Wait for provisioning (1-2 minutes)
3. Database will auto-connect to your app

### Step 4: Set Environment Variables
Go to your app → Settings → Variables → Add these:

```env
# Database (Auto-filled by Railway MySQL)
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_NAME=resumeiq_x
DB_USER=${{MySQL.MYSQL_USER}}
DB_PASS=${{MySQL.MYSQL_PASSWORD}}

# LLM (REQUIRED - Use your Groq key)
GROQ_API_KEY=your_groq_api_key_here

# Email (REQUIRED - Use Gmail app password)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_gmail_app_password
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME=ResumeIQ-X

# SMS (Optional)
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_token
TWILIO_PHONE=+1234567890

# Application
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}
APP_ENV=production
```

### Step 5: Setup Database
1. Go to your app → Settings → Generate Domain
2. Wait for deployment to complete
3. Open Railway CLI or use web terminal:

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Import database schema
railway run bash
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql
php database/run_migrations.php
exit
```

### Step 6: Test Deployment
1. Open your Railway app URL
2. Test user registration
3. Upload a test resume
4. Check admin dashboard

**Done! Your app is live! 🎉**

---

## 🌊 Alternative: Heroku Deployment

### Quick Steps
```bash
# Install Heroku CLI
npm install -g heroku

# Login
heroku login

# Create app
heroku create resumeiq-x-prod

# Add MySQL
heroku addons:create cleardb:ignite

# Set environment variables
heroku config:set GROQ_API_KEY=your_key
heroku config:set SMTP_HOST=smtp.gmail.com
heroku config:set SMTP_USER=your_email@gmail.com
heroku config:set SMTP_PASS=your_app_password

# Deploy
git push heroku main

# Setup database
heroku run bash
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql
php database/run_migrations.php
exit
```

---

## 🔑 Getting API Keys - Quick Links

### 1. Groq API Key (FREE - Recommended)
1. Visit: https://console.groq.com/keys
2. Sign up with Google/GitHub
3. Click "Create API Key"
4. Copy key → Use in `GROQ_API_KEY`

### 2. Gmail App Password (FREE - Required)
1. Enable 2FA: https://myaccount.google.com/security
2. Generate app password: https://myaccount.google.com/apppasswords
3. Select "Mail" and "Other (Custom name)"
4. Copy 16-character password → Use in `SMTP_PASS`

### 3. Twilio (Optional - Trial Free)
1. Sign up: https://www.twilio.com/try-twilio
2. Verify your phone number
3. Get free trial credits ($15)
4. Copy: Account SID, Auth Token, Phone Number
5. Note: Trial only sends to verified numbers

---

## 🧪 Testing Your Deployment

### 1. Homepage Test
- [ ] Homepage loads correctly
- [ ] AI chat widget appears
- [ ] All links work

### 2. User Registration Test
- [ ] Register new user
- [ ] Receive email OTP
- [ ] Verify email
- [ ] Login successful

### 3. Resume Upload Test
- [ ] Upload PDF resume
- [ ] Click "Analyze"
- [ ] Processing count shows 1
- [ ] Progress bar animates
- [ ] Analysis completes
- [ ] View results

### 4. Admin Dashboard Test
- [ ] Login as admin
- [ ] See resume queue
- [ ] Real-time stats update
- [ ] Analyze button works
- [ ] Download resume works

### 5. Recruiter Portal Test
- [ ] Register as recruiter
- [ ] Create job posting
- [ ] View candidates
- [ ] Send email to candidate

---

## 🐛 Common Issues & Quick Fixes

### Issue: Database Connection Failed
**Fix:**
```bash
# Check database variables
railway variables  # or heroku config

# Test connection
railway run bash
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS
```

### Issue: LLM API Error
**Fix:**
- Verify `GROQ_API_KEY` is set correctly
- Check API key is active at https://console.groq.com/keys
- Test with: `php test_ai_chat.php`

### Issue: Email Not Sending
**Fix:**
- Use Gmail app password, not regular password
- Enable "Less secure app access" (if using regular Gmail)
- Check SMTP credentials: `railway variables get SMTP_USER`

### Issue: Processing Count Shows "—"
**Fix:**
- This was fixed in latest commit
- Ensure you deployed latest code: `git pull && railway up`

---

## 📊 Post-Deployment Monitoring

### Check Logs
```bash
# Railway
railway logs

# Heroku
heroku logs --tail
```

### Monitor Performance
- Railway Dashboard → Metrics
- Check CPU, Memory, Network usage
- Set up alerts for errors

### Database Backup
```bash
# Railway
railway run mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > backup.sql

# Heroku
heroku run bash
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > backup.sql
```

---

## 🎯 Next Steps After Deployment

### 1. Custom Domain (Optional)
- Railway: Settings → Domains → Add Custom Domain
- Point your domain's CNAME to Railway URL

### 2. SSL Certificate (Automatic)
- Railway provides free SSL automatically
- Your app will be accessible via HTTPS

### 3. Monitoring & Analytics
- Set up error tracking (Sentry, Rollbar)
- Add Google Analytics
- Monitor uptime (UptimeRobot)

### 4. Scaling (If Needed)
- Railway: Upgrade plan for more resources
- Add Redis for caching
- Enable CDN for static assets

---

## 📞 Support

### Documentation
- Full Guide: `DEPLOYMENT_GUIDE.md`
- README: `README.md`
- Architecture: `Project_Info/Architecture.md`

### Help
- GitHub Issues: https://github.com/silentgeniux01/ResumeIQ-X/issues
- Email: mayurkove@example.com

---

## ✅ Deployment Complete Checklist

- [ ] Code pushed to GitHub
- [ ] Railway/Heroku account created
- [ ] Project deployed
- [ ] MySQL database added
- [ ] Environment variables set
- [ ] Database schema imported
- [ ] Migrations run
- [ ] Homepage loads
- [ ] User registration works
- [ ] Resume analysis works
- [ ] Admin dashboard accessible
- [ ] AI chat responds
- [ ] Email OTP sends
- [ ] Custom domain configured (optional)
- [ ] Monitoring setup (optional)

---

**🎉 Congratulations! Your ResumeIQ-X is now live!**

**Built by: MAYUR GOPAL KOVE**  
**Repository: https://github.com/silentgeniux01/ResumeIQ-X**
