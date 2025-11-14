<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

class PDF extends FPDF {
    public $headers = ['Equipment','Type','Department','Status','Start Date','End Date','Task/Description'];
    public $widths;
    private $headerImage;

    function __construct($orientation='P',$unit='mm',$size='A4') {
        parent::__construct($orientation,$unit,$size);

        // Compute usable width
        $usableWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        // Adjusted ratios to fit portrait layout
        $ratios = [0.20,0.08,0.15,0.10,0.12,0.12,0.23];
        $this->widths = array_map(fn($r)=>$r*$usableWidth,$ratios);

        // Path to header image
        $this->headerImage = __DIR__ . '/../header.png';
    }

    function Header() {
        // Header image (BatStateU letterhead)
        if(file_exists($this->headerImage)){
            $this->Image($this->headerImage, 10, 5, 190, 40);
        }

        // Move below image
        $this->SetY(50);

        // Title section
        $this->SetFont('Arial','B',13);
        $this->Cell(0,10,'MAINTENANCE & STATUS REPORT',0,1,'C');

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
        // Footer motto + page number
        $this->SetY(-20);
        $this->SetFont('Arial','I',9);
        $this->SetTextColor(255,87,87);
        $this->Cell(0,8,'Leading Innovations, Transforming Lives, Building the Nation',0,1,'C');

        $this->SetFont('Arial','I',8);
        $this->SetTextColor(0);
        $this->Cell(0,5,'Page '.$this->PageNo(),0,0,'C');
    }
}

// --- PDF start ---
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',8);

$widths = $pdf->widths;

// ✅ LEFT JOIN ensures all equipment are listed
$queries = [
    "SELECT e.asset_tag AS equip, 'Desktop' AS type, e.department_office AS department,
            mr.status, mr.start_date, mr.end_date, mr.description
     FROM desktop e
     LEFT JOIN maintenance_records mr ON mr.equipment_id=e.id AND mr.equipment_type='desktop'",

    "SELECT e.asset_tag AS equip, 'Laptop' AS type, e.department AS department,
            mr.status, mr.start_date, mr.end_date, mr.description
     FROM laptops e
     LEFT JOIN maintenance_records mr ON mr.equipment_id=e.id AND mr.equipment_type='laptop'",

    "SELECT e.asset_tag AS equip, 'Printer' AS type, e.department AS department,
            mr.status, mr.start_date, mr.end_date, mr.description
     FROM printers e
     LEFT JOIN maintenance_records mr ON mr.equipment_id=e.id AND mr.equipment_type='printer'",

    "SELECT e.asset_tag AS equip, 'Switch' AS type, e.department AS department,
            mr.status, mr.start_date, mr.end_date, mr.description
     FROM switch e
     LEFT JOIN maintenance_records mr ON mr.equipment_id=e.id AND mr.equipment_type='switch'",

    "SELECT e.asset_tag AS equip, 'Telephone' AS type, e.department AS department,
            mr.status, mr.start_date, mr.end_date, mr.description
     FROM telephone e
     LEFT JOIN maintenance_records mr ON mr.equipment_id=e.id AND mr.equipment_type='telephone'"
];

// === Populate rows ===
foreach($queries as $sql){
    $result = $conn->query($sql);
    while($row=$result->fetch_assoc()){
        $pdf->Cell($widths[0],8,$row['equip'] ?: '-',1,0,'L');
        $pdf->Cell($widths[1],8,$row['type'],1,0,'C');
        $pdf->Cell($widths[2],8,$row['department'] ?: '-',1,0,'C');
        $pdf->Cell($widths[3],8,$row['status'] ?: 'No Record',1,0,'C');
        $pdf->Cell($widths[4],8,$row['start_date'] ?: '-',1,0,'C');
        $pdf->Cell($widths[5],8,$row['end_date'] ?: '-',1,0,'C');
        $pdf->Cell($widths[6],8,$row['description'] ?: '-',1,1,'L');
    }
}

// === Output ===
$pdf->Output('I','Maintenance_Status_Report.pdf');
?>
