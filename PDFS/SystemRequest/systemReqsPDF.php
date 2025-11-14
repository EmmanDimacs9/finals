<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "SYSTEM REQUEST FORM");

class PDF extends TemplatePDF {
    function DrawCheckbox($label, $checked = false, $w = 0, $h = 6) {
        $x = $this->GetX();
        $y = $this->GetY();
        $boxSize = 3.5;
        $this->Rect($x + 1, $y + ($h - $boxSize) / 2, $boxSize, $boxSize);
        if ($checked) {
            $this->SetDrawColor(0, 0, 0);
            $this->SetLineWidth(0.3);
            $yMid = $y + ($h - $boxSize) / 2;
            $this->Line($x + 1.4, $yMid + 2, $x + 2.1, $yMid + 3);
            $this->Line($x + 2.1, $yMid + 3, $x + 4, $yMid + 1.2);
        }
        $this->SetXY($x + 1 + $boxSize + 2, $y);
        $labelWidth = ($w > 0) ? $w - ($boxSize + 3) : 0;
        $this->Cell($labelWidth, $h, $label, 0, 1, 'L');
    }
}

// --- Collect POST data ---
$office = $_POST['office'] ?? '';
$sysType = $_POST['sysType'] ?? [];
$urgency = $_POST['urgency'] ?? [];
$nameSystem = $_POST['nameSystem'] ?? '';
$descRequest = $_POST['descRequest'] ?? '';
$remarks = $_POST['remarks'] ?? '';

$reqByName = $_POST['reqByName'] ?? '';
$reqByDesignation = $_POST['reqByDesignation'] ?? '';
$reqByDate = $_POST['reqByDate'] ?? '';
$recApprovalName = $_POST['recApprovalName'] ?? '';
$recApprovalDesignation = $_POST['recApprovalDesignation'] ?? '';
$recApprovalDate = $_POST['recApprovalDate'] ?? '';
$approvedByName = $_POST['approvedByName'] ?? '';
$approvedByDesignation = $_POST['approvedByDesignation'] ?? '';
$approvedByDate = $_POST['approvedByDate'] ?? '';

$ictDate = $_POST['ictDate'] ?? '';
$ictAssigned = $_POST['ictAssigned'] ?? '';
$ictTasks = $_POST['ictTasks'] ?? '';
$ictWorkByName = $_POST['ictWorkByName'] ?? '';
$ictWorkByDesignation = $_POST['ictWorkByDesignation'] ?? '';
$ictWorkByDate = $_POST['ictWorkByDate'] ?? '';
$ictConformeName = $_POST['ictConformeName'] ?? '';
$ictConformeDesignation = $_POST['ictConformeDesignation'] ?? '';
$ictConformeDate = $_POST['ictConformeDate'] ?? '';

$pdf = new PDF('P', 'mm', 'A4');
$pdf->setTitleText('SYSTEM REQUEST FORM');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$fullWidth = 190;
$rowHeight = 7;
$leftMargin = 10;

// --- Office / Unit ---
$pdf->SetX($leftMargin);
$cellHeight = 8;
$pdf->Cell(50, $cellHeight, 'Requesting Office/Unit:', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Rect($x, $y, $fullWidth - 50, $cellHeight);
$pdf->SetXY($x + 2, $y + 2);
$pdf->Cell($fullWidth - 54, 4, $office, 0, 1, 'L');
$pdf->SetY($y + $cellHeight);

// --- Type of Request ---
$options = ['Correction of system issue', 'System enhancement', 'New System'];
$cellHeight = count($options) * 6;
$pdf->SetX($leftMargin);
$pdf->Cell(50, $cellHeight, 'Type of Request:', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Rect($x, $y, $fullWidth - 50, $cellHeight);
foreach ($options as $opt) {
    $pdf->SetXY($x + 3, $y + 1);
    $pdf->DrawCheckbox($opt, in_array($opt, $sysType), $fullWidth - 50 - 6, 5);
    $y += 6;
}

// --- Urgency ---
$options = [
    'Immediate attention required',
    'Handle in normal priority',
    'Defer until new system is developed'
];
$cellHeight = count($options) * 6;
$pdf->SetX($leftMargin);
$pdf->Cell(50, $cellHeight, 'Urgency:', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Rect($x, $y, $fullWidth - 50, $cellHeight);
foreach ($options as $opt) {
    $pdf->SetXY($x + 3, $y + 1);
    $pdf->DrawCheckbox($opt, in_array($opt, $urgency), $fullWidth - 50 - 6, 5);
    $y += 6;
}

// --- Name of System (aligned fix) ---
$pdf->SetX($leftMargin);
$cellHeight = 8; // uniform height
$pdf->Cell(50, $cellHeight, ' Proposed System:', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Rect($x, $y, $fullWidth - 50, $cellHeight);
$pdf->SetXY($x + 2, $y + 2);
$pdf->Cell($fullWidth - 54, 4, $nameSystem, 0, 1, 'L');
$pdf->SetY($y + $cellHeight);

// --- Description ---
$pdf->SetX($leftMargin);
$pdf->Cell(50, $rowHeight, 'Description of Request:', 1, 0, 'L');
$pdf->MultiCell($fullWidth - 50, $rowHeight, $descRequest, 1, 'L');

// --- Remarks ---
$pdf->SetX($leftMargin);
$cellHeight = 8;
$pdf->Cell(50, $cellHeight, 'Remarks:', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Rect($x, $y, $fullWidth - 50, $cellHeight);
$pdf->SetXY($x + 2, $y + 2);
$pdf->Cell($fullWidth - 54, 4, $remarks, 0, 1, 'L');
$pdf->SetY($y + $cellHeight);

// --- Signature Section ---
$colWidth = ($pdf->GetPageWidth() - 20) / 3;
$yStart = $pdf->GetY();
$blockHeight = 38;
$pdf->SetFont('Arial', '', 9);

// Requested by
$pdf->Rect($leftMargin, $yStart, $colWidth, $blockHeight);
$pdf->SetXY($leftMargin, $yStart + 3);
$pdf->MultiCell($colWidth, 5, "Requested by:\n$reqByName\n$reqByDesignation\nDate: $reqByDate", 0, 'C');

// Recommending Approval
$pdf->Rect($leftMargin + $colWidth, $yStart, $colWidth, $blockHeight);
$pdf->SetXY($leftMargin + $colWidth, $yStart + 3);
$pdf->MultiCell($colWidth, 5, "Recommending Approval:\n$recApprovalName\n$recApprovalDesignation\nDate: $recApprovalDate", 0, 'C');

// Approved by
$pdf->Rect($leftMargin + 2 * $colWidth, $yStart, $colWidth, $blockHeight);
$pdf->SetXY($leftMargin + 2 * $colWidth, $yStart + 3);
$pdf->MultiCell($colWidth, 5, "Approved by:\n$approvedByName\n$approvedByDesignation\nDate: $approvedByDate", 0, 'C');

$pdf->SetY($yStart + $blockHeight + 3);

// --- ICT Section ---
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetX($leftMargin);
$pdf->Cell($fullWidth, 7, '--- To be completed by ICT Services ---', 1, 1, 'C');
$pdf->SetFont('Arial', '', 9);

$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Date:', 1, 0, 'L');
$pdf->Cell(160, 6, $ictDate, 1, 1, 'L');

$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Assigned to:', 1, 0, 'L');
$pdf->Cell(160, 6, $ictAssigned, 1, 1, 'L');

$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Tasks:', 1, 0, 'L');
$pdf->MultiCell(160, 6, $ictTasks, 1, 'L');

$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Work Done by:', 1, 0, 'L');
$pdf->MultiCell(160, 6, "$ictWorkByName ($ictWorkByDesignation) - Date: $ictWorkByDate", 1, 'L');

$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Confirmed by:', 1, 0, 'L');
$pdf->MultiCell(160, 6, "$ictConformeName ($ictConformeDesignation) - Date: $ictConformeDate", 1, 'L');

// --- Footer ---
$pdf->Ln(2);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetX($leftMargin);
$pdf->MultiCell(0, 4, "Note: Attach SRS and flowchart if new system is requested.");
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 9);
$pdf->SetX($leftMargin);
$pdf->Cell(0, 6, 'Tracking Number: _____________', 0, 1, 'L');

$pdf->Output('I', 'System_Request_Form.pdf');
?>
