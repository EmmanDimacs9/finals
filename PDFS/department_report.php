<?php
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

class PDF extends FPDF {
    public $headers = ['Department','Desktops','Laptops','Printers','Switches','Telephones','Access Points','Total Cost'];
    public $widths;
    private $headerImage;

    function __construct($orientation='L', $unit='mm', $size='A4') {
        parent::__construct($orientation, $unit, $size);

        // Page width (A4 landscape = 297mm)
        $usableWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        // Adjusted ratios to fit the landscape width (more space available)
        $ratios = [0.30, 0.08, 0.08, 0.08, 0.08, 0.10, 0.10, 0.18];
        $this->widths = array_map(fn($r) => $r * $usableWidth, $ratios);

        // Path to letterhead header image
        $this->headerImage = __DIR__ . '/../header.png';
    }

    function Header() {
        // Header image at top
        if (file_exists($this->headerImage)) {
            $this->Image($this->headerImage, 10, 5, 277, 40); // fits landscape width
        }

        // Move below the header image
        $this->SetY(50);

        // Title
        $this->SetFont('Arial','B',13);
        $this->Cell(0,10,'DEPARTMENT ANALYSIS REPORT',0,1,'C');

        $this->SetFont('Arial','',11);
        $this->Cell(0,8,'Information and Communications Technology Services',0,1,'C');
        $this->Ln(4);

        // Table header
        $this->SetFont('Arial','B',8.5);
        foreach ($this->headers as $i => $h) {
            $this->Cell($this->widths[$i], 9, $h, 1, 0, 'C');
        }
        $this->Ln();
    }

    function Footer() {
        // Footer motto
        $this->SetY(-20);
        $this->SetFont('Arial','I',9);
        $this->SetTextColor(255, 87, 87);
        $this->Cell(0,8,'Leading Innovations, Transforming Lives, Building the Nation',0,1,'C');

        // Page number
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(0);
        $this->Cell(0,5,'Page '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

$widths = $pdf->widths;

// Collect department list from all tables
$departments = [];

// Desktop
$res = $conn->query("SELECT DISTINCT department_office as dept FROM desktop");
while ($r = $res->fetch_assoc()) $departments[] = $r['dept'];

// Laptops
$res = $conn->query("SELECT DISTINCT department as dept FROM laptops");
while ($r = $res->fetch_assoc()) $departments[] = $r['dept'];

// Printers
$res = $conn->query("SELECT DISTINCT department as dept FROM printers");
while ($r = $res->fetch_assoc()) $departments[] = $r['dept'];

// Switches
$res = $conn->query("SELECT DISTINCT department as dept FROM switch");
while ($r = $res->fetch_assoc()) $departments[] = $r['dept'];

// Telephones
$res = $conn->query("SELECT DISTINCT department as dept FROM telephone");
while ($r = $res->fetch_assoc()) $departments[] = $r['dept'];

$departments = array_unique(array_filter($departments));

$grandTotal = 0;

// Loop through departments
foreach ($departments as $dept) {
    // Counts and total cost per department
    $desktop = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(unit_price),0) total FROM desktop WHERE department_office='$dept'")->fetch_assoc();
    $laptop  = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(unit_price),0) total FROM laptops WHERE department='$dept'")->fetch_assoc();
    $printer = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(unit_price),0) total FROM printers WHERE department='$dept'")->fetch_assoc();
    $switch  = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(unit_price),0) total FROM switch WHERE department='$dept'")->fetch_assoc();
    $tel     = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(unit_price),0) total FROM telephone WHERE department='$dept'")->fetch_assoc();

    // Access points (if table exists)
    $access  = ['c'=>0,'total'=>0];
    if ($conn->query("SHOW TABLES LIKE 'accesspoint'")->num_rows) {
        $access = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(unit_price),0) total FROM accesspoint WHERE department='$dept'")->fetch_assoc();
    }

    $totalCost = $desktop['total'] + $laptop['total'] + $printer['total'] + $switch['total'] + $tel['total'] + $access['total'];
    $grandTotal += $totalCost;

    // Table rows
    $pdf->Cell($widths[0], 8, $dept, 1, 0, 'L');
    $pdf->Cell($widths[1], 8, $desktop['c'], 1, 0, 'C');
    $pdf->Cell($widths[2], 8, $laptop['c'], 1, 0, 'C');
    $pdf->Cell($widths[3], 8, $printer['c'], 1, 0, 'C');
    $pdf->Cell($widths[4], 8, $switch['c'], 1, 0, 'C');
    $pdf->Cell($widths[5], 8, $tel['c'], 1, 0, 'C');
    $pdf->Cell($widths[6], 8, $access['c'], 1, 0, 'C');
    $pdf->Cell($widths[7], 8, $totalCost ? chr(8369).number_format($totalCost,2) : '-', 1, 1, 'R');
}

// Grand total row
$pdf->SetFont('Arial','B',9);
$pdf->Cell(array_sum($widths)-$widths[7], 10, 'GRAND TOTAL', 1, 0, 'R');
$pdf->Cell($widths[7], 10, chr(8369).number_format($grandTotal,2), 1, 1, 'R');

$pdf->Output('I', 'Department_Analysis_Report.pdf');
?>
