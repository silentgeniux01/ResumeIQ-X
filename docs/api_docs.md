# ResumeIQ-X API Documentation
### Semantic Resume Intelligence Service Interface

---

# Overview

ResumeIQ-X exposes a modular REST API gateway built using Node.js that connects the frontend interface with the Python semantic intelligence engine.

The API enables:

Resume upload  
Semantic intelligence execution  
Career alignment prediction  
Confidence score estimation  
Talent intelligence scoring  
Recruiter dashboard analytics retrieval  
System health monitoring  
Future authentication integration  

Base API URL:

http://localhost:5000/api

---

# API Architecture Flow

Frontend UI
↓
Node API Gateway (Express.js)
↓
Python Semantic Intelligence Engine
↓
Embedding Layer
↓
Vector Store (FAISS)
↓
Database Storage Layer

---

# Health Check Endpoint

## Endpoint

GET /

## Description

Checks whether the ResumeIQ-X backend server is running.

## Example Request

GET http://localhost:5000/

## Example Response

ResumeIQ-X API Server Running Successfully 🚀

---

# Resume Upload Endpoint

## Endpoint

POST /api/upload

## Description

Uploads a resume file and triggers the semantic intelligence pipeline execution.

Supported formats:

TXT  
PDF  
DOCX  
PNG  
JPG  
JPEG  

Supports OCR fallback automatically.

---

# Request Parameters

Form-data:

| Parameter | Type | Required | Description |
|----------|------|----------|-------------|
| resume | file | Yes | Resume file |
| targetRole | string | No | Optional role alignment target |

Example:

resume: candidate_resume.pdf  
targetRole: Data Scientist

---

# Example Upload Request (JavaScript)
const formData = new FormData()
formData.append("resume", file)
formData.append("targetRole", "AI Engineer")
fetch("http://localhost:5000/api/upload⁠�", { method: "POST", body: formData })
---

# Example Upload Response
{ "message": "Resume processed successfully", "analysis": {
"detected_skills": [...],
"resume_strength_score": 82,
"confidence_score": 0.78,
"career_readiness_score": 74,
"semantic_alignment": {
"predicted_role": "AI Engineer", "predicted_role_score": 0.82, "alignment_level": "Strong Alignment"
},
"job_recommendations": [...],
"skill_gap_analysis": {...},
"talent_score": 81
} }

---

# Recruiter Jobs Dataset Endpoint

## Endpoint

GET /api/jobs

## Description

Returns available job-role intelligence dataset used by recruiter dashboard filtering system.

## Example Request

GET http://localhost:5000/api/jobs

## Example Response
[ { "title": "Data Scientist", "skills_required": [ "python", "machine learning", "pandas" ] }, { "title": "AI Engineer", "skills_required": [ "python", "tensorflow", "deep learning" ] } ]

---

# Authentication Endpoint (Future Expansion)

## Endpoint

POST /api/auth/login

## Description

Authenticates recruiter account access.

## Example Request
{ "email": "recruiter@example.com", "password": "secure_password" }

## Example Response
{ "token": "jwt_access_token" }

---

# Semantic Role Alignment Output Structure

Example:
{ "predicted_role": "AI Engineer", "predicted_role_score": 0.82, "target_role_score": 0.76, "alignment_level": "Strong Alignment", "career_trajectory_roles": [ "ML Engineer", "Research Engineer" ] }

---

# Confidence Estimation Output Structure

Example:
{ "confidence_score": 0.76, "confidence_level": "High", "confidence_signals": { "skill_density": 0.80, "semantic_alignment": 0.75, "domain_strength": 0.72 } }

---

# Skill Gap Analysis Output Structure

Example:
{ "target_role": "Data Scientist", "missing_skills": [ "numpy", "statistics" ], "learning_plan": [ "Consider learning numpy", "Consider learning statistics" ] }

---

# Talent Intelligence Score Output Structure

Example:
{ "talent_score": 81, "talent_category": "Industry Ready", "career_readiness_score": 78 }

---

# Vector Store Internal API Layer

Module:

ai_engine_python/vector_store.py

Capabilities:

resume embedding indexing  
semantic similarity search  
duplicate resume detection  
candidate clustering  
semantic recruiter filtering  

Example Usage:
index_resume_vector(resume_text)
search_similar_resumes(query_text)
detect_duplicate_resume(resume_text)

---

# Error Response Format

Example:
{ "error": "Resume file not found" }

Example:
{ "error": "Unsupported file format" }

Example:
{ "error": "AI pipeline execution failed" }

---

# Standard API Response Structure

ResumeIQ-X responses follow structured JSON format:
{ message, analysis, metadata, error }

This ensures compatibility with:

Frontend dashboards  
Recruiter analytics panels  
ATS integrations  
External research pipelines  
Future microservice deployments  

---

# Planned Future Endpoints

Upcoming production-grade API endpoints:

POST /api/batch_upload  
GET /api/candidate_similarity  
GET /api/semantic_search  
GET /api/vector_index_status  
POST /api/recruiter_feedback  
POST /api/model_retrain_trigger  

These endpoints will evolve ResumeIQ-X into a full semantic hiring intelligence platform capable of large-scale recruiter analytics deployment.

---

# Integration Compatibility

ResumeIQ-X API supports integration with:

ATS systems  
Recruiter dashboards  
Career recommendation engines  
Talent intelligence platforms  
Future AFIS-X intelligence architecture modules