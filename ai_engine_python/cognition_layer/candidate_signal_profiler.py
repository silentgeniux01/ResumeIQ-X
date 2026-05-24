"""
==========================================================
ResumeIQ-X Candidate Signal Profiler
Resume Intelligence Density Analyzer

MIT-grade production cognition module
AFIS-X compatible architecture component

Purpose:

Transforms resume evidence signals into structured
candidate maturity indicators used by:

• recruiter intelligence engines
• capability scoring pipelines
• career trajectory modeling
• talent ranking systems
• confidence estimation stabilizers

Outputs:

technical_depth_score
toolchain_complexity_score
domain_focus_score
skill_entropy_score
resume_signal_strength
learning_signal_strength
career_signal_consistency

==========================================================
"""

from typing import Dict, Any
import math


"""
==========================================================
SKILL COMPLEXITY TIERS

Higher-tier tools contribute stronger depth signals.
Extendable knowledge structure.
==========================================================
"""

SKILL_TIERS = {

    "python": 3,
    "java": 3,
    "machine_learning": 4,
    "deep_learning": 5,
    "statistics": 5,
    "numpy": 4,
    "pandas": 4,
    "sql": 3,
    "javascript": 3,
    "html": 1,
    "css": 1

}


"""
==========================================================
SAFE NORMALIZATION
==========================================================
"""

def normalize(value: float, scale: float = 1.0) -> float:

    value = value / scale

    if value <= 0:
        return 0.0

    if value >= 1:
        return 1.0

    return round(value, 4)


"""
==========================================================
TECHNICAL DEPTH ESTIMATOR
==========================================================
"""

def compute_technical_depth(

    detected_skills: Dict[str, Any]

) -> float:

    skills = detected_skills.get("skills", [])

    if not skills:
        return 0.0

    depth_sum = 0

    for skill in skills:

        depth_sum += SKILL_TIERS.get(skill, 2)

    return normalize(

        depth_sum,

        len(skills) * 5

    )


"""
==========================================================
TOOLCHAIN COMPLEXITY ESTIMATOR
==========================================================
"""

def compute_toolchain_complexity(

    detected_skills: Dict[str, Any]

) -> float:

    skills = detected_skills.get("skills", [])

    diversity = len(set(skills))

    return normalize(

        math.log(diversity + 1),

        3

    )


"""
==========================================================
DOMAIN FOCUS ESTIMATOR

Measures specialization strength.
==========================================================
"""

def compute_domain_focus(

    domain_distribution: Dict[str, float]

) -> float:

    if not domain_distribution:

        return 0.0

    strongest_domain = max(

        domain_distribution.values()

    )

    return normalize(

        strongest_domain

    )


"""
==========================================================
SKILL ENTROPY ESTIMATOR

Measures exploration vs specialization.
==========================================================
"""

def compute_skill_entropy(

    domain_distribution: Dict[str, float]

) -> float:

    entropy = 0

    for value in domain_distribution.values():

        if value > 0:

            entropy -= value * math.log(value)

    return normalize(

        entropy,

        1.5

    )


"""
==========================================================
LEARNING SIGNAL STRENGTH

Measures growth readiness potential.
==========================================================
"""

def compute_learning_signal(

    capability_vector: Dict[str, float]

) -> float:

    return normalize(

        capability_vector.get(

            "learning_velocity",

            0

        )

    )


"""
==========================================================
CAREER SIGNAL CONSISTENCY

Measures alignment stability between:

domains + semantic alignment
==========================================================
"""

def compute_signal_consistency(

    semantic_alignment: Dict[str, Any],

    domain_distribution: Dict[str, float]

) -> float:

    role_score = semantic_alignment.get(

        "predicted_role_score",

        0

    )

    domain_focus = compute_domain_focus(

        domain_distribution

    )

    return normalize(

        (role_score + domain_focus) / 2

    )


"""
==========================================================
RESUME SIGNAL STRENGTH AGGREGATOR
==========================================================
"""

def compute_resume_signal_strength(

    technical_depth_score: float,

    toolchain_complexity_score: float,

    domain_focus_score: float

) -> float:

    combined = (

        technical_depth_score * 0.4 +

        toolchain_complexity_score * 0.3 +

        domain_focus_score * 0.3

    )

    return normalize(combined)


"""
==========================================================
MAIN SIGNAL PROFILER ENGINE
==========================================================
"""

def build_candidate_signal_profile(

    detected_skills: Dict[str, Any],

    domain_distribution: Dict[str, float],

    semantic_alignment: Dict[str, Any],

    capability_vector: Dict[str, float]

) -> Dict[str, float]:


    technical_depth_score = compute_technical_depth(

        detected_skills

    )


    toolchain_complexity_score = compute_toolchain_complexity(

        detected_skills

    )


    domain_focus_score = compute_domain_focus(

        domain_distribution

    )


    skill_entropy_score = compute_skill_entropy(

        domain_distribution

    )


    learning_signal_strength = compute_learning_signal(

        capability_vector

    )


    career_signal_consistency = compute_signal_consistency(

        semantic_alignment,

        domain_distribution

    )


    resume_signal_strength = compute_resume_signal_strength(

        technical_depth_score,

        toolchain_complexity_score,

        domain_focus_score

    )


    """
    FINAL PROFILE OUTPUT
    """

    return {

        "technical_depth_score":

        technical_depth_score,

        "toolchain_complexity_score":

        toolchain_complexity_score,

        "domain_focus_score":

        domain_focus_score,

        "skill_entropy_score":

        skill_entropy_score,

        "resume_signal_strength":

        resume_signal_strength,

        "learning_signal_strength":

        learning_signal_strength,

        "career_signal_consistency":

        career_signal_consistency

    }