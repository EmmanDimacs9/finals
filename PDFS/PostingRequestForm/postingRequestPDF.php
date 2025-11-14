<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';

include '../../logger.php';
$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "REQUEST FOR POSTING OF ANNOUNCEMENTS / GREETINGS");

class PDF extends TemplatePDF {

    // ✅ Checkbox with ✓ tick using vector drawing
    function DrawCheckbox($label, $checked = false) {
        $x = $this->GetX();
        $y = $this->GetY();

        // Draw the square box
        $this->Rect($x, $y, 5, 5);

        if ($checked) {
            // Draw a small ✓ inside the box (two line strokes)
            $this->SetDrawColor(0, 0, 0);
            $this->SetLineWidth(0.4);
            $this->Line($x + 1, $y + 2.5, $x + 2.3, $y + 4);   // left slant
            $this->Line($x + 2.3, $y + 4, $x + 4.5, $y + 1.5); // right slant
        }

        // Add label text beside box
        $this->SetXY($x + 7, $y - 1);
        $this->Cell(50, 7, $label, 0, 1, 'L');
    }
}

$pdf = new PDF('P', 'mm', 'Legal');
$pdf->AddPage();

// Title aligned with table below
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetXY(10, $pdf->GetY()); // Start at same X position as table
$pdf->Cell(190, 12, 'REQUEST FOR POSTING OF ANNOUNCEMENTS / GREETINGS', 1, 1, 'C');

// --- College/Office ---
$pdf->SetFont('Arial', '', 9);
$labelWidth = 50;
$contentWidth = 140;
$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell($labelWidth, 7, "College / Office:", 1, 0);
$pdf->Cell($contentWidth, 7, $_POST['college_office'] ?? '', 1, 1);

// --- Purpose ---
$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell($labelWidth, 20, "Purpose:", 1, 0);
$pdf->MultiCell($contentWidth, 20, $_POST['purpose'] ?? '', 1);

// --- Means of Posting ---
$means = $_POST['means'] ?? [];
$labelWidth = 50;
$contentWidth = 140;
$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell($labelWidth, 40, "Means of Posting:", 1, 0);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Cell($contentWidth, 40, '', 1, 1); // container for checkboxes
$pdf->SetXY($x, $y);

$pdf->SetX(60);
$pdf->DrawCheckbox("Bulletin Board", in_array("Bulletin Board", $means));
$pdf->SetX(60);
$pdf->DrawCheckbox("View Board", in_array("View Board", $means));
$pdf->SetX(60);
$pdf->DrawCheckbox("LED Board", in_array("LED Board", $means));
$pdf->SetX(60);
$pdf->DrawCheckbox("Social Media", in_array("Social Media", $means));

$pdf->Ln(2);
$pdf->SetX(60);
$pdf->Cell(0, 5, "Indicate Specific Location / Media Site: " . ($_POST['location'] ?? ''), 0, 1);
$pdf->SetX(60);
$pdf->MultiCell(140, 5, $_POST['media_notes'] ?? '', 0);

// --- Brief Content ---
// Use wider label cell to avoid overlap
$labelWidth = 50;
$contentWidth = 140;
$startY = $pdf->GetY();
$pdf->SetXY(10, $startY); // Ensure proper X position
$pdf->Cell($labelWidth, 40, "Brief Content and Layout:", 1, 0);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Cell($contentWidth, 40, '', 1, 0); // Empty border
$pdf->SetXY($x, $y);
$pdf->MultiCell($contentWidth, 5, $_POST['content'] ?? '', 0);
// Ensure we're at the bottom of the 40mm section
$pdf->SetXY(10, $startY + 40);

// --- Posting Period ---
$pdf->SetXY(10, $pdf->GetY());
// Use a wider temporary label width to fit longer label text
$tempLabelWidth = 70;
$tempContentWidth = 120;
$pdf->Cell($tempLabelWidth, 7, "Posting Period (Maximum 30 days):", 1, 0);
$pdf->Cell($tempContentWidth, 7, $_POST['period'] ?? '', 1, 1);

// --- Requested + Recommended (side by side) ---
$h = 35;
$y = $pdf->GetY();
$startX = 10;
$totalWidth = 190;

// Draw single outer border to avoid double lines
$pdf->Rect($startX, $y, $totalWidth, $h);

// Draw vertical separator line
$pdf->Line($startX + 95, $y, $startX + 95, $y + $h);

// Requested By (left cell)
$pdf->SetXY($startX + 2, $y + 2);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(88, 5, "Requested by:", 0, 2, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->MultiCell(88, 5, ($_POST['requested_by'] ?? 'NAME OF HEAD OF OFFICE/UNIT') . "\n" . ($_POST['requested_designation'] ?? 'Position/Designation'), 0, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(88, 5, "Date Signed: ____________________", 0, 2, 'L');

// Recommended Approval (right cell)
$pdf->SetXY($startX + 95 + 2, $y + 2);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(88, 5, "Recommending Approval:", 0, 2, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->MultiCell(88, 5, ($_POST['recommended_by'] ?? 'Engr. JONNAH R. MELO') . "\n" . ($_POST['recommended_designation'] ?? 'Head, ICT Services'), 0, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(88, 5, "Date Signed: ____________________", 0, 2, 'L');

// Move Y position to the bottom of the first section
$pdf->SetY($y + $h);

// --- Approved + Remarks (side by side) ---
$y = $pdf->GetY();
$startX = 10;
$totalWidth = 190;

// Draw single outer border to avoid double lines
$pdf->Rect($startX, $y, $totalWidth, $h);

// Draw vertical separator line
$pdf->Line($startX + 95, $y, $startX + 95, $y + $h);

// Approved By (left cell)
$pdf->SetXY($startX + 2, $y + 2);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(88, 5, "Approved by:", 0, 2, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->MultiCell(88, 5, ($_POST['approved_by'] ?? 'Atty. ALVIN R. DE SILVA') . "\n" . ($_POST['approved_designation'] ?? 'Chancellor'), 0, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(88, 5, "Date Signed: ____________________", 0, 2, 'L');

// Remarks (right cell)
$pdf->SetXY($startX + 95 + 2, $y + 2);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(88, 5, "Remarks:", 0, 2, 'L');
$pdf->MultiCell(88, 5, ($_POST['remarks'] ?? ''), 0, 'L');

// Move Y position to the bottom of the second section
$pdf->SetY($y + $h);

// --- Note + Tracking ---
$pdf->SetFont('Arial', 'I', 7);
$pdf->MultiCell(210, 50, "Note: It is understood that the posting shall be removed after the approved duration.", 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(190, 7, "Tracking Number: ___________________", 0, 1, 'R');

$pdf->Output('I', "PostingRequestForm.pdf");
?>
