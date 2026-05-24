# Railway Deployment Guide for ResumeIQ-X

## Current Status
✅ GitHub repository connected to Railway  
⚠️ Deployment failing at healthcheck stage  
🔧 **Solution**: Switched from Apache to PHP built-in server (simpler, more reliable)

---

## Quick Fix Steps

### 1. Commit and Push Latest Changes
```bash
git add railway.toml nixpacks.toml Dockerfile.simple start.sh RAILWAY_DEPLOYMENT_GUIDE.md
git commit -m "fix: Switch to Nixpacks with PHP built-in server for Railway"
git push origin main
```

### 2. Configure Railway Environment Variables

Go to your Railway project → Variables tab and add:

#### **Database (Required)**
```
DB_HOST=<your-railway-mysql-host>
DB_PORT=3306
DB_NAME=resumeiqx
DB_USER=<your-db-user>
DB_PASS=<your-db-password>
```

#### **Application (Required)**
```
APP_ENV=production
APP_NAME=ResumeIQ-X
APP_SECRET_KEY=<generate-random-64-char-string>
NODE_API_PORT=5000
```

#### **Email (Required for OTP)**
```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<your-gmail>
MAIL_PASSWORD=<your-gmail-app-password>
MAIL_FROM_NAME=ResumeIQ-X
MAIL_FROM_ADDRESS=noreply@resumeiqx.ai
```

#### **AI Chat (Optional but Recommended)**
```
GROQ_API_KEY=<your-groq-api-key>
MEERA_FORCE_PROVIDER=groq
```

#### **SMS (Optional)**
```
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=<your-sid>
TWILIO_AUTH_TOKEN=<your-token>
TWILIO_FROM_NUMBER=<your-number>
```

### 3. Add MySQL Database Service

In Railway:
1. Click **"+ New"** → **"Database"** → **"Add MySQL"**
2. Railway will automatically create `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS` variables
3. Manually add `DB_NAME=resumeiqx`

### 4. Import Database Schema

After deployment succeeds, import your database:

**Option A: Using Railway CLI**
```bash
railway run mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database.sql
```

**Option B: Using phpMyAdmin or MySQL Workbench**
- Connect using Railway's database credentials
- Import your `database.sql` file

---

## Why Previous Deployments Failed

### Issue 1: Apache PORT Configuration
- Apache's `Listen ${PORT}` doesn't support environment variable substitution
- Railway assigns dynamic ports, Apache couldn't bind correctly

### Issue 2: Supervisor Complexity
- Running multiple processes (Apache + Node.js) via supervisor added complexity
- Healthcheck couldn't reach the service reliably

### Issue 3: Long Build Times
- Docker image with Apache, Python, Node.js took 4-5 minutes to build
- Healthcheck timeout was too short for the build process

---

## New Approach: Nixpacks + PHP Built-in Server

### ✅ Advantages
1. **Simpler**: No Apache, no supervisor, just PHP + Node.js
2. **Faster**: Nixpacks builds in ~2 minutes (vs 5+ minutes with Docker)
3. **Reliable**: PHP built-in server handles PORT correctly
4. **Easier to debug**: Fewer moving parts

### How It Works
```
nixpacks.toml → Railway builds with Nix packages
                ↓
         PHP 8.2 + Node.js 18 + Python 3.11
                ↓
         start.sh runs:
         1. Node.js API (background)
         2. PHP server (foreground on $PORT)
                ↓
         /health.php responds → Deployment succeeds
```

---

## Troubleshooting

### If Deployment Still Fails

#### Check Build Logs
```
Railway Dashboard → Deployments → Click failed deployment → View Logs
```

Look for:
- ❌ `npm install` errors → Check `node_api/package.json`
- ❌ `pip install` errors → Check `ai_engine_python/requirements.txt`
- ❌ Port binding errors → Check if PORT is set correctly

#### Check Runtime Logs
```
Railway Dashboard → Deployments → Click deployment → View Logs
```

Look for:
- ❌ `Connection refused` → Database not connected
- ❌ `Permission denied` → File permissions issue
- ❌ `Module not found` → Missing dependencies

#### Manual Healthcheck Test
Once deployed, test the healthcheck endpoint:
```bash
curl https://your-app.railway.app/health.php
```

Expected response:
```json
{
  "status": "ok",
  "service": "ResumeIQ-X",
  "timestamp": 1234567890
}
```

---

## Alternative: Use Dockerfile.simple

If Nixpacks fails, switch to the simpler Dockerfile:

1. Edit `railway.toml`:
```toml
[build]
builder = "DOCKERFILE"
dockerfilePath = "Dockerfile.simple"

[deploy]
healthcheckPath = "/health.php"
healthcheckTimeout = 180
restartPolicyType = "ON_FAILURE"
restartPolicyMaxRetries = 5
```

2. Commit and push:
```bash
git add railway.toml
git commit -m "fix: Switch to Dockerfile.simple"
git push origin main
```

---

## Post-Deployment Steps

### 1. Test the Application
- Visit `https://your-app.railway.app`
- Try user registration with email OTP
- Upload a test resume
- Check admin dashboard

### 2. Configure Custom Domain (Optional)
```
Railway Dashboard → Settings → Domains → Add Custom Domain
```

### 3. Enable HTTPS (Automatic)
Railway automatically provides SSL certificates for all deployments.

### 4. Monitor Logs
```
Railway Dashboard → Deployments → View Logs
```

---

## Estimated Deployment Time

| Phase | Time |
|-------|------|
| Build (Nixpacks) | 2-3 minutes |
| Deploy | 30 seconds |
| Healthcheck | 10-30 seconds |
| **Total** | **3-4 minutes** |

---

## Cost Estimate

Railway Pricing (as of 2024):
- **Hobby Plan**: $5/month (500 hours)
- **Pro Plan**: $20/month (unlimited)

Your app uses:
- 1 Web Service (PHP + Node.js)
- 1 MySQL Database

**Estimated cost**: $5-10/month on Hobby plan

---

## Need Help?

### Railway Support
- Discord: https://discord.gg/railway
- Docs: https://docs.railway.app

### Common Issues
1. **Build fails**: Check `nixpacks.toml` syntax
2. **Healthcheck fails**: Check `/health.php` is accessible
3. **Database connection fails**: Verify `DB_*` environment variables
4. **File upload fails**: Check directory permissions in `start.sh`

---

## Next Steps After Successful Deployment

1. ✅ Test all features (registration, login, resume upload, AI chat)
2. ✅ Import production database
3. ✅ Configure email SMTP (Gmail App Password)
4. ✅ Add Groq API key for AI chat
5. ✅ Set up custom domain (optional)
6. ✅ Enable monitoring and alerts

---

**Created by**: MAYUR GOPAL KOVE  
**Last Updated**: 2026-05-03
