"""
==========================================================
ResumeIQ-X Semantic Capability Estimator
Cognition Layer Capability Vector Engine v2
AFIS-X Compatible Production Version
==========================================================

Purpose:

Transforms resume signals into capability-space vector
usable for:

• recruiter intelligence
• trajectory modeling
• talent clustering
• hiring confidence estimation
• capability indexing
• AFIS-X cognition expansion
"""

from typing import Dict, Any
import math


# ==================================================
# DOMAIN → CAPABILITY MAP
# ==================================================

DOMAIN_CAPABILITY_MAP = {

    "web_development": "engineering",
    "software_engineering": "engineering",
    "backend_engineering": "engineering",
    "frontend_engineering": "engineering",

    "data_science": "analytics",
    "statistics": "analytics",

    "artificial_intelligence": "research",
    "machine_learning": "research",

}


# ==================================================
# SKILL COMPLEXITY WEIGHTS
# ==================================================

SKILL_COMPLEXITY_WEIGHTS = {

    "python": 0.95,
    "machine_learning": 1.0,
    "deep_learning": 1.0,

    "sql": 0.75,
    "pandas": 0.85,
    "numpy": 0.85,
    "statistics": 0.95,

    "java": 0.80,
    "javascript": 0.65,

    "html": 0.45,
    "css": 0.45

}


DEFAULT_SKILL_WEIGHT = 0.5


# ==================================================
# SAFE NORMALIZER
# ==================================================

def normalize(value: float) -> float:

    if value <= 0:
        return 0.0

    if value >= 1:
        return 1.0

    return round(value, 4)


# ==================================================
# SAFE LOG SCALE
# ==================================================

def safe_log_scale(value: int) -> float:

    if value <= 0:
        return 0

    return math.log(value + 1) / 3


# ==================================================
# DOMAIN SIGNAL ENGINE
# ==================================================

def compute_domain_signals(
    domain_distribution: Dict[str, float]
) -> Dict[str, float]:

    engineering = 0
    analytics = 0
    research = 0

    if not domain_distribution:
        return {
            "engineering": 0,
            "analytics": 0,
            "research": 0
        }

    for domain, weight in domain_distribution.items():

        capability = DOMAIN_CAPABILITY_MAP.get(domain)

        if capability == "engineering":
            engineering += weight

        elif capability == "analytics":
            analytics += weight

        elif capability == "research":
            research += weight

    return {
        "engineering": engineering,
        "analytics": analytics,
        "research": research
    }


# ==================================================
# SKILL SIGNAL ENGINE
# ==================================================

def compute_skill_signals(
    detected_skills: Dict[str, Any]
) -> Dict[str, float]:

    engineering = 0
    analytics = 0

    if not detected_skills:
        return {
            "engineering": 0,
            "analytics": 0
        }

    skills = detected_skills.get("skills", [])

    for skill in skills:

        weight = SKILL_COMPLEXITY_WEIGHTS.get(
            skill,
            DEFAULT_SKILL_WEIGHT
        )

        engineering += weight * 0.18
        analytics += weight * 0.22

    return {
        "engineering": engineering,
        "analytics": analytics
    }


# ==================================================
# SEMANTIC ROLE SIGNAL ENGINE
# ==================================================

def compute_semantic_role_signals(
    semantic_alignment: Dict[str, Any]
) -> Dict[str, float]:

    if not semantic_alignment:
        return {
            "research": 0,
            "product": 0
        }

    semantic_strength = semantic_alignment.get(
        "predicted_role_score",
        0
    )

    research_signal = semantic_strength * 0.5
    product_signal = semantic_strength * 0.35

    return {
        "research": research_signal,
        "product": product_signal
    }


# ==================================================
# LEARNING VELOCITY ENGINE
# ==================================================

def estimate_learning_velocity(
    detected_skills: Dict[str, Any]
) -> float:

    if not detected_skills:
        return 0

    skill_count = len(
        detected_skills.get("skills", [])
    )

    return normalize(
        safe_log_scale(skill_count)
    )


# ==================================================
# MAIN CAPABILITY VECTOR ENGINE
# ==================================================

def compute_capability_vector(
    detected_skills: Dict[str, Any],
    domain_distribution: Dict[str, float],
    semantic_alignment: Dict[str, Any]
) -> Dict[str, float]:


    domain_signals = compute_domain_signals(
        domain_distribution
    )

    skill_signals = compute_skill_signals(
        detected_skills
    )

    semantic_signals = compute_semantic_role_signals(
        semantic_alignment
    )

    learning_velocity = estimate_learning_velocity(
        detected_skills
    )


    engineering_score = (
        domain_signals["engineering"]
        + skill_signals["engineering"]
    )


    analytics_score = (
        domain_signals["analytics"]
        + skill_signals["analytics"]
    )


    research_score = (
        domain_signals["research"]
        + semantic_signals["research"]
    )


    product_score = (
        semantic_signals["product"]
        + 0.25
    )


    capability_vector = {

        "engineering_capability":
            normalize(engineering_score),

        "analytics_capability":
            normalize(analytics_score),

        "research_capability":
            normalize(research_score),

        "product_capability":
            normalize(product_score),

        "learning_velocity":
            learning_velocity

    }


    return capability_vector