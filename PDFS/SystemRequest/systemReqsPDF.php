<?php
require('../../includes/fpdf/fpdf.php');

// Get form data
$office = $_POST['office'] ?? '';
$sysType = $_POST['sysType'] ?? [];
$urgency = $_POST['urgency'] ?? [];
$nameSystem = $_POST['nameSystem'] ?? '';
$descRequest = $_POST['descRequest'] ?? '';

// Requested by
$reqByName = $_POST['reqByName'] ?? '';
$reqByDesignation = $_POST['reqByDesignation'] ?? '';
$reqByDate = $_POST['reqByDate'] ?? '';

// Recommending approval
$recApprovalName = $_POST['recApprovalName'] ?? 'Director, ICT Services';
$recApprovalDate = $_POST['recApprovalDate'] ?? '';

// Approved by
$approvedByName = $_POST['approvedByName'] ?? 'Vice President for Development and External Affairs';
$approvedByDate = $_POST['approvedByDate'] ?? '';

// Remarks and ICT sections
$remarks = $_POST['remarks'] ?? '';
$ictDate = $_POST['ictDate'] ?? '';
$ictAssigned = $_POST['ictAssigned'] ?? '';
$ictTasks = $_POST['ictTasks'] ?? '';

// Work done by and conforme
$ictWorkByName = $_POST['ictWorkByName'] ?? '';
$ictWorkByDesignation = $_POST['ictWorkByDesignation'] ?? '';
$ictWorkByDate = $_POST['ictWorkByDate'] ?? '';
$ictConformeName = $_POST['ictConformeName'] ?? '';
$ictConformeDesignation = $_POST['ictConformeDesignation'] ?? '';
$ictConformeDate = $_POST['ictConformeDate'] ?? '';

$reference = 'BatStateU-FO-ICT-03';
$effectivitydate = 'July 31, 2023';
$revisionNo = '02';
$logoPath = '../../images/bsutneu.png'; // *** Baguhin niyo kung saan yung logo ng school niyo ***

// Para mapadali ang buhay ko sa pagayos ng header at footer
class PDF extends FPDF {
    function Header() {
        // No header
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Times', '', 8);
        $this->Cell(0, 10, 'Tracking No.: _________________', 0, 0, 'R');
    }
}

// Instantiate PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// -- Helper Variables --
$pageWidth = 190; 
$x = $pdf->GetX();
$y = $pdf->GetY();

$headerHeight = 15;
$logoWidth = 25;

// Logo Placeholder Box
$pdf->Rect($x, $y, $logoWidth, $headerHeight);
$pdf->Image($logoPath, $x+5, $y+1, 14); 

// Header Data
$pdf->SetXY($x + $logoWidth, $y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(65, $headerHeight, ' Reference No.: '. $reference, 1, 0, 'L');
$pdf->Cell(55, $headerHeight, ' Effectivity Date: '. $effectivitydate, 1, 0, 'L');
$pdf->Cell(45, $headerHeight, ' Revision No.: '. $revisionNo, 1, 1, 'L');

// Title
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell($pageWidth, 8, 'SYSTEM REQUEST FORM', 1, 1, 'C');

// Request
$pdf->SetFont('Times', '', 10);
$labelWidth = 40;
$inputWidth = $pageWidth - $labelWidth;
$rowHeight = 10;

$pdf->Cell($labelWidth, $rowHeight, ' Requesting Office/Unit:', 1, 0, 'L');
$pdf->Cell($inputWidth, $rowHeight, ' ' . $office, 1, 1, 'L'); 

$boxHeight = 30;
$halfWidth = $pageWidth / 2;
$yPos = $pdf->GetY();

$pdf->Rect($x, $yPos, $halfWidth, $boxHeight);
$pdf->Rect($x + $halfWidth, $yPos, $halfWidth, $boxHeight);

$typeLabelWidth = 35;
$urgencyLabelWidth = 25;
$pdf->Line($x + $typeLabelWidth, $yPos, $x + $typeLabelWidth, $yPos + $boxHeight);
$pdf->Line($x + $halfWidth + $urgencyLabelWidth, $yPos, $x + $halfWidth + $urgencyLabelWidth, $yPos + $boxHeight);

$pdf->SetXY($x, $yPos+2);
$pdf->Cell($typeLabelWidth, 5, ' Type of Request:', 0, 0, 'L');

$pdf->SetXY($x + $typeLabelWidth + 2, $yPos+5);
$correctionChecked = in_array('Correction of system issue', $sysType) ? '[X]' : '[  ]';
$pdf->Cell(5, 5, $correctionChecked, 0, 0); $pdf->Cell(40, 5, 'Correction of system issue', 0, 1); 
$pdf->SetX($x + $typeLabelWidth + 2);
$enhancementChecked = in_array('System enhancement', $sysType) ? '[X]' : '[  ]';
$pdf->Cell(5, 5, $enhancementChecked, 0, 0); $pdf->Cell(40, 5, 'System enhancement', 0, 1); 
$pdf->SetX($x + $typeLabelWidth + 2);
$newSystemChecked = in_array('New System', $sysType) ? '[X]' : '[  ]';
$pdf->Cell(5, 5, $newSystemChecked, 0, 0); $pdf->Cell(40, 5, 'New System', 0, 1); 

$pdf->SetXY($x + $halfWidth, $yPos+12);
$pdf->Cell($urgencyLabelWidth, 5, ' Urgency:', 0, 0, 'L');

$pdf->SetXY($x + $halfWidth + $urgencyLabelWidth + 2, $yPos+5);
$immediateChecked = in_array('Immediate attention required', $urgency) ? '[X]' : '[  ]';
$pdf->Cell(5, 5, $immediateChecked, 0, 0); $pdf->Cell(40, 5, 'Immediate attention required', 0, 1); 
$pdf->SetX($x + $halfWidth + $urgencyLabelWidth + 2);
$normalChecked = in_array('Handle in normal priority', $urgency) ? '[X]' : '[  ]';
$pdf->Cell(5, 5, $normalChecked, 0, 0); $pdf->Cell(40, 5, 'Handle in normal priority', 0, 1); 
$pdf->SetX($x + $halfWidth + $urgencyLabelWidth + 2);
$deferChecked = in_array('Defer until new system is developed', $urgency) ? '[X]' : '[  ]';
$pdf->Cell(5, 5, $deferChecked, 0, 0); $pdf->Cell(40, 5, 'Defer until new system is', 0, 1); 
$pdf->SetX($x + $halfWidth + $urgencyLabelWidth + 7); // Indent wrapped text
$pdf->Cell(40, 4, 'developed', 0, 1);

$pdf->SetY($yPos + $boxHeight);

$currentY = $pdf->GetY();
$systemRowHeight = 15; 

$pdf->Rect($x, $currentY, $labelWidth, $systemRowHeight);
$pdf->SetXY($x, $currentY + 2); 
$pdf->Cell($labelWidth, 4, ' Name of the', 0, 2, 'L'); 
$pdf->Cell($labelWidth, 4, ' Existing/ Proposed', 0, 2, 'L'); 
$pdf->Cell($labelWidth, 4, ' System:', 0, 0, 'L'); 

$pdf->SetXY($x + $labelWidth, $currentY);
$pdf->Cell($inputWidth, $systemRowHeight, ' ' . $nameSystem, 1, 1, 'L');

// Description of request
$descHeight = 35;
$yDesc = $pdf->GetY();

$pdf->Rect($x, $yDesc, $labelWidth, $descHeight);
$pdf->SetXY($x, $yDesc + 2);
$pdf->SetFont('Times', '', 10);
$pdf->Write(5, " Description of\n Request:");

$pdf->SetXY($x, $yDesc + 12);
$pdf->SetFont('Times', 'I', 8); 
$pdf->MultiCell($labelWidth, 3.5, "(Detailed functional and/or technical information. Use attachment if necessary)", 0, 'L');

$pdf->Rect($x + $labelWidth, $yDesc, $inputWidth, $descHeight);
$pdf->SetXY($x + $labelWidth + 2, $yDesc + 2);
$pdf->SetFont('Times', '', 10);
$pdf->MultiCell($inputWidth - 4, 4, $descRequest, 0, 'L');
$pdf->SetY($yDesc + $descHeight);

// Mga signatures, requested by and recommended approval
/*
    Dito yung may issue na overlapping, ang actual issue dito ay yung 
    BOLD na NAME na dapat ay nasa Recommending Approval na cell, kaso
    makulit at ayaw lumipat. Mamarkahan ko ng arrow comment kung nasaan, scroll mo lang
*/
$sigRowHeight = 30;
$ySig = $pdf->GetY();

$pdf->Rect($x, $ySig, $halfWidth, $sigRowHeight); 
$pdf->Rect($x + $halfWidth, $ySig, $halfWidth, $sigRowHeight); 

$pdf->SetXY($x, $ySig);
$pdf->SetFont('Times', '', 10);
$pdf->Cell($halfWidth, 6, ' Requested by:', 0, 1, 'L');

$contentStartY = $ySig + 15;

$pdf->SetFont('Times', 'B', 10);
$pdf->SetY($contentStartY); 
$pdf->MultiCell($halfWidth, 4, strtoupper($reqByName), 0, 'C');

$pdf->SetFont('Times', '', 10);
$pdf->Cell($halfWidth, 5, $reqByDesignation, 0, 1, 'C');
$pdf->Cell($halfWidth, 5, 'Date: ' . ($reqByDate ? date('F j, Y', strtotime($reqByDate)) : '_______________'), 0, 1, 'C');

$pdf->SetXY($x + $halfWidth, $ySig);
$pdf->SetFont('Times', '', 10);
$pdf->Cell($halfWidth, 6, ' Recommending Approval:', 0, 1, 'L');

$pdf->SetFont('Times', 'B', 10);
$pdf->SetXY($x + $halfWidth, $ySig + 8);
$pdf->Cell($halfWidth, 5, 'NAME', 0, 1, 'C');

$pdf->SetFont('Times', '', 10);
$pdf->SetXY($x + $halfWidth, $ySig + 13);
$pdf->Cell($halfWidth, 5, $recApprovalName, 0, 1, 'C');
$pdf->SetXY($x + $halfWidth, $ySig + 18);
$pdf->Cell($halfWidth, 5, 'Date: ' . ($recApprovalDate ? date('F j, Y', strtotime($recApprovalDate)) : '_______________'), 0, 1, 'C');

$pdf->SetY($ySig + $sigRowHeight);

// Approved by
$approveHeight = 30;
$pdf->SetFont('Times', '', 10);
$pdf->Cell($pageWidth, 6, ' Approved by:', 'LTR', 1, 'L');
$pdf->Cell($pageWidth, 24, '', 'LRB', 1, 'C'); 

$yApprove = $pdf->GetY() - 20;
$pdf->SetY($yApprove);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell($pageWidth, 5, 'NAME', 0, 1, 'C');
$pdf->SetFont('Times', '', 10);
$pdf->Cell($pageWidth, 5, $approvedByName, 0, 1, 'C');
$pdf->Cell($pageWidth, 5, 'Date: ' . ($approvedByDate ? date('F j, Y', strtotime($approvedByDate)) : '_______________'), 0, 1, 'C'); 

$pdf->SetY($pdf->GetY() + 5); 

// Remarks
$pdf->Cell($pageWidth, 8, ' Remarks: ' . $remarks, 1, 1, 'L');

$pdf->SetFont('Times', 'I', 9);
$pdf->Cell($pageWidth, 6, '------------------------- To be completed by the ICT Services -------------------------', 1, 1, 'C');

$pdf->SetFont('Times', '', 10);
$pdf->Cell(40, 8, ' Date:', 1, 0, 'L');
$pdf->Cell(50, 8, ' ' . ($ictDate ? date('F j, Y', strtotime($ictDate)) : ''), 1, 0, 'L'); 
$pdf->Cell(25, 8, ' Assigned to:', 1, 0, 'L');
$pdf->Cell(75, 8, ' ' . $ictAssigned, 1, 1, 'L'); 

// Description of Accomplished Tasks
$yTask = $pdf->GetY();
$taskHeight = 20;

$pdf->Rect($x, $yTask, $labelWidth, $taskHeight);
$pdf->SetXY($x, $yTask + 2);
$pdf->Write(5, " Description of\n Accomplished\n Tasks:");

$pdf->Rect($x + $labelWidth, $yTask, $inputWidth, $taskHeight);
$pdf->SetXY($x + $labelWidth + 2, $yTask + 2);
$pdf->SetFont('Times', '', 10);
$pdf->MultiCell($inputWidth - 4, 4, $ictTasks, 0, 'L');
$pdf->SetY($yTask + $taskHeight);

// Footer
$footerHeight = 35;
$yFooter = $pdf->GetY();

$pdf->Rect($x, $yFooter, $halfWidth, $footerHeight);
$pdf->Rect($x + $halfWidth, $yFooter, $halfWidth, $footerHeight);

$pdf->SetXY($x, $yFooter);
$pdf->SetFont('Times', '', 10);
$pdf->Cell($halfWidth, 6, ' Work Done by:', 0, 1, 'L');

// Signature Line
$lineY = $yFooter + 18;
$lineMargin = 15; 
$pdf->Line($x + $lineMargin, $lineY, $x + $halfWidth - $lineMargin, $lineY); // Line above name

$pdf->SetXY($x, $lineY + 1);
$pdf->Cell($halfWidth, 5, $ictWorkByName, 0, 1, 'C');

$textIndent = $x + $lineMargin; 
$pdf->SetX($textIndent);
$pdf->Cell(25, 5, 'Designation: ' . $ictWorkByDesignation, 0, 1, 'L'); 
$pdf->SetX($textIndent);
$pdf->Cell(15, 5, 'Date: ' . ($ictWorkByDate ? date('F j, Y', strtotime($ictWorkByDate)) : ''), 0, 1, 'L'); 

$pdf->SetXY($x + $halfWidth, $yFooter);
$pdf->SetFont('Times', '', 10);
$pdf->Cell($halfWidth, 6, ' Conforme:', 0, 1, 'L');

$lineXStart = $x + $halfWidth + $lineMargin;
$lineXEnd = $x + $pageWidth - $lineMargin;
$pdf->Line($lineXStart, $lineY, $lineXEnd, $lineY);

$pdf->SetXY($x + $halfWidth, $lineY + 1);
$pdf->Cell($halfWidth, 5, $ictConformeName, 0, 1, 'C');

$textIndentRight = $lineXStart;
$pdf->SetX($textIndentRight);
$pdf->Cell(25, 5, 'Designation: ' . $ictConformeDesignation, 0, 1, 'L');
$pdf->SetX($textIndentRight);
$pdf->Cell(15, 5, 'Date: ' . ($ictConformeDate ? date('F j, Y', strtotime($ictConformeDate)) : ''), 0, 1, 'L');
$pdf->SetY($yFooter + $footerHeight);

$pdf->Ln(2);
$pdf->SetFont('Times', 'I', 8);
$pdf->MultiCell($pageWidth, 4, 'Required Attachments: If new system is requested, kindly attach the proposed System Requirements Specification (SRS) and algorithm flowchart of the proposed system.');

$pdf->Output();
?>