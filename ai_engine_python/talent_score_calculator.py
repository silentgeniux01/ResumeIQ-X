"""
====================================================
TALENT SCORE CALCULATOR ENGINE
ResumeIQ-X Intelligence Layer v9
====================================================

Purpose:

Compute unified Talent Intelligence Score (TIS)

Combines:

resume strength
semantic compatibility
confidence estimation
skill dependency completeness
career readiness

Output:

single recruiter-grade ranking metric

Used By:

career ranking dashboards
candidate comparison engines
similarity clustering weighting
future AFIS intelligence integration

Future Ready:

multi-candidate ranking
team composition optimization
portfolio-level human capital allocation
"""



class TalentScoreCalculator:


    """
    ========================================
    INITIALIZE ENGINE
    ========================================
    """

    def __init__(

        self,

        resume_weight=0.25,

        compatibility_weight=0.30,

        confidence_weight=0.20,

        dependency_weight=0.15,

        readiness_weight=0.10

    ):

        self.resume_weight = resume_weight

        self.compatibility_weight = compatibility_weight

        self.confidence_weight = confidence_weight

        self.dependency_weight = dependency_weight

        self.readiness_weight = readiness_weight


    """
    ========================================
    DEPENDENCY COMPLETENESS SCORE
    ========================================

    Measures:

    how structurally complete candidate skill stack is
    """


    def dependency_score(

        self,

        missing_dependencies

    ):

        total_dependencies = len(missing_dependencies)

        if total_dependencies == 0:

            return 100

        deduction = total_dependencies * 8

        score = max(100 - deduction, 40)

        return score


    """
    ========================================
    NORMALIZE SCORE
    ========================================
    """


    def normalize_score(

        self,

        value,

        max_value=100

    ):

        if value > max_value:

            return max_value

        if value < 0:

            return 0

        return value


    """
    ========================================
    MAIN TALENT SCORE ENGINE
    ========================================
    """


    def compute_talent_score(

        self,

        resume_score,

        compatibility_score,

        confidence_score,

        missing_dependencies,

        career_readiness_score

    ):

        dependency_score = self.dependency_score(

            missing_dependencies

        )


        talent_score = (

            resume_score * self.resume_weight +

            compatibility_score * self.compatibility_weight +

            confidence_score * self.confidence_weight +

            dependency_score * self.dependency_weight +

            career_readiness_score * self.readiness_weight

        )


        talent_score = round(

            self.normalize_score(

                talent_score

            ),

            2

        )


        return talent_score


    """
    ========================================
    TALENT CATEGORY CLASSIFIER
    ========================================
    """


    def classify_candidate(

        self,

        talent_score

    ):

        if talent_score >= 85:

            return "Elite Candidate"

        elif talent_score >= 70:

            return "High Potential Candidate"

        elif talent_score >= 55:

            return "Competitive Candidate"

        else:

            return "Needs Skill Strengthening"


    """
    ========================================
    FULL TALENT REPORT GENERATOR
    ========================================
    """


    def generate_talent_report(

        self,

        resume_score,

        compatibility_score,

        confidence_score,

        missing_dependencies,

        career_readiness_score

    ):

        talent_score = self.compute_talent_score(

            resume_score,

            compatibility_score,

            confidence_score,

            missing_dependencies,

            career_readiness_score

        )


        category = self.classify_candidate(

            talent_score

        )


        return {

            "talent_score": talent_score,

            "candidate_category": category

        }
def compute_talent_score(
    resume_strength_score,
    confidence_score,
    career_readiness_score,
    semantic_alignment_score
):

    talent_score = (
        resume_strength_score * 0.30 +
        confidence_score * 100 * 0.25 +
        career_readiness_score * 0.25 +
        semantic_alignment_score * 100 * 0.20
    )

    return {
        "talent_score": round(talent_score, 2),
        "talent_category": "Computed"
    }