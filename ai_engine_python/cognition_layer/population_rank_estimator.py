"""
==================================================
ResumeIQ-X Population Rank Estimator
Candidate Positioning Intelligence Engine
MIT / PhD Grade Cognitive Module
==================================================

Purpose
-------
Places candidate inside estimated talent population
distribution using capability signals + confidence +
trajectory + readiness metrics.

Capabilities
------------
✔ percentile estimation
✔ readiness classification
✔ promotion track detection
✔ leadership track inference
✔ cluster positioning logic
✔ recruiter-style benchmarking signals
"""


# ==================================================
# HELPER: NORMALIZE SCORE
# ==================================================

def normalize(score, max_value=100):

    if score is None:
        return 0

    return min(score / max_value, 1.0)


# ==================================================
# CAPABILITY INDEX ENGINE
# ==================================================

def compute_capability_index(capability_vector):

    if not capability_vector:
        return 0

    values = list(capability_vector.values())

    return sum(values) / len(values)


# ==================================================
# CAREER MOMENTUM ENGINE
# ==================================================

def compute_career_momentum(

        confidence_score,
        readiness_score,
        trajectory_prediction):

    growth_velocity = trajectory_prediction.get(
        "career_growth_velocity", 0
    )

    readiness_norm = normalize(readiness_score)

    return (

        confidence_score * 0.4
        + readiness_norm * 0.3
        + growth_velocity * 0.3

    )


# ==================================================
# POPULATION PERCENTILE ESTIMATOR
# ==================================================

def estimate_percentile(

        capability_index,
        confidence_score,
        readiness_score):

    readiness_norm = normalize(readiness_score)

    composite_score = (

        capability_index * 0.5
        + confidence_score * 0.3
        + readiness_norm * 0.2

    )

    percentile = int(composite_score * 100)

    return percentile


# ==================================================
# TALENT CLUSTER CLASSIFIER
# ==================================================

def classify_talent_cluster(percentile):

    if percentile >= 85:
        return "Elite Talent Cluster"

    elif percentile >= 65:
        return "High Growth Talent Cluster"

    elif percentile >= 45:
        return "Emerging Talent Cluster"

    elif percentile >= 25:
        return "Developing Talent Cluster"

    else:
        return "Early Stage Talent Cluster"


# ==================================================
# PROMOTION TRACK DETECTOR
# ==================================================

def detect_promotion_track(

        trajectory_prediction,
        capability_vector):

    growth_velocity = trajectory_prediction.get(
        "career_growth_velocity", 0
    )

    leadership_score = capability_vector.get(
        "leadership_capability", 0
    )

    if growth_velocity > 0.70 and leadership_score > 0.50:

        return "Fast Promotion Track"

    if growth_velocity > 0.50:

        return "Moderate Promotion Track"

    return "Standard Growth Track"


# ==================================================
# LEADERSHIP TRACK ESTIMATOR
# ==================================================

def estimate_leadership_track(capability_vector):

    leadership_score = capability_vector.get(
        "leadership_capability", 0
    )

    if leadership_score > 0.70:
        return "Strong Leadership Track Candidate"

    if leadership_score > 0.40:
        return "Potential Leadership Track Candidate"

    return "Individual Contributor Track Candidate"


# ==================================================
# POPULATION POSITION SUMMARY ENGINE
# ==================================================

def generate_population_position_summary(

        percentile,
        cluster,
        promotion_track,
        leadership_track):

    return [

        f"Candidate positioned within top {100 - percentile}% of evaluated talent distribution",

        f"Cluster classification: {cluster}",

        f"Promotion trajectory classification: {promotion_track}",

        f"Leadership trajectory classification: {leadership_track}"

    ]


# ==================================================
# MASTER POPULATION RANK ESTIMATOR
# ==================================================

def estimate_population_rank(

        capability_vector,
        confidence_score,
        readiness_score,
        trajectory_prediction):

    capability_index = compute_capability_index(
        capability_vector
    )

    career_momentum = compute_career_momentum(

        confidence_score,
        readiness_score,
        trajectory_prediction

    )

    percentile = estimate_percentile(

        capability_index,
        confidence_score,
        readiness_score

    )

    cluster = classify_talent_cluster(
        percentile
    )

    promotion_track = detect_promotion_track(

        trajectory_prediction,
        capability_vector

    )

    leadership_track = estimate_leadership_track(
        capability_vector
    )

    summary = generate_population_position_summary(

        percentile,
        cluster,
        promotion_track,
        leadership_track

    )

    return {

        "population_percentile":
        percentile,

        "capability_index":
        round(capability_index, 3),

        "career_momentum_score":
        round(career_momentum, 3),

        "talent_cluster":
        cluster,

        "promotion_track":
        promotion_track,

        "leadership_track":
        leadership_track,

        "population_position_summary":
        summary
    }