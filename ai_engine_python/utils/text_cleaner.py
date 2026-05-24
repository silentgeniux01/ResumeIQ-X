"""
==================================================
ResumeIQ-X Advanced Text Cleaner
Semantic-Safe Resume Preprocessing Engine
Production + Research Grade NLP Preparation Layer
==================================================
"""

import re
import unicodedata


# ==================================================
# OPTIONAL STOPWORD LIST
# (Minimal ATS-safe filtering)
# ==================================================

STOPWORDS = {

    "the", "and", "is", "in", "to", "of",
    "a", "for", "on", "with", "as",
    "by", "at", "an"
}


# ==================================================
# UNICODE NORMALIZATION
# Fix smart quotes / encoding issues
# ==================================================

def normalize_unicode(text):

    return unicodedata.normalize("NFKD", text)


# ==================================================
# REMOVE EMAIL ADDRESSES
# ==================================================

def remove_emails(text):

    return re.sub(

        r"\S+@\S+",

        " ",

        text

    )


# ==================================================
# REMOVE URLS
# ==================================================

def remove_urls(text):

    return re.sub(

        r"http\S+|www\S+",

        " ",

        text

    )


# ==================================================
# REMOVE PHONE NUMBERS
# ==================================================

def remove_phone_numbers(text):

    return re.sub(

        r"\+?\d[\d\s\-]{7,15}",

        " ",

        text

    )


# ==================================================
# REMOVE SPECIAL CHARACTERS
# KEEP ATS-RELEVANT SYMBOLS
# ==================================================

def remove_special_characters(text):

    return re.sub(

        r"[^a-zA-Z0-9\s\+\#\.]",

        " ",

        text

    )


# ==================================================
# OCR NOISE CORRECTION
# Handles common scanned resume artifacts
# ==================================================

def correct_ocr_noise(text):

    text = text.replace("|", " ")

    text = text.replace("—", "-")

    text = text.replace("•", " ")

    text = text.replace("·", " ")

    return text


# ==================================================
# MULTIPLE SPACE NORMALIZATION
# ==================================================

def normalize_spaces(text):

    return re.sub(

        r"\s+",

        " ",

        text

    ).strip()


# ==================================================
# OPTIONAL STOPWORD FILTER
# Safe for embeddings + ATS parsing
# ==================================================

def remove_stopwords(text):

    tokens = text.split()

    filtered = [

        word

        for word in tokens

        if word not in STOPWORDS

    ]

    return " ".join(filtered)


# ==================================================
# MASTER CLEANING PIPELINE
# ==================================================

def clean_text(text, remove_stopword_flag=False):

    if not text:

        return ""


    # Unicode normalization

    text = normalize_unicode(text)


    # lowercase normalization

    text = text.lower()


    # remove emails

    text = remove_emails(text)


    # remove urls

    text = remove_urls(text)


    # remove phone numbers

    text = remove_phone_numbers(text)


    # fix OCR artifacts

    text = correct_ocr_noise(text)


    # remove special characters

    text = remove_special_characters(text)


    # normalize spacing

    text = normalize_spaces(text)


    # optional stopword removal

    if remove_stopword_flag:

        text = remove_stopwords(text)


    return text


# ==================================================
# TOKEN COUNT ESTIMATOR
# Useful for embedding calibration
# ==================================================

def estimate_token_count(text):

    if not text:

        return 0

    return len(text.split())


# ==================================================
# TEXT QUALITY CHECKER
# Detects unusable resumes
# ==================================================

def validate_text_quality(text):

    if not text:

        return False

    if len(text.split()) < 25:

        return False

    return True