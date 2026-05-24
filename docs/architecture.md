# ResumeIQ-X Architecture
### AI-Based Resume Analyzer and Job Recommendation Intelligence System

---

# Overview

ResumeIQ-X is a modular semantic talent intelligence pipeline designed to perform:

- Resume parsing
- Skill extraction
- Domain reasoning
- Semantic role alignment
- Confidence estimation
- Career readiness scoring
- Talent intelligence ranking
- Skill-gap detection
- Vector similarity indexing

The system is designed to evolve into a scalable **Talent Intelligence Platform** compatible with future integration into **AFIS-X** and enterprise recruitment ecosystems.

---

# High-Level System Architecture

Frontend (HTML / CSS / JS)
↓
Node API Gateway (Express.js)
↓
Python AI Engine
↓
Semantic Intelligence Pipeline
↓
Vector Store (FAISS)
↓
Database Layer (MySQL / JSON storage)
↓
Recruiter Dashboard Analytics

---

# Core System Layers

## 1️⃣ Frontend Intelligence Layer

Location:

frontend/

Responsibilities:

- Resume upload interface
- Dashboard analytics visualization
- Semantic role alignment rendering
- Recruiter candidate ranking UI
- Backend health monitoring
- API communication abstraction

Key Files:

frontend/js/upload.js  
frontend/js/dashboard.js  
frontend/js/result.js  
frontend/js/recruiter_dashboard.js  
frontend/js/api_client.js  
frontend/js/index.js  

---

## 2️⃣ Node API Orchestration Layer

Location:

node_api/

Responsibilities:

- Resume upload routing
- Python pipeline execution bridge
- Authentication support
- Recruiter analytics API
- Middleware security hooks
- Future microservice routing compatibility

Key Modules:

node_api/server.js  
node_api/controllers/uploadController.js  
node_api/controllers/jobController.js  
node_api/controllers/authController.js  
node_api/services/pythonBridge.js  
node_api/routes/uploadRoute.js  
node_api/routes/jobRoute.js  
node_api/routes/authRoute.js  

Acts as:

AI Gateway Layer

---

## 3️⃣ Python Semantic Intelligence Engine

Location:

ai_engine_python/

This is the core intelligence brain of ResumeIQ-X.

Pipeline Responsibilities:

resume ingestion  
multi-format parsing  
OCR fallback support  
skill extraction  
semantic role matching  
confidence modeling  
career readiness estimation  
talent score computation  
skill-gap reasoning  
vector indexing  

---

# Pipeline Execution Flow

parser.py
↓
resume_pipeline.py
↓
text_cleaner.py
↓
skill_extractor.py
↓
semantic_role_matcher.py
↓
confidence_estimator.py
↓
scorer.py
↓
talent_score_calculator.py
↓
recommender.py
↓
skill_gap_detector.py
↓
vector_store.py

---

## 4️⃣ Semantic Embedding Engine

Location:

ai_engine_python/embedding_engine.py

Uses:

Sentence Transformers (MiniLM)

Capabilities:

- Resume semantic encoding
- Role similarity scoring
- Candidate clustering readiness
- Duplicate detection
- Vector indexing support
- Future RAG compatibility

---

## 5️⃣ Vector Memory Layer

Location:

ai_engine_python/vector_store.py

Technology:

FAISS

Capabilities:

resume similarity search  
candidate clustering  
semantic filtering  
duplicate resume detection  
persistent talent memory indexing  

Transforms system from:

resume analyzer

into:

semantic talent intelligence platform

---

## 6️⃣ Knowledge Configuration Layer

Location:

ai_engine_python/models/

Files:

job_roles.json  
skill_library.json  
resume_scoring_weights.json  
file_format_config.json  
model_registry.json  

Responsibilities:

role ontology  
skill ontology  
scoring parameterization  
parser configuration  
model version control  

Supports:

config-driven AI pipeline behavior

---

## 7️⃣ Talent Intelligence Scoring Stack

Modules:

semantic_role_matcher.py  
confidence_estimator.py  
scorer.py  
talent_score_calculator.py  

Computes:

resume_strength_score  
confidence_score  
career_readiness_score  
semantic_alignment_score  
talent_intelligence_score  

Produces recruiter-grade analytics output.

---

## 8️⃣ Skill Gap Intelligence Engine

Module:

skill_gap_detector.py

Detects:

missing skills  
coverage score  
gap severity  
weak domains  
learning roadmap suggestions  

Supports:

career trajectory modeling  
candidate improvement planning  

---

## 9️⃣ Database Layer

Location:

backend_php/  
database/  

Stores:

analysis_results  
semantic_role_scores  
confidence_scores  
career_readiness_scores  
domain_distribution  
candidate similarity metadata  

Designed for:

analytics persistence  
recruiter dashboard querying  
future recommendation learning loops  

---

## 🔟 Recruiter Intelligence Dashboard Layer

Location:

frontend/recruiter_dashboard.html

Capabilities:

candidate ranking  
talent score filtering  
confidence-based shortlist generation  
semantic role alignment comparison  
multi-candidate analytics view  

---

# Intelligence Signals Used in ResumeIQ-X

The pipeline evaluates resumes using:

Skill Density  
Semantic Role Alignment  
Domain Distribution  
Confidence Estimation  
Career Readiness Score  
Talent Intelligence Score  
Skill Gap Coverage  
Vector Similarity Context  

This enables:

semantic hiring intelligence instead of keyword matching

---

# Supported Resume Formats

TXT  
PDF  
DOCX  
PNG  
JPG  
JPEG  

OCR Support:

Tesseract OCR integration enabled

Future Support Planned:

LinkedIn Export PDFs  
Europass CV  
Canva Resume Layouts  
Google Docs Resume Export  

---

# AI Models Used

Sentence Transformers MiniLM

Purpose:

semantic embedding generation  
role similarity ranking  
candidate clustering support  
vector memory indexing  

Future Upgrade Path:

OpenAI Embeddings  
Instructor Models  
Domain-Finetuned Resume Transformers  

---

# Vector Store Capabilities

Powered by:

FAISS

Supports:

resume similarity search  
candidate clustering  
duplicate resume detection  
semantic recruiter filtering  
future RAG augmentation pipelines  

---

# ResumeIQ-X Intelligence Pipeline Output

The system produces:

Detected Skills  
Domain Distribution  
Semantic Role Alignment  
Confidence Score  
Career Readiness Score  
Resume Strength Score  
Talent Intelligence Score  
Job Recommendations  
Skill Gap Analysis  
Execution Metadata  

---

# Future Upgrade Roadmap

Planned enhancements:

Graph-based skill ontology engine  
LLM-powered resume rewriting assistant  
Recruiter semantic search engine  
Career trajectory prediction engine  
Multi-resume batch ranking pipeline  
Realtime ATS scoring feedback loop  
Vector RAG career assistant chatbot  

---

# Research Positioning

ResumeIQ-X qualifies as:

Semantic Resume Intelligence System  
AI Hiring Readiness Predictor  
Talent Capability Ranking Engine  
Career Alignment Forecasting Model  

Suitable for:

Research Portfolio  
Startup MVP Deployment  
MIT-Level Systems Portfolio  
PhD AI Systems Demonstration  
AFIS-X Integration Layer Expansion