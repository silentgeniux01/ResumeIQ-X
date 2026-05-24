"""
==================================================
ResumeIQ-X Skill Extraction Engine v5
Enterprise Cognitive Ontology Skill Intelligence Layer

Capabilities:

• ontology-driven skill detection
• alias matching engine
• regex token boundary detection
• phrase-aware skill recognition
• contextual frequency weighting
• domain strength estimation
• maturity inference engine
• knowledge-graph expansion
• semantic inference ready
• transformer upgrade compatible

==================================================
"""

import json
import os
import re
from collections import defaultdict


# ==================================================
# LOAD SKILL ONTOLOGY
# ==================================================

BASE_DIR = os.path.dirname(__file__)

SKILL_LIBRARY_PATH = os.path.join(
    BASE_DIR,
    "models",
    "skill_library.json"
)


def load_skill_library():

    if not os.path.exists(SKILL_LIBRARY_PATH):

        raise FileNotFoundError(
            "skill_library.json missing"
        )

    with open(
        SKILL_LIBRARY_PATH,
        "r",
        encoding="utf-8"
    ) as file:

        return json.load(file)


SKILL_LIBRARY = load_skill_library()


# ==================================================
# TEXT NORMALIZATION ENGINE
# ==================================================

def normalize_text(text):

    text = text.lower()

    text = re.sub(
        r"[^a-z0-9\s]",
        " ",
        text
    )

    text = re.sub(
        r"\s+",
        " ",
        text
    )

    return text.strip()


# ==================================================
# REGEX COMPILATION ENGINE
# (performance booster)
# ==================================================

def compile_skill_patterns():

    pattern_lookup = {}

    for domain, domain_data in SKILL_LIBRARY["domains"].items():

        for skill, metadata in domain_data["skills"].items():

            variants = [skill] + metadata.get(
                "aliases",
                []
            )

            compiled_patterns = []

            for variant in variants:

                compiled_patterns.append(

                    re.compile(

                        rf"\b{re.escape(variant)}\b"

                    )

                )

            pattern_lookup[skill] = (

                domain,
                metadata,
                compiled_patterns
            )

    return pattern_lookup


SKILL_PATTERNS = compile_skill_patterns()


# ==================================================
# CORE SKILL EXTRACTION ENGINE
# ==================================================

def extract_skills(resume_text):

    resume_text = normalize_text(resume_text)

    detected_skills = set()

    skill_frequency = defaultdict(int)

    skill_maturity_map = {}

    domain_distribution = defaultdict(float)


    """
    TOKEN MATCH ENGINE
    """

    for skill, (

        domain,
        metadata,
        patterns

    ) in SKILL_PATTERNS.items():

        for pattern in patterns:

            matches = pattern.findall(resume_text)

            if matches:

                detected_skills.add(skill)

                frequency = len(matches)

                skill_frequency[skill] += frequency

                domain_distribution[domain] += frequency

                skill_maturity_map[skill] = metadata.get(
                    "level",
                    "core"
                )

                break


    """
    NORMALIZE DOMAIN DISTRIBUTION
    """

    total = sum(domain_distribution.values())

    if total > 0:

        domain_distribution = {

            k: round(v / total, 4)

            for k, v in domain_distribution.items()

        }


    return {

        "skills":

            list(detected_skills),

        "skill_frequency":

            dict(skill_frequency),

        "domain_distribution":

            dict(domain_distribution),

        "skill_maturity":

            skill_maturity_map

    }


# ==================================================
# KNOWLEDGE GRAPH EXPANSION ENGINE
# ==================================================

def expand_related_skills(skill_list):

    expanded_skills = set(skill_list)

    for domain_data in SKILL_LIBRARY["domains"].values():

        for skill, metadata in domain_data["skills"].items():

            if skill in skill_list:

                expanded_skills.update(

                    metadata.get(

                        "related_skills",

                        []

                    )

                )

    return list(expanded_skills)


# ==================================================
# DOMAIN STRENGTH ENGINE
# ==================================================

def compute_domain_strength(domain_distribution):

    if not domain_distribution:

        return {}

    return {

        domain:

        round(score * 100, 2)

        for domain, score in domain_distribution.items()

    }


# ==================================================
# SKILL GRAPH SUMMARY ENGINE
# ==================================================

def generate_skill_graph_summary(extraction):

    domain_distribution = extraction.get(
        "domain_distribution",
        {}
    )

    primary_domain = None

    if domain_distribution:

        primary_domain = max(

            domain_distribution,

            key=domain_distribution.get

        )


    return {

        "total_skills_detected":

            len(extraction.get(

                "skills",

                []

            )),

        "primary_domain":

            primary_domain,

        "skill_maturity_map":

            extraction.get(

                "skill_maturity",

                {}

            )

    }


# ==================================================
# MASTER PIPELINE ENTRYPOINT
# ==================================================

def run_skill_extraction_pipeline(resume_text):

    extraction = extract_skills(resume_text)

    expanded = expand_related_skills(

        extraction["skills"]

    )

    domain_strength = compute_domain_strength(

        extraction["domain_distribution"]

    )

    summary = generate_skill_graph_summary(

        extraction

    )


    return {

        "detected_skills":

            extraction["skills"],

        "expanded_skill_graph":

            expanded,

        "domain_distribution":

            domain_strength,

        "skill_frequency":

            extraction["skill_frequency"],

        "skill_maturity":

            extraction["skill_maturity"],

        "skill_graph_summary":

            summary

    }