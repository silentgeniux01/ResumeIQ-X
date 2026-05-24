"""
==================================================
ResumeIQ-X ENTERPRISE PDF READER ENGINE v8
Robust Multi-Strategy Resume Text Extraction Layer
Supports:
- PyMuPDF (fast extraction)
- pdfplumber (layout-safe extraction)
- OCR fallback (scanned resumes)
- Image resumes
==================================================
"""

import os


# ==================================================
# ENGINE 1: FAST EXTRACTION (PyMuPDF)
# ==================================================

def read_with_pymupdf(file_path):

    try:

        import fitz

        text = ""

        doc = fitz.open(file_path)

        for page in doc:
            page_text = page.get_text()

            if page_text:
                text += page_text + "\n"

        doc.close()

        return text.strip()

    except Exception as error:

        print("[PDF ENGINE ERROR - PyMuPDF]", error)

        return ""


# ==================================================
# ENGINE 2: STRUCTURED EXTRACTION (pdfplumber)
# ==================================================

def read_with_pdfplumber(file_path):

    try:

        import pdfplumber

        text = ""

        with pdfplumber.open(file_path) as pdf:

            for page in pdf.pages:

                extracted = page.extract_text()

                if extracted:
                    text += extracted + "\n"

        return text.strip()

    except Exception as error:

        print("[PDF ENGINE ERROR - pdfplumber]", error)

        return ""


# ==================================================
# ENGINE 3: OCR FALLBACK (SCANNED PDF SUPPORT)
# ==================================================

def read_with_ocr(file_path):

    try:

        from pdf2image import convert_from_path
        import pytesseract

        images = convert_from_path(file_path)

        text = ""

        for image in images:

            extracted = pytesseract.image_to_string(image)

            if extracted:
                text += extracted + "\n"

        return text.strip()

    except Exception as error:

        print("[PDF ENGINE ERROR - OCR]", error)

        return ""


# ==================================================
# ENGINE 4: IMAGE RESUME SUPPORT (PNG / JPG)
# ==================================================

def read_image_text(file_path):

    try:

        import pytesseract
        from PIL import Image

        image = Image.open(file_path)

        text = pytesseract.image_to_string(image)

        return text.strip()

    except Exception as error:

        print("[IMAGE OCR ERROR]", error)

        return ""


# ==================================================
# TEXT QUALITY VALIDATOR
# ==================================================

def is_valid_text(text):

    if not text:
        return False

    if len(text.strip()) < 40:
        return False

    return True


# ==================================================
# MASTER EXTRACTION CONTROLLER
# ==================================================

def read_pdf(file_path):

    if not os.path.exists(file_path):

        raise FileNotFoundError(
            "Resume file not found: " + file_path
        )


    print("[PDF Reader] Using PyMuPDF engine...")

    text = read_with_pymupdf(file_path)

    if is_valid_text(text):

        print("[PDF Reader] Extracted via PyMuPDF")

        return text


    print("[PDF Reader] Using pdfplumber fallback...")

    text = read_with_pdfplumber(file_path)

    if is_valid_text(text):

        print("[PDF Reader] Extracted via pdfplumber")

        return text


    print("[PDF Reader] Using OCR fallback...")

    text = read_with_ocr(file_path)

    if is_valid_text(text):

        print("[PDF Reader] Extracted via OCR")

        return text


    raise Exception("Resume text extraction failed")


# ==================================================
# COMMAND LINE ENTRY POINT
# Usage: python pdf_reader.py <file_path>
# ==================================================

if __name__ == "__main__":

    import sys

    if len(sys.argv) < 2:
        print("[PDF Reader] Usage: python pdf_reader.py <file_path>", file=sys.stderr)
        sys.exit(1)

    file_path = sys.argv[1]

    ext = os.path.splitext(file_path)[1].lower()

    if ext in ['.png', '.jpg', '.jpeg']:
        text = read_image_text(file_path)
    else:
        text = read_pdf(file_path)

    if text:
        print(text)
    else:
        print("[PDF Reader] No text extracted", file=sys.stderr)
        sys.exit(1)
