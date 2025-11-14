<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "PREVENTIVE MAINTENANCE PLAN");

$items = $_POST['items'] ?? [];

class PDF extends TemplatePDF {
    function Header() {
        parent::Header();

        $this->SetFont('Arial','',10);
        $this->Cell(30, 6, 'Office/College', 1, 0, 'C');
        $this->Cell(110, 6, ' ', 1, 0, 'C');
        $this->Cell(10, 6, 'FY:', 1, 0, 'C');
        $this->Cell(40, 6, ' ', 1, 1, 'C');

        $this->SetFont('Arial','B',9);
        $this->Cell(40,7,'Item',1,0,'C');
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        foreach ($months as $m) {
            $this->Cell(12.5,7,$m,1,0,'C');
        }
        $this->Ln();
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->setTitleText('PREVENTIVE MAINTENANCE PLAN');
$pdf->AddPage();

// --- Table Content ---
$pdf->SetFont('Arial','',9);
$rowHeight = 6.5;
$maxRows   = 15;
$rowsPrinted = 0;
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

foreach ($items as $item) {
    $pdf->Cell(40, $rowHeight, $item['description'], 1, 0);
    foreach ($months as $m) {
        $mark = ($item['schedule'] == 'M') ? 'M'
              : (($item['schedule'] == 'Q' && in_array($m,['Mar','Jun','Sep','Dec'])) ? 'Q'
              : (($item['schedule'] == 'SA' && in_array($m,['Jun','Dec'])) ? 'SA' : ''));
        $pdf->Cell(12.5, $rowHeight, $mark, 1, 0, 'C');
    }
    $pdf->Ln();
    $rowsPrinted++;
}

while ($rowsPrinted < $maxRows) {
    $pdf->Cell(40, $rowHeight, '', 1, 0);
    foreach ($months as $m) {
        $pdf->Cell(12.5, $rowHeight, '', 1, 0, 'C');
    }
    $pdf->Ln();
    $rowsPrinted++;
}

$pdf->SetFont('Arial','I',9);
$pdf->Cell(0,6,'Legend: M = Monthly, Q = Quarterly, SA = Semi-Annually',1,1,'C');

// --- Signatories Section ---
$pdf->SetFont('Arial','',8);
$pageWidth   = $pdf->GetPageWidth() - 20;
$colWidth    = $pageWidth / 2;
$blockHeight = 38; 
$marginLeft  = 10;
$yStart      = $pdf->GetY() + 2;

// --- Row 1: Prepared / Reviewed ---
$pdf->Rect($marginLeft, $yStart, $colWidth, $blockHeight);
$pdf->Rect($marginLeft + $colWidth, $yStart, $colWidth, $blockHeight);

// Prepared By (Left)
$pdf->SetXY($marginLeft + 2, $yStart + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Prepared By:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + 2);
$pdf->MultiCell($colWidth - 4, 4,
"____________________
Assistant Director, General Services/Head, GSO
ICT Services Staff/
Head, Project and Facility Management/
Health Services Staff/
Laboratory Technician

Date Signed: ________________", 0, 'C');

// Reviewed By (Right)
$pdf->SetXY($marginLeft + $colWidth + 2, $yStart + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Reviewed By:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + $colWidth + 2);
$pdf->MultiCell($colWidth - 4, 4,
"____________________
Director, Administration Services
Director, ICT Services/Head, ICT Services
Vice Chancellor for Administration and Finance
Head, Health Services/
Dean/Head, Academic Affairs

Date Signed: ________________", 0, 'C');

// --- Row 2: Approved / Remarks ---
$yStart2 = $yStart + $blockHeight + 2;
$pdf->Rect($marginLeft, $yStart2, $colWidth, $blockHeight);
$pdf->Rect($marginLeft + $colWidth, $yStart2, $colWidth, $blockHeight);

// Approved By
$pdf->SetXY($marginLeft + 2, $yStart2 + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Approved By:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + 2);
$pdf->MultiCell($colWidth - 4, 4,
"____________________
Vice President for Administration and Finance/
Vice President for Development and External Affairs/
Vice Chancellor for Development and External Affairs/
Chancellor/
Vice Chancellor for Academic Affairs

Date Signed: ________________", 0, 'C');

// Remarks
$pdf->SetXY($marginLeft + $colWidth + 2, $yStart2 + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Remarks:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + $colWidth + 2);
$pdf->MultiCell($colWidth - 4, 4, "______________________________________________________________", 0, 'L');

$pdf->Output('I','Preventive_Maintenance_Plan.pdf');
?>
