# ResumeIQ-X Setup Guide
### Complete Installation & Execution Instructions

This guide explains how to install and run the ResumeIQ-X semantic resume intelligence platform on a local development machine.

The setup includes:

Frontend UI  
Node.js API Gateway  
Python Semantic Intelligence Engine  
Vector Store (FAISS)  
OCR Support (Tesseract)  

Follow steps carefully for a successful installation.

---

# Environment Configuration (Required First Step)

ResumeIQ-X uses a `.env` file at the project root for all credentials and configuration. **Never commit `.env` to version control.**

## Step 0: Create Your .env File

Copy the template:

```
cp .env.example .env
```

On Windows:

```
copy .env.example .env
```

Open `.env` and fill in your values:

```
DB_HOST=your_database_host
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

NODE_API_URL=http://127.0.0.1:5000
APP_URL=http://localhost
```

For Python paths:
- **Windows**: `PYTHON_EXECUTABLE=ai_engine_python/venv/Scripts/python.exe`
- **Linux/Mac**: `PYTHON_EXECUTABLE=ai_engine_python/venv/bin/python3`

## Security Rules

- `.env` is listed in `.gitignore` — it will never be committed
- Use `.env.example` as the template (safe to commit, contains no real credentials)
- Rotate credentials immediately if they are ever accidentally exposed
- For production, set environment variables directly on the server instead of using a `.env` file

---

# System Requirements

Minimum requirements:

Python 3.10+
Node.js 18+
Git installed
Tesseract OCR installed
4GB RAM recommended
Internet connection (for embedding model download)

Recommended:

Python 3.11
Node.js 20+
8GB RAM

---

# Step 1: Clone Repository

Clone the ResumeIQ-X repository:

git clone https://github.com/yourusername/ResumeIQ-X.git

Navigate into project folder:

cd ResumeIQ-X

---

# Step 2: Create Python Virtual Environment

Windows:

python -m venv venv

Activate environment:

venv\Scripts\activate

Linux / Mac:

python3 -m venv venv

source venv/bin/activate

---

# Step 3: Install Python Dependencies

Inside activated virtual environment:

pip install -r requirements.txt

Install FAISS vector database:

pip install faiss-cpu

Install embedding model dependency:

pip install sentence-transformers

---

# Step 4: Install Tesseract OCR (Required for Image Resume Support)

Download installer:

https://github.com/tesseract-ocr/tesseract

Install using default settings.

After installation verify:

tesseract --version

Expected output:

tesseract 5.x.x installed

If command not recognized:

Add Tesseract installation folder to system PATH.

Example path:

C:\Program Files\Tesseract-OCR

---

# Step 5: Install Node.js Dependencies

Navigate to Node API folder:

cd node_api

Install packages:

npm install

Expected packages installed:

express
multer
cors
child_process
dotenv
jsonwebtoken (optional future use)

---

# Step 6: Start Node API Server

Inside node_api folder:

node server.js

Expected output:

ResumeIQ-X API running on port 5000

Server health endpoint:

http://localhost:5000/

Should return:

ResumeIQ-X API Server Running Successfully 🚀

---

# Step 7: Test Python AI Engine

Navigate to:

ai_engine_python

Run test command:

python parser.py ../uploads/resumes/sample_resume.txt

Expected output:

JSON semantic analysis result

Example:

{
"detected_skills": [...],
"resume_strength_score": 75,
"confidence_score": 0.72
}

---

# Step 8: Launch Frontend Application

Navigate to:

frontend/

Open file:

index.html

in browser.

Recommended:

Use Live Server extension in VS Code

or

Double-click index.html

---

# Step 9: Upload Test Resume

Open:

upload_resume.html

Upload sample resume:

TXT
PDF
DOCX
PNG
JPG

Expected behavior:

Resume processed
Semantic analysis generated
Dashboard redirected automatically

---

# Step 10: Verify Vector Store Activation

Navigate to:

ai_engine_python/vector_memory/

Expected files generated:

resume_index.faiss
metadata.json

This confirms semantic memory indexing is working.

---

# Step 11: Verify Embedding Engine Download

First execution downloads transformer model automatically:

all-MiniLM-L6-v2

Stored inside:

Python cache directory

Example:

C:\Users\<username>\.cache\huggingface

No manual setup required.

---

# Step 12: Verify Database (Optional PHP Layer)

Navigate to:

database/

Import schema:

schema.sql

Import seed data:

seed.sql

Using:

MySQL Workbench

or

phpMyAdmin

---

# Step 13: System Health Checklist

Verify:

Node server running
Python virtual environment active
Embedding model downloaded
FAISS installed
Tesseract installed
Frontend opened successfully

If all pass:

ResumeIQ-X ready for execution

---

# Example Execution Flow

User uploads resume

↓

Node API receives file

↓

Python parser executes pipeline

↓

Embedding engine analyzes resume

↓

Semantic role matcher predicts career alignment

↓

Confidence estimator computes reliability score

↓

Talent score calculator evaluates readiness

↓

Vector store indexes resume embedding

↓

Dashboard displays results

---

# Common Issues and Solutions

Issue:

ModuleNotFoundError

Solution:

Activate virtual environment

venv\Scripts\activate

---

Issue:

Cannot connect to AI server

Solution:

Run Node server

node server.js

---

Issue:

FAISS not installed

Solution:

pip install faiss-cpu

---

Issue:

Tesseract command not found

Solution:

Add Tesseract installation folder to PATH

---

Issue:

Embedding model downloading slowly

Solution:

Check internet connection

Model downloads only once

---

# Development Mode Execution Order

Recommended startup order:

Activate virtual environment

↓

Start Node server

↓

Open frontend/index.html

↓

Upload resume

---

# Production Deployment Recommendation (Future)

Suggested deployment stack:

Frontend → Vercel

Node API → Render / Railway

Python Engine → FastAPI container

Vector Store → Persistent volume

Database → PostgreSQL

---

# Integration Compatibility

ResumeIQ-X supports integration with:

Recruiter dashboards

ATS systems

Career recommendation platforms

AI hiring assistants

Future AFIS-X intelligence modules

---

# Setup Completed Successfully

ResumeIQ-X is now ready to perform:

Semantic resume understanding  
Career alignment prediction  
Talent readiness scoring  
Skill gap detection  
Vector similarity indexing  
Recruiter intelligence analytics