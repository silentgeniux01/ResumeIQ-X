# PDF Text Extraction Fix

## Problem Identified

The Railway logs show that PDF text extraction is completely broken:

```
Resume text length: 970
Resume text preview: opensource \(anonymous\ D:20260417075607+00'00' \(unspecified\...
```

This is PDF metadata/binary data, NOT actual resume content!

## Root Cause

1. **Python not available on Railway** - The `pdf_reader.py` script can't run
2. **pdftotext not installed** - Command-line tool not available
3. **Fallback extraction fails** - Only extracts PDF metadata, not content

## Solution Options

### Option 1: Use Cloudinary's Text Extraction (RECOMMENDED)
Cloudinary can extract text from PDFs using OCR.

**Pros:**
- Already using Cloudinary for storage
- No additional dependencies
- Works on Railway
- Handles image-based PDFs

**Implementation:**
```php
// When uploading to Cloudinary, request text extraction
$uploadResult = $cloudinary->uploadApi()->upload($filePath, [
    'resource_type' => 'raw',
    'ocr' => 'adv_ocr', // Advanced OCR
]);

// Get extracted text
$textUrl = $uploadResult['info']['ocr']['adv_ocr']['data'][0]['textAnnotations'][0]['description'];
```

### Option 2: Use External PDF API
Use a service like PDF.co or PDFShift API.

**Pros:**
- Reliable text extraction
- Handles complex PDFs

**Cons:**
- Additional API cost
- External dependency

### Option 3: Install Composer PDF Library
Use `smalot/pdfparser` PHP library.

**Pros:**
- Pure PHP solution
- No external dependencies
- Works on Railway

**Cons:**
- Requires Composer
- May not handle all PDF types

## Recommended Fix: Use smalot/pdfparser

This is the best solution for Railway deployment.

### Step 1: Install via Composer

Create `composer.json`:
```json
{
    "require": {
        "smalot/pdfparser": "^2.0"
    }
}
```

Run: `composer install`

### Step 2: Update PDF Extraction

```php
require_once __DIR__ . '/../vendor/autoload.php';
use Smalot\PdfParser\Parser;

function extractPDFText($filePath) {
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();
        return trim($text);
    } catch (Exception $e) {
        error_log("PDF parsing failed: " . $e->getMessage());
        return null;
    }
}
```

### Step 3: Railway Configuration

Add to `nixpacks.toml`:
```toml
[phases.setup]
aptPkgs = ["..."]

[phases.install]
cmds = ["composer install --no-dev --optimize-autoloader"]
```

## Immediate Workaround

For now, ask users to upload:
1. **TXT files** - Plain text resumes
2. **DOCX files** - Word documents (easier to extract)
3. **Image-based resumes** - Use OCR service

## Testing

After implementing the fix:

1. Upload a PDF resume
2. Check Railway logs for:
   ```
   Resume text length: 2543
   Resume text preview: John Doe Software Engineer with 5 years...
   ```
3. Verify analysis shows proper data

---

**Next Steps:**
1. Install `smalot/pdfparser` via Composer
2. Update PDF extraction function
3. Test with real resume PDFs
4. Deploy to Railway
