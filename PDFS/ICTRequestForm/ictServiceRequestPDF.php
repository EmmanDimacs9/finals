<?php

require_once __DIR__ . '/../../includes/fpdf/fpdf.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';

include '../../logger.php';
$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "ICT SERVICE REQUEST FORM");

$campus         = $_POST['campus'] ?? '';
$ict_srf_no     = $_POST['ict_srf_no'] ?? '';
$client_name    = $_POST['client_name'] ?? '';
$technician     = $_POST['technician'] ?? '';
$office         = $_POST['office'] ?? '';
$date_time_call = $_POST['date_time_call'] ?? '';
$response_time      = $_POST['response_time'] ?? '';
$requirements       = $_POST['requirements'] ?? '';
$accomplishment     = $_POST['accomplishment'] ?? '';
$remarks            = $_POST['remarks'] ?? '';
$accomp_response    = $_POST['accomp_response'] ?? '';
$accomp_service     = $_POST['accomp_service'] ?? '';

$eval_response  = $_POST['eval_response'] ?? '';
$eval_quality   = $_POST['eval_quality'] ?? '';
$eval_courtesy  = $_POST['eval_courtesy'] ?? '';
$eval_overall   = $_POST['eval_overall'] ?? '';

class PDF extends FPDF {
    private $logoPath;
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);
        // Set margins before any content
        $this->SetMargins(10, 10, 10);
        // Try to find logo file
        $logoPaths = [
            __DIR__ . '/../../images/bsutneu.png',
            __DIR__ . '/../../images/logo.png',
            __DIR__ . '/../../images/BSU.jpg',
        ];
        foreach ($logoPaths as $path) {
            if (file_exists($path)) {
                $this->logoPath = $path;
                break;
            }
        }
        $this->SetAutoPageBreak(true, 15);
    }

    function Header() {
        // === HEADER BORDER: Draw outer border around entire header block ===
        $headerStartY = 10;
        $headerHeight = 38; // Total header height
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->Rect(10, $headerStartY, 190, $headerHeight);
        
        // === COLUMN DEFINITIONS (4 Columns) ===
        // Column 1 (Logo Section): X=10 to X=40, Width=30mm
        $col1Start = 10;
        $col1Width = 30;
        $col1End = $col1Start + $col1Width; // X=40
        
        // Column 2 (Reference No. Section): X=40 to X=100, Width=60mm
        $col2Start = 40;
        $col2Width = 60;
        $col2End = $col2Start + $col2Width; // X=100
        
        // Column 3 (Effectivity Date Section): X=100 to X=160, Width=60mm
        $col3Start = 100;
        $col3Width = 60;
        $col3End = $col3Start + $col3Width; // X=160
        
        // Column 4 (Revision No. Section): X=160 to X=200, Width=40mm
        $col4Start = 160;
        $col4Width = 40;
        $col4End = $col4Start + $col4Width; // X=200
        
        // Top row height (above title row)
        $topRowHeight = 26; // From Y=10 to Y=36
        $titleRowY = 36; // Position where title row starts
        $titleRowHeight = 10; // Height of title row
        
        // === COLUMN 1: LOGO SECTION (Top Row) ===
        // Logo is 25x25mm, centered within Column 1
        $logoCenterX = $col1Start + ($col1Width / 2); // Center of Column 1 (X=25)
        $logoCenterY = $headerStartY + ($topRowHeight / 2); // Center Y vertically (Y=23)
        $logoRadius = 12.5;  // Radius for 25mm diameter logo
        
        // Draw black circular border for logo
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->Ellipse($logoCenterX, $logoCenterY, $logoRadius, $logoRadius);
        
        // Display logo image if available
        if ($this->logoPath && file_exists($this->logoPath)) {
            $logoSize = 25; // 25x25mm as specified
            $logoX = $logoCenterX - ($logoSize / 2);
            $logoY = $logoCenterY - ($logoSize / 2);
            $this->Image($this->logoPath, $logoX, $logoY, $logoSize, $logoSize);
        } else {
            // Draw placeholder - matching the description exactly
            // Outer green ring (laurel wreath)
            $this->SetFillColor(0, 128, 0);
            $this->SetDrawColor(0, 128, 0);
            $this->Ellipse($logoCenterX, $logoCenterY, $logoRadius + 1, $logoRadius + 1, 'F');
            
            // White ring
            $this->SetFillColor(255, 255, 255);
            $this->Ellipse($logoCenterX, $logoCenterY, $logoRadius - 1, $logoRadius - 1, 'F');
            
            // Red shield in center (torch emblem)
            $this->SetFillColor(220, 20, 60);
            $this->Ellipse($logoCenterX, $logoCenterY, 7, 7, 'F');
            
            // White torch in center
            $this->SetFillColor(255, 255, 255);
            $this->SetXY($logoCenterX - 2, $logoCenterY - 2);
            $this->Cell(4, 4, '', 0, 0, 'C');
            
            // Text around logo (centered within Column 1)
            $this->SetFont('Arial', 'B', 5);
            $this->SetTextColor(220, 20, 60);
            $textX = $col1Start; // Start of Column 1
            $this->SetXY($textX, 17);
            $this->Cell($col1Width, 3, 'BATANGAS STATE', 0, 0, 'C');
            $this->SetXY($textX, 20);
            $this->Cell($col1Width, 3, 'UNIVERSITY', 0, 0, 'C');
            $this->SetFont('Arial', '', 4);
            $this->SetXY($textX, 23);
            $this->Cell($col1Width, 2, 'The National', 0, 0, 'C');
            $this->SetXY($textX, 25);
            $this->Cell($col1Width, 2, 'Engineering University', 0, 0, 'C');
        }
        
        // === COLUMN 2: REFERENCE NO. SECTION ===
        // Position within Column 2 with left padding
        $refPaddingX = 3; // Padding from left border of column
        $refStartX = $col2Start + $refPaddingX;
        $refY = 13; // Vertical position for text
        $refLineHeight = 4.5; // Height between lines
        $refWidth = $col2Width - ($refPaddingX * 2); // Available width minus padding
        
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);
        
        // Reference No. - left aligned within Column 2
        $this->SetXY($refStartX, $refY);
        $this->Cell($refWidth, $refLineHeight, 'Reference No.: BatStateU-FO-ICT-01', 0, 1, 'L');
        
        // === COLUMN 3: EFFECTIVITY DATE SECTION ===
        // Position within Column 3 with left padding
        $effPaddingX = 3; // Padding from left border of column
        $effStartX = $col3Start + $effPaddingX;
        $effY = $refY; // Same Y position as Reference No. (aligned horizontally)
        $effLineHeight = $refLineHeight;
        $effWidth = $col3Width - ($effPaddingX * 2); // Available width minus padding
        
        // Effectivity Date - left aligned within Column 3
        $this->SetXY($effStartX, $effY);
        $this->Cell($effWidth, $effLineHeight, 'Effectivity Date: May 18, 2022', 0, 1, 'L');
        
        // === COLUMN 4: REVISION NUMBER SECTION ===
        // Position within Column 4 with right padding
        $revPaddingX = 3; // Padding from right border of column
        $revStartX = $col4Start + $revPaddingX;
        $revY = $refY; // Same Y position as Reference No. (aligned horizontally)
        $revLineHeight = $refLineHeight;
        $revWidth = $col4Width - ($revPaddingX * 2); // Available width minus padding
        
        // Revision No. - right-aligned within Column 4
        $this->SetXY($revStartX, $revY);
        $this->Cell($revWidth, $revLineHeight, 'Revision No.: 02', 0, 1, 'R');
        
        // === VERTICAL COLUMN DIVIDERS (Top Row) ===
        // Draw vertical lines to separate columns (inside the header border)
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        
        // Vertical line 1: Separating logo column from reference column
        $this->Line($col2Start, $headerStartY, $col2Start, $titleRowY);
        
        // Vertical line 2: Separating reference column from effectivity column
        $this->Line($col3Start, $headerStartY, $col3Start, $titleRowY);
        
        // Vertical line 3: Separating effectivity column from revision column
        $this->Line($col4Start, $headerStartY, $col4Start, $titleRowY);
        
        // === HORIZONTAL LINE: Separating top row from title row ===
        $this->Line($col1Start, $titleRowY, $col4End, $titleRowY);
        
        // === TITLE ROW (Second Row) ===
        // Left cell: "Title:" label
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255); // White fill
        $this->Rect($col1Start, $titleRowY, $col1Width, $titleRowHeight, 'DF');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY($col1Start + 2, $titleRowY + 2);
        $this->Cell($col1Width - 4, 6, 'Title:', 0, 0, 'L');
        
        // Right cell: "ICT SERVICE REQUEST FORM" (spans Columns 2, 3, 4 with gray background)
        $titleBarWidth = $col2Width + $col3Width + $col4Width; // Total width of columns 2, 3, 4
        $this->SetFillColor(192, 192, 192); // Light gray fill
        $this->Rect($col2Start, $titleRowY, $titleBarWidth, $titleRowHeight, 'DF');
        
        // Title text centered in the shaded bar
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY($col2Start, $titleRowY + 2);
        $this->Cell($titleBarWidth, 6, 'ICT SERVICE REQUEST FORM', 0, 0, 'C');
        
        // Reset Y position after header (below the title row)
        $this->SetY($headerStartY + $headerHeight + 2); // Position after header
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
    
    // Custom Ellipse method for FPDF (draws circle/ellipse using curves)
    function Ellipse($x, $y, $rx, $ry = null, $style = '') {
        // If $ry is not provided, assume it's a circle ($rx = radius)
        if ($ry === null) {
            $ry = $rx;
        }
        
        // Draw ellipse/circle using Bezier curve approximation
        $k = $this->k;
        
        // Define magic number for Bezier curves to approximate ellipse (4/3 * (sqrt(2) - 1))
        $magic = 0.5522847498;
        
        // Determine operation based on style
        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' || $style == 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        
        // Draw ellipse using 4 cubic Bezier curves starting from rightmost point
        // Move to rightmost point (x+rx, y)
        $this->_out(sprintf('%.2F %.2F m', ($x+$rx)*$k, ($this->h-$y)*$k));
        
        // Curve 1: Right to Top
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', 
            ($x+$rx)*$k, ($this->h-($y-$ry*$magic))*$k,
            ($x+$rx*$magic)*$k, ($this->h-($y-$ry))*$k,
            ($x)*$k, ($this->h-($y-$ry))*$k));
        
        // Curve 2: Top to Left
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx*$magic)*$k, ($this->h-($y-$ry))*$k,
            ($x-$rx)*$k, ($this->h-($y-$ry*$magic))*$k,
            ($x-$rx)*$k, ($this->h-$y)*$k));
        
        // Curve 3: Left to Bottom
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx)*$k, ($this->h-($y+$ry*$magic))*$k,
            ($x-$rx*$magic)*$k, ($this->h-($y+$ry))*$k,
            ($x)*$k, ($this->h-($y+$ry))*$k));
        
        // Curve 4: Bottom to Right (closing)
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx*$magic)*$k, ($this->h-($y+$ry))*$k,
            ($x+$rx)*$k, ($this->h-($y+$ry*$magic))*$k,
            ($x+$rx)*$k, ($this->h-$y)*$k));
        
        $this->_out($op);
    }
    
    function DrawCheckMark($x, $y) {
        // Draw a check mark (✓) using lines
        $this->SetLineWidth(0.5);
        // First line: bottom-left to middle
        $this->Line($x + 2, $y + 4, $x + 5, $y + 6.5);
        // Second line: middle to top-right
        $this->Line($x + 5, $y + 6.5, $x + 9, $y + 2);
        $this->SetLineWidth(0.2);
    }
    
    function EvaluationRow($statement, $selected) {
        $this->SetFont('Arial', '', 8);
        $rowHeight = 8; // Match header height
        $y = $this->GetY();

        // Statement cell - align with header (80mm width)
        $this->Cell(80, $rowHeight, $statement, 1, 0, 'L');
        
        // Satisfaction level cells (5 to 1) - align with header (22mm each)
    for ($i = 5; $i >= 1; $i--) {
        $x = $this->GetX();
            $y_current = $this->GetY();

            // Draw cell border (22mm width, 8mm height)
            $this->Cell(22, $rowHeight, '', 1, 0, 'C');

            // Draw check mark if selected
        if ($selected == $i) {
                $this->DrawCheckMark($x, $y_current);
        }
    }
    $this->Ln();
}

    function DrawUnderline($x, $y, $width) {
        $this->Line($x, $y + 2, $x + $width, $y + 2);
    }
    
    function GetMultiCellHeight($w, $h, $txt) {
        // Calculate height needed for MultiCell text
        $nb = 0;
        $s = str_replace("\r", '', $txt);
        if ($s == '') return $h;
        
        $cw = &$this->CurrentFont['cw'];
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl * $h;
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

// --- Service Request Details Section ---
// All cells have thin black borders (1 parameter in Cell function)
$cellHeight = 7; // Standard cell height

// Note: Title is now in the header, so start with Campus row
// Row 1: Campus (label) | Campus value | ICT SRF NO. (label) | ICT SRF NO. value
$pdf->Cell(30, $cellHeight, 'Campus', 1, 0, 'L');
$pdf->Cell(65, $cellHeight, $campus, 1, 0, 'L');
$pdf->Cell(30, $cellHeight, 'ICT SRF NO.:', 1, 0, 'L');
$pdf->Cell(65, $cellHeight, $ict_srf_no, 1, 1, 'L');

// Row 3: Office/Building (label) | Office value | Technician assigned (label) | Technician value
$pdf->Cell(30, $cellHeight, 'Office/Building', 1, 0, 'L');
$pdf->Cell(65, $cellHeight, $office, 1, 0, 'L');
$pdf->Cell(30, $cellHeight, 'Technician assigned', 1, 0, 'L');
$pdf->Cell(65, $cellHeight, $technician, 1, 1, 'L');

// Row 4: Client's Name (label) | Client name value | Signature (label) | Signature value
$pdf->Cell(30, $cellHeight, 'Client\'s Name', 1, 0, 'L');
$pdf->Cell(65, $cellHeight, $client_name, 1, 0, 'L');
$pdf->Cell(30, $cellHeight, 'Signature', 1, 0, 'L');
$pdf->Cell(65, $cellHeight, '', 1, 1, 'L');

// Row 5: Date/Time of Call (label) | Date/Time value | Required Response Time (label) | Response Time value
// "Required Response Time" needs 2 lines, so this row needs to be taller
$row5Height = 7; // Height for row with wrapped text
$x = $pdf->GetX();
$y = $pdf->GetY();
// Draw Date/Time of Call label and value cells
$pdf->Cell(30, $row5Height, 'Date/Time of Call', 1, 0, 'L');
$pdf->Cell(65, $row5Height, $date_time_call, 1, 0, 'L');
// "Required Response Time" label - needs to wrap in cell
$x = $pdf->GetX();
$y = $pdf->GetY();
// Draw border for label cell first
$pdf->Rect($x, $y, 30, $row5Height);
// Add wrapped text in the cell
$pdf->SetXY($x + 1, $y + 1);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(28, 3.5, "Required\nResponse Time", 0, 'C');
// Draw border and value for response time cell
$pdf->SetXY($x + 30, $y);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(65, $row5Height, $response_time, 1, 1, 'L');

// Row 6: Services Requirements (label and content in single cell, no separate column)
$x = $pdf->GetX();
$y = $pdf->GetY();

// Calculate height needed - ensure minimum height
$minHeight = $cellHeight; // Minimum cell height
$labelHeight = 5; // Height for label text

// Calculate content height
if (!empty($requirements)) {
    $contentHeight = $pdf->GetMultiCellHeight(186, 6, $requirements);
} else {
    $contentHeight = 6; // Default height for empty content
}

// Total height: label (5) + spacing (2) + content + bottom padding (4)
// Ensure minimum height is maintained
$totalHeight = max($minHeight, $labelHeight + 2 + $contentHeight + 4);

// Draw border for the entire cell (full width)
$pdf->Rect(10, $y, 190, $totalHeight);

// Add label "Services Requirements:" at the top
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(12, $y + 2);
$pdf->Cell(186, $labelHeight, 'Services Requirements:', 0, 0, 'L');

// Add content below label with proper spacing
$pdf->SetFont('Arial', '', 9);
$contentY = $y + $labelHeight + 4; // Position after label with spacing
$pdf->SetXY(12, $contentY);
if (!empty($requirements)) {
    $pdf->MultiCell(186, 6, $requirements, 0, 'L');
} else {
    // Just set position for empty cell
    $pdf->SetXY(12, $contentY);
}

// Ensure we move to next line after requirements cell
$pdf->SetXY(10, $y + $totalHeight);

// --- Accomplishment Section ---
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(190, $cellHeight, 'ACCOMPLISHMENT (to be accomplished by the assigned technician)', 1, 1, 'L');
$pdf->SetFont('Arial', '', 9);

// Accomplishment fields - all in vertical layout (no horizontal columns)
// Row 1: Response Time (label) | Response Time value (full width)
$pdf->Cell(30, $cellHeight, 'Response Time:', 1, 0, 'L');
$pdf->Cell(160, $cellHeight, $accomp_response, 1, 1, 'L');

// Row 2: Service Time (label) | Service Time value (full width)
$pdf->Cell(30, $cellHeight, 'Service Time:', 1, 0, 'L');
$pdf->Cell(160, $cellHeight, $accomp_service, 1, 1, 'L');

// Row 3: Remarks (label) | Remarks value (large multi-line cell, full width)
$pdf->Cell(30, $cellHeight, 'Remarks:', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
// Calculate height needed for remarks
$remarksHeight = max($cellHeight, $pdf->GetMultiCellHeight(160, 6, $remarks));
// Draw border for remarks cell
$pdf->Rect($x, $y, 160, $remarksHeight);
// Add text with padding
$pdf->SetXY($x + 2, $y + 2);
if (!empty($remarks)) {
    $pdf->MultiCell(156, 6, $remarks, 0, 'L');
} else {
    // Just set position for empty cell
    $pdf->SetXY($x + 2, $y + 2);
}
// Move to next line after remarks cell
$pdf->SetXY(10, $y + $remarksHeight);

// --- Customer Satisfaction Survey Section ---
$pdf->Ln(3);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(190, 4, "Thank you for giving us the opportunity to serve you better. Please help us by taking a few minutes to inform us about the technical assistance/service that you have just been provided. Put a check (✔) on the column that corresponds to your level of satisfaction.", 0, 'L');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 8);

// Table header row - Evaluation Statements and Satisfaction Levels
$pdf->SetFillColor(220, 220, 220);
$headerHeight = 8; // Height for header cells

// Evaluation Statements header cell
$xStart = $pdf->GetX();
$yStart = $pdf->GetY();
$pdf->Rect($xStart, $yStart, 80, $headerHeight, 'DF');
$pdf->SetXY($xStart, $yStart);
$pdf->Cell(80, $headerHeight, 'Evaluation Statements', 0, 0, 'C');

// Satisfaction level headers with numbers below
$levels = [
    ['Very Satisfied', '5'],
    ['Satisfied', '4'],
    ['Neither Satisfied nor Dissatisfied', '3'],
    ['Dissatisfied', '2'],
    ['Very Dissatisfied', '1']
];

$xPos = $xStart + 80; // Start after Evaluation Statements column
foreach ($levels as $level) {
    // Draw cell border with fill
    $pdf->Rect($xPos, $yStart, 22, $headerHeight, 'DF');
    
    // Add text on top (centered, smaller font) - positioned in upper half
    $pdf->SetFont('Arial', 'B', 7);
    
    // Handle "Neither Satisfied nor Dissatisfied" separately - it needs to wrap
    if (strpos($level[0], 'Neither Satisfied nor Dissatisfied') !== false) {
        // Use smaller font for wrapped text to prevent collapse
        $pdf->SetFont('Arial', 'B', 6);
        
        // Line 1: "Neither Satisfied nor" - centered
        $pdf->SetXY($xPos, $yStart + 0.5);
        $pdf->Cell(22, 2.2, 'Neither Satisfied nor', 0, 0, 'C');
        
        // Line 2: "Dissatisfied" - centered
        $pdf->SetXY($xPos, $yStart + 2.7);
        $pdf->Cell(22, 2.2, 'Dissatisfied', 0, 0, 'C');
    } else {
        // Single line text - use Cell for proper centering
        $pdf->SetXY($xPos, $yStart + 0.5);
        $pdf->Cell(22, 3.5, $level[0], 0, 0, 'C');
    }
    
    // Add number below (centered, larger font) - positioned in lower half
    $pdf->SetXY($xPos, $yStart + 5.5); // Position number at bottom (below text area)
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(22, 2.5, $level[1], 0, 0, 'C');
    
    // Move to next column
    $xPos += 22;
}

// Move to next line for content rows
$pdf->SetXY(10, $yStart + $headerHeight);

// Reset font for content rows
$pdf->SetFont('Arial', '', 8);
$pdf->SetFillColor(255, 255, 255);

// Rows
$pdf->evaluationRow('Response time to your initial call for service', $eval_response);
$pdf->evaluationRow('Quality of service provided to resolve the problem', $eval_quality);
$pdf->evaluationRow('Courtesy and professionalism of the attending ICT staff', $eval_courtesy);
$pdf->evaluationRow('Overall satisfaction with the assistance/service provided', $eval_overall);

// Conforme Section
$pdf->Ln(4);
$pdf->Cell(190, 7, 'Conforme:', 1, 1);
$pdf->Ln(8);
$pdf->Cell(190, 7, "Client's Signature Over Printed Name", 0, 1, 'C');
$pdf->Ln(6);
$pdf->Cell(190, 7, "Office/Building", 0, 1, 'C');
$pdf->Ln(6);
$pdf->Cell(190, 7, "Date Signed", 0, 1, 'C');

$pdf->Output('I', 'ICT_Service_Request_Form.pdf');
?>
