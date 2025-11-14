<?php
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

// --- Filters from modal form ---
$date_from = $_POST['date_from'] ?? '';
$date_to   = $_POST['date_to'] ?? '';
$department = $_POST['department_id'] ?? '';       // now posts department name
$equipment_category = $_POST['equipment_category'] ?? ''; // now posts category name

class PDF extends FPDF {
    public $headers = ['ID','Type','Asset Tag','Property/Equip','Department','Assigned To','Location','Cost','Date Acquired'];
    public $widths;
    private $headerImage;

    function __construct($orientation='L',$unit='mm',$size='A4') {
        parent::__construct($orientation,$unit,$size);
        $usableWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        // Adjusted ratios for landscape orientation (more space available)
        $ratios = [0.05,0.09,0.15,0.15,0.12,0.15,0.12,0.09,0.08];
        $this->widths = array_map(fn($r)=>$r*$usableWidth,$ratios);

        // Path to letterhead image
        $this->headerImage = __DIR__ . '/../header.png';
    }

    function Header(){
        // Show BatStateU header
        if(file_exists($this->headerImage)){
            $this->Image($this->headerImage, 10, 5, 277, 40); // fits landscape width
        }

        // Move cursor below header
        $this->SetY(50);

        // Title Section
        $this->SetFont('Arial','B',13);
        $this->Cell(0,10,'COMPLETE INVENTORY REPORT',0,1,'C');

        $this->SetFont('Arial','',11);
        $this->Cell(0,8,'Information and Communications Technology Services',0,1,'C');
        $this->Ln(4);

        // Table header
        $this->SetFont('Arial','B',8.5);
        foreach($this->headers as $i=>$h){
            $this->Cell($this->widths[$i],9,$h,1,0,'C');
        }
        $this->Ln();
    }

    function Footer(){
        // Motto and page number
        $this->SetY(-20);
        $this->SetFont('Arial','I',9);
        $this->SetTextColor(255,87,87);
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
$grandTotal = 0;

// --- Table mappings ---
$tables = [
    "desktop"     => ["Desktop","department_office"],
    "laptops"     => ["Laptop","department"],
    "printers"    => ["Printer","department"],
    "switch"      => ["Switch","department"],
    "telephone"   => ["Telephone","department"],
    "accesspoint" => ["AccessPt","department"]
];

foreach($tables as $table => [$type,$deptField]){
    $sql = "SELECT id, asset_tag, property_equipment, $deptField AS department,
                   assigned_person, location, unit_price, date_acquired
            FROM $table WHERE 1=1";

    // --- Apply filters ---
    if(!empty($date_from)){
        $sql .= " AND date_acquired >= '".$conn->real_escape_string($date_from)."'";
    }
    if(!empty($date_to)){
        $sql .= " AND date_acquired <= '".$conn->real_escape_string($date_to)."'";
    }
    if(!empty($department)){
        $sql .= " AND $deptField = '".$conn->real_escape_string($department)."'";
    }
    if(!empty($equipment_category)){
        $sql .= " AND property_equipment = '".$conn->real_escape_string($equipment_category)."'";
    }

    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()){
        $pdf->Cell($widths[0],8,$row['id'],1,0,'C');
        $pdf->Cell($widths[1],8,$type,1,0,'C');
        $pdf->Cell($widths[2],8,$row['asset_tag'],1,0,'L');
        $pdf->Cell($widths[3],8,$row['property_equipment'],1,0,'C');
        $pdf->Cell($widths[4],8,$row['department'],1,0,'C');
        $pdf->Cell($widths[5],8,$row['assigned_person'],1,0,'C');
        $pdf->Cell($widths[6],8,$row['location'],1,0,'C');
        $cost = $row['unit_price'] ? (float)$row['unit_price'] : 0;
        $grandTotal += $cost;
        $pdf->Cell($widths[7],8,$cost ? number_format($cost,2) : '-',1,0,'R');
        $pdf->Cell($widths[8],8,$row['date_acquired'] ?: '-',1,1,'C');
    }
}

// --- Grand Total ---
$pdf->SetFont('Arial','B',9);
$pdf->Cell(array_sum($widths)-($widths[7]+$widths[8]),10,'GRAND TOTAL',1,0,'R');
$pdf->Cell($widths[7],10,chr(8369).number_format($grandTotal,2),1,0,'R');
$pdf->Cell($widths[8],10,'',1,1,'C');

$pdf->Output('I','Complete_Inventory_Report.pdf');
?>
