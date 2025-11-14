<?php
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

class PDF extends FPDF {
    public $headers = ['ID','Type','Asset Tag','Property/Equip','Department','Assigned To','Location','Cost','Missing Fields'];
    public $widths;
    private $headerImage;

    function __construct($orientation='P', $unit='mm', $size='A4') {
        parent::__construct($orientation, $unit, $size);
        $usableWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        // Adjusted ratios to fit neatly in portrait orientation
        $ratios = [0.05, 0.08, 0.13, 0.12, 0.11, 0.12, 0.12, 0.09, 0.18];
        $this->widths = array_map(fn($r) => $r * $usableWidth, $ratios);

        // Path to BatStateU letterhead
        $this->headerImage = __DIR__ . '/../header.png';
    }

    function Header() {
        // Header image (BatStateU letterhead)
        if (file_exists($this->headerImage)) {
            $this->Image($this->headerImage, 10, 5, 190, 40);
        }

        // Move cursor below image
        $this->SetY(50);

        // Title section
        $this->SetFont('Arial','B',13);
        $this->Cell(0,10,'INCOMPLETE EQUIPMENT RECORDS REPORT',0,1,'C');

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
        // Motto and page number
        $this->SetY(-20);
        $this->SetFont('Arial','I',9);
        $this->SetTextColor(255, 87, 87);
        $this->Cell(0,8,'Leading Innovations, Transforming Lives, Building the Nation',0,1,'C');

        $this->SetFont('Arial','I',8);
        $this->SetTextColor(0);
        $this->Cell(0,5,'Page '.$this->PageNo(),0,0,'C');
    }
}

// --- PDF Start ---
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',8);
$widths = $pdf->widths;

// --- Table mappings ---
$tables = [
    "desktop"     => ["Desktop",   "department_office"],
    "laptops"     => ["Laptop",    "department"],
    "printers"    => ["Printer",   "department"],
    "switch"      => ["Switch",    "department"],
    "telephone"   => ["Telephone", "department"],
    "accesspoint" => ["AccessPt",  "department"]
];

foreach ($tables as $table => [$type, $deptField]) {
    $sql = "SELECT id, asset_tag, property_equipment, $deptField AS department,
                   assigned_person, location, unit_price, date_acquired
            FROM $table";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        // Detect missing fields
        $missing = [];
        if (empty($row['asset_tag'])) $missing[] = 'Asset Tag';
        if (empty($row['property_equipment'])) $missing[] = 'Property/Equip';
        if (empty($row['department'])) $missing[] = 'Department';
        if (empty($row['assigned_person'])) $missing[] = 'Assigned To';
        if (empty($row['location'])) $missing[] = 'Location';
        if (empty($row['unit_price'])) $missing[] = 'Cost';
        if (empty($row['date_acquired'])) $missing[] = 'Date Acquired';

        if (!empty($missing)) {
            $pdf->Cell($widths[0], 8, $row['id'], 1, 0, 'C');
            $pdf->Cell($widths[1], 8, $type, 1, 0, 'C');
            $pdf->Cell($widths[2], 8, $row['asset_tag'] ?: '-', 1, 0, 'C');
            $pdf->Cell($widths[3], 8, $row['property_equipment'] ?: '-', 1, 0, 'C');
            $pdf->Cell($widths[4], 8, $row['department'] ?: '-', 1, 0, 'C');
            $pdf->Cell($widths[5], 8, $row['assigned_person'] ?: '-', 1, 0, 'C');
            $pdf->Cell($widths[6], 8, $row['location'] ?: '-', 1, 0, 'C');
            $pdf->Cell($widths[7], 8, $row['unit_price'] ? number_format($row['unit_price'], 2) : '-', 1, 0, 'R');
            $pdf->Cell($widths[8], 8, implode(', ', $missing), 1, 1, 'L');
        }
    }
}

$pdf->Output('I', 'Incomplete_Inventory_Report.pdf');
?>
