"""
==================================================
ResumeIQ-X Candidate Memory Index Engine
Multi-Candidate Intelligence Retrieval Layer
MIT / PhD Grade Cognitive Module
==================================================

Purpose
-------
Stores candidate embeddings and retrieves similar
profiles for benchmarking and clustering intelligence.

Capabilities
------------
✔ candidate embedding storage
✔ similarity retrieval
✔ nearest-neighbour ranking
✔ cluster-aware benchmarking
✔ trajectory comparison memory
✔ recruiter-grade candidate referencing
"""


import os
import json
import numpy as np

from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity


# ==================================================
# EMBEDDING MODEL
# ==================================================

MODEL_NAME = "sentence-transformers/all-MiniLM-L6-v2"

embedding_model = SentenceTransformer(MODEL_NAME)


# ==================================================
# MEMORY STORAGE PATH
# ==================================================

BASE_DIR = os.path.dirname(os.path.dirname(__file__))

MEMORY_PATH = os.path.join(
    BASE_DIR,
    "memory_store",
    "candidate_memory.json"
)


# Ensure directory exists

os.makedirs(
    os.path.dirname(MEMORY_PATH),
    exist_ok=True
)


# ==================================================
# MEMORY LOAD / SAVE UTILITIES
# ==================================================

def load_memory():

    if not os.path.exists(MEMORY_PATH):

        return []

    try:

        with open(MEMORY_PATH, "r") as f:

            return json.load(f)

    except Exception:

        return []


def save_memory(memory):

    with open(MEMORY_PATH, "w") as f:

        json.dump(memory, f, indent=2)


# ==================================================
# VECTOR CREATION ENGINE
# ==================================================

def create_candidate_vector(

        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction):

    """
    Creates structured embedding input.
    """

    vector_text = f"""

    engineering capability {capability_vector.get('engineering_capability',0)}
    analytics capability {capability_vector.get('analytics_capability',0)}
    research capability {capability_vector.get('research_capability',0)}
    product capability {capability_vector.get('product_capability',0)}
    leadership capability {capability_vector.get('leadership_capability',0)}

    confidence score {confidence_score}

    readiness score {readiness_score}

    trajectory {trajectory_prediction.get('long_term_role','unknown')}

    """

    return embedding_model.encode(vector_text)


# ==================================================
# STORE CANDIDATE PROFILE
# ==================================================

def store_candidate_profile(

        candidate_id,
        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction):

    memory = load_memory()

    vector = create_candidate_vector(

        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction

    )

    memory.append({

        "candidate_id": candidate_id,

        "embedding": vector.tolist(),

        "trajectory":

        trajectory_prediction.get(
            "long_term_role",
            "unknown"
        )

    })

    save_memory(memory)


# ==================================================
# SIMILARITY SEARCH ENGINE
# ==================================================

def retrieve_similar_candidates(

        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction,
        top_k=5):

    memory = load_memory()

    if not memory:

        return []

    query_vector = create_candidate_vector(

        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction

    )

    embeddings = np.array([

        entry["embedding"]

        for entry in memory

    ])

    similarities = cosine_similarity(

        [query_vector],
        embeddings

    )[0]

    ranked_indices = np.argsort(

        similarities

    )[::-1][:top_k]

    similar_profiles = []

    for idx in ranked_indices:

        candidate = memory[idx]

        similar_profiles.append({

            "candidate_id":

            candidate["candidate_id"],

            "similarity_score":

            float(similarities[idx]),

            "trajectory":

            candidate.get(
                "trajectory",
                "unknown"
            )

        })

    return similar_profiles


# ==================================================
# TALENT CLUSTER INSIGHT ENGINE
# ==================================================

def generate_cluster_insight(similar_profiles):

    if not similar_profiles:

        return "No historical cluster available"

    avg_similarity = sum(

        profile["similarity_score"]

        for profile in similar_profiles

    ) / len(similar_profiles)

    if avg_similarity > 0.80:

        return "Candidate strongly matches existing high-confidence cluster"

    if avg_similarity > 0.60:

        return "Candidate moderately aligned with known growth trajectories"

    return "Candidate represents emerging unique capability profile"


# ==================================================
# MASTER MEMORY INTERFACE
# ==================================================

def candidate_memory_pipeline(

        candidate_id,
        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction):

    """
    Pipeline-safe wrapper.
    """

    store_candidate_profile(

        candidate_id,
        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction

    )

    similar_profiles = retrieve_similar_candidates(

        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction

    )

    cluster_insight = generate_cluster_insight(

        similar_profiles

    )

    return {

        "similar_candidates":

        similar_profiles,

        "cluster_insight":

        cluster_insight,

        "memory_index_enabled":

        True

    }