"""
==================================================
ResumeIQ-X Decision Explainability Engine
Human-Readable AI Reasoning Generator
MIT / PhD Grade Cognitive Module
==================================================

Purpose:
--------
Transforms pipeline signals into recruiter-style
natural reasoning explanations.

Capabilities:
-------------
✔ Explains role prediction logic
✔ Explains confidence score drivers
✔ Explains capability strengths
✔ Explains trajectory predictions
✔ Generates improvement guidance
✔ Produces structured reasoning summary
"""


# ==================================================
# ROLE PREDICTION EXPLAINER
# ==================================================

def explain_role_prediction(
        semantic_alignment,
        capability_vector):

    predicted_role = semantic_alignment.get(
        "predicted_role"
    )

    score = semantic_alignment.get(
        "predicted_role_score", 0
    )

    explanation = []

    if score > 0.65:

        explanation.append(
            "Strong semantic similarity with target role responsibilities"
        )

    elif score > 0.40:

        explanation.append(
            "Moderate alignment with expected role skill patterns"
        )

    else:

        explanation.append(
            "Weak alignment with role-specific competency signals"
        )


    if capability_vector.get("analytics_capability", 0) > 0.6:

        explanation.append(
            "High analytical reasoning capability supports role suitability"
        )


    if capability_vector.get("engineering_capability", 0) > 0.6:

        explanation.append(
            "Strong engineering capability strengthens implementation readiness"
        )


    return {

        "predicted_role": predicted_role,
        "role_alignment_score": score,
        "role_prediction_reasoning": explanation
    }


# ==================================================
# CONFIDENCE EXPLAINER
# ==================================================

def explain_confidence(
        confidence_score,
        domain_distribution,
        semantic_alignment):

    reasoning = []

    if confidence_score > 0.75:

        reasoning.append(
            "Prediction confidence high due to strong multi-domain consistency"
        )

    elif confidence_score > 0.45:

        reasoning.append(
            "Moderate prediction confidence supported by partial domain alignment"
        )

    else:

        reasoning.append(
            "Low prediction confidence due to fragmented capability signals"
        )


    if len(domain_distribution) >= 3:

        reasoning.append(
            "Candidate demonstrates cross-domain adaptability"
        )

    alignment_level = semantic_alignment.get(
        "alignment_level",
        ""
    )

    reasoning.append(
        f"Semantic alignment classification: {alignment_level}"
    )


    return {

        "confidence_score": confidence_score,
        "confidence_reasoning": reasoning
    }


# ==================================================
# CAPABILITY EXPLAINER
# ==================================================

def explain_capabilities(capability_vector):

    strengths = []

    weaknesses = []

    for capability, score in capability_vector.items():

        if score >= 0.65:

            strengths.append(capability)

        elif score <= 0.30:

            weaknesses.append(capability)


    return {

        "capability_strengths": strengths,
        "capability_weaknesses": weaknesses
    }


# ==================================================
# TRAJECTORY EXPLAINER
# ==================================================

def explain_trajectory(
        trajectory_prediction):

    explanation = []

    short_role = trajectory_prediction.get(
        "short_term_role"
    )

    mid_role = trajectory_prediction.get(
        "mid_term_role"
    )

    long_role = trajectory_prediction.get(
        "long_term_role"
    )

    growth_velocity = trajectory_prediction.get(
        "career_growth_velocity", 0
    )


    explanation.append(
        f"Short-term trajectory indicates transition toward {short_role}"
    )

    explanation.append(
        f"Mid-term trajectory suggests evolution into {mid_role}"
    )

    explanation.append(
        f"Long-term projection aligns with {long_role}"
    )


    if growth_velocity > 0.65:

        explanation.append(
            "High projected career acceleration potential detected"
        )

    elif growth_velocity < 0.35:

        explanation.append(
            "Growth velocity limited by current skill maturity gaps"
        )


    return {

        "trajectory_reasoning": explanation
    }


# ==================================================
# IMPROVEMENT PRIORITY ENGINE
# ==================================================

def generate_improvement_priorities(
        skill_gap_analysis,
        capability_vector):

    priorities = []

    missing_skills = skill_gap_analysis.get(
        "missing_skills", []
    )


    for skill in missing_skills[:5]:

        priorities.append(
            f"Develop proficiency in {skill}"
        )


    weakest_capability = min(
        capability_vector,
        key=capability_vector.get
    )


    priorities.append(
        f"Strengthen {weakest_capability} capability dimension"
    )


    return {

        "improvement_priorities": priorities
    }


# ==================================================
# MASTER DECISION EXPLAINER
# ==================================================

def generate_decision_explanation(

        semantic_alignment,
        capability_vector,
        trajectory_prediction,
        confidence_score,
        domain_distribution,
        skill_gap_analysis):

    role_explanation = explain_role_prediction(

        semantic_alignment,
        capability_vector

    )

    confidence_explanation = explain_confidence(

        confidence_score,
        domain_distribution,
        semantic_alignment

    )

    capability_explanation = explain_capabilities(

        capability_vector

    )

    trajectory_explanation = explain_trajectory(

        trajectory_prediction

    )

    improvement_plan = generate_improvement_priorities(

        skill_gap_analysis,
        capability_vector

    )


    return {

        "role_prediction_explanation":
        role_explanation,

        "confidence_explanation":
        confidence_explanation,

        "capability_explanation":
        capability_explanation,

        "trajectory_explanation":
        trajectory_explanation,

        "improvement_plan":
        improvement_plan
    }