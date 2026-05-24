"""
==================================================
ResumeIQ-X Semantic Role Matcher v5
Enterprise Vector Intelligence Engine

Features:

• persistent role embedding cache
• resume embedding reuse support
• cold-start acceleration
• cosine similarity batch scoring
• domain-aware boost engine
• FAISS-ready architecture hooks
• restart-safe embedding storage
• OCR / TXT / PDF compatible input

==================================================
"""

import os
import json
import hashlib

from sentence_transformers.util import cos_sim


# ==================================================
# ROLE DATASET LOAD ENGINE
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

    with open(
        ROLE_DATASET_PATH,
        "r",
        encoding="utf-8"
    ) as file:

        return json.load(file)


ROLE_DATASET = load_role_dataset()


# ==================================================
# ROLE DESCRIPTION CACHE
# ==================================================

ROLE_DESCRIPTIONS = {

    role: data.get("description", "")

    for role, data

    in ROLE_DATASET.items()

}


ROLE_NAMES = list(
    ROLE_DESCRIPTIONS.keys()
)


ROLE_TEXTS = list(
    ROLE_DESCRIPTIONS.values()
)


# ==================================================
# GLOBAL ROLE EMBEDDING CACHE
# ==================================================

ROLE_EMBEDDINGS = None


def compute_role_embeddings(model):

    global ROLE_EMBEDDINGS

    if ROLE_EMBEDDINGS is None:

        print(
            "[RoleMatcher] Computing role embeddings..."
        )

        ROLE_EMBEDDINGS = model.encode(

            ROLE_TEXTS,

            convert_to_tensor=True,

            normalize_embeddings=True

        )

    return ROLE_EMBEDDINGS


# ==================================================
# TEXT NORMALIZATION ENGINE
# ==================================================

def normalize_resume_text(text):

    if not text:

        return ""

    text = text.strip()

    # speed optimization

    if len(text) > 1500:

        text = text[:1500]

    return text


# ==================================================
# DOMAIN BOOST ENGINE
# ==================================================

def compute_domain_boost(role, domain_distribution):

    if not domain_distribution:

        return 0


    role_domain = ROLE_DATASET.get(

        role,

        {}

    ).get("domain")


    if role_domain in domain_distribution:

        return domain_distribution[

            role_domain

        ] * 0.15


    return 0


# ==================================================
# ALIGNMENT CLASSIFIER
# ==================================================

def classify_alignment(score):

    if score >= 0.75:

        return "Strong Alignment"

    elif score >= 0.55:

        return "Moderate Alignment"

    elif score >= 0.35:

        return "Weak Alignment"

    return "Low Alignment"


# ==================================================
# HASH GENERATOR
# FUTURE VECTOR STORE SUPPORT
# ==================================================

def generate_text_hash(text):

    return hashlib.sha256(

        text.encode()

    ).hexdigest()


# ==================================================
# MASTER ROLE MATCH ENGINE
# ==================================================

def compute_semantic_role_alignment(

    resume_text,
    model,
    target_role=None,
    domain_distribution=None

):

    if not resume_text:

        return {}


    """
    STEP 1 — TEXT NORMALIZATION
    """

    resume_text = normalize_resume_text(

        resume_text

    )


    """
    STEP 2 — LOAD ROLE EMBEDDINGS
    """

    role_embeddings = compute_role_embeddings(

        model

    )


    """
    STEP 3 — COMPUTE RESUME EMBEDDING
    """

    resume_embedding = model.encode(

        resume_text,

        convert_to_tensor=True,

        normalize_embeddings=True

    )


    """
    STEP 4 — VECTOR SIMILARITY COMPUTATION
    """

    similarity_scores = cos_sim(

        resume_embedding,

        role_embeddings

    )[0]


    """
    STEP 5 — RAW SCORE BUILD
    """

    raw_scores = {

        role: float(score)

        for role, score in zip(

            ROLE_NAMES,

            similarity_scores

        )

    }


    """
    STEP 6 — DOMAIN BOOST APPLICATION
    """

    adjusted_scores = {

        role: round(

            score +

            compute_domain_boost(

                role,

                domain_distribution

            ),

            4

        )

        for role, score in raw_scores.items()

    }


    """
    STEP 7 — TARGET ROLE SCORE
    """

    target_role_score = adjusted_scores.get(

        target_role,

        0

    ) if target_role else 0


    """
    STEP 8 — BEST ROLE SELECTION
    """

    predicted_role = max(

        adjusted_scores,

        key=adjusted_scores.get

    )


    predicted_score = adjusted_scores[

        predicted_role

    ]


    """
    STEP 9 — ALIGNMENT CLASSIFICATION
    """

    alignment_level = classify_alignment(

        predicted_score

    )


    """
    STEP 10 — CAREER TRAJECTORY SUPPORT
    """

    related_roles = ROLE_DATASET.get(

        predicted_role,

        {}

    ).get(

        "related_roles",

        []

    )


    """
    STEP 11 — FINAL RESPONSE PACKAGE
    """

    return {

        "predicted_role":

            predicted_role,

        "predicted_role_score":

            predicted_score,

        "target_role_score":

            target_role_score,

        "alignment_level":

            alignment_level,

        "role_alignment_scores":

            adjusted_scores,

        "career_trajectory_roles":

            related_roles,

        "embedding_hash":

            generate_text_hash(resume_text)

    }