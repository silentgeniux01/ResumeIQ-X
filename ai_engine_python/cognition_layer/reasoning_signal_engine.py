"""
==========================================================
ResumeIQ-X Reasoning Signal Engine
High-Level Cognitive Interpretation Layer

MIT-grade production cognition module
AFIS-X compatible architecture component

Purpose:

Transforms structured intelligence signals into
human-style reasoning outputs such as:

• hidden_strengths
• risk_flags
• promotion_probability
• career_stability_score
• research_potential_score
• leadership_readiness_score
• decision_confidence_score

Used for:

• recruiter intelligence simulation
• hiring confidence estimation
• promotion modeling
• trajectory stabilization scoring
• candidate maturity interpretation
• AFIS-X reasoning layer expansion

==========================================================
"""

from typing import Dict, Any


"""
==========================================================
SAFE NORMALIZATION
==========================================================
"""

def normalize(value: float) -> float:

    if value <= 0:
        return 0.0

    if value >= 1:
        return 1.0

    return round(value, 4)


"""
==========================================================
HIDDEN STRENGTH DETECTOR
==========================================================
"""

def detect_hidden_strengths(

    capability_vector: Dict[str, float],

    signal_profile: Dict[str, float]

):

    strengths = []

    if capability_vector.get("analytics_capability", 0) > 0.6:

        strengths.append("Strong analytical reasoning ability")

    if capability_vector.get("engineering_capability", 0) > 0.6:

        strengths.append("Robust engineering foundation")

    if capability_vector.get("research_capability", 0) > 0.55:

        strengths.append("Research-oriented problem solving")

    if signal_profile.get("learning_signal_strength", 0) > 0.6:

        strengths.append("High learning adaptability")

    if signal_profile.get("technical_depth_score", 0) > 0.6:

        strengths.append("Deep technical capability signals")

    return strengths


"""
==========================================================
RISK FLAG DETECTOR
==========================================================
"""

def detect_risk_flags(

    signal_profile: Dict[str, float],

    career_direction_vector: Dict[str, float]

):

    risks = []

    if signal_profile.get("technical_depth_score", 0) < 0.35:

        risks.append("Low technical depth signal")

    if signal_profile.get("career_signal_consistency", 0) < 0.4:

        risks.append("Career direction instability risk")

    entropy = career_direction_vector.get(

        "career_entropy",

        0.5

    )

    if entropy < 0.3:

        risks.append("Over-specialization risk")

    if entropy > 0.8:

        risks.append("Lack of specialization focus")

    return risks


"""
==========================================================
PROMOTION PROBABILITY ESTIMATOR
==========================================================
"""

def estimate_promotion_probability(

    capability_vector: Dict[str, float],

    signal_profile: Dict[str, float],

    trajectory_prediction: Dict[str, Any]

):

    velocity = trajectory_prediction.get(

        "career_growth_velocity",

        0

    )

    learning = capability_vector.get(

        "learning_velocity",

        0

    )

    depth = signal_profile.get(

        "technical_depth_score",

        0

    )

    score = (

        velocity * 0.4 +

        learning * 0.3 +

        depth * 0.3

    )

    return normalize(score)


"""
==========================================================
CAREER STABILITY ESTIMATOR
==========================================================
"""

def estimate_career_stability(

    semantic_alignment: Dict[str, Any],

    signal_profile: Dict[str, float]

):

    alignment = semantic_alignment.get(

        "predicted_role_score",

        0

    )

    consistency = signal_profile.get(

        "career_signal_consistency",

        0

    )

    stability = (

        alignment * 0.5 +

        consistency * 0.5

    )

    return normalize(stability)


"""
==========================================================
RESEARCH POTENTIAL ESTIMATOR
==========================================================
"""

def estimate_research_potential(

    capability_vector: Dict[str, float],

    signal_profile: Dict[str, float]

):

    research_capability = capability_vector.get(

        "research_capability",

        0

    )

    entropy = signal_profile.get(

        "skill_entropy_score",

        0

    )

    potential = (

        research_capability * 0.7 +

        entropy * 0.3

    )

    return normalize(potential)


"""
==========================================================
LEADERSHIP READINESS ESTIMATOR
==========================================================
"""

def estimate_leadership_readiness(

    capability_vector: Dict[str, float],

    trajectory_prediction: Dict[str, Any]

):

    product_capability = capability_vector.get(

        "product_capability",

        0

    )

    growth_velocity = trajectory_prediction.get(

        "career_growth_velocity",

        0

    )

    leadership_score = (

        product_capability * 0.6 +

        growth_velocity * 0.4

    )

    return normalize(leadership_score)


"""
==========================================================
DECISION CONFIDENCE ESTIMATOR
==========================================================
"""

def compute_decision_confidence(

    trajectory_prediction: Dict[str, Any],

    signal_profile: Dict[str, float]

):

    trajectory_confidence = trajectory_prediction.get(

        "trajectory_confidence",

        0

    )

    resume_signal_strength = signal_profile.get(

        "resume_signal_strength",

        0

    )

    decision_score = (

        trajectory_confidence * 0.5 +

        resume_signal_strength * 0.5

    )

    return normalize(decision_score)


"""
==========================================================
MAIN REASONING ENGINE
==========================================================
"""

def build_reasoning_signals(

    capability_vector: Dict[str, float],

    signal_profile: Dict[str, float],

    career_direction_vector: Dict[str, float],

    trajectory_prediction: Dict[str, Any],

    semantic_alignment: Dict[str, Any]

):

    hidden_strengths = detect_hidden_strengths(

        capability_vector,

        signal_profile

    )


    risk_flags = detect_risk_flags(

        signal_profile,

        career_direction_vector

    )


    promotion_probability = estimate_promotion_probability(

        capability_vector,

        signal_profile,

        trajectory_prediction

    )


    career_stability_score = estimate_career_stability(

        semantic_alignment,

        signal_profile

    )


    research_potential_score = estimate_research_potential(

        capability_vector,

        signal_profile

    )


    leadership_readiness_score = estimate_leadership_readiness(

        capability_vector,

        trajectory_prediction

    )


    decision_confidence_score = compute_decision_confidence(

        trajectory_prediction,

        signal_profile

    )


    """
    FINAL STRUCTURED OUTPUT
    """

    return {

        "hidden_strengths":

        hidden_strengths,

        "risk_flags":

        risk_flags,

        "promotion_probability":

        promotion_probability,

        "career_stability_score":

        career_stability_score,

        "research_potential_score":

        research_potential_score,

        "leadership_readiness_score":

        leadership_readiness_score,

        "decision_confidence_score":

        decision_confidence_score

    }