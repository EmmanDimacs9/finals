<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

class PDF extends FPDF {
    public $headers = ['Date Acquired','Type','Asset Tag','Department','Assigned To','Location','Cost'];
    public $widths;
    private $headerImage;

    function __construct($orientation='P',$unit='mm',$size='A4') {
        parent::__construct($orientation,$unit,$size);
        $usableWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        // Adjusted ratios to fit neatly in portrait orientation
        $ratios = [0.15,0.10,0.18,0.17,0.15,0.15,0.10];
        $this->widths = array_map(fn($r)=>$r*$usableWidth,$ratios);

        // Path to letterhead image
        $this->headerImage = __DIR__ . '/../header.png';
    }

    function Header() {
        // Show letterhead image (top only)
        if(file_exists($this->headerImage)){
            $this->Image($this->headerImage, 10, 5, 190, 40); // fit top area only
        }

        // Move below header
        $this->SetY(50);

        // Report Title
        $this->SetFont('Arial','B',13);
        $this->Cell(0,10,'ACQUISITION TIMELINE REPORT',0,1,'C');

        $this->SetFont('Arial','',11);
        $this->Cell(0,8,'Information and Communications Technology Services',0,1,'C');
        $this->Ln(4);

        // Table header
        $this->SetFont('Arial','B',9);
        foreach($this->headers as $i=>$h){
            $this->Cell($this->widths[$i],9,$h,1,0,'C');
        }
        $this->Ln();
    }

    function Footer() {
        // Motto + page number
        $this->SetY(-20);
        $this->SetFont('Arial','I',9);
        $this->SetTextColor(255,87,87);
        $this->Cell(0,8,'Leading Innovations, Transforming Lives, Building the Nation',0,1,'C');

        $this->SetFont('Arial','I',8);
        $this->SetTextColor(0);
        $this->Cell(0,5,'Page '.$this->PageNo(),0,0,'C');
    }
}

// === Create PDF ===
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',8);

$widths = $pdf->widths;
$grandTotal = 0;

// === Data Query ===
$sql = "
    SELECT date_acquired, 'Desktop' AS type, asset_tag, department_office AS department, assigned_person, location, unit_price
    FROM desktop
    UNION ALL
    SELECT date_acquired, 'Laptop' AS type, asset_tag, department, assigned_person, location, unit_price
    FROM laptops
    UNION ALL
    SELECT date_acquired, 'Printer' AS type, asset_tag, department, assigned_person, location, unit_price
    FROM printers
    UNION ALL
    SELECT date_acquired, 'Switch' AS type, asset_tag, department, assigned_person, location, unit_price
    FROM switch
    UNION ALL
    SELECT date_acquired, 'Telephone' AS type, asset_tag, department, assigned_person, location, unit_price
    FROM telephone
    ORDER BY date_acquired ASC
";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    $cost = (float)($row['unit_price'] ?? 0);
    $grandTotal += $cost;

    $pdf->Cell($widths[0],8,$row['date_acquired'] ?: '-',1,0,'C');
    $pdf->Cell($widths[1],8,$row['type'],1,0,'C');
    $pdf->Cell($widths[2],8,$row['asset_tag'],1,0,'L');
    $pdf->Cell($widths[3],8,$row['department'] ?: '-',1,0,'C');
    $pdf->Cell($widths[4],8,$row['assigned_person'] ?: '-',1,0,'C');
    $pdf->Cell($widths[5],8,$row['location'] ?: '-',1,0,'C');
    $pdf->Cell($widths[6],8,$cost ? number_format($cost,2) : '-',1,1,'R');
}

// === Grand Total ===
$pdf->SetFont('Arial','B',9);
$pdf->Cell(array_sum($widths)-$widths[6],10,'GRAND TOTAL',1,0,'R');
$pdf->Cell($widths[6],10,number_format($grandTotal,2),1,1,'R');

// Output
$pdf->Output('I','Acquisition_Timeline_Report.pdf');
?>
