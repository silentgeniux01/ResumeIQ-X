"""
==========================================================
ResumeIQ-X Career Trajectory Predictor
Career Evolution Simulation Engine

MIT-grade production cognition module
AFIS-X compatible architecture component

Purpose:

Predicts candidate career evolution trajectory using:

• career_direction_vector
• capability_vector
• candidate_signal_profile
• semantic_role_alignment

Outputs:

short_term_role
mid_term_role
long_term_role
career_growth_velocity
role_transition_probability
trajectory_confidence

Used for:

• recruiter intelligence engines
• role evolution prediction
• promotion readiness estimation
• capability transition modeling
• AFIS-X cognition simulation layer

==========================================================
"""

from typing import Dict, Any


"""
==========================================================
CAREER AXIS ROLE MAP

Defines role hierarchy across capability axes.
Extendable knowledge structure.
==========================================================
"""

CAREER_PATHS = {

    "engineering": [

        "Frontend Developer",

        "Backend Developer",

        "Full Stack Developer",

        "Software Engineer",

        "Senior Software Engineer"

    ],

    "analytics": [

        "Data Analyst",

        "Business Analyst",

        "Data Scientist",

        "Senior Data Scientist"

    ],

    "research": [

        "ML Engineer",

        "AI Engineer",

        "Research Engineer",

        "Applied Scientist"

    ]

}


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
SELECT PRIMARY CAREER AXIS
==========================================================
"""

def detect_primary_axis(

    career_direction_vector: Dict[str, float]

) -> str:

    axes = {

        "engineering":

        career_direction_vector.get(

            "engineering_direction",

            0

        ),

        "analytics":

        career_direction_vector.get(

            "analytics_direction",

            0

        ),

        "research":

        career_direction_vector.get(

            "research_direction",

            0

        )

    }

    return max(

        axes,

        key=axes.get

    )


"""
==========================================================
GROWTH VELOCITY ESTIMATION
==========================================================
"""

def estimate_growth_velocity(

    capability_vector: Dict[str, float],

    signal_profile: Dict[str, float]

) -> float:

    learning_velocity = capability_vector.get(

        "learning_velocity",

        0

    )

    resume_strength = signal_profile.get(

        "resume_signal_strength",

        0

    )

    technical_depth = signal_profile.get(

        "technical_depth_score",

        0

    )

    velocity = (

        learning_velocity * 0.4 +

        resume_strength * 0.3 +

        technical_depth * 0.3

    )

    return normalize(velocity)


"""
==========================================================
ROLE TRANSITION PROBABILITY ESTIMATION
==========================================================
"""

def estimate_transition_probability(

    semantic_alignment: Dict[str, Any],

    signal_profile: Dict[str, float]

) -> float:

    role_score = semantic_alignment.get(

        "predicted_role_score",

        0

    )

    consistency = signal_profile.get(

        "career_signal_consistency",

        0

    )

    probability = (

        role_score * 0.6 +

        consistency * 0.4

    )

    return normalize(probability)


"""
==========================================================
ROLE SELECTION ENGINE
==========================================================
"""

def select_roles_from_axis(

    axis: str,

    growth_velocity: float

):

    roles = CAREER_PATHS.get(axis, [])

    if not roles:

        return (

            "Generalist",

            "Specialist",

            "Advanced Specialist"

        )

    if growth_velocity < 0.3:

        return (

            roles[0],

            roles[1] if len(roles) > 1 else roles[0],

            roles[2] if len(roles) > 2 else roles[-1]

        )

    elif growth_velocity < 0.6:

        return (

            roles[1],

            roles[2] if len(roles) > 2 else roles[-1],

            roles[3] if len(roles) > 3 else roles[-1]

        )

    else:

        return (

            roles[2],

            roles[3] if len(roles) > 3 else roles[-1],

            roles[-1]

        )


"""
==========================================================
TRAJECTORY CONFIDENCE ESTIMATION
==========================================================
"""

def compute_trajectory_confidence(

    growth_velocity: float,

    transition_probability: float

) -> float:

    confidence = (

        growth_velocity * 0.5 +

        transition_probability * 0.5

    )

    return normalize(confidence)


"""
==========================================================
MAIN TRAJECTORY PREDICTION ENGINE
==========================================================
"""

def predict_career_trajectory(

    career_direction_vector: Dict[str, float],

    capability_vector: Dict[str, float],

    signal_profile: Dict[str, float],

    semantic_alignment: Dict[str, Any]

) -> Dict[str, Any]:


    """
    DETECT PRIMARY CAREER AXIS
    """

    primary_axis = detect_primary_axis(

        career_direction_vector

    )


    """
    ESTIMATE GROWTH VELOCITY
    """

    growth_velocity = estimate_growth_velocity(

        capability_vector,

        signal_profile

    )


    """
    ESTIMATE ROLE TRANSITION PROBABILITY
    """

    transition_probability = estimate_transition_probability(

        semantic_alignment,

        signal_profile

    )


    """
    SELECT CAREER ROLES
    """

    short_term_role, mid_term_role, long_term_role = \
select_roles_from_axis(

            primary_axis,

            growth_velocity

        )


    """
    TRAJECTORY CONFIDENCE
    """

    trajectory_confidence = compute_trajectory_confidence(

        growth_velocity,

        transition_probability

    )


    """
    FINAL OUTPUT STRUCTURE
    """

    return {

        "primary_career_axis":

        primary_axis,

        "short_term_role":

        short_term_role,

        "mid_term_role":

        mid_term_role,

        "long_term_role":

        long_term_role,

        "career_growth_velocity":

        growth_velocity,

        "role_transition_probability":

        transition_probability,

        "trajectory_confidence":

        trajectory_confidence

    }