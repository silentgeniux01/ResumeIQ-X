"""
==================================================
ResumeIQ-X MASTER INTELLIGENCE PIPELINE v6
Enterprise Cognitive Execution Engine
Persistent Embedding + Adaptive Parallelism Enabled
==================================================
"""

import os
import sys
import time
import multiprocessing
from concurrent.futures import ThreadPoolExecutor


CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.dirname(CURRENT_DIR)

if PROJECT_ROOT not in sys.path:
    sys.path.insert(0, PROJECT_ROOT)


# ==================================================
# UTILITIES
# ==================================================

from utils.text_cleaner import clean_text
from utils.pdf_reader import read_pdf

try:
    from utils.image_reader import read_image_text
    OCR_AVAILABLE = True
except Exception:
    OCR_AVAILABLE = False


# ==================================================
# CORE MODULES
# ==================================================

from skill_extractor import extract_skills
from semantic_role_matcher import compute_semantic_role_alignment
from confidence_estimator import compute_confidence_score
from scorer import generate_resume_score_report
from recommender import generate_recommendation_summary
from skill_gap_detector import analyze_skill_gap
from talent_score_calculator import compute_talent_score


# ==================================================
# COGNITION LAYER MODULES
# ==================================================

from cognition_layer.skill_graph_expander import run_skill_graph_expansion
from cognition_layer.semantic_capability_estimator import compute_capability_vector
from cognition_layer.latent_skill_inference import infer_latent_skills
from cognition_layer.career_vector_builder import build_career_direction_vector
from cognition_layer.candidate_signal_profiler import build_candidate_signal_profile
from cognition_layer.trajectory_predictor import predict_career_trajectory
from cognition_layer.candidate_memory_index import candidate_memory_pipeline

from cognition_layer.persistent_embedding_store import (
    get_or_create_embedding
)


# ==================================================
# FILE LOADER ENGINE
# ==================================================

def read_txt(path):

    with open(path, "r", errors="ignore") as file:
        return file.read()


def load_resume_text(file_path):

    # Get extension from file path or original URL
    # Temp files have no extension — detect from content
    ext = file_path.split(".")[-1].lower().split("?")[0]  # handle URL params

    # If no recognizable extension (temp file), try to detect from content
    if ext not in ["txt", "pdf", "png", "jpg", "jpeg", "doc", "docx"]:
        # Try PDF magic bytes
        try:
            with open(file_path, "rb") as f:
                header = f.read(5)
            if header.startswith(b"%PDF"):
                ext = "pdf"
            elif header.startswith(b"\x89PNG"):
                ext = "png"
            elif header[:2] in [b"\xff\xd8", b"BM"]:
                ext = "jpg"
            else:
                ext = "txt"  # fallback — try as text
        except Exception:
            ext = "txt"

    if ext == "txt":
        return read_txt(file_path)

    if ext == "pdf":
        return read_pdf(file_path)

    if ext in ["doc", "docx"]:
        # Try reading as text fallback
        try:
            return read_txt(file_path)
        except Exception:
            raise Exception("Unsupported resume format: docx")

    if OCR_AVAILABLE and ext in ["png", "jpg", "jpeg"]:
        return read_image_text(file_path)

    # Last resort — try as plain text
    try:
        return read_txt(file_path)
    except Exception:
        raise Exception("Unsupported resume format")


# ==================================================
# DOMAIN DISTRIBUTION ENGINE (SMART FALLBACK VERSION)
# ==================================================

DOMAIN_MAP = {

    "python": "data_science",
    "pandas": "data_science",
    "numpy": "data_science",

    "tensorflow": "artificial_intelligence",
    "machine_learning": "artificial_intelligence",

    "html": "web_development",
    "css": "web_development",
    "javascript": "web_development",

    "mysql": "backend_engineering",
    "mongodb": "backend_engineering"

}


def compute_domain_distribution(skill_list):

    if not skill_list:
        return {}

    distribution = {}

    for skill in skill_list:

        domain = DOMAIN_MAP.get(skill, "general_engineering")

        distribution[domain] = distribution.get(domain, 0) + 1

    total = sum(distribution.values())

    return {

        k: round(v / total, 4)
        for k, v in distribution.items()
    }


# ==================================================
# CAREER READINESS ENGINE
# ==================================================

def compute_career_readiness(domain_distribution):

    if not domain_distribution:
        return 0

    diversity_score = len(domain_distribution) / 4

    return round(min(diversity_score * 100, 100), 2)


# ==================================================
# MASTER PIPELINE ENGINE
# ==================================================

def process_resume(file_path, model, resume_id,
                   target_role="Data Scientist"):

    start = time.time()


    # ------------------------------------------------
    # STEP 1 TEXT EXTRACTION
    # ------------------------------------------------

    raw_text = load_resume_text(file_path)

    if not raw_text:
        raise Exception("Resume text extraction failed")

    cleaned_text = clean_text(raw_text)


    # ------------------------------------------------
    # STEP 2 PERSISTENT EMBEDDING
    # ------------------------------------------------

    resume_embedding = get_or_create_embedding(
        resume_id,
        cleaned_text
    )


    # ------------------------------------------------
    # STEP 3 SKILL EXTRACTION
    # ------------------------------------------------

    detected_skills = extract_skills(cleaned_text)

    detected_skills = run_skill_graph_expansion(
        cleaned_text,
        detected_skills
    )

    domain_distribution = compute_domain_distribution(
        detected_skills.get("skills", [])
    )


    # ------------------------------------------------
    # STEP 4 SEMANTIC ROLE MATCH
    # ------------------------------------------------

    semantic_alignment = compute_semantic_role_alignment(
        cleaned_text,
        model,
        target_role,
        domain_distribution
    ) or {}


    semantic_alignment_score = semantic_alignment.get(
        "predicted_role_score",
        0
    )


    # ------------------------------------------------
    # STEP 5 PARALLEL COGNITION ENGINE
    # ------------------------------------------------

    workers = min(4, multiprocessing.cpu_count())

    with ThreadPoolExecutor(max_workers=workers) as executor:

        latent_future = executor.submit(
            infer_latent_skills,
            model,
            detected_skills,
            domain_distribution,
            semantic_alignment
        )

        capability_future = executor.submit(
            compute_capability_vector,
            detected_skills,
            domain_distribution,
            semantic_alignment
        )

        gap_future = executor.submit(
            analyze_skill_gap,
            detected_skills.get("skills", []),
            domain_distribution,
            target_role
        )

        recommendation_future = executor.submit(
            generate_recommendation_summary,
            detected_skills,
            domain_distribution,
            semantic_alignment
        )

        latent_skill_report = latent_future.result()
        capability_vector = capability_future.result()
        skill_gap_report = gap_future.result()
        recommendation_summary = recommendation_future.result()


    # ------------------------------------------------
    # STEP 6 SIGNAL PROFILE
    # ------------------------------------------------

    candidate_signal_profile = build_candidate_signal_profile(
        detected_skills,
        domain_distribution,
        semantic_alignment,
        capability_vector
    )


    # ------------------------------------------------
    # STEP 7 TRAJECTORY ENGINE
    # ------------------------------------------------

    career_direction_vector = build_career_direction_vector(
        detected_skills,
        domain_distribution,
        semantic_alignment,
        capability_vector,
        latent_skill_report
    )


    trajectory_prediction = predict_career_trajectory(
        career_direction_vector,
        capability_vector,
        candidate_signal_profile,
        semantic_alignment
    )


    # ------------------------------------------------
    # STEP 8 SCORE ENGINE
    # ------------------------------------------------

    career_readiness_score = compute_career_readiness(
        domain_distribution
    )


    confidence_score = compute_confidence_score(
        detected_skills.get("skills", []),
        domain_distribution,
        semantic_alignment,
        detected_skills.get("skill_maturity", {})
    )


    score_report = generate_resume_score_report(
        detected_skills,
        domain_distribution,
        semantic_alignment,
        confidence_score,
        career_readiness_score
    )


    resume_strength_score = score_report.get(
        "resume_score",
        70
    )


    compute_talent_score(
        resume_strength_score,
        confidence_score,
        career_readiness_score,
        semantic_alignment_score
    )


    # ------------------------------------------------
    # STEP 9 MEMORY INDEX UPDATE (SAFE MODE)
    # ------------------------------------------------

    try:

        candidate_memory_pipeline(
            candidate_id=str(resume_id),
            capability_vector=capability_vector,
            confidence_score=confidence_score,
            readiness_score=career_readiness_score,
            trajectory_prediction=trajectory_prediction
        )

    except Exception as memory_error:

        print("[Memory Index Warning]", memory_error)


    execution_time = round(time.time() - start, 3)


    # ------------------------------------------------
    # FINAL OUTPUT PACKAGE
    # ------------------------------------------------

    return {

        "resume_strength_score": resume_strength_score,

        "confidence_score": confidence_score,

        "career_readiness_score": career_readiness_score,

        "semantic_role_alignment": semantic_alignment,

        "domain_distribution": domain_distribution,

        "skill_maturity":
            detected_skills.get("skill_maturity", {}),

        "missing_dependencies":
            skill_gap_report.get("missing_skills", []),

        "learning_recommendations":
            recommendation_summary,

        "trajectory_prediction":
            trajectory_prediction,

        "capability_vector":
            capability_vector,

        "candidate_signal_profile":
            candidate_signal_profile,

        "latent_skill_report":
            latent_skill_report,

        "career_direction_vector":
            career_direction_vector,

        "execution_time":
            execution_time
    }