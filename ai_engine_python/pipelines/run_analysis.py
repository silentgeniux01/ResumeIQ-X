"""
==================================================
ResumeIQ-X Pipeline Execution Controller v13
Enterprise Streaming Pipeline Engine
Signal Fusion Enabled Edition
Guaranteed DB Persistence Version
==================================================
"""

import os
import sys
import json
import time
import tempfile
import requests
import mysql.connector
from mysql.connector import pooling

from sentence_transformers import SentenceTransformer


# ==================================================
# PATH INIT
# ==================================================

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.dirname(CURRENT_DIR)

if PROJECT_ROOT not in sys.path:
    sys.path.insert(0, PROJECT_ROOT)

from pipelines.resume_pipeline import process_resume


# ==================================================
# MODEL SINGLETON
# ==================================================

MODEL = None


def get_embedding_model():

    global MODEL

    if MODEL is None:

        print("[Embedding] Loading model once...")

        MODEL = SentenceTransformer(
            "sentence-transformers/all-MiniLM-L6-v2",
            device="cpu"
        )

        print("[Embedding] Model ready")

    return MODEL


# ==================================================
# DATABASE CONFIG
# ==================================================

DB_CONFIG = dict(
    host="monorail.proxy.rlwy.net",
    user="root",
    password="FzOAGAJqKTQAyTjMoNszrzFHQEvXAlVr",
    database="railway",
    port=33459
)


DB_POOL = pooling.MySQLConnectionPool(
    pool_name="resumeiq_pool",
    pool_size=5,
    **DB_CONFIG
)


def get_connection():
    return DB_POOL.get_connection()


# ==================================================
# STATUS ENGINE
# ==================================================

def update_status(resume_id, status, progress):

    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("""

        UPDATE resumes
        SET analysis_status=%s,
            analysis_progress=%s
        WHERE id=%s

    """, (status, progress, resume_id))

    conn.commit()

    cursor.close()
    conn.close()

    print(f"[Status] {status} ({progress}%)")


# ==================================================
# CLOUD DOWNLOAD ENGINE
# ==================================================

def download_resume(url):

    print("[Pipeline] Downloading cloud file...")

    response = requests.get(url, stream=True, timeout=30)

    if response.status_code != 200:
        raise Exception(f"Cloud download failed: HTTP {response.status_code}")

    # Preserve original file extension from URL
    url_path = url.split("?")[0]  # strip query params
    original_ext = url_path.split(".")[-1].lower()
    if original_ext not in ["pdf", "txt", "doc", "docx", "png", "jpg", "jpeg"]:
        original_ext = "pdf"  # default to pdf

    suffix = f".{original_ext}"
    temp = tempfile.NamedTemporaryFile(delete=False, suffix=suffix)

    for chunk in response.iter_content(8192):
        temp.write(chunk)

    temp.close()

    print(f"[Pipeline] Downloaded to {temp.name} ({original_ext})")
    return temp.name


# ==================================================
# SIGNAL FUSION ENGINE (SAFE ENRICHMENT LAYER)
# ==================================================

def enrich_result_vectors(result):

    capability = result.get("capability_vector", {})

    engineering = capability.get("engineering_capability", 0)
    analytics = capability.get("analytics_capability", 0)

    if not result.get("talent_category"):

        if engineering > 0.65:
            result["talent_category"] = "Engineering Specialist"

        elif analytics > 0.65:
            result["talent_category"] = "Analytics Specialist"

        else:
            result["talent_category"] = "Generalist Engineer"


    missing = result.get("missing_dependencies", [])

    if not result.get("learning_recommendations"):

        roadmap = []

        for skill in missing[:5]:
            roadmap.append(f"Learn {skill}")

        result["learning_recommendations"] = roadmap


    strength = result.get("resume_strength_score", 0)

    if strength > 70:
        decision = "Strong Hire"

    elif strength > 50:
        decision = "Moderate Hire"

    else:
        decision = "Needs Improvement"

    result["recruiter_decision_label"] = decision


    readiness = result.get("career_readiness_score", 0)

    result["hire_confidence_score"] = round(
        (strength + readiness) / 2,
        2
    )


    signal_profile = result.get(
        "candidate_signal_profile",
        {}
    )

    signal_strength = signal_profile.get(
        "resume_signal_strength",
        0.5
    )

    result["ats_compatibility_score"] = round(
        signal_strength,
        2
    )

    return result


# ==================================================
# UPSERT ENGINE
# ==================================================

def upsert_analysis_results(resume_id, result):

    conn = get_connection()

    cursor = conn.cursor()

    cursor.execute("""

    INSERT INTO analysis_results (

        resume_id,
        resume_strength_score,
        confidence_score,
        career_readiness_score,
        talent_category,
        semantic_role_scores,
        domain_distribution,
        skill_maturity,
        missing_dependencies,
        learning_recommendations,
        trajectory_prediction,
        capability_vector,
        candidate_signal_profile,
        latent_skill_report,
        career_direction_vector,
        recruiter_decision_label,
        hire_confidence_score,
        ats_compatibility_score,
        summary

    )

    VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)

    ON DUPLICATE KEY UPDATE

        resume_strength_score=VALUES(resume_strength_score),
        confidence_score=VALUES(confidence_score),
        career_readiness_score=VALUES(career_readiness_score),
        talent_category=VALUES(talent_category),
        semantic_role_scores=VALUES(semantic_role_scores),
        domain_distribution=VALUES(domain_distribution),
        skill_maturity=VALUES(skill_maturity),
        missing_dependencies=VALUES(missing_dependencies),
        learning_recommendations=VALUES(learning_recommendations),
        trajectory_prediction=VALUES(trajectory_prediction),
        capability_vector=VALUES(capability_vector),
        candidate_signal_profile=VALUES(candidate_signal_profile),
        latent_skill_report=VALUES(latent_skill_report),
        career_direction_vector=VALUES(career_direction_vector),
        recruiter_decision_label=VALUES(recruiter_decision_label),
        hire_confidence_score=VALUES(hire_confidence_score),
        ats_compatibility_score=VALUES(ats_compatibility_score),
        summary=VALUES(summary)

    """, (

        int(resume_id),

        result.get("resume_strength_score", 0),
        result.get("confidence_score", 0),
        result.get("career_readiness_score", 0),
        result.get("talent_category", ""),

        json.dumps(result.get("semantic_role_scores", {})),
        json.dumps(result.get("domain_distribution", {})),
        json.dumps(result.get("skill_maturity", {})),
        json.dumps(result.get("missing_dependencies", [])),
        json.dumps(result.get("learning_recommendations", [])),
        json.dumps(result.get("trajectory_prediction", {})),
        json.dumps(result.get("capability_vector", {})),
        json.dumps(result.get("candidate_signal_profile", {})),
        json.dumps(result.get("latent_skill_report", {})),
        json.dumps(result.get("career_direction_vector", {})),

        result.get("recruiter_decision_label", ""),
        result.get("hire_confidence_score", 0),
        result.get("ats_compatibility_score", 0),

        result.get(
            "summary",
            "Resume analyzed successfully using ResumeIQ-X pipeline"
        )

    ))

    conn.commit()

    cursor.close()
    conn.close()

    print("[DB] Analysis stored successfully")

    return True


# ==================================================
# PIPELINE ENGINE
# ==================================================

def run_pipeline(resume_id, file_path):

    print(f"[Pipeline] Resume ID: {resume_id}")

    temp_file = None

    try:

        update_status(resume_id, "processing", 10)

        if file_path.startswith("http"):

            temp_file = download_resume(file_path)

            file_path = temp_file

        update_status(resume_id, "processing", 30)

        model = get_embedding_model()

        update_status(resume_id, "processing", 55)

        result = process_resume(
            file_path,
            model,
            resume_id
        )

        update_status(resume_id, "processing", 75)

        result = enrich_result_vectors(result)

        upsert_analysis_results(resume_id, result)

        update_status(resume_id, "completed", 100)

        print("[Pipeline] Completed successfully")

    except Exception as e:

        print("[Pipeline Error]", e)

        update_status(resume_id, "failed", 0)

    finally:

        if temp_file and os.path.exists(temp_file):
            os.remove(temp_file)


# ==================================================
# ENTRY
# ==================================================

def main():

    if len(sys.argv) < 3:
        return

    resume_id = sys.argv[1]
    file_path = sys.argv[2]

    if resume_id == "warmup":

        get_embedding_model()
        return

    start = time.time()

    run_pipeline(resume_id, file_path)

    print(
        "[Execution Time]",
        round(time.time() - start, 2),
        "seconds"
    )


if __name__ == "__main__":
    main()