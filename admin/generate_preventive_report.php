<?php
require_once __DIR__ . '/../includes/pdf_template.php';
require_once '../includes/session.php';
require_once '../includes/db.php';
include '../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "PREVENTIVE MAINTENANCE PLAN");

// Get data from POST
$office_college = $_POST['office_college'] ?? 'ICT Services';
$reference_no = $_POST['reference_no'] ?? 'BatStateU-DOC-AF-04';
$effectivity_date_raw = $_POST['effectivity_date'] ?? '';
$revision_no = $_POST['revision_no'] ?? '02';
$fy = $_POST['fy'] ?? '2025';
$prepared_by = $_POST['prepared_by'] ?? '';
$prepared_date_raw = $_POST['prepared_date'] ?? '';
$reviewed_by = $_POST['reviewed_by'] ?? '';
$reviewed_date_raw = $_POST['reviewed_date'] ?? '';
$approved_by = $_POST['approved_by'] ?? '';
$approved_date_raw = $_POST['approved_date'] ?? '';

// Format dates
function formatDate($dateStr) {
    if (empty($dateStr)) return '';
    $date = DateTime::createFromFormat('Y-m-d', $dateStr);
    return $date ? $date->format('M d, Y') : $dateStr;
}

$effectivity_date = formatDate($effectivity_date_raw);
$prepared_date = formatDate($prepared_date_raw);
$reviewed_date = formatDate($reviewed_date_raw);
$approved_date = formatDate($approved_date_raw);

// Get equipment data
$equipment_data = json_decode($_POST['equipment_data'] ?? '{}', true);
$categories = json_decode($_POST['categories'] ?? '[]', true);

// Build items array from equipment data
$items = [];
foreach ($categories as $cat) {
    $categoryKey = is_string($cat['id']) ? $cat['id'] : strtolower(str_replace(' ', '', $cat['name']));
    $equipment = $equipment_data[$categoryKey] ?? [];
    
    foreach ($equipment as $eq) {
        $name = $eq['name'] ?? '';
        $schedule = $eq['schedule'] ?? [];
        
        // Convert schedule object to array format for each month
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $itemSchedule = [];
        foreach ($months as $m) {
            $itemSchedule[$m] = $schedule[$m] ?? '';
        }
        
        $items[] = [
            'description' => $name,
            'schedule' => $itemSchedule
        ];
    }
}

class PDF extends TemplatePDF {
    public $reference_no = '';
    public $effectivity_date = '';
    public $revision_no = '';
    public $office_college = '';
    public $fy = '';
    private $firstPageHeaderDone = false;
    
    function Header() {
        // Only show full header on first page
        if (!$this->firstPageHeaderDone) {
            // Header row height - match the image
            $headerHeight = 20;
            $yStart = 10;
            $xStart = 10;
            
            // Logo at top left - Use official BSU logo
            $logoPaths = [
                __DIR__ . '/../images/bsutneu.png',
                __DIR__ . '/../images/Batangas-State-Univer LOGO.jpg',
                __DIR__ . '/../images/BSU.jpg',
                __DIR__ . '/../images/Ict logs.png',
                __DIR__ . '/../images/logo.png'
            ];
            
            $logoPath = null;
            foreach ($logoPaths as $path) {
                if (file_exists($path)) {
                    $logoPath = $path;
                    break;
                }
            }
            
            // Draw logo cell with border - same height as other cells
            $logoCellWidth = 25;
            $this->SetXY($xStart, $yStart);
            $this->Cell($logoCellWidth, $headerHeight, '', 1, 0, 'C');
            
            // Place logo image centered vertically in the cell
            if ($logoPath) {
                $logoSize = 20;
                $logoX = $xStart + ($logoCellWidth - $logoSize) / 2;
                $logoY = $yStart + ($headerHeight - $logoSize) / 2;
                $this->Image($logoPath, $logoX, $logoY, $logoSize);
            }
            
            // Calculate remaining width for text cells
            $pageWidth = $this->GetPageWidth() - 20; // Total usable width
            $remainingWidth = $pageWidth - $logoCellWidth;
            
            // Divide remaining width into 3 equal segments (Reference No., Effectivity Date, Revision No.)
            $segmentWidth = $remainingWidth / 3;
            
            // Top header row: Reference No. + Effectivity Date + Revision No.
            $xAfterLogo = $xStart + $logoCellWidth;
            $this->SetXY($xAfterLogo, $yStart);
            
            // Reference No. segment - label and value together, center-aligned
            $this->SetFont('Arial','',9);
            $refText = 'Reference No.: ' . $this->reference_no;
            $this->Cell($segmentWidth, $headerHeight, $refText, 1, 0, 'C');
            
            // Effectivity Date segment - label and value together, center-aligned
            $effText = 'Effectivity Date: ' . $this->effectivity_date;
            $this->Cell($segmentWidth, $headerHeight, $effText, 1, 0, 'C');
            
            // Revision No. segment - label and value together, center-aligned
            $revText = 'Revision No.: ' . $this->revision_no;
            $this->Cell($segmentWidth, $headerHeight, $revText, 1, 1, 'C');
            
            // Main title: PREVENTIVE MAINTENANCE PLAN - directly connected, no spacing
            $this->SetFont('Arial','B',13);
            $this->Cell(0, 12, 'PREVENTIVE MAINTENANCE PLAN', 1, 1, 'C');

            // Office/College and FY row - directly connected, no spacing
            $this->SetFont('Arial','',10);
            $this->Cell(30, 6, 'Office/College', 1, 0, 'C');
            $this->Cell(110, 6, $this->office_college, 1, 0, 'C');
            $this->Cell(10, 6, 'FY:', 1, 0, 'C');
            $this->Cell(40, 6, $this->fy, 1, 1, 'C');

            $this->firstPageHeaderDone = true;
        }
        
        // Legend and page number section - show on every page before table
        if ($this->PageNo() > 1 || $this->firstPageHeaderDone) {
            // Horizontal line
            $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());
            $this->Ln(3);
            
            // Legend - centered
            $this->SetFont('Arial','I',9);
            $this->Cell(0, 5, 'Legend: M = Monthly, Q = Quarterly, SA = Semi-Annually', 0, 1, 'C');
            
            // Horizontal line
            $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());
            $this->Ln(2);
            
            // Page number - centered (use {nb} placeholder which will be replaced)
            $this->SetFont('Arial','I',8);
            $this->Cell(0, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'C');
            
            // Horizontal line
            $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());
            $this->Ln(3);
        }
        
        // Table header: Item and months - show on every page
        $this->SetFont('Arial','B',9);
        $this->Cell(40, 7, 'Item', 1, 0, 'C');
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        foreach ($months as $m) {
            $this->Cell(12.5, 7, $m, 1, 0, 'C');
        }
        $this->Ln();
    }
    
    function Footer() {
        // Footer is handled in Header() with legend section
        // No additional footer needed
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->reference_no = $reference_no;
$pdf->effectivity_date = $effectivity_date;
$pdf->revision_no = $revision_no;
$pdf->office_college = $office_college;
$pdf->fy = $fy;
$pdf->setTitleText(''); // Don't use parent title, we're customizing the header
$pdf->AliasNbPages(); // Enable page numbering
$pdf->SetAutoPageBreak(true, 15); // Auto page break with 15mm margin for footer
$pdf->AddPage();

// Add legend and page number on first page (after header, before table)
$pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
$pdf->Ln(3);
$pdf->SetFont('Arial','I',9);
$pdf->Cell(0, 5, 'Legend: M = Monthly, Q = Quarterly, SA = Semi-Annually', 0, 1, 'C');
$pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0, 5, 'Page ' . $pdf->PageNo() . ' of {nb}', 0, 1, 'C');
$pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
$pdf->Ln(3);

// --- Table Content ---
$pdf->SetFont('Arial','',9);
$rowHeight = 6.5;
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

foreach ($items as $item) {
    // Check if we need a new page before adding the row
    // Account for row height + table header (7mm) + legend section that will be added on new page
    if ($pdf->GetY() + $rowHeight > $pdf->GetPageHeight() - 15) {
        $pdf->AddPage();
    }
    
    $pdf->Cell(40, $rowHeight, $item['description'], 1, 0);
    foreach ($months as $m) {
        $mark = $item['schedule'][$m] ?? '';
        $pdf->Cell(12.5, $rowHeight, $mark, 1, 0, 'C');
    }
    $pdf->Ln();
}

// --- Signatories Section ---
$pdf->SetFont('Arial','',8);
$pageWidth   = $pdf->GetPageWidth() - 20;
$colWidth    = $pageWidth / 2;
$blockHeight = 38; 
$marginLeft  = 10;
$yStart      = $pdf->GetY() + 2;

// --- Row 1: Prepared / Reviewed ---
$pdf->Rect($marginLeft, $yStart, $colWidth, $blockHeight);
$pdf->Rect($marginLeft + $colWidth, $yStart, $colWidth, $blockHeight);

// Prepared By (Left)
$pdf->SetXY($marginLeft + 2, $yStart + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Prepared by:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + 2);
$pdf->Cell($colWidth - 4, 8, '', 0, 1, 'L'); // Space for signature
$preparedName = $prepared_by ? strtoupper($prepared_by) : '';
$pdf->SetFont('Arial','B',8);
$pdf->SetX($marginLeft + 2);
$pdf->Cell($colWidth - 4, 4, $preparedName, 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + 2);
$pdf->Cell($colWidth - 4, 4, 'ICT Services Staff', 0, 1, 'L');
$pdf->SetX($marginLeft + 2);
$preparedDateText = $prepared_date ? $prepared_date : '';
$pdf->Cell($colWidth - 4, 4, 'Date Signed: ' . $preparedDateText, 0, 1, 'L');

// Reviewed By (Right)
$pdf->SetXY($marginLeft + $colWidth + 2, $yStart + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Reviewed by:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + $colWidth + 2);
$pdf->Cell($colWidth - 4, 8, '', 0, 1, 'L'); // Space for signature
$reviewedName = $reviewed_by ? strtoupper($reviewed_by) : '';
$pdf->SetFont('Arial','B',8);
$pdf->SetX($marginLeft + $colWidth + 2);
$pdf->Cell($colWidth - 4, 4, $reviewedName, 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + $colWidth + 2);
$pdf->Cell($colWidth - 4, 4, 'Head, ICT Services', 0, 1, 'L');
$pdf->SetX($marginLeft + $colWidth + 2);
$reviewedDateText = $reviewed_date ? $reviewed_date : '';
$pdf->Cell($colWidth - 4, 4, 'Date Signed: ' . $reviewedDateText, 0, 1, 'L');

// --- Row 2: Approved / Remarks ---
$yStart2 = $yStart + $blockHeight + 2;
$pdf->Rect($marginLeft, $yStart2, $colWidth, $blockHeight);
$pdf->Rect($marginLeft + $colWidth, $yStart2, $colWidth, $blockHeight);

// Approved By
$pdf->SetXY($marginLeft + 2, $yStart2 + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Approved by:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + 2);
$pdf->Cell($colWidth - 4, 8, '', 0, 1, 'L'); // Space for signature
$approvedName = $approved_by ? strtoupper($approved_by) : '';
$pdf->SetFont('Arial','B',8);
$pdf->SetX($marginLeft + 2);
$pdf->Cell($colWidth - 4, 4, $approvedName, 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + 2);
$pdf->Cell($colWidth - 4, 4, 'Vice Chancellor for Development and External Affairs/', 0, 1, 'L');
$pdf->SetX($marginLeft + 2);
$approvedDateText = $approved_date ? $approved_date : '';
$pdf->Cell($colWidth - 4, 4, 'Date Signed: ' . $approvedDateText, 0, 1, 'L');

// Remarks
$pdf->SetXY($marginLeft + $colWidth + 2, $yStart2 + 2);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($colWidth - 4, 4, "Remarks:", 0, 1, 'L');
$pdf->SetFont('Arial','',8);
$pdf->SetX($marginLeft + $colWidth + 2);
$pdf->Cell($colWidth - 4, 30, '', 0, 1, 'L'); // Blank space for remarks

$pdf->Output('I','Preventive_Maintenance_Plan.pdf');
?>



