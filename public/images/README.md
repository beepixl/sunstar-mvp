# Logo Setup for Invoice PDFs and Emails

## How to Add Your Logo

Place your logo files in this directory:

### For PDF Invoices:
- **File**: `logo.png`
- **Path**: `/public/images/logo.png`
- **Recommended Size**: 200px wide x 80px tall (max)
- **Format**: PNG with transparent background (preferred)

### For Email Invoices (optional):
- **File**: `logo-white.png` 
- **Path**: `/public/images/logo-white.png`
- **Note**: White version for black email header background
- **Alternative**: If only `logo.png` exists, it will be auto-inverted to white

## Logo Requirements:
- **Format**: PNG (preferred) or JPG
- **Max Width**: 200px
- **Max Height**: 80px
- **Background**: Transparent (for PNG)
- **Colors**: Works best with black/white or grayscale design

## Current Setup:
- If no logo is found, the company name "SUNSTAR LOGISTICS" will be displayed
- Logo is automatically centered in the header
- Logo scales proportionally if larger than max dimensions

---

Simply drop your `logo.png` file in this directory and it will automatically appear on all generated invoices!

