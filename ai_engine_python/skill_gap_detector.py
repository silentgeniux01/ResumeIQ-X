"""
==================================================
ResumeIQ-X Skill Gap Intelligence Engine
Semantic Career Gap Detection + Learning Planner
Production + Research Grade Capability Analyzer
==================================================
"""

import json
import os


# ==================================================
# LOAD ROLE REQUIREMENT DATASET
# ==================================================

BASE_DIR = os.path.dirname(__file__)

ROLE_DATASET_PATH = os.path.join(
    BASE_DIR,
    "models",
    "job_roles.json"
)


def load_role_dataset():

    if not os.path.exists(ROLE_DATASET_PATH):

        raise FileNotFoundError(
            "job_roles.json missing"
        )

    with open(ROLE_DATASET_PATH, "r") as f:

        return json.load(f)


ROLE_DATASET = load_role_dataset()


# ==================================================
# DETECT SKILL GAPS
# ==================================================

def detect_skill_gap(
    user_skills,
    target_role
):

    if target_role not in ROLE_DATASET:

        return {

            "error": "Unknown target role"

        }


    required_skills = ROLE_DATASET[target_role].get(
        "required_skills",
        []
    )


    missing_skills = list(

        set(required_skills)

        -

        set(user_skills)

    )


    coverage_score = round(

        1 - (len(missing_skills) /

        len(required_skills)),

        4

    )


    return {

        "target_role": target_role,

        "required_skills": required_skills,

        "missing_skills": missing_skills,

        "coverage_score": coverage_score

    }


# ==================================================
# DOMAIN GAP ANALYSIS ENGINE
# ==================================================

def detect_domain_gap(
    domain_distribution,
    target_role
):

    role_lower = target_role.lower()

    weak_domains = []


    for domain, score in domain_distribution.items():

        if domain not in role_lower and score < 0.20:

            weak_domains.append(domain)


    return weak_domains


# ==================================================
# LEARNING PRIORITY ENGINE
# ==================================================

def generate_learning_plan(
    missing_skills
):

    learning_plan = []

    for skill in missing_skills:

        learning_plan.append(

            f"Learn {skill} to improve eligibility"

        )


    return learning_plan


# ==================================================
# SKILL GAP SEVERITY CLASSIFIER
# ==================================================

def classify_gap_severity(
    coverage_score
):

    if coverage_score >= 0.80:

        return "Low Gap"

    elif coverage_score >= 0.50:

        return "Moderate Gap"

    else:

        return "High Gap"


# ==================================================
# MASTER SKILL GAP PIPELINE
# ==================================================

def analyze_skill_gap(
    detected_skills,
    domain_distribution,
    target_role
):

    gap_data = detect_skill_gap(

        detected_skills,
        target_role
    )


    if "error" in gap_data:

        return gap_data


    weak_domains = detect_domain_gap(

        domain_distribution,
        target_role
    )


    learning_plan = generate_learning_plan(

        gap_data["missing_skills"]
    )


    severity = classify_gap_severity(

        gap_data["coverage_score"]
    )


    return {

        "target_role": target_role,

        "missing_skills": gap_data["missing_skills"],

        "coverage_score": gap_data["coverage_score"],

        "gap_severity": severity,

        "weak_domains": weak_domains,

        "learning_plan": learning_plan

    }