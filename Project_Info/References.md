# ResumeIQ-X — References

## 1. AI & LLM APIs

### Groq (Primary LLM Provider)
- **Documentation:** https://console.groq.com/docs
- **Models Used:** llama-3.3-70b-versatile, llama3-8b-8192, gemma2-9b-it
- **API Endpoint:** `https://api.groq.com/openai/v1/chat/completions`
- **Free Tier:** 100,000 tokens/day
- **Why Chosen:** Fastest inference speed, free tier, OpenAI-compatible API

### OpenAI (Fallback #2)
- **Documentation:** https://platform.openai.com/docs
- **Model Used:** gpt-4o-mini
- **API Endpoint:** `https://api.openai.com/v1/chat/completions`
- **Pricing:** ~$0.15/1M input tokens
- **Why Chosen:** Most reliable, best JSON output quality

### Google Gemini (Fallback #3)
- **Documentation:** https://ai.google.dev/gemini-api/docs
- **Models Used:** gemini-2.0-flash-lite, gemini-2.0-flash, gemini-2.5-flash
- **API Endpoint:** `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent`
- **Free Tier:** 15 requests/minute
- **Why Chosen:** Free tier available, good multilingual support

### Anthropic Claude (Fallback #4)
- **Documentation:** https://docs.anthropic.com
- **Model Used:** claude-3-5-haiku-20241022
- **API Endpoint:** `https://api.anthropic.com/v1/messages`
- **Why Chosen:** Excellent at structured output, good reasoning

### DeepSeek (Fallback #5)
- **Documentation:** https://platform.deepseek.com/docs
- **Model Used:** deepseek-chat
- **API Endpoint:** `https://api.deepseek.com/v1/chat/completions`
- **Why Chosen:** Cost-effective, OpenAI-compatible API

### Ollama (Local Fallback)
- **Documentation:** https://ollama.ai/docs
- **Model Used:** llama3.1:latest (8B parameters, 4.9GB)
- **API Endpoint:** `http://localhost:11434/api/generate`
- **Why Chosen:** Works offline, no API costs, final safety net

---

## 2. Cloud Services

### Cloudinary (File Storage)
- **Documentation:** https://cloudinary.com/documentation
- **SDK Used:** REST API (no SDK — pure cURL)
- **Upload Endpoint:** `https://api.cloudinary.com/v1_1/{cloud_name}/raw/upload`
- **Free Tier:** 25GB storage, 25GB bandwidth/month
- **Why Chosen:** Reliable CDN, easy REST API, free tier sufficient

### Railway (Hosting + Database)
- **Documentation:** https://docs.railway.app
- **Services Used:** Web Service (Docker), MySQL Service
- **Pricing:** ~$5/month for hobby plan
- **Why Chosen:** Easy Docker deployment, managed MySQL, automatic SSL

### Twilio (SMS)
- **Documentation:** https://www.twilio.com/docs/sms
- **API Endpoint:** `https://api.twilio.com/2010-04-01/Accounts/{SID}/Messages.json`
- **Pricing:** ~$0.0075/SMS
- **Why Chosen:** Most reliable SMS provider, excellent documentation

---

## 3. PHP Libraries & Extensions

### PDO (PHP Data Objects)
- **Documentation:** https://www.php.net/manual/en/book.pdo.php
- **Used For:** All database queries (prepared statements)
- **Why Chosen:** Database-agnostic, prevents SQL injection

### cURL
- **Documentation:** https://www.php.net/manual/en/book.curl.php
- **Used For:** All HTTP requests (LLM APIs, Cloudinary, Twilio)
- **Why Chosen:** Built into PHP, full HTTP control

### OpenSSL
- **Documentation:** https://www.php.net/manual/en/book.openssl.php
- **Used For:** SMTP STARTTLS encryption
- **Why Chosen:** Required for secure email delivery

### ZipArchive
- **Documentation:** https://www.php.net/manual/en/class.ziparchive.php
- **Used For:** DOCX file extraction (ZIP format)
- **Why Chosen:** Built into PHP, no external library needed

---

## 4. Python Libraries

### PyMuPDF (fitz)
- **Documentation:** https://pymupdf.readthedocs.io
- **PyPI:** https://pypi.org/project/PyMuPDF/
- **Used For:** Primary PDF text extraction
- **Why Chosen:** Fastest, most accurate, handles complex PDFs

### pdfplumber
- **Documentation:** https://github.com/jsvine/pdfplumber
- **PyPI:** https://pypi.org/project/pdfplumber/
- **Used For:** Secondary PDF extraction (layout-aware)
- **Why Chosen:** Better for tables and structured PDFs

### pytesseract
- **Documentation:** https://github.com/madmaze/pytesseract
- **Used For:** OCR for scanned/image PDFs
- **Why Chosen:** Python wrapper for Tesseract OCR engine

### pdf2image
- **Documentation:** https://github.com/Belval/pdf2image
- **Used For:** Convert PDF pages to images for OCR
- **Why Chosen:** Required for OCR pipeline

---

## 5. Frontend Libraries

### Chart.js
- **Documentation:** https://www.chartjs.org/docs/latest/
- **Version:** 4.4.0
- **CDN (original):** https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
- **Local Copy:** `frontend/assets/js/chart.min.js`
- **Used For:** Radar, doughnut, bar, polar area, funnel charts
- **Why Chosen:** Lightweight, beautiful defaults, easy API

### Font Awesome
- **Documentation:** https://fontawesome.com/docs
- **Version:** 6.4.0
- **CDN:** https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css
- **Used For:** Icons throughout the UI
- **Why Chosen:** Comprehensive icon set, CSS-based

### Google Fonts
- **URL:** https://fonts.googleapis.com
- **Fonts Used:** Inter (body), Space Grotesk (headings)
- **Why Chosen:** Professional, modern typography

---

## 6. Development Tools

### XAMPP
- **URL:** https://www.apachefriends.org
- **Version:** Latest
- **Used For:** Local development server (Apache + PHP + MySQL)

### Ollama
- **URL:** https://ollama.ai
- **Used For:** Running local LLM (llama3.1)
- **Install:** `winget install Ollama.Ollama`

### Docker
- **Documentation:** https://docs.docker.com
- **Used For:** Production containerization

### Railway
- **URL:** https://railway.app
- **Used For:** Cloud deployment

---

## 7. Standards & Protocols

| Standard | Usage |
|----------|-------|
| HTTP/1.1 | All API communication |
| SMTP (RFC 5321) | Email delivery |
| STARTTLS (RFC 3207) | Secure SMTP |
| JSON (RFC 8259) | API request/response format |
| bcrypt | Password hashing |
| Base64 | SMTP authentication encoding |
| UTF-8 | All text encoding |
| ISO 8601 | Date/time format in DB |

---

## 8. Security References

| Reference | Application |
|-----------|-------------|
| OWASP Top 10 | SQL injection, XSS prevention |
| PHP Security Best Practices | Session management, input validation |
| Twilio Security | SMS OTP best practices |
| NIST Password Guidelines | bcrypt cost factor selection |

---

## 9. Academic References

| Topic | Reference |
|-------|-----------|
| LLM-based Information Extraction | "Large Language Models for Information Extraction" (2023) |
| Resume Parsing | "Automated Resume Screening Using NLP" (2022) |
| Hiring Bias Reduction | "AI in Recruitment: Reducing Bias" (2023) |
| Fallback Systems | "Resilient AI Systems Design Patterns" (2024) |

---

## 10. Project Repository

| Item | Details |
|------|---------|
| **Creator** | Mayur Gopal Kove |
| **Created** | 2024-2026 |
| **Language** | PHP, Python, JavaScript |
| **Database** | MySQL 9.4 (Railway) |
| **Deployment** | Railway (Docker) |
| **Local Dev** | XAMPP (Windows) |
