"""
=====================================================
SKILL GRAPH ENGINE v2
ResumeIQ-X Knowledge Graph Intelligence Layer
=====================================================

Purpose:

Transform detected skills into structured intelligence

Capabilities:

skill hierarchy reasoning
dependency detection
domain classification
learning path generation
skill maturity inference
dependency completeness scoring
domain density modeling

Future Ready:

graph neural networks
ontology reasoning
semantic embeddings fusion
career trajectory prediction
AFIS integration
"""

import json
import os


class SkillGraphEngine:


    """
    ============================================
    INITIALIZATION
    ============================================
    """

    def __init__(self):

        self.skill_library = self.load_skill_library()


    """
    ============================================
    LOAD SKILL KNOWLEDGE GRAPH
    ============================================
    """

    def load_skill_library(self):

        path = os.path.join(

            os.path.dirname(__file__),

            "models",

            "skill_library.json"

        )

        with open(path, "r") as file:

            return json.load(file)


    """
    ============================================
    NORMALIZE SKILL USING ALIASES
    ============================================
    """

    def normalize_skill(self, skill):

        aliases = self.skill_library["aliases"]

        skill = skill.lower()

        for canonical, variations in aliases.items():

            if skill == canonical:

                return canonical

            if skill in variations:

                return canonical

        return skill


    """
    ============================================
    NORMALIZE SKILL LIST
    ============================================
    """

    def normalize_skills(self, skills):

        normalized = []

        for skill in skills:

            normalized.append(

                self.normalize_skill(skill)

            )

        return list(set(normalized))


    """
    ============================================
    DOMAIN CLASSIFICATION ENGINE
    ============================================
    """

    def classify_domains(self, skills):

        domain_map = self.skill_library["domains"]

        detected_domains = {}

        for skill in skills:

            domain = domain_map.get(skill)

            if domain:

                detected_domains.setdefault(

                    domain, []

                ).append(skill)

        return detected_domains


    """
    ============================================
    DOMAIN DENSITY SCORE
    ============================================

    Measures specialization strength
    """

    def compute_domain_density(self, domain_map):

        density_scores = {}

        for domain, skills in domain_map.items():

            density_scores[domain] = round(

                len(skills) * 12,

                2

            )

        return density_scores


    """
    ============================================
    SKILL DEPENDENCY ENGINE
    ============================================
    """

    def detect_missing_dependencies(self, skills):

        dependency_graph = self.skill_library["dependencies"]

        missing_dependencies = []

        for skill in skills:

            required = dependency_graph.get(skill)

            if required:

                for dependency in required:

                    if dependency not in skills:

                        missing_dependencies.append(

                            dependency

                        )

        return list(set(missing_dependencies))


    """
    ============================================
    DEPENDENCY COMPLETENESS SCORE
    ============================================
    """

    def dependency_completeness_score(

        self,

        skills,

        missing_dependencies

    ):

        if len(skills) == 0:

            return 0

        completeness = (

            len(skills)

            /

            (len(skills) + len(missing_dependencies))

        ) * 100

        return round(completeness, 2)


    """
    ============================================
    LEARNING PATH GENERATOR
    ============================================
    """

    def generate_learning_path(

        self,

        missing_dependencies

    ):

        learning_path = []

        for skill in missing_dependencies:

            learning_path.append(

                f"Recommended: Learn {skill}"

            )

        return learning_path


    """
    ============================================
    SKILL MATURITY ESTIMATION
    ============================================

    Uses co-skill presence to estimate depth
    """

    def estimate_skill_maturity(

        self,

        skills

    ):

        maturity_levels = {}

        hierarchy = self.skill_library["hierarchy"]

        for skill in skills:

            parent = hierarchy.get(skill)

            if parent and parent in skills:

                maturity_levels[skill] = "Intermediate"

            elif parent:

                maturity_levels[skill] = "Beginner"

            else:

                maturity_levels[skill] = "Advanced"

        return maturity_levels


    """
    ============================================
    LATENT CAPABILITY DETECTION
    ============================================

    Infers hidden strengths based on clusters
    """

    def infer_latent_capabilities(

        self,

        domain_map

    ):

        inferred = []

        for domain, skills in domain_map.items():

            if len(skills) >= 3:

                inferred.append(

                    f"Strong capability in {domain}"

                )

        return inferred


    """
    ============================================
    MASTER GRAPH REPORT GENERATOR
    ============================================
    """

    def generate_skill_graph_report(

        self,

        detected_skills

    ):

        normalized_skills = self.normalize_skills(

            detected_skills

        )


        domain_map = self.classify_domains(

            normalized_skills

        )


        domain_density = self.compute_domain_density(

            domain_map

        )


        missing_dependencies = (

            self.detect_missing_dependencies(

                normalized_skills

            )

        )


        completeness_score = (

            self.dependency_completeness_score(

                normalized_skills,

                missing_dependencies

            )

        )


        learning_path = self.generate_learning_path(

            missing_dependencies

        )


        maturity_levels = self.estimate_skill_maturity(

            normalized_skills

        )


        latent_capabilities = (

            self.infer_latent_capabilities(

                domain_map

            )

        )


        return {

            "normalized_skills":

            normalized_skills,

            "domains":

            domain_map,

            "domain_density":

            domain_density,

            "missing_dependencies":

            missing_dependencies,

            "dependency_completeness_score":

            completeness_score,

            "learning_path":

            learning_path,

            "skill_maturity":

            maturity_levels,

            "latent_capabilities":

            latent_capabilities

        }