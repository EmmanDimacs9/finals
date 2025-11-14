<?php
// Shared PDF template for consistent header/footer across all system-generated PDFs
// Usage:
//   require_once __DIR__ . '/pdf_template.php';
//   $pdf = new TemplatePDF('P','mm','A4');
//   $pdf->setTitleText('YOUR FORM TITLE');
//   $pdf->AddPage();

require_once __DIR__ . '/fpdf/fpdf.php';

class TemplatePDF extends FPDF {
    private $titleText = '';
    private $headerImagePath;
    private $footerImagePath;

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);
        // Allow dropping exact header/footer PNGs exported from the DOCX template
        // Provide two fallbacks so integrators can place assets in either location
        $this->headerImagePath = $this->firstExisting([
            __DIR__ . '/../assets/template/header.png',
            __DIR__ . '/../assets/template/Header.png',
            __DIR__ . '/../header.png',
        ]);
        $this->footerImagePath = $this->firstExisting([
            __DIR__ . '/../assets/template/footer.png',
            __DIR__ . '/../assets/template/Footer.png',
            __DIR__ . '/../footer.png',
        ]);
        $this->SetAutoPageBreak(true, 20);
        $this->AliasNbPages();
    }

    public function setTitleText($title) {
        $this->titleText = (string)$title;
    }

    public function Header() {
        // If a header image exists (from DOCX), place it full width for pixel-perfect match
        if ($this->headerImagePath && file_exists($this->headerImagePath)) {
            // Preserve aspect ratio by setting width to page width minus margins
            $leftMargin = 10;
            $rightMargin = 10;
            $usableWidth = $this->GetPageWidth() - $leftMargin - $rightMargin;
            // Determine image height to avoid overlap
            $imgHeight = 0;
            $imgInfo = @getimagesize($this->headerImagePath);
            if ($imgInfo && isset($imgInfo[0]) && $imgInfo[0] > 0) {
                $imgHeight = $usableWidth * ($imgInfo[1] / $imgInfo[0]);
            } else {
                // Fallback height if metadata is unavailable
                $imgHeight = 28;
            }
            $this->Image($this->headerImagePath, $leftMargin, 10, $usableWidth);
            // Push cursor below rendered image plus a little spacing
            $this->SetY(10 + $imgHeight + 4);
        }

        // Optional centered title bar to match template placement
        if ($this->titleText !== '') {
            $this->SetFont('Arial', 'B', 13);
            $this->Cell(0, 12, $this->titleText, 1, 1, 'C');
        }
    }

    public function Footer() {
        $this->SetY(-22);

        // If a footer image exists (from DOCX), draw it first
        if ($this->footerImagePath && file_exists($this->footerImagePath)) {
            $leftMargin = 10;
            $rightMargin = 10;
            $usableWidth = $this->GetPageWidth() - $leftMargin - $rightMargin;
            // Place slightly above bottom margin so it is fully visible
            $this->Image($this->footerImagePath, $leftMargin, $this->GetPageHeight() - 22, $usableWidth);
        }

        // Page numbering over the footer as per common template patterns
        $this->SetY(-12);
        $this->SetFont('Arial','',8);
        $this->Cell(0, 8, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }

    private function firstExisting(array $paths) {
        foreach ($paths as $p) {
            if (file_exists($p)) return $p;
        }
        return null;
    }
}

?>

