# ResumeIQ-X 🚀

**AI-Powered Talent Intelligence Platform**

ResumeIQ-X is an enterprise-grade resume analysis and talent management system that uses advanced LLM technology to evaluate candidates, match them with job roles, and provide actionable insights for recruiters.

![ResumeIQ-X Dashboard](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)
![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![Python](https://img.shields.io/badge/Python-3.9+-yellow)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Features

### 🤖 AI-Powered Analysis
- **Multi-LLM Support**: Groq, OpenAI, Gemini, Anthropic, DeepSeek with automatic fallback
- **Sector Detection**: Automatically identifies candidate's industry (Engineering, Medical, Finance, Arts, etc.)
- **Skill Extraction**: Advanced NLP-based skill identification and categorization
- **Career Trajectory Prediction**: ML-powered career path recommendations
- **Semantic Role Matching**: Intelligent job-candidate matching algorithm

### 👥 Multi-Role System
- **Admin Dashboard**: Complete system oversight, resume queue management, real-time analytics
- **Recruiter Portal**: Job posting management, candidate shortlisting, email campaigns
- **Candidate Portal**: Resume upload, analysis tracking, status monitoring

### 💬 AI Chat Assistant
- **Intelligent Help**: Context-aware AI assistant on every page
- **Multi-Provider Fallback**: Cloud LLMs with local Ollama fallback
- **Creator Credit**: Built by MAYUR GOPAL KOVE

### 📊 Real-Time Analytics
- **Live Dashboard**: Real-time resume processing statistics with smooth animations
- **Progress Tracking**: Visual progress bars with percentage indicators
- **Status Monitoring**: Pending, Processing, Completed, Failed states

### 🔐 Security Features
- **Email & Mobile OTP Verification**: Dual-factor authentication
- **Session Management**: Secure PHP session handling with role-based access
- **Input Validation**: Comprehensive sanitization and validation
- **SQL Injection Protection**: Prepared statements throughout

### 📧 Communication System
- **Email Integration**: PHPMailer with SMTP support
- **SMS Gateway**: Twilio, MSG91, Fast2SMS integration
- **Bulk Email**: Professional email templates for candidate outreach
- **Communication History**: Complete audit trail

---

## 🏗️ Architecture

```
ResumeIQ-X/
├── frontend/              # PHP-based frontend
│   ├── admin_dashboard.php
│   ├── recruiter_dashboard.php
│   ├── dashboard.php (candidate)
│   ├── components/        # Reusable components (AI chat widget)
│   └── assets/            # CSS, JS, images
├── backend_php/           # PHP backend API
│   ├── ai_chat.php        # AI assistant endpoint
│   ├── start_analysis.php # Resume analysis engine
│   ├── llm_helper.php     # Multi-LLM integration
│   └── session_guard.php  # Authentication
├── ai_engine_python/      # Python AI engine
│   ├── pipelines/         # Analysis pipelines
│   ├── cognition_layer/   # Advanced AI features
│   └── utils/             # PDF parsing, text cleaning
├── database/              # SQL migrations
├── node_api/              # WebSocket server (optional)
└── docs/                  # Documentation
```

---

## 🚀 Quick Start

### Prerequisites
- **PHP**: 8.0 or higher
- **Python**: 3.9 or higher
- **MySQL**: 5.7 or higher
- **Composer**: For PHP dependencies
- **Node.js**: 16+ (optional, for WebSocket)

### Installation

#### 1. Clone Repository
```bash
git clone https://github.com/YOUR_USERNAME/ResumeIQ-X.git
cd ResumeIQ-X
```

#### 2. Configure Environment
```bash
cp .env.example .env
```

Edit `.env` with your credentials:
```env
# Database
DB_HOST=localhost
DB_NAME=resumeiq_x
DB_USER=root
DB_PASS=your_password

# LLM API Keys (at least one required)
GROQ_API_KEY=your_groq_key
OPENAI_API_KEY=your_openai_key
GEMINI_API_KEY=your_gemini_key

# Email (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password

# SMS (Optional)
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE=+1234567890
```

#### 3. Database Setup
```bash
# Import database schema
mysql -u root -p resumeiq_x < database/schema.sql

# Run migrations
php database/run_migrations.php
```

#### 4. Python Setup
```bash
cd ai_engine_python
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt
```

#### 5. Start Application
```bash
# Start PHP server
php -S localhost:8000 -t frontend/

# Start Python AI engine (separate terminal)
cd ai_engine_python
python pipelines/resume_pipeline.py

# Start WebSocket server (optional, separate terminal)
cd node_api
npm install
node server.js
```

#### 6. Access Application
- **Homepage**: http://localhost:8000
- **Admin Login**: http://localhost:8000/admin_login.html
- **Recruiter Login**: http://localhost:8000/recruiter_login.html
- **User Login**: http://localhost:8000/user_login.html

---

## 🌐 Cloud Deployment

### Supported Platforms
- ✅ **Railway**: Recommended (Dockerfile included)
- ✅ **Heroku**: Supported
- ✅ **AWS EC2**: Supported
- ✅ **DigitalOcean**: Supported
- ✅ **Google Cloud Run**: Supported

### Railway Deployment (Recommended)

#### 1. Install Railway CLI
```bash
npm install -g @railway/cli
railway login
```

#### 2. Initialize Project
```bash
railway init
railway link
```

#### 3. Add Environment Variables
```bash
railway variables set DB_HOST=your_db_host
railway variables set DB_NAME=resumeiq_x
railway variables set DB_USER=your_user
railway variables set DB_PASS=your_password
railway variables set GROQ_API_KEY=your_key
# ... add all variables from .env
```

#### 4. Deploy
```bash
railway up
```

#### 5. Setup Database
```bash
# Connect to Railway MySQL
railway run mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql
```

### Environment Variables for Production
```env
# Required
DB_HOST=production_host
DB_NAME=resumeiq_x
DB_USER=production_user
DB_PASS=strong_password
GROQ_API_KEY=your_groq_key

# Recommended
OPENAI_API_KEY=backup_key
GEMINI_API_KEY=backup_key
SMTP_HOST=smtp.gmail.com
SMTP_USER=production_email@domain.com
SMTP_PASS=app_password

# Optional
TWILIO_ACCOUNT_SID=for_sms
TWILIO_AUTH_TOKEN=for_sms
CLOUDINARY_URL=for_file_storage
```

---

## 📖 Documentation

### User Guides
- [Admin Dashboard Guide](docs/admin_dashboard_guide.md)
- [Recruiter Workflow](RECRUITER_WORKFLOW.md)
- [AI Chat Assistant](AI_CHAT_ASSISTANT_GUIDE.md)
- [SMS Setup Guide](SMS_SETUP_GUIDE.md)

### Technical Documentation
- [Architecture Overview](Project_Info/Architecture.md)
- [AI Engine Details](AI_CHAT_ARCHITECTURE.md)
- [Database Schema](database/schema.sql)
- [API Documentation](docs/api_documentation.md)

### Troubleshooting
- [AI Chat Troubleshooting](AI_CHAT_TROUBLESHOOTING.md)
- [Processing Count Fix](PROCESSING_COUNT_FINAL_FIX.md)
- [SMS Delivery Issues](SMS_SETUP_GUIDE.md)

---

## 🔧 Configuration

### LLM Provider Priority
Default fallback chain (configurable in `backend_php/llm_helper.php`):
1. **Groq** (Fastest, Free)
2. **OpenAI** (Most Reliable)
3. **Gemini** (Google)
4. **Anthropic** (Claude)
5. **DeepSeek** (Alternative)
6. **Ollama** (Local Fallback)

### Force Specific Provider
```env
MEERA_FORCE_PROVIDER=groq  # Use only Groq
```

### SMS Gateway Selection
```env
SMS_GATEWAY=twilio  # Options: twilio, msg91, fast2sms
```

---

## 🧪 Testing

### Run Tests
```bash
# PHP Backend Tests
php test_ai_chat.php
php check_twilio_status.php

# Python AI Engine Tests
cd ai_engine_python
python -m pytest tests/

# Frontend Tests
# Open test_chat_api.html in browser
```

### Test Accounts
```
Admin:
Email: admin@resumeiq.com
Password: admin123

Recruiter:
Email: recruiter@resumeiq.com
Password: recruiter123

User:
Email: user@resumeiq.com
Password: user123
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards
- **PHP**: PSR-12
- **Python**: PEP 8
- **JavaScript**: ES6+
- **SQL**: Prepared statements only

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Creator

**MAYUR GOPAL KOVE**

- GitHub: [@mayurkove](https://github.com/mayurkove)
- Email: mayurkove@example.com
- LinkedIn: [Mayur Kove](https://linkedin.com/in/mayurkove)

---

## 🙏 Acknowledgments

- **LLM Providers**: Groq, OpenAI, Google Gemini, Anthropic, DeepSeek
- **Libraries**: PHPMailer, PyMuPDF, Twilio SDK
- **Inspiration**: Modern talent acquisition challenges

---

## 📊 Project Stats

- **Lines of Code**: 50,000+
- **Files**: 200+
- **Languages**: PHP, Python, JavaScript, SQL
- **AI Models**: 6 LLM providers
- **Features**: 50+ core features

---

## 🔮 Roadmap

### Version 2.0 (Planned)
- [ ] Video interview analysis
- [ ] Blockchain-based credential verification
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native)
- [ ] Multi-language support
- [ ] API marketplace

### Version 1.5 (In Progress)
- [x] AI chat assistant
- [x] Real-time processing stats
- [x] Recruiter portal
- [x] Email campaigns
- [ ] Advanced reporting
- [ ] Bulk operations

---

## 📞 Support

For support, email support@resumeiq.com or open an issue on GitHub.

---

## ⭐ Star History

If you find this project useful, please consider giving it a star! ⭐

---

**Built with ❤️ by MAYUR GOPAL KOVE**
