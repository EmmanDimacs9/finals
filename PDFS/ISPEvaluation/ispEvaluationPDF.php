<?php
// Start output buffering to prevent any output before PDF
ob_start();

require_once __DIR__ . '/../../includes/pdf_template.php';

require_once '../../includes/session.php';
require_once '../../includes/db.php';

	include '../../logger.php';
	$uid = $_SESSION['user_id'] ?? 0;
	$uname = $_SESSION['user_name'] ?? 'SYSTEM';
	logAdminAction($uid, $uname, "Generated Report", "EXISTING INTERNET SERVICE PROVIDER'S EVALUATION");
// Collect POST data
$provider   = $_POST['provider_name'] ?? '';
$date_eval  = $_POST['evaluation_date'] ?? '';
$address    = $_POST['address'] ?? '';
$contact    = $_POST['contact_person'] ?? '';
$period     = $_POST['period'] ?? '';
$position   = $_POST['position'] ?? '';
$tel_no     = $_POST['tel_no'] ?? '';

$uptime_rate    = $_POST['uptime_rate'] ?? '';
$uptime_remarks = $_POST['uptime_remarks'] ?? '';
$latency_rate   = $_POST['latency_rate'] ?? '';
$latency_remarks= $_POST['latency_remarks'] ?? '';
$support_rate   = $_POST['support_rate'] ?? '';
$support_remarks= $_POST['support_remarks'] ?? '';

$evaluator  = $_POST['evaluator'] ?? '';
$supervisor = $_POST['supervisor'] ?? '';

// Convert earned rate to numeric for total calculation
function rateToNumber($rate) {
    return intval(str_replace('%','',$rate));
}

$totalEarned = rateToNumber($uptime_rate) + rateToNumber($latency_rate) + rateToNumber($support_rate);

class PDF extends TemplatePDF {
    private $customTitleText = '';
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'Legal') {
        parent::__construct($orientation, $unit, $size);
    }
    
    public function Header() {
        // Check for header image using possible locations
        $possibleHeaderPaths = [
            __DIR__ . '/../../assets/template/header.png',
            __DIR__ . '/../../assets/template/Header.png',
            __DIR__ . '/../../header.png',
        ];
        
        $headerImagePath = null;
        foreach ($possibleHeaderPaths as $path) {
            if (file_exists($path)) {
                $headerImagePath = $path;
                break;
            }
        }
        
        // If a header image exists, render it
        if ($headerImagePath && file_exists($headerImagePath)) {
            $leftMargin = 10;
            $rightMargin = 10;
            $usableWidth = $this->GetPageWidth() - $leftMargin - $rightMargin;
            $imgHeight = 0;
            $imgInfo = @getimagesize($headerImagePath);
            if ($imgInfo && isset($imgInfo[0]) && $imgInfo[0] > 0) {
                $imgHeight = $usableWidth * ($imgInfo[1] / $imgInfo[0]);
            } else {
                $imgHeight = 28;
            }
            $this->Image($headerImagePath, $leftMargin, 10, $usableWidth);
            $this->SetY(10 + $imgHeight + 4);
        }

        // Title bar matching table width (190mm) and centered
        if ($this->customTitleText !== '') {
            $this->SetFont('Arial', 'B', 13);
            $titleWidth = 190;
            $xPos = ($this->GetPageWidth() - $titleWidth) / 2;
            $this->SetX($xPos);
            $this->Cell($titleWidth, 12, $this->customTitleText, 1, 1, 'C');
            $this->SetX($xPos);
        }
    }
    
    public function setTitleText($title) {
        parent::setTitleText($title);
        $this->customTitleText = (string)$title;
    }
}

// --- USE LEGAL SIZE ---
$pdf = new PDF('P','mm','Legal');  // ✅ change A4 → Legal
$pdf->setTitleText("EXISTING INTERNET SERVICE PROVIDER'S EVALUATION");
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

// --- Align all content to match title position (190mm centered) ---
$titleWidth = 190;
$tableXPos = ($pdf->GetPageWidth() - $titleWidth) / 2;
$pdf->SetX($tableXPos);

// --- Provider Info ---
$pdf->Cell(40, 7, 'External Providers Name:', 1, 0, 'L');
$pdf->Cell(150, 7, $provider, 1, 1, 'L');

$pdf->SetX($tableXPos);
$pdf->Cell(40, 7, 'Address:', 1, 0, 'L');
$pdf->Cell(70, 7, $address, 1, 0, 'L');
$pdf->Cell(40, 7, 'Date of Evaluation:', 1, 0, 'L');
$pdf->Cell(40, 7, $date_eval, 1, 1, 'L');

$pdf->SetX($tableXPos);
$pdf->Cell(40, 7, 'Contact Person:', 1, 0, 'L');
$pdf->Cell(70, 7, $contact, 1, 0, 'L');
$pdf->Cell(40, 7, 'Period Covered:', 1, 0, 'L');
$pdf->Cell(40, 7, $period, 1, 1, 'L');

$pdf->SetX($tableXPos);
$pdf->Cell(40, 7, 'Position:', 1, 0, 'L');
$pdf->Cell(70, 7, $position, 1, 0, 'L');
$pdf->Cell(40, 7, 'Tel. No.:', 1, 0, 'L');
$pdf->Cell(40, 7, $tel_no, 1, 1, 'L');

// ================= CRITERIA ================= //
$pdf->SetX($tableXPos);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(65, 7, 'CRITERIA', 1, 0, 'C');
$pdf->Cell(40, 7, 'Weight', 1, 0, 'C');
$pdf->Cell(40, 7, 'Earned', 1, 0, 'C');
$pdf->Cell(45, 7, 'Remarks', 1, 1, 'C');

// --- UPTIME ---
$pdf->SetX($tableXPos);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(190, 7, '1. UPTIME COMMITMENT', 1, 1, 'L');

$uptime_options = [
    "99.0% - 100% Uptime" => "60%",
    "98.0% - 98.9% Uptime" => "50%",
    "97.0% - 97.9% Uptime" => "40%",
    "96.0% - 96.9% Uptime" => "30%",
    "0.0% - 95.9% Uptime"  => "20%",
];

foreach ($uptime_options as $label => $weight) {
    $earned = ($uptime_rate == $weight) ? $uptime_rate : '';
    $remarks = ($uptime_rate == $weight) ? $uptime_remarks : '';
    $pdf->SetX($tableXPos);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(65, 7, $label, 1, 0, 'L');
    $pdf->Cell(40, 7, $weight, 1, 0, 'C');
    $pdf->Cell(40, 7, $earned, 1, 0, 'C');
    $pdf->Cell(45, 7, $remarks, 1, 1, 'L');
}

// --- NETWORK LATENCY ---
$pdf->SetX($tableXPos);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(190, 7, '2. NETWORK LATENCY', 1, 1, 'L');

$latency_options = [
    "Low / Low / Low" => "30%",
    "Low / Low / High" => "25%",
    "High / Low / Low" => "25%",
    "High / Low / High" => "20%",
    "Low / High / High" => "20%",
    "High / High / Low" => "20%",
    "High / High / High" => "15%",
];

foreach ($latency_options as $label => $weight) {
    $earned = ($latency_rate == $weight) ? $latency_rate : '';
    $remarks = ($latency_rate == $weight) ? $latency_remarks : '';
    $pdf->SetX($tableXPos);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(65, 7, $label, 1, 0, 'L');
    $pdf->Cell(40, 7, $weight, 1, 0, 'C');
    $pdf->Cell(40, 7, $earned, 1, 0, 'C');
    $pdf->Cell(45, 7, $remarks, 1, 1, 'L');
}

// --- TECHNICAL SUPPORT ---
$pdf->SetX($tableXPos);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(190, 7, '3. TECHNICAL SUPPORT RESPONSE AND ACCESSIBILITY', 1, 1, 'L');

$support_options = [
    "Within 48h or less + Updates ≤ 2h" => "10%",
    "Within 48h or less + Updates > 2h" => "9%",
    "More than 48h + Updates ≤ 2h" => "8%",
    "More than 48h + Updates > 2h" => "7%",
];

foreach ($support_options as $label => $weight) {
    $earned = ($support_rate == $weight) ? $support_rate : '';
    $remarks = ($support_rate == $weight) ? $support_remarks : '';
    $pdf->SetX($tableXPos);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(65, 7, $label, 1, 0, 'L');
    $pdf->Cell(40, 7, $weight, 1, 0, 'C');
    $pdf->Cell(40, 7, $earned, 1, 0, 'C');
    $pdf->Cell(45, 7, $remarks, 1, 1, 'L');
}

// --- TOTAL ---
$pdf->SetX($tableXPos);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(65, 7, 'TOTAL', 1, 0, 'R');
$pdf->Cell(40, 7, '100%', 1, 0, 'C');
$pdf->Cell(40, 7, $totalEarned . "%", 1, 0, 'C');
$pdf->Cell(45, 7, '', 1, 1, 'L');

// --- Evaluators ---
$pdf->Ln(3);
$pdf->SetX($tableXPos);
$pdf->SetFont('Arial','',9);
$pdf->Cell(95, 10, "Evaluated by: $evaluator", 1, 0, 'L');
$pdf->Cell(95, 10, "Reviewed & Approved: $supervisor", 1, 1, 'L');

// ================= PERFORMANCE ================= //
$pdf->Ln(4);

// Header row
$pdf->SetX($tableXPos);
$pdf->SetFillColor(200,200,150);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(95, 7, 'PERFORMANCE', 1, 0, 'C', true);
$pdf->Cell(95, 7, 'Recommendations:', 1, 1, 'C', true);

// Sub-header
$pdf->SetX($tableXPos);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(47.5, 7, 'Rating', 1, 0, 'C');
$pdf->Cell(47.5, 7, 'Description', 1, 0, 'C');


// Recommendations text
$recommendations = "                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               ";

// Print recommendations box
$pdf->SetFont('Arial','',8);
$y_start = $pdf->GetY();
$pdf->MultiCell(95, 7, $recommendations, 1, 'L');

// Go back for Performance rows
$pdf->SetXY($tableXPos, $y_start + 7);

// Performance table with highlighting
$performance = [
    ['91–100%', 'Outstanding'],
    ['81–90%', 'Very Satisfactory'],
    ['71–80%', 'Satisfactory'],
    ['61–70%', 'Unsatisfactory'],
    ['Below 61%', 'Poor'],
];

foreach ($performance as $row) {
    $range = $row[0];
    $desc = $row[1];

    // Determine highlight
    $highlight = false;
    if ($totalEarned >= 91 && strpos($range,'91–100%')!==false) $highlight = true;
    elseif ($totalEarned >= 81 && $totalEarned <= 90 && strpos($range,'81–90%')!==false) $highlight = true;
    elseif ($totalEarned >= 71 && $totalEarned <= 80 && strpos($range,'71–80%')!==false) $highlight = true;
    elseif ($totalEarned >= 61 && $totalEarned <= 70 && strpos($range,'61–70%')!==false) $highlight = true;
    elseif ($totalEarned < 61 && strpos($range,'Below 61%')!==false) $highlight = true;

    // Reset X position for each performance row
    $pdf->SetX($tableXPos);
    
    if ($highlight) {
        $pdf->SetFillColor(200,200,150);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(47.5, 7, $range, 1, 0, 'C', true);
        $pdf->Cell(47.5, 7, $desc, 1, 1, 'C', true);
    } else {
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(47.5, 7, $range, 1, 0, 'C');
        $pdf->Cell(47.5, 7, $desc, 1, 1, 'C');
    }
}

// Clean any output buffer before sending PDF
ob_end_clean();

$pdf->Output('I','ISP_Evaluation.pdf');
