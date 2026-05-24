"""
==========================================================
ResumeIQ-X Latent Skill Inference Engine
ULTRA FAST PARALLEL COGNITION VERSION
Embedding-ready architecture compatible
==========================================================
"""

from typing import Dict, List, Any


# ==========================================================
# LATENT SKILL GRAPH
# ==========================================================

LATENT_SKILL_GRAPH = {

    "machine_learning": [
        "numpy",
        "pandas",
        "statistics",
        "feature_engineering",
        "model_validation"
    ],

    "deep_learning": [
        "linear_algebra",
        "optimization",
        "tensor_operations"
    ],

    "sql": [
        "data_cleaning",
        "data_filtering",
        "query_optimization"
    ],

    "python": [
        "debugging",
        "scripting",
        "automation"
    ],

    "javascript": [
        "dom_manipulation",
        "async_programming"
    ],

    "backend_development": [
        "api_design",
        "database_schema_design"
    ]
}


# ==========================================================
# ROLE LATENT SIGNALS
# ==========================================================

ROLE_LATENT_SKILLS = {

    "Data Scientist": [
        "statistics",
        "pandas",
        "numpy",
        "feature_engineering"
    ],

    "ML Engineer": [
        "model_deployment",
        "pipeline_design"
    ],

    "Backend Developer": [
        "api_design",
        "database_indexing"
    ],

    "Full Stack Developer": [
        "state_management",
        "frontend_backend_integration"
    ]
}


# ==========================================================
# DOMAIN LATENT SIGNALS
# ==========================================================

DOMAIN_LATENT_SKILLS = {

    "data_science": [
        "statistics",
        "data_cleaning"
    ],

    "artificial_intelligence": [
        "optimization",
        "linear_algebra"
    ],

    "web_development": [
        "responsive_design",
        "browser_rendering"
    ]
}


# ==========================================================
# FAST UNIQUE MERGE
# ==========================================================

def merge_unique(base, new):

    return list(set(base) | set(new))


# ==========================================================
# GRAPH PROPAGATION
# ==========================================================

def propagate_skill_graph(explicit_skills):

    inferred = []

    for skill in explicit_skills:

        inferred.extend(

            LATENT_SKILL_GRAPH.get(

                skill,

                []

            )

        )

    return inferred


# ==========================================================
# ROLE SIGNAL PROPAGATION
# ==========================================================

def propagate_role_signals(semantic_alignment):

    predicted_role = semantic_alignment.get(

        "predicted_role",

        None

    )

    return ROLE_LATENT_SKILLS.get(

        predicted_role,

        []

    )


# ==========================================================
# DOMAIN SIGNAL PROPAGATION
# ==========================================================

def propagate_domain_signals(

    domain_distribution,

    threshold=0.15

):

    inferred = []

    for domain, weight in domain_distribution.items():

        if weight >= threshold:

            inferred.extend(

                DOMAIN_LATENT_SKILLS.get(

                    domain,

                    []

                )

            )

    return inferred


# ==========================================================
# MAIN ENGINE (MODEL READY VERSION)
# ==========================================================

def infer_latent_skills(

    model,  # shared embedding model (future ready)

    detected_skills: Dict[str, Any],

    domain_distribution: Dict[str, float],

    semantic_alignment: Dict[str, Any]

) -> Dict[str, Any]:


    explicit_skills = detected_skills.get(

        "skills",

        []

    )


    # ------------------------------------------------------
    # GRAPH PROPAGATION
    # ------------------------------------------------------

    graph_inferred = propagate_skill_graph(

        explicit_skills

    )


    # ------------------------------------------------------
    # ROLE PROPAGATION
    # ------------------------------------------------------

    role_inferred = propagate_role_signals(

        semantic_alignment

    )


    # ------------------------------------------------------
    # DOMAIN PROPAGATION
    # ------------------------------------------------------

    domain_inferred = propagate_domain_signals(

        domain_distribution

    )


    # ------------------------------------------------------
    # MERGE SIGNALS
    # ------------------------------------------------------

    inferred_skills = merge_unique(

        graph_inferred,

        role_inferred

    )


    inferred_skills = merge_unique(

        inferred_skills,

        domain_inferred

    )


    # ------------------------------------------------------
    # REMOVE EXPLICIT SKILLS
    # ------------------------------------------------------

    explicit_set = set(explicit_skills)

    inferred_skills = [

        skill

        for skill in inferred_skills

        if skill not in explicit_set

    ]


    # ------------------------------------------------------
    # CONFIDENCE SCORE
    # ------------------------------------------------------

    confidence_score = min(

        0.25 + len(inferred_skills) * 0.05,

        0.85

    )


    # ------------------------------------------------------
    # FINAL OUTPUT
    # ------------------------------------------------------

    return {

        "latent_skills":

        sorted(inferred_skills),

        "latent_skill_count":

        len(inferred_skills),

        "latent_inference_confidence":

        round(confidence_score, 4)

    }