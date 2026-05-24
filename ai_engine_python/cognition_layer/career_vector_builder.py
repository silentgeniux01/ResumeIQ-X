"""
==========================================================
ResumeIQ-X Career Vector Builder
Dynamic Career Direction Intelligence Engine

MIT-grade production cognition module
AFIS-X compatible architecture component

Purpose:

Builds structured career-direction vector from:

• detected skills
• domain distribution
• semantic role alignment
• capability vector
• latent skill inference

Used for:

• trajectory prediction
• recruiter intelligence
• career positioning modeling
• readiness forecasting
• long-term role evolution simulation

==========================================================
"""

from typing import Dict, Any
import math


"""
==========================================================
DOMAIN → CAREER AXIS MAP
==========================================================
"""

DOMAIN_CAREER_AXES = {

    "web_development": "engineering",

    "software_engineering": "engineering",

    "backend_engineering": "engineering",

    "frontend_engineering": "engineering",

    "data_science": "analytics",

    "statistics": "analytics",

    "artificial_intelligence": "research",

    "machine_learning": "research"

}


"""
==========================================================
ROLE → CAREER AXIS MAP
==========================================================
"""

ROLE_DIRECTION_MAP = {

    "Data Scientist": "analytics",

    "ML Engineer": "research",

    "AI Engineer": "research",

    "Backend Developer": "engineering",

    "Frontend Developer": "engineering",

    "Full Stack Developer": "engineering",

    "Research Engineer": "research",

    "Data Analyst": "analytics"

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
DOMAIN SIGNAL ENGINE
==========================================================
"""

def compute_domain_direction(

    domain_distribution: Dict[str, float]

) -> Dict[str, float]:

    engineering = 0.0
    analytics = 0.0
    research = 0.0

    for domain, weight in domain_distribution.items():

        axis = DOMAIN_CAREER_AXES.get(domain)

        if axis == "engineering":

            engineering += weight

        elif axis == "analytics":

            analytics += weight

        elif axis == "research":

            research += weight

    return {

        "engineering": engineering,

        "analytics": analytics,

        "research": research

    }


"""
==========================================================
ROLE ALIGNMENT SIGNAL ENGINE
==========================================================
"""

def compute_role_direction(

    semantic_alignment: Dict[str, Any]

) -> Dict[str, float]:

    engineering = 0.0
    analytics = 0.0
    research = 0.0

    predicted_role = semantic_alignment.get(

        "predicted_role"

    )

    role_strength = semantic_alignment.get(

        "predicted_role_score",

        0.0

    )

    axis = ROLE_DIRECTION_MAP.get(predicted_role)

    if axis == "engineering":

        engineering += role_strength

    elif axis == "analytics":

        analytics += role_strength

    elif axis == "research":

        research += role_strength

    return {

        "engineering": engineering,

        "analytics": analytics,

        "research": research

    }


"""
==========================================================
LATENT SIGNAL ENGINE
==========================================================
"""

def compute_latent_direction(

    latent_skill_report: Dict[str, Any]

) -> float:

    latent_count = latent_skill_report.get(

        "latent_skill_count",

        0

    )

    return math.log(latent_count + 1) / 5


"""
==========================================================
CAPABILITY VECTOR SIGNAL ENGINE
==========================================================
"""

def compute_capability_direction(

    capability_vector: Dict[str, float]

) -> Dict[str, float]:

    return {

        "engineering":

        capability_vector.get(

            "engineering_capability",

            0

        ),

        "analytics":

        capability_vector.get(

            "analytics_capability",

            0

        ),

        "research":

        capability_vector.get(

            "research_capability",

            0

        )

    }


"""
==========================================================
CAREER VECTOR BUILDER CORE ENGINE
==========================================================
"""

def build_career_direction_vector(

    detected_skills: Dict[str, Any],

    domain_distribution: Dict[str, float],

    semantic_alignment: Dict[str, Any],

    capability_vector: Dict[str, float],

    latent_skill_report: Dict[str, Any]

) -> Dict[str, float]:


    """
    DOMAIN SIGNAL
    """

    domain_signal = compute_domain_direction(

        domain_distribution

    )


    """
    ROLE SIGNAL
    """

    role_signal = compute_role_direction(

        semantic_alignment

    )


    """
    CAPABILITY SIGNAL
    """

    capability_signal = compute_capability_direction(

        capability_vector

    )


    """
    LATENT SIGNAL
    """

    latent_signal = compute_latent_direction(

        latent_skill_report

    )


    """
    AGGREGATE VECTOR BUILDING
    """

    engineering_direction = (

        domain_signal["engineering"]

        + role_signal["engineering"]

        + capability_signal["engineering"]

        + latent_signal

    )


    analytics_direction = (

        domain_signal["analytics"]

        + role_signal["analytics"]

        + capability_signal["analytics"]

        + latent_signal

    )


    research_direction = (

        domain_signal["research"]

        + role_signal["research"]

        + capability_signal["research"]

        + latent_signal

    )


    leadership_direction = (

        capability_vector.get(

            "product_capability",

            0

        ) * 0.6

    )


    """
    NORMALIZATION
    """

    engineering_direction = normalize(

        engineering_direction

    )

    analytics_direction = normalize(

        analytics_direction

    )

    research_direction = normalize(

        research_direction

    )

    leadership_direction = normalize(

        leadership_direction

    )


    """
    CAREER ENTROPY ESTIMATION

    Measures specialization vs exploration profile
    """

    entropy = (

        engineering_direction

        + analytics_direction

        + research_direction

    ) / 3


    entropy = normalize(entropy)


    """
    FINAL VECTOR OUTPUT
    """

    return {

        "engineering_direction":

        engineering_direction,

        "analytics_direction":

        analytics_direction,

        "research_direction":

        research_direction,

        "leadership_direction":

        leadership_direction,

        "career_entropy":

        entropy

    }