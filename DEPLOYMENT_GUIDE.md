# ResumeIQ-X Cloud Deployment Guide 🚀

Complete guide for deploying ResumeIQ-X to various cloud platforms.

---

## 📋 Pre-Deployment Checklist

### ✅ Required Files
- [x] `.env.example` (template for environment variables)
- [x] `Dockerfile` (containerization)
- [x] `railway.toml` (Railway configuration)
- [x] `supervisord.conf` (process management)
- [x] `.htaccess` (Apache configuration)
- [x] `database/schema.sql` (database structure)

### ✅ Environment Variables to Set
```env
# Database (CRITICAL)
DB_HOST=your_production_db_host
DB_NAME=resumeiq_x
DB_USER=your_db_user
DB_PASS=strong_secure_password

# LLM API Keys (At least ONE required)
GROQ_API_KEY=your_groq_api_key
OPENAI_API_KEY=your_openai_key (optional backup)
GEMINI_API_KEY=your_gemini_key (optional backup)

# Email (REQUIRED for OTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_specific_password
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME=ResumeIQ-X

# SMS (Optional but recommended)
SMS_GATEWAY=twilio
TWILIO_ACCOUNT_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_token
TWILIO_PHONE=+1234567890

# Application
APP_URL=https://your-domain.com
APP_ENV=production
```

---

## 🚂 Railway Deployment (Recommended)

### Why Railway?
- ✅ Free tier available
- ✅ Automatic HTTPS
- ✅ Built-in MySQL database
- ✅ Easy environment variable management
- ✅ GitHub integration
- ✅ Dockerfile support

### Step-by-Step Deployment

#### 1. Prepare Repository
```bash
# Ensure all changes are committed
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

#### 2. Create Railway Account
1. Go to [railway.app](https://railway.app)
2. Sign up with GitHub
3. Authorize Railway to access your repositories

#### 3. Create New Project
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Initialize project
railway init

# Link to your Railway project
railway link
```

#### 4. Add MySQL Database
1. In Railway dashboard, click "New"
2. Select "Database" → "MySQL"
3. Wait for provisioning
4. Copy connection details

#### 5. Set Environment Variables
```bash
# Set all required variables
railway variables set DB_HOST=$MYSQL_HOST
railway variables set DB_NAME=resumeiq_x
railway variables set DB_USER=$MYSQL_USER
railway variables set DB_PASS=$MYSQL_PASSWORD
railway variables set GROQ_API_KEY=your_groq_key
railway variables set SMTP_HOST=smtp.gmail.com
railway variables set SMTP_USER=your_email@gmail.com
railway variables set SMTP_PASS=your_app_password
railway variables set APP_URL=https://your-app.railway.app
railway variables set APP_ENV=production

# Or set via Railway dashboard (Settings → Variables)
```

#### 6. Deploy Application
```bash
# Deploy from local
railway up

# Or connect GitHub repo in Railway dashboard
# Settings → Connect GitHub → Select Repository → Deploy
```

#### 7. Setup Database
```bash
# Connect to Railway MySQL
railway run bash

# Inside container, import schema
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql

# Run migrations
php database/run_migrations.php

# Exit
exit
```

#### 8. Verify Deployment
1. Open your Railway app URL
2. Test homepage loads
3. Try user registration
4. Upload a test resume
5. Check admin dashboard

### Railway Configuration Files

#### `railway.toml`
```toml
[build]
builder = "DOCKERFILE"
dockerfilePath = "Dockerfile"

[deploy]
startCommand = "supervisord -c /app/supervisord.conf"
healthcheckPath = "/"
healthcheckTimeout = 300
restartPolicyType = "ON_FAILURE"
restartPolicyMaxRetries = 10
```

---

## 🌊 Heroku Deployment

### Step-by-Step

#### 1. Install Heroku CLI
```bash
# macOS
brew tap heroku/brew && brew install heroku

# Windows
# Download from https://devcenter.heroku.com/articles/heroku-cli

# Linux
curl https://cli-assets.heroku.com/install.sh | sh
```

#### 2. Login and Create App
```bash
heroku login
heroku create resumeiq-x-production
```

#### 3. Add MySQL Database
```bash
# Add ClearDB MySQL addon
heroku addons:create cleardb:ignite

# Get database URL
heroku config:get CLEARDB_DATABASE_URL
```

#### 4. Set Environment Variables
```bash
heroku config:set DB_HOST=your_cleardb_host
heroku config:set DB_NAME=your_cleardb_name
heroku config:set DB_USER=your_cleardb_user
heroku config:set DB_PASS=your_cleardb_pass
heroku config:set GROQ_API_KEY=your_groq_key
heroku config:set SMTP_HOST=smtp.gmail.com
heroku config:set SMTP_USER=your_email@gmail.com
heroku config:set SMTP_PASS=your_app_password
heroku config:set APP_ENV=production
```

#### 5. Create Procfile
```bash
echo "web: supervisord -c supervisord.conf" > Procfile
```

#### 6. Deploy
```bash
git add .
git commit -m "Configure for Heroku"
git push heroku main
```

#### 7. Setup Database
```bash
heroku run bash
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql
php database/run_migrations.php
exit
```

---

## ☁️ AWS EC2 Deployment

### Step-by-Step

#### 1. Launch EC2 Instance
- **AMI**: Ubuntu 22.04 LTS
- **Instance Type**: t2.medium (minimum)
- **Storage**: 20GB SSD
- **Security Group**: Allow ports 80, 443, 22

#### 2. Connect to Instance
```bash
ssh -i your-key.pem ubuntu@your-ec2-ip
```

#### 3. Install Dependencies
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache, PHP, MySQL
sudo apt install apache2 php8.1 php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml mysql-server -y

# Install Python
sudo apt install python3 python3-pip python3-venv -y

# Install Node.js (optional)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

#### 4. Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/YOUR_USERNAME/ResumeIQ-X.git
sudo chown -R www-data:www-data ResumeIQ-X
cd ResumeIQ-X
```

#### 5. Configure Environment
```bash
sudo cp .env.example .env
sudo nano .env
# Edit with your production values
```

#### 6. Setup Database
```bash
sudo mysql -u root -p
CREATE DATABASE resumeiq_x;
CREATE USER 'resumeiq_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON resumeiq_x.* TO 'resumeiq_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
sudo mysql -u resumeiq_user -p resumeiq_x < database/schema.sql
```

#### 7. Configure Apache
```bash
sudo nano /etc/apache2/sites-available/resumeiq-x.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/ResumeIQ-X/frontend
    
    <Directory /var/www/ResumeIQ-X/frontend>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/resumeiq_error.log
    CustomLog ${APACHE_LOG_DIR}/resumeiq_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite resumeiq-x.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 8. Setup Python Environment
```bash
cd /var/www/ResumeIQ-X/ai_engine_python
sudo python3 -m venv venv
sudo venv/bin/pip install -r requirements.txt
```

#### 9. Setup SSL (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d your-domain.com
```

---

## 🐳 Docker Deployment

### Using Docker Compose

#### 1. Create `docker-compose.yml`
```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "80:80"
    environment:
      - DB_HOST=db
      - DB_NAME=resumeiq_x
      - DB_USER=root
      - DB_PASS=rootpassword
      - GROQ_API_KEY=${GROQ_API_KEY}
      - SMTP_HOST=${SMTP_HOST}
      - SMTP_USER=${SMTP_USER}
      - SMTP_PASS=${SMTP_PASS}
    depends_on:
      - db
    volumes:
      - ./uploads:/app/uploads
  
  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
      - MYSQL_DATABASE=resumeiq_x
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/schema.sql:/docker-entrypoint-initdb.d/schema.sql

volumes:
  mysql_data:
```

#### 2. Deploy
```bash
docker-compose up -d
```

---

## 🔒 Security Checklist

### Before Going Live

- [ ] Change all default passwords
- [ ] Set strong `DB_PASS`
- [ ] Use app-specific passwords for SMTP
- [ ] Enable HTTPS/SSL
- [ ] Set `APP_ENV=production`
- [ ] Disable PHP error display
- [ ] Configure firewall rules
- [ ] Set up database backups
- [ ] Enable rate limiting
- [ ] Configure CORS properly
- [ ] Review `.gitignore` (ensure `.env` is excluded)
- [ ] Set secure session settings
- [ ] Enable SQL injection protection (already done via prepared statements)
- [ ] Validate all user inputs (already implemented)

---

## 📊 Post-Deployment

### Monitoring
```bash
# Check application logs
railway logs  # Railway
heroku logs --tail  # Heroku
sudo tail -f /var/log/apache2/resumeiq_error.log  # EC2
```

### Database Backup
```bash
# Railway
railway run mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > backup.sql

# EC2
mysqldump -u resumeiq_user -p resumeiq_x > backup_$(date +%Y%m%d).sql
```

### Performance Optimization
- Enable PHP OPcache
- Configure MySQL query cache
- Use CDN for static assets
- Enable gzip compression
- Implement Redis caching (optional)

---

## 🆘 Troubleshooting

### Common Issues

#### Database Connection Failed
```bash
# Check database credentials
railway variables  # Railway
heroku config  # Heroku

# Test connection
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME
```

#### LLM API Errors
```bash
# Verify API keys
railway variables get GROQ_API_KEY

# Test API
php test_ai_chat.php
```

#### File Upload Issues
```bash
# Check permissions
sudo chown -R www-data:www-data uploads/
sudo chmod -R 755 uploads/
```

#### Email Not Sending
```bash
# Test SMTP
php -r "mail('test@example.com', 'Test', 'Test message');"

# Check SMTP credentials
railway variables get SMTP_USER
```

---

## 📞 Support

For deployment issues:
- GitHub Issues: https://github.com/YOUR_USERNAME/ResumeIQ-X/issues
- Email: support@resumeiq.com
- Documentation: See README.md

---

**Deployment Guide by MAYUR GOPAL KOVE**
