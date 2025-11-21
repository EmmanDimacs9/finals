<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "SYSTEM REQUEST FORM");

class PDF extends TemplatePDF {
    function Header() {
        // BSU Logo
        $logoPath = __DIR__ . '/../../images/bsutneu.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 10, 20, 20);
        }
        
        // Header information table - exact match to image
        $this->SetFont('Arial', '', 8);
        $this->SetXY(40, 10);
        
        // First row of header table
        $this->Cell(55, 5, 'Reference No.: BatStateU-FO-ICT-03', 1, 0, 'L');
        $this->Cell(50, 5, 'Effectivity Date: July 31, 2023', 1, 0, 'L');
        $this->Cell(25, 5, 'Revision No.: 02', 1, 1, 'L');
        
        // Second row - empty for spacing
        $this->SetX(40);
        $this->Cell(130, 5, '', 1, 1, 'L');
        
        // Title row
        $this->SetX(40);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(130, 8, 'SYSTEM REQUEST FORM', 1, 1, 'C');
        
        $this->Ln(5);
    }
    
    function DrawCheckbox($label, $checked = false) {
        $x = $this->GetX();
        $y = $this->GetY();
        
        // Draw checkbox square
        $this->Rect($x, $y + 1, 3, 3);
        if ($checked) {
            $this->SetFont('Arial', 'B', 8);
            $this->SetXY($x + 0.5, $y + 0.5);
            $this->Cell(3, 3, 'X', 0, 0, 'C');
        }
        
        // Draw label
        $this->SetFont('Arial', '', 9);
        $this->SetXY($x + 5, $y);
        $this->Cell(0, 5, $label, 0, 1, 'L');
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
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

$leftMargin = 15;
$fullWidth = 180;

// Start content after header
$pdf->SetY(38);

// --- Requesting Office/Unit ---
$pdf->SetX($leftMargin);
$pdf->Cell(50, 8, 'Requesting Office/Unit:', 1, 0, 'L');
$pdf->Cell(130, 8, $office, 1, 1, 'L');

// --- Combined Type of Request and Urgency Row ---
$startY = $pdf->GetY();

// Type of Request section
$pdf->SetXY($leftMargin, $startY);
$pdf->Cell(25, 18, 'Type of Request:', 1, 0, 'L');

// Type of Request checkboxes container
$pdf->SetXY($leftMargin + 25, $startY);
$pdf->Cell(65, 18, '', 1, 0, 'L');

// Draw Type of Request checkboxes
$typeOptions = ['Correction of system issue', 'System enhancement', 'New System'];
$yPos = $startY + 2;
foreach ($typeOptions as $opt) {
    $pdf->SetXY($leftMargin + 27, $yPos);
    $pdf->DrawCheckbox($opt, in_array($opt, $sysType));
    $yPos += 5;
}

// Urgency section
$pdf->SetXY($leftMargin + 90, $startY);
$pdf->Cell(20, 18, 'Urgency:', 1, 0, 'L');

// Urgency checkboxes container
$pdf->SetXY($leftMargin + 110, $startY);
$pdf->Cell(70, 18, '', 1, 0, 'L');

// Draw Urgency checkboxes
$urgencyOptions = [
    'Immediate attention required',
    'Handle in normal priority', 
    'Defer until new system is developed'
];
$yPos = $startY + 2;
foreach ($urgencyOptions as $opt) {
    $pdf->SetXY($leftMargin + 112, $yPos);
    $pdf->DrawCheckbox($opt, in_array($opt, $urgency));
    $yPos += 5;
}

$pdf->SetY($startY + 18);

// --- Name of Existing/Proposed System ---
$pdf->SetX($leftMargin);
$pdf->Cell(50, 8, 'Name of the Existing /', 1, 0, 'L');
$pdf->Cell(130, 8, $nameSystem, 1, 1, 'L');

$pdf->SetX($leftMargin);
$pdf->Cell(50, 8, 'Proposed System:', 1, 0, 'L');
$pdf->Cell(130, 8, '', 1, 1, 'L');

// --- Description of Request ---
$pdf->SetX($leftMargin);
$pdf->Cell(50, 30, 'Description of Request:', 1, 0, 'L');

$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Rect($x, $y, 130, 30);

// Add italic note
$pdf->SetXY($x + 2, $y + 2);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(126, 4, '(Detailed functional and/or technical information. Use', 0, 1, 'L');
$pdf->SetX($x + 2);
$pdf->Cell(126, 4, 'attachment if necessary)', 0, 1, 'L');

// Add description content
$pdf->SetXY($x + 2, $y + 10);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(126, 4, $descRequest, 0, 'L');

$pdf->SetY($y + 30);

// --- Signature Section ---
$yStart = $pdf->GetY();

// Requested by and Recommending Approval row
$pdf->SetXY($leftMargin, $yStart);
$pdf->Cell(90, 8, 'Requested by:', 1, 0, 'L');
$pdf->Cell(90, 8, 'Recommending Approval:', 1, 1, 'L');

// Names row
$pdf->SetX($leftMargin);
$pdf->Cell(45, 8, 'NAME OF REQUESTING OFFICIAL / PERSONNEL', 1, 0, 'C');
$pdf->Cell(45, 8, 'NAME', 1, 0, 'C');
$pdf->Cell(45, 8, 'Director, ICT Services', 1, 0, 'C');
$pdf->Cell(45, 8, 'NAME', 1, 1, 'C');

// Values row
$pdf->SetX($leftMargin);
$pdf->Cell(45, 8, $reqByName, 1, 0, 'C');
$pdf->Cell(45, 8, 'Designation:', 1, 0, 'C');
$pdf->Cell(45, 8, 'Date:', 1, 0, 'C');
$pdf->Cell(45, 8, 'Date:', 1, 1, 'C');

// Designation and Date values
$pdf->SetX($leftMargin);
$pdf->Cell(45, 8, $reqByDesignation, 1, 0, 'C');
$pdf->Cell(45, 8, $reqByDate, 1, 0, 'C');
$pdf->Cell(45, 8, $recApprovalDate, 1, 0, 'C');
$pdf->Cell(45, 8, '', 1, 1, 'C');

// Approved by section
$pdf->SetX($leftMargin);
$pdf->Cell(180, 8, 'Approved by:', 1, 1, 'L');

$pdf->SetX($leftMargin);
$pdf->Cell(180, 15, '', 1, 1, 'L'); // Space for signature

$pdf->SetX($leftMargin);
$pdf->Cell(180, 8, 'NAME', 1, 1, 'C');

$pdf->SetX($leftMargin);
$pdf->Cell(90, 8, 'Vice President for Development and External Affairs', 1, 0, 'C');
$pdf->Cell(90, 8, 'Date:', 1, 1, 'C');

// Remarks section
$pdf->SetX($leftMargin);
$pdf->Cell(180, 8, 'Remarks:', 1, 1, 'L');

$pdf->SetX($leftMargin);
$pdf->Cell(180, 15, $remarks, 1, 1, 'L');

// --- ICT Services Section ---
$pdf->SetFont('Arial', '', 9);
$pdf->SetX($leftMargin);
$pdf->Cell(180, 8, '---------------------- To be completed by the ICT Services ----------------------', 1, 1, 'C');

// Date row
$pdf->SetX($leftMargin);
$pdf->Cell(30, 8, 'Date:', 1, 0, 'L');
$pdf->Cell(150, 8, $ictDate, 1, 1, 'L');

// Assigned to row
$pdf->SetX($leftMargin);
$pdf->Cell(30, 8, 'Assigned to:', 1, 0, 'L');
$pdf->Cell(150, 8, $ictAssigned, 1, 1, 'L');

// Description of Accomplished Tasks
$pdf->SetX($leftMargin);
$pdf->Cell(30, 20, 'Description of', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Rect($x, $y, 150, 20);
$pdf->SetXY($x + 2, $y + 2);
$pdf->MultiCell(146, 4, $ictTasks, 0, 'L');

$pdf->SetXY($leftMargin, $y);
$pdf->Cell(30, 10, '', 0, 1, 'L');
$pdf->SetX($leftMargin);
$pdf->Cell(30, 10, 'Accomplished Tasks:', 1, 0, 'L');
$pdf->SetY($y + 20);

// Work Done by and Conforme section
$pdf->SetX($leftMargin);
$pdf->Cell(30, 8, 'Work Done by:', 1, 0, 'L');
$pdf->Cell(75, 8, 'Conforme:', 1, 0, 'L');
$pdf->Cell(75, 8, '', 1, 1, 'L');

// Signature spaces
$pdf->SetX($leftMargin);
$pdf->Cell(30, 20, '', 1, 0, 'L');
$pdf->Cell(75, 20, '', 1, 0, 'L');
$pdf->Cell(75, 20, '', 1, 1, 'L');

// Signature labels
$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Signature over Printed Name', 1, 0, 'C');
$pdf->Cell(75, 6, 'Signature over Printed Name', 1, 0, 'C');
$pdf->Cell(75, 6, '', 1, 1, 'C');

// Designation row
$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Designation:', 1, 0, 'L');
$pdf->Cell(75, 6, 'Designation:', 1, 0, 'L');
$pdf->Cell(75, 6, '', 1, 1, 'L');

// Date row
$pdf->SetX($leftMargin);
$pdf->Cell(30, 6, 'Date:', 1, 0, 'L');
$pdf->Cell(75, 6, 'Date:', 1, 0, 'L');
$pdf->Cell(75, 6, '', 1, 1, 'L');

// --- Footer ---
$pdf->Ln(3);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetX($leftMargin);
$pdf->MultiCell(180, 4, "Required Attachments: If new system is requested, kindly attach the proposed System Requirements Specification (SRS) and algorithm flowchart of the proposed system.", 0, 'L');

$pdf->Output('I', 'System_Request_Form.pdf');
?>
