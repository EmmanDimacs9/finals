<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';

include '../../logger.php';
$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "REQUEST FOR POSTING OF ANNOUNCEMENTS / GREETINGS");

class PDF extends TemplatePDF {
    // Keep custom helpers only
    // ✅ Checkbox with drawn checkmark
    function DrawCheckbox($label, $checked = false, $w = 60, $h = 7) {
        $x = $this->GetX();
        $y = $this->GetY();

        // draw checkbox
        $this->Rect($x, $y + 1.5, 4, 4);

        if ($checked) {
            $this->SetDrawColor(0, 0, 0);
            $this->SetLineWidth(0.4);
            $this->Line($x + 0.8, $y + 3.5, $x + 1.8, $y + 5); // left slant
            $this->Line($x + 1.8, $y + 5, $x + 3.6, $y + 2);   // right slant
        }

        // label
        $this->SetXY($x + 6, $y);
        $this->Cell($w - 6, $h, $label, 0, 1, 'L');
    }
}

// --- Collect POST Data ---
$college       = $_POST['college']       ?? '';
$purpose       = $_POST['purpose']       ?? '';
$posting       = $_POST['posting']       ?? [];
$location      = $_POST['location']      ?? '';
$content       = $_POST['content']       ?? '';
$postingPeriod = $_POST['postingPeriod'] ?? '';

// --- Create PDF ---
$pdf = new PDF('P','mm','A4');
$pdf->setTitleText('REQUEST FOR POSTING OF ANNOUNCEMENTS / GREETINGS');
$pdf->AddPage();
$pdf->SetFont('Arial','',11);
$fullWidth = 190;
$rowHeight = 10;

// --- College / Office ---
$pdf->Cell(50, $rowHeight, 'College / Office:', 1, 0);
$pdf->Cell($fullWidth - 50, $rowHeight, $college, 1, 1);

// --- Purpose ---
$pdf->Cell(50, $rowHeight, 'Purpose:', 1, 0);
$pdf->MultiCell($fullWidth - 50, $rowHeight, $purpose, 1);

// --- Means of Posting ---
$options = ['Bulletin Board', 'View Board', 'LED Board', 'Social Media'];
$lineHeight = 8;
$locationHeight = 16; // allocate space for location field
$cellHeight = count($options) * $lineHeight + $locationHeight;

$pdf->Cell(50, $cellHeight, 'Means of Posting:', 1, 0, 'L');

// right border container
$x = $pdf->GetX();
$y = $pdf->GetY();
$w = $fullWidth - 50;
$pdf->Rect($x, $y, $w, $cellHeight);

// checkboxes
$pdf->SetFont('Arial','',10);
$yy = $y + 2;
foreach ($options as $opt) {
    $pdf->SetXY($x + 3, $yy);
    $pdf->DrawCheckbox($opt, in_array($opt, $posting), $w - 5, $lineHeight);
    $yy += $lineHeight;
}

// location text
$pdf->SetXY($x + 3, $yy + 2);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell($w - 5, 6, "Specific Location / Media Site:\n" . $location, 0, 'L');

// move Y to bottom of the whole cell
$pdf->SetY($y + $cellHeight);

// --- Brief Content and Layout ---
$pdf->SetFont('Arial','',11);
$pdf->Cell(50, $rowHeight, 'Brief Content and Layout:', 1, 0);
$pdf->MultiCell($fullWidth - 50, $rowHeight, $content, 1);

// --- Posting Period ---
$pdf->Cell(50, $rowHeight, 'Posting Period:', 1, 0);
$pdf->Cell($fullWidth - 50, $rowHeight, $postingPeriod, 1, 1);

// --- Signature Blocks (3 columns) ---
$colWidth = ($pdf->GetPageWidth() - 20) / 3;
$yStart = $pdf->GetY();
$blockHeight = 50;

// Requested by
$pdf->Rect(10, $yStart, $colWidth, $blockHeight);
$pdf->SetXY(10, $yStart + 5);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell($colWidth, 7, "Requested by:", 0, 'L');
$pdf->SetX(10);
$pdf->MultiCell($colWidth, 7, "\nNAME OF HEAD OF OFFICE/UNIT\nPosition/Designation", 0, 'C');
$pdf->SetX(10);
$pdf->MultiCell($colWidth, 7, "Date Signed: ___________", 0, 'L');

// Recommending Approval
$pdf->Rect(10 + $colWidth, $yStart, $colWidth, $blockHeight);
$pdf->SetXY(10 + $colWidth, $yStart + 5);
$pdf->MultiCell($colWidth, 7, "Recommending Approval:", 0, 'L');
$pdf->SetX(10 + $colWidth);
$pdf->MultiCell($colWidth, 7, "\nEngr. JONNAH R. MELO\nHead, ICT Services", 0, 'C');
$pdf->SetX(10 + $colWidth);
$pdf->MultiCell($colWidth, 7, "Date Signed: ___________", 0, 'L');

// Approved by
$pdf->Rect(10 + 2 * $colWidth, $yStart, $colWidth, $blockHeight);
$pdf->SetXY(10 + 2 * $colWidth, $yStart + 5);
$pdf->MultiCell($colWidth, 7, "Approved by:", 0, 'L');
$pdf->SetX(10 + 2 * $colWidth);
$pdf->MultiCell($colWidth, 7, "\nAtty. ALVIN R. DE SILVA\nChancellor", 0, 'C');
$pdf->SetX(10 + 2 * $colWidth);
$pdf->MultiCell($colWidth, 7, "Date Signed: ___________", 0, 'L');

// Move to below signatures
$pdf->SetY($yStart + $blockHeight);

// --- Remarks ---
$pdf->Cell(30, $rowHeight * 2, 'Remarks:', 1, 0);
$pdf->Cell($fullWidth - 30, $rowHeight * 2, '', 1, 1);
$pdf->Ln(5);

// --- Note ---
$pdf->SetFont('Arial','I',9);
$pdf->MultiCell(0, 6, 'Note: It is understood that the posting shall be removed after the approved duration.');

// --- Tracking Number ---
$pdf->Ln(5);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0, 8, 'Tracking Number: _____________', 0, 1, 'L');



// --- Output ---
$pdf->Output('I', 'Announcement_Request.pdf');
?>
