<?php
// Include FPDF library
require '../../includes/fpdf/fpdf.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

// Check if user is logged in
if (!isLoggedIn()) {
    die('Access denied. Please log in.');
}

// Log the action
$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "PREVENTIVE MAINTENANCE CHECKLIST");

// Get form data from POST
$office = $_POST['office'] ?? '';
$fy = $_POST['fy'] ?? date('Y');
$effectivity_date = $_POST['effectivity_date'] ?? date('Y-m-d');
$equipment_types = $_POST['equipment_type'] ?? [];
$frequencies = $_POST['frequency'] ?? [];
$equipment_tags_json = $_POST['equipment_tags'] ?? '[]';
$activities_data_json = $_POST['activities_data'] ?? '{}';
$remarks = $_POST['remarks'] ?? [];
// Auto-generate conducted_by - always use Ronald C. Tud
$conducted_by = 'Ronald C. Tud';
$conducted_date = $_POST['conducted_date'] ?? date('Y-m-d');
$verified_by = $_POST['verified_by'] ?? '';
$verified_date = $_POST['verified_date'] ?? date('Y-m-d');

// Corrective action data
$corrective_dates = $_POST['corrective_date'] ?? [];
$corrective_actions = $_POST['corrective_action'] ?? [];
$corrective_offices = $_POST['corrective_office'] ?? [];
$corrective_accomplished = $_POST['corrective_accomplished'] ?? [];
$corrective_remarks = $_POST['corrective_remarks'] ?? [];

// Parse JSON data
$equipment_tags = json_decode($equipment_tags_json, true) ?? [];
$activities_data = json_decode($activities_data_json, true) ?? [];

// Format dates
$effectivity_date_formatted = date('M d, Y', strtotime($effectivity_date));
$conducted_date_formatted = date('M d, Y', strtotime($conducted_date));
$verified_date_formatted = date('M d, Y', strtotime($verified_date));

// Reference number and revision
$example_reference_num = 'BatStateU-DOC-AF-05';
$example_revision_no = '01'; 

// Helper function to truncate text to fit cell width
function truncateText($pdf, $text, $maxWidth) {
    $textWidth = $pdf->GetStringWidth($text);
    if ($textWidth <= $maxWidth) {
        return $text;
    }
    // Truncate text to fit
    while ($textWidth > $maxWidth && strlen($text) > 0) {
        $text = substr($text, 0, -1);
        $textWidth = $pdf->GetStringWidth($text);
    }
    return $text;
}

// Instantiate FPDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetFont('Times', '', 9); 
$pageWidth = 297 - 20; // 277mm

// Header (Logo, Ref, Dates)
$pdf->SetFont('Times', 'B', 9); 
$headerHeight = 15;
$logoWidth = 25;
$refWidth = 101;
$revWidth = 50;
$dateWidth = $pageWidth - $logoWidth - $refWidth; // 100

$x = $pdf->GetX();
$y = $pdf->GetY();

$logoPath = __DIR__ . '/../../images/bsutneu.png'; // Absolute path to logo

$pdf->Rect($x, $y, $logoWidth, $headerHeight);

if (file_exists($logoPath)) {
    // Image(file, x, y, width, height)
    $pdf->Image($logoPath, $x + 5, $y, 15, 0);
} else {
    // Fallback text if logo is not found
    $pdf->SetFont('Times', 'B', 8);
    $pdf->Cell($logoWidth, $headerHeight, 'Logo Missing', 0, 0, 'C');
}

$pdf->SetXY($x + $logoWidth, $y);

// Reference Number cell
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell($refWidth, $headerHeight, ' Reference No.: ' . $example_reference_num, 1, 0, 'L');

// Effectivity/Revision Date box
$pdf->Cell($refWidth, $headerHeight, ' Effectivity Date: ' . $effectivity_date_formatted, 1, 0, 'L');
$pdf->Cell($revWidth, $headerHeight, ' Revision No: ' . $example_revision_no, 1, 1, 'L');

/*
    Depcrecated na code block ito wag niyo nalang pansining
*/
// $pdf->Rect($x + $logoWidth + $refWidth, $y, $dateWidth, $headerHeight);
// $pdf->SetXY($x + $logoWidth + $refWidth, $y);
// $pdf->Cell($dateWidth, $headerHeight / 2, ' Effectivity Date: ' . $example_effectivity_date, 0, 2, 'L');
// $pdf->SetXY($x + $logoWidth + $refWidth, $y + ($headerHeight / 2));
// $pdf->Cell($dateWidth, $headerHeight / 2, ' Revision No.: ' . $example_revision_no, 'T', 1, 'L');

// Nandito yung Title
$pdf->SetFont('Times', 'B', 12); 
$pdf->Cell($pageWidth, 8, 'PREVENTIVE MAINTENANCE CHECKLIST AND CORRECTIVE ACTION RECORD', 1, 1, 'C');

// Office/College & FY
$pdf->SetFont('Times', '', 9); 
$sec3Height = 8;
$officeWidth = 238.5;
$fyWidth = $pageWidth - $officeWidth; // 38.5
$pdf->Cell($officeWidth, $sec3Height, ' Office/College: ' . $office, 1, 0, 'L');
$pdf->Cell($fyWidth, $sec3Height, ' FY: ' . $fy, 1, 1, 'L');


// Type of Equipment & Frequency
$freqWidth = 64; 
$typeWidth = $pageWidth - $freqWidth; // 213
$sec4Row1Height = 5;
$sec4Row2Height = 11;
$totalHeight = $sec4Row1Height + $sec4Row2Height; // 16mm

$x = $pdf->GetX();
$y = $pdf->GetY();

// Draw Boxes
$pdf->Rect($x, $y, $typeWidth, $sec4Row1Height);
$pdf->Rect($x, $y + $sec4Row1Height, $typeWidth, $sec4Row2Height);
$pdf->Rect($x + $typeWidth, $y, $freqWidth, $totalHeight);

// Populate "Type" section
$pdf->SetXY($x + 2, $y + 0.5);
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell($typeWidth - 4, $sec4Row1Height, 'Tick appropriate box with (/) if checked item is ok. Put an (x) mark if item is not okay.', 0, 2, 'L');

$pdf->SetXY($x + 2, $y + $sec4Row1Height);
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell(45, 5, 'TYPE OF EQUIPMENT/ITEM', 0, 0, 'L');

// Row 2, line 1 of checkboxes - Use actual form data
$pdf->SetFont('Times', '', 9); 
$vehicle_checked = in_array('Vehicle', $equipment_types) ? 'X' : '';
$acu_checked = in_array('ACU', $equipment_types) ? 'X' : '';
$ict_checked = in_array('ICT Equipment', $equipment_types) ? 'X' : '';
$medical_checked = in_array('Medical/Dental Equipment', $equipment_types) ? 'X' : '';

$pdf->Cell(5, 5, $vehicle_checked, 1, 0, 'C');
$pdf->Cell(20, 5, ' Vehicle', 0, 0);
$pdf->Cell(5, 5, $acu_checked, 1, 0, 'C');
$pdf->Cell(20, 5, ' ACU', 0, 0);
$pdf->Cell(5, 5, $ict_checked, 1, 0, 'C'); 
$pdf->Cell(35, 5, ' ICT Equipment', 0, 0);
$pdf->Cell(5, 5, $medical_checked, 1, 0, 'C');
$pdf->Cell(40, 5, ' Medical/Dental Equipment', 0, 2); 

// Row 2, line 2 of checkboxes
$building_checked = in_array('Building', $equipment_types) ? 'X' : '';
$emu_checked = in_array('EMU', $equipment_types) ? 'X' : '';
$lab_checked = in_array('Laboratory Equipment', $equipment_types) ? 'X' : '';
$others_checked = in_array('Others', $equipment_types) ? 'X' : '';
$others_text = $_POST['equipment_type_other'] ?? '';

$pdf->SetX($x + 2 + 45); 
$pdf->Cell(5, 5, $building_checked, 1, 0, 'C');
$pdf->Cell(20, 5, ' Building', 0, 0);
$pdf->Cell(5, 5, $emu_checked, 1, 0, 'C');
$pdf->Cell(20, 5, ' EMU', 0, 0);
$pdf->Cell(5, 5, $lab_checked, 1, 0, 'C');
$pdf->Cell(35, 5, ' Laboratory Equipment', 0, 0);
$pdf->Cell(5, 5, $others_checked, 1, 0, 'C');
$others_label = $others_text ? ' Others, specify: ' . $others_text : ' Others, specify: ____________';
$pdf->Cell(40, 5, $others_label, 0, 1);

// Populate natin yung "Frequency" box
$freqBoxX = $x + $typeWidth;
$pdf->SetXY($freqBoxX + 2, $y + 1);
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell($freqWidth - 4, 5, 'FREQUENCY', 0, 2, 'L');

$pdf->SetFont('Times', '', 9); 
$monthly_checked = in_array('Monthly', $frequencies) ? 'X' : '';
$quarterly_checked = in_array('Quarterly', $frequencies) ? 'X' : '';
$semi_checked = in_array('Semi-annually', $frequencies) ? 'X' : '';
$annually_checked = in_array('Annually', $frequencies) ? 'X' : '';

$pdf->SetX($freqBoxX + 2); 
$pdf->Cell(5, 5, $monthly_checked, 1, 0, 'C');
$pdf->Cell(25, 5, ' Monthly', 0, 0);
$pdf->Cell(5, 5, $quarterly_checked, 1, 0, 'C');
$pdf->Cell(25, 5, ' Quarterly', 0, 2);

$pdf->SetX($freqBoxX + 2);
$pdf->Cell(5, 5, $semi_checked, 1, 0, 'C');
$pdf->Cell(25, 5, ' Semi-annually', 0, 0);
$pdf->Cell(5, 5, $annually_checked, 1, 0, 'C');
$pdf->Cell(25, 5, ' Annually', 0, 1);

// Set Y position correctly for the next section
$pdf->SetY($y + $totalHeight);


// Main Activities Table yung matabang table sa baba lang ng types of equipment
// Define column widths
$col1Width = 65; 
$col2Width = 150;
$col3Width = $pageWidth - $col1Width - $col2Width;

// Calculate sub-column width based on number of equipment (max 12, default to 10 if empty)
$equipment_count = count($equipment_tags);
if ($equipment_count == 0) {
    $equipment_count = 10; // Default to 10 columns if no equipment selected
} elseif ($equipment_count > 12) {
    $equipment_count = 12; // Max 12 columns
}
$subColWidth = $col2Width / $equipment_count;

// Table Header
$pdf->SetFont('Times', 'B', 12); 
$y = $pdf->GetY();
$x = $pdf->GetX();
$headerRowHeight = 5;
$activitiesRowHeight = 10; // Increased to accommodate longer text 

$pdf->Cell($col1Width, $activitiesRowHeight*2, 'ACTIVITIES', 1, 0, 'C');

$pdf->SetXY($x + $col1Width, $y);
$pdf->Cell($col2Width, $activitiesRowHeight, 'EQUIPMENT NO./ITEMS LOCATION', 1, 2, 'C');
$pdf->SetX($x + $col1Width);

// Sub-headers - Use actual equipment tags
$pdf->SetFont('Times', 'B', 5); 
$pdf->SetFillColor(211, 211, 211); 
// Truncate equipment codes to fit within cell width (with 1mm padding on each side)
$maxTextWidth = $subColWidth - 2;

// Display equipment tags (up to 12)
for ($i = 0; $i < $equipment_count; $i++) {
    if ($i < count($equipment_tags)) {
        $equipment_tag = truncateText($pdf, $equipment_tags[$i], $maxTextWidth);
        $pdf->Cell($subColWidth, $activitiesRowHeight, $equipment_tag, 1, 0, 'C', true);
    } else {
        // Empty headers for remaining columns
        $pdf->Cell($subColWidth, $activitiesRowHeight, '', 1, 0, 'C', true);
    }
}


$pdf->SetXY($x + $col1Width + $col2Width, $y);
$pdf->SetFont('Times', 'B', 12); 
$pdf->Cell($col3Width, $activitiesRowHeight*2, 'REMARKS', 1, 1, 'C');

// Table Data
$activities = [
    ' 1. Perform virus checkup',
    ' 2. Update Software/Firmware',
    ' 3. Uninstall unused software',
    ' 4. Run Disk Cleanup',
    ' 5. Defrag Hard Drive',
    ' 6. House Keeping',
    '    a) monitor, mouse, and keyboard',
    '    b) printer, scanner',
    '    c) router, switch, & access point',
    '    d) IP Phone, CCTV, View board'
];

$pdf->SetFont('Times', '', 9); 
$cellHeight = 6;
$activity_keys = ['1', '2', '3', '4', '5', '6', '6a', '6b', '6c', '6d'];

for ($i = 0; $i < count($activities); $i++) {
    $pdf->Cell($col1Width, $cellHeight, $activities[$i], 1, 0, 'L');
    
    // Get activity key for this row
    $activity_key = $activity_keys[$i] ?? ($i + 1);
    
    // Display checkmarks for each equipment
    for ($j = 0; $j < $equipment_count; $j++) {
        $mark = '';
        if ($j < count($equipment_tags)) {
            // Check if there's data for this activity and equipment
            if (isset($activities_data[$activity_key][$j])) {
                if ($activities_data[$activity_key][$j] == 'ok') {
                    $mark = '/'; // Use '/' as per instructions
                } else {
                    $mark = 'X';
                }
            }
        }
        
        // Set font to bold for both checkmark and X to make them more visible
        if ($mark != '') {
            $pdf->SetFont('Times', 'B', 9);
        } else {
            $pdf->SetFont('Times', '', 9);
        }
        
        $pdf->Cell($subColWidth, $cellHeight, $mark, 1, 0, 'C');
    }
    
    // Reset font back to normal after equipment cells
    $pdf->SetFont('Times', '', 9);
    
    // Remarks column
    $remark_text = isset($remarks[$i]) ? substr($remarks[$i], 0, 50) : ''; // Limit remark length
    $pdf->Cell($col3Width, $cellHeight, $remark_text, 1, 1, 'L');
}

// Dito yung Signatories na dalawang row
// Row 1: Conducted by row
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell(65, 10, ' Conducted by:', 1, 0, 'L');
$pdf->SetFont('Times', '', 9); 
$pdf->Cell(75, 10, $conducted_by, 1, 0, 'C');
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell(20, 10, 'Date: ', 1, 0, 'R');
$pdf->SetFont('Times', '', 9);
$pdf->Cell(55, 10, $conducted_date_formatted, 1, 0, 'C');
$pdf->Cell($pageWidth - 65 - 75 - 20 - 55, 10, '', 1, 1, 'C'); // Dynamic width for signature

// Verified by row
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell(65, 10, ' Verified by:', 1, 0, 'L');
$pdf->SetFont('Times', '', 9);
$pdf->Cell(75, 10, $verified_by, 1, 0, 'C');
$pdf->SetFont('Times', 'B', 9); 
$pdf->Cell(20, 10, ' Date: ', 1, 0, 'R');
$pdf->SetFont('Times', '', 9);
$pdf->Cell(55, 10, $verified_date_formatted, 1, 0, 'C');
$pdf->Cell($pageWidth - 65 - 75 - 20 - 55, 10, '', 1, 1, 'C'); // Dynamic width for signature


//  This is the Corrective Action Table
// Headers
$pdf->SetFont('Times', 'B', 9); 
$pdf->SetFillColor(211, 211, 211);
$pdf->Cell($pageWidth, 6, '', 1, 1, 'C', true);

$headerHeight = 6;
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(30, $headerHeight, 'Date', 1, 0, 'C', true);
$pdf->Cell(110, $headerHeight, 'Corrective Action', 1, 0, 'C', true);
$pdf->Cell(40, $headerHeight, 'Office Responsible', 1, 0, 'C', true);
$pdf->Cell(40, $headerHeight, 'Date Accomplished', 1, 0, 'C', true);
$pdf->Cell(57, $headerHeight, 'Remarks', 1, 1, 'C', true);

// Data Rows - Use actual corrective action data
$pdf->SetFont('Times', '', 9); 
$blankRowHeight = 6;
$corrective_count = max(count($corrective_dates), count($corrective_actions), count($corrective_offices), count($corrective_accomplished), count($corrective_remarks));
$max_rows = max(5, $corrective_count);

for ($i = 0; $i < $max_rows; $i++) {
    $date_val = isset($corrective_dates[$i]) && !empty($corrective_dates[$i]) ? date('M d, Y', strtotime($corrective_dates[$i])) : '';
    $action_val = isset($corrective_actions[$i]) ? substr($corrective_actions[$i], 0, 60) : '';
    $office_val = isset($corrective_offices[$i]) ? substr($corrective_offices[$i], 0, 30) : '';
    $accomplished_val = isset($corrective_accomplished[$i]) && !empty($corrective_accomplished[$i]) ? date('M d, Y', strtotime($corrective_accomplished[$i])) : '';
    $remark_val = isset($corrective_remarks[$i]) ? substr($corrective_remarks[$i], 0, 40) : '';
    
    $pdf->Cell(30, $blankRowHeight, $date_val, 1, 0, 'C');
    $pdf->Cell(110, $blankRowHeight, $action_val, 1, 0, 'L');
    $pdf->Cell(40, $blankRowHeight, $office_val, 1, 0, 'C');
    $pdf->Cell(40, $blankRowHeight, $accomplished_val, 1, 0, 'C');
    $pdf->Cell(57, $blankRowHeight, $remark_val, 1, 1, 'L');
}

// Finally output the pdf conversion
$pdf->Output('I', 'Preventive_Maintenance_Checklist.pdf');
?>
