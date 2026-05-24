"""
==================================================
ResumeIQ-X Skill Graph Expansion Engine
Dynamic Semantic Skill Discovery Layer
MIT / PhD Grade Cognitive Module
==================================================

Purpose:
--------
Infers new skills not explicitly present in skill_library.json
using embedding similarity + domain proximity reasoning.

Capabilities:
-------------
✔ Detect unseen technologies
✔ Map emerging tools into known domains
✔ Infer semantic skill relationships
✔ Expand candidate capability graph
✔ Enable adaptive intelligence runtime learning
"""


import os
import json
import numpy as np

from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity


# ==================================================
# LOAD EMBEDDING MODEL
# ==================================================

MODEL_NAME = "sentence-transformers/all-MiniLM-L6-v2"

embedding_model = SentenceTransformer(MODEL_NAME)


# ==================================================
# LOAD SKILL LIBRARY
# ==================================================

BASE_DIR = os.path.dirname(os.path.dirname(__file__))

SKILL_LIBRARY_PATH = os.path.join(
    BASE_DIR,
    "models",
    "skill_library.json"
)


def load_skill_library():

    if not os.path.exists(SKILL_LIBRARY_PATH):
        return {}

    with open(SKILL_LIBRARY_PATH, "r") as f:
        return json.load(f)


SKILL_LIBRARY = load_skill_library()

KNOWN_SKILLS = list(SKILL_LIBRARY.keys())


# ==================================================
# PRECOMPUTE KNOWN SKILL EMBEDDINGS
# ==================================================

KNOWN_SKILL_EMBEDDINGS = embedding_model.encode(
    KNOWN_SKILLS
)


# ==================================================
# TEXT TOKENIZATION HELPER
# ==================================================

def extract_candidate_terms(cleaned_text):

    """
    Extract potential skill-like tokens from resume text.
    Lightweight heuristic tokenizer.
    """

    tokens = cleaned_text.lower().split()

    filtered_tokens = [

        token.strip(".,()[]{}:;")

        for token in tokens

        if len(token) > 3
    ]

    return list(set(filtered_tokens))


# ==================================================
# SEMANTIC MATCH ENGINE
# ==================================================

def compute_similarity(term_embedding):

    similarities = cosine_similarity(
        [term_embedding],
        KNOWN_SKILL_EMBEDDINGS
    )[0]

    best_index = np.argmax(similarities)

    best_skill = KNOWN_SKILLS[best_index]

    best_score = similarities[best_index]

    return best_skill, float(best_score)


# ==================================================
# DOMAIN INFERENCE ENGINE
# ==================================================

def infer_domain(skill):

    """
    Infers domain from known skill graph.
    """

    return SKILL_LIBRARY.get(
        skill,
        {}
    ).get("domain", "unknown")


# ==================================================
# MAIN EXPANSION ENGINE
# ==================================================

def expand_skill_graph(cleaned_text,
                       detected_skills,
                       similarity_threshold=0.62):

    """
    Core runtime skill graph expansion engine.
    """

    candidate_terms = extract_candidate_terms(
        cleaned_text
    )

    existing_skill_set = set(
        detected_skills.get("skills", [])
    )

    inferred_skills = []

    for term in candidate_terms:

        if term in existing_skill_set:
            continue

        try:

            term_embedding = embedding_model.encode(
                term
            )

            best_skill, score = compute_similarity(
                term_embedding
            )

            if score >= similarity_threshold:

                inferred_skills.append({

                    "term": term,
                    "mapped_skill": best_skill,
                    "confidence": round(score, 4),
                    "domain": infer_domain(best_skill)

                })

        except Exception:
            continue


    # Remove duplicates

    unique_results = {

        item["mapped_skill"]: item

        for item in inferred_skills

    }.values()


    return {

        "expanded_skills": list(unique_results),

        "expansion_count": len(unique_results),

        "semantic_expansion_enabled": True

    }


# ==================================================
# GRAPH AUGMENTATION ENGINE
# ==================================================

def merge_expanded_skills(detected_skills,
                         expansion_report):

    """
    Merges inferred skills into skill graph.
    """

    expanded = expansion_report.get(
        "expanded_skills",
        []
    )

    base_skills = set(
        detected_skills.get("skills", [])
    )

    for item in expanded:

        base_skills.add(
            item["mapped_skill"]
        )

    return {

        **detected_skills,

        "skills": list(base_skills),

        "semantic_expansion": expansion_report

    }


# ==================================================
# PUBLIC PIPELINE INTERFACE
# ==================================================

def run_skill_graph_expansion(cleaned_text,
                             detected_skills):

    """
    Pipeline-safe wrapper.
    """

    expansion_report = expand_skill_graph(

        cleaned_text,
        detected_skills

    )

    augmented_skill_graph = merge_expanded_skills(

        detected_skills,
        expansion_report

    )

    return augmented_skill_graph