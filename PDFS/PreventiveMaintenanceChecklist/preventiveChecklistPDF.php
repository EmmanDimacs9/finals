<?php
require_once __DIR__ . '/../../includes/fpdf/fpdf.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "PREVENTIVE MAINTENANCE CHECKLIST AND CORRECTIVE ACTION RECORD");

// Get form data
$office = $_POST['office'] ?? '';
$fy = $_POST['fy'] ?? date('Y');
$effectivity_date = $_POST['effectivity_date'] ?? date('Y-m-d');
$equipment_types = $_POST['equipment_type'] ?? [];
$frequencies = $_POST['frequency'] ?? [];
$conducted_by = $_POST['conducted_by'] ?? '';
$conducted_date = $_POST['conducted_date'] ?? date('Y-m-d');
$verified_by = $_POST['verified_by'] ?? '';
$verified_date = $_POST['verified_date'] ?? date('Y-m-d');
$remarks = $_POST['remarks'] ?? [];

// Get equipment tags from form
$equipment_tags_list = [];
if (isset($_POST['equipment_tags']) && !empty($_POST['equipment_tags'])) {
    $equipment_tags_list = json_decode($_POST['equipment_tags'], true);
    if (!is_array($equipment_tags_list)) {
        $equipment_tags_list = [];
    }
}

// Get activities data from form
$activities_data = [];
if (isset($_POST['activities_data']) && !empty($_POST['activities_data'])) {
    $activities_data = json_decode($_POST['activities_data'], true);
    if (!is_array($activities_data)) {
        $activities_data = [];
    }
}

// Also extract from individual activity fields as fallback
foreach ($_POST as $key => $value) {
    if (strpos($key, 'activity_') === 0) {
        $parts = explode('_', $key);
        if (count($parts) >= 3) {
            $activity = $parts[1];
            $eq_index = intval($parts[2]);
            if (!isset($activities_data[$activity])) {
                $activities_data[$activity] = [];
            }
            if ($value === 'ok' || $value === 'not_ok') {
                $activities_data[$activity][$eq_index] = $value;
            }
        }
    }
}

// Get corrective action data
$corrective_dates = $_POST['corrective_date'] ?? [];
$corrective_actions = $_POST['corrective_action'] ?? [];
$corrective_offices = $_POST['corrective_office'] ?? [];
$corrective_accomplished = $_POST['corrective_accomplished'] ?? [];
$corrective_remarks = $_POST['corrective_remarks'] ?? [];

// If no equipment tags provided, use defaults
if (empty($equipment_tags_list)) {
    $equipment_tags_list = ['ICT-LC_S1TSR-SW001', 'ICT-LC_S1TSR-SW002', 'ICT-LC_S1TSR-SW003', 'ICT-LC_S1TSR-SW004', 'ICT-LC_S1TSR-TP001'];
}

class PreventiveChecklistPDF extends FPDF {
    private $logoPath;
    private $effectivity_date;
    
    public function __construct($orientation = 'L', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(false); // Disable auto page break to ensure 1 page
        
        // Try to find logo
        $logoPaths = [
            __DIR__ . '/../../images/bsutneu.png',
            __DIR__ . '/../../images/BSU.jpg',
            __DIR__ . '/../../images/logo.png',
        ];
        foreach ($logoPaths as $path) {
            if (file_exists($path)) {
                $this->logoPath = $path;
                break;
            }
        }
    }
    
    public function setEffectivityDate($date) {
        $this->effectivity_date = $date;
    }
    
    function Header() {
        // ---------------------------
        // TOP HEADER BORDER SECTIONS - EXACT MATCH TO REFERENCE DESIGN
        //----------------------------
        
        // For Landscape: Adjust dimensions (297mm width vs 210mm in portrait)
        // Outer border: 10mm from top, spans header area (reduced height for 1 page fit)
        $outer_x = 10;
        $outer_y = 8;
        $outer_width = 277; // 297 - 20 (margins)
        $outer_height = 22; // Reduced from 25 to fit on 1 page
        
        // Outer border
        $this->Rect($outer_x, $outer_y, $outer_width, $outer_height);
        
        // Left Logo Box (40mm width, full height)
        $this->Rect($outer_x, $outer_y, 40, $outer_height);
        
        // Logo image
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, $outer_x + 5, $outer_y + 5, 30, 15);
        } else {
            // Fallback text
            $this->SetXY($outer_x + 2, $outer_y + 8);
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(36, 3, 'BATANGAS STATE', 0, 1, 'C');
            $this->SetXY($outer_x + 2, $this->GetY());
            $this->Cell(36, 3, 'UNIVERSITY', 0, 1, 'C');
        }
        
        // Reference Number Box (positioned after logo)
        $ref_x = $outer_x + 40;
        $ref_y = $outer_y;
        $ref_box_width = 90;
        $ref_box_height = 11; // Reduced from 12
        $this->Rect($ref_x, $ref_y, $ref_box_width, $ref_box_height);
        $this->SetFont('Arial', '', 9);
        $this->SetXY($ref_x + 2, $ref_y + 3);
        $this->Cell(0, 0, "Reference No.: BatStateU-DOC-AF-05");
        
        // Effective Date Box (next to reference)
        $eff_x = $ref_x + $ref_box_width;
        $eff_box_width = 90;
        $this->Rect($eff_x, $ref_y, $eff_box_width, $ref_box_height);
        $eff_date = $this->effectivity_date ? date('M d, Y', strtotime($this->effectivity_date)) : date('M d, Y');
        $this->SetXY($eff_x + 2, $ref_y + 3);
        $this->Cell(0, 0, "Effectivity Date: " . $eff_date);
        
        // Revision No Box (remaining width)
        $rev_x = $eff_x + $eff_box_width;
        $rev_box_width = $outer_width - ($rev_x - $outer_x);
        $this->Rect($rev_x, $ref_y, $rev_box_width, $ref_box_height);
        $this->SetXY($rev_x + 2, $ref_y + 3);
        $this->Cell(0, 0, "Revision No.: 01");
        
        // Title Row (spans width after logo)
        $title_y = $ref_y + $ref_box_height;
        $title_height = $outer_height - $ref_box_height;
        $title_width = $outer_width - 40;
        $this->Rect($ref_x, $title_y, $title_width, $title_height);
        $this->SetFont('Arial', 'B', 11);
        $this->SetXY($ref_x + 5, $title_y + 4);
        $this->Cell(0, 0, "PREVENTIVE MAINTENANCE CHECKLIST AND CORRECTIVE ACTION RECORD");
    }
    
    function DrawCheckbox($checked = false, $x = null, $y = null, $size = 3.5) {
        if ($x === null) $x = $this->GetX();
        if ($y === null) $y = $this->GetY();
        
        $this->Rect($x, $y, $size, $size);
        if ($checked) {
            $this->SetFont('Arial', 'B', 9);
            $this->SetXY($x + 0.3, $y - 0.3);
            $this->Cell($size, $size, 'X', 0, 0, 'C');
            $this->SetFont('Arial', '', 8);
        }
    }
    
    function DrawCheckmark($x = null, $y = null) {
        if ($x === null) $x = $this->GetX();
        if ($y === null) $y = $this->GetY();
        
        // Draw a slash (/) to indicate checked/ok
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($x + 1, $y - 0.5);
        $this->Cell(3.5, 3.5, '/', 0, 0, 'C');
        $this->SetFont('Arial', '', 8);
    }
}

$pdf = new PreventiveChecklistPDF('L', 'mm', 'A4');
$pdf->setEffectivityDate($effectivity_date);
$pdf->AddPage();

// -------------------------------
// OFFICE / COLLEGE and FY Row - Optimized for 1 page
// -------------------------------
$pdf->SetXY(10, 30);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 7, "Office/College:", 1, 0);
$pdf->Cell(160, 7, $office ?: "SERVER ROOM", 1, 0);
$pdf->Cell(20, 7, "FY", 1, 0);
$pdf->Cell(67, 7, $fy, 1, 1);

// -------------------------------
// TICK APPROPRIATE BOXES
// -------------------------------
$pdf->SetXY(10, 37);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(277, 5, "Tick appropriate box with (/) if checked item is ok. Put an (x) mark if item is not okay.", 1, 1);

// -------------------------------
// TYPE OF EQUIPMENT/ITEM AND FREQUENCY SECTION
// Single bordered rectangle divided vertically - EXACT MATCH TO IMAGE
// -------------------------------
$section_start_x = 10;
$section_start_y = 42;
$section_total_width = 277; // Full width minus margins
$section_height = 10;

// Draw outer border (single rectangle containing both sections)
$pdf->Rect($section_start_x, $section_start_y, $section_total_width, $section_height);

// Draw vertical divider line between TYPE OF EQUIPMENT/ITEM and FREQUENCY
$divider_x = 200; // Position where the divider should be
$pdf->Line($divider_x, $section_start_y, $divider_x, $section_start_y + $section_height);

// LEFT SECTION: TYPE OF EQUIPMENT/ITEM
$equipment_types_array = is_array($equipment_types) ? $equipment_types : [];
$equipment_other = $_POST['equipment_type_other'] ?? '';

// Calculate proper positioning to avoid overlaps - EXACT MATCH IMAGE
$left_padding = 3; // Padding from left border
$right_padding = 5; // Padding before divider (increased to prevent overlap)
$left_start_x = $section_start_x + $left_padding; // 13mm from left border
$left_end_x = $divider_x - $right_padding; // 195mm (5mm before divider at 200mm)
$left_available_width = $left_end_x - $left_start_x; // 182mm

// TYPE OF EQUIPMENT/ITEM label
$pdf->SetXY($left_start_x, 44); // 13mm from left, 2mm from top border
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 0, "TYPE OF EQUIPMENT/ITEM");

// Equipment types in grid layout (4 columns, 2 rows) - EXACT POSITIONING
$types = [
    ['Vehicle', 'ACU', 'ICT Equipment', 'Medical/Dental Equipment'],
    ['Building', 'EMU', 'Laboratory Equipment', 'Others']
];

// Calculate column positions - ensure no overlap with borders
$num_cols = 4;
$col_spacing = 2.5; // Space between columns
$total_col_spacing = $col_spacing * ($num_cols - 1); // 7.5mm total
$checkbox_size = 3;
$label_spacing = 1.5; // Space between checkbox and label
$col_width = ($left_available_width - $total_col_spacing) / $num_cols; // ~43.625mm per column

// Calculate exact X positions for each column to prevent overlaps
$col_positions = [];
for ($i = 0; $i < $num_cols; $i++) {
    $col_positions[$i] = $left_start_x + ($i * $col_width) + ($i * $col_spacing);
}

$checkbox_start_y = 49; // Y position for first row of checkboxes
$row_spacing = 3.5; // Vertical spacing between rows

foreach ($types as $row_index => $row) {
    foreach ($row as $col_index => $type) {
        $checked = in_array($type, $equipment_types_array);
        
        // Use pre-calculated column positions
        $checkbox_x = $col_positions[$col_index];
        $checkbox_y = $checkbox_start_y + ($row_index * $row_spacing);
        
        // Verify position doesn't exceed boundaries
        if ($checkbox_x + $checkbox_size + 25 < $left_end_x) {
            // Draw checkbox
            $pdf->SetXY($checkbox_x, $checkbox_y);
            $pdf->DrawCheckbox($checked, $checkbox_x, $checkbox_y, $checkbox_size);
            
            // Prepare label
            $label = $type;
            if ($type === 'Others') {
                $label = "Others, specify: " . $equipment_other;
            }
            
            // Position label next to checkbox
            $label_x = $checkbox_x + $checkbox_size + $label_spacing;
            $pdf->SetXY($label_x, $checkbox_y);
            $pdf->SetFont('Arial', '', 8);
            
            // Ensure label doesn't exceed column boundary
            $max_label_width = ($col_positions[min($col_index + 1, $num_cols - 1)] ?? $left_end_x) - $label_x - 2;
            $max_chars = floor($max_label_width / 1.2);
            if (strlen($label) > $max_chars && $max_chars > 8) {
                $label = substr($label, 0, $max_chars - 3) . '...';
            }
            $pdf->Cell(0, 0, $label);
        }
    }
}

// RIGHT SECTION: FREQUENCY - EXACT POSITIONING
$freq_left_padding = 5; // Padding after divider (increased to prevent overlap)
$freq_right_padding = 4; // Padding from right border
$freq_start_x = $divider_x + $freq_left_padding; // 205mm (5mm after divider)
$freq_end_x = $section_start_x + $section_total_width - $freq_right_padding; // 283mm (4mm before right border)
$freq_available_width = $freq_end_x - $freq_start_x; // 78mm

$pdf->SetXY($freq_start_x, 44); // 205mm from left, 2mm from top border
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 0, "FREQUENCY");

$frequencies_array = is_array($frequencies) ? $frequencies : [];

// Frequency options (2 columns, 2 rows) - EXACT POSITIONING
$freq_options = [
    ['Monthly', 'Quarterly'],
    ['Semi-annually', 'Annually']
];

// Calculate column positions for frequency - ensure no overlap
$freq_num_cols = 2;
$freq_col_spacing = 2.5; // Space between columns
$freq_total_col_spacing = $freq_col_spacing * ($freq_num_cols - 1); // 2.5mm
$freq_col_width = ($freq_available_width - $freq_total_col_spacing) / $freq_num_cols; // ~37.75mm per column

// Calculate exact X positions for each frequency column
$freq_col_positions = [];
for ($i = 0; $i < $freq_num_cols; $i++) {
    $freq_col_positions[$i] = $freq_start_x + ($i * $freq_col_width) + ($i * $freq_col_spacing);
}

$freq_checkbox_start_y = 49; // Aligned with equipment checkboxes

foreach ($freq_options as $row_index => $row) {
    foreach ($row as $col_index => $freq) {
        $checked = in_array($freq, $frequencies_array);
        
        // Use pre-calculated column positions
        $checkbox_x = $freq_col_positions[$col_index];
        $checkbox_y = $freq_checkbox_start_y + ($row_index * $row_spacing);
        
        // Verify position doesn't exceed boundaries
        if ($checkbox_x + $checkbox_size + 20 < $freq_end_x) {
            // Draw checkbox
            $pdf->SetXY($checkbox_x, $checkbox_y);
            $pdf->DrawCheckbox($checked, $checkbox_x, $checkbox_y, $checkbox_size);
            
            // Position label next to checkbox
            $label_x = $checkbox_x + $checkbox_size + $label_spacing;
            $pdf->SetXY($label_x, $checkbox_y);
            $pdf->SetFont('Arial', '', 8);
            
            // Ensure label doesn't exceed column boundary
            $freq_max_label_width = ($freq_col_positions[min($col_index + 1, $freq_num_cols - 1)] ?? $freq_end_x) - $label_x - 2;
            $freq_max_chars = floor($freq_max_label_width / 1.2);
            $freq_label = $freq;
            if (strlen($freq_label) > $freq_max_chars && $freq_max_chars > 6) {
                $freq_label = substr($freq_label, 0, $freq_max_chars - 3) . '...';
            }
            $pdf->Cell(0, 0, $freq_label);
        }
    }
}

// ---------------------------------------------
// EQUIPMENT LOCATION TABLE HEADER - TWO-ROW STRUCTURE
// ---------------------------------------------
// Calculate column widths first
$activity_col_width = 60;
$num_equipment = min(count($equipment_tags_list), 12);
$remarks_col_width = 26;
$available_for_equipment = 277 - $activity_col_width - $remarks_col_width;
$equipment_col_width = $num_equipment > 0 ? $available_for_equipment / $num_equipment : 26;

// Ensure minimum width for equipment columns
$min_equipment_width = 18;
if ($equipment_col_width < $min_equipment_width && $num_equipment > 0) {
    // Adjust remarks column to give more space
    $required_equipment = $min_equipment_width * $num_equipment;
    $remarks_col_width = max(20, 277 - $activity_col_width - $required_equipment);
    $equipment_col_width = (277 - $activity_col_width - $remarks_col_width) / $num_equipment;
}

// Header structure: Two rows with specific layout matching image
$header_start_y = 52;
$header_row_height = 7; // Height of each header row
$header_total_height = $header_row_height * 2; // Total header height = 14mm

// ROW 1: Top row of header
$pdf->SetXY(10, $header_start_y);

// ACTIVITIES column (spans full height - text in top row)
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($activity_col_width, $header_row_height, "ACTIVITIES", 1, 0, "C");

// EQUIPMENT NO./ ITEMS LOCATION (spans all equipment columns, top row only)
$equipment_header_width = $equipment_col_width * $num_equipment;
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($equipment_header_width, $header_row_height, "EQUIPMENT NO./ ITEMS LOCATION", 1, 0, "C");

// REMARKS column (spans full height - text in top row)
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($remarks_col_width, $header_row_height, "REMARKS", 1, 1, "C");

// ROW 2: Bottom row of header
$pdf->SetXY(10, $header_start_y + $header_row_height);

// ACTIVITIES (bottom half - empty cell, but maintains full height span)
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($activity_col_width, $header_row_height, "", 1, 0, "C");

// Individual equipment tags (bottom row - separate cells for each equipment)
$pdf->SetFont('Arial', 'B', 7);
for ($i = 0; $i < $num_equipment; $i++) {
    $eq_tag = isset($equipment_tags_list[$i]) ? $equipment_tags_list[$i] : '';
    // Truncate if too long to fit in cell
    $max_chars = floor($equipment_col_width / 1.5);
    if (strlen($eq_tag) > $max_chars) {
        $eq_tag = substr($eq_tag, 0, $max_chars - 3) . '...';
    }
    $pdf->Cell($equipment_col_width, $header_row_height, $eq_tag, 1, 0, "C");
}

// REMARKS (bottom half - empty cell, but maintains full height span)
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($remarks_col_width, $header_row_height, "", 1, 1, "C");

// Reset position for table body - start after the two-row header
$pdf->SetY($header_start_y + $header_total_height);
$pdf->SetX(10);

// ---------------------------------------------
// ACTIVITIES (Rows 13–18 with sub-items)
// ---------------------------------------------
$pdf->SetFont('Arial', '', 8);

$activities = [
    '13' => "13. Perform virus checkup",
    '14' => "14. Update Software/Firmware",
    '15' => "15. Uninstall unused software",
    '16' => "16. Run Disk Cleanup",
    '17' => "17. Defrag Hard Drive",
    '18' => "18. House Keeping",
    '18i' => "      i) monitor, mouse, and keyboard",
    '18j' => "     j) printer, scanner",
    '18k' => "     k) router, switch, & access point",
    '18l' => "     l) IP Phone, CCTV, View board",
];

$row_height = 6; // Reduced from 8 to fit on 1 page

foreach ($activities as $act_key => $act) {
    // ACTIVITIES column
    $pdf->Cell($activity_col_width, $row_height, $act, 1, 0);

    // Equipment columns
    for ($i = 0; $i < $num_equipment; $i++) {
        $cell_x = $pdf->GetX();
        $cell_y = $pdf->GetY();
        
        // Check if there's a mark for this activity/equipment
        $status = '';
        if (isset($activities_data[$act_key][$i])) {
            $status = $activities_data[$act_key][$i];
        }
        
        // Draw empty cell with border
        $pdf->Cell($equipment_col_width, $row_height, "", 1, 0);
        
        // Draw mark if status exists - positioned after cell is drawn
        if ($status === 'ok') {
            // Draw checkmark (/) - centered in cell
            $center_x = $cell_x + ($equipment_col_width / 2) - 1.5;
            $center_y = $cell_y + ($row_height / 2) - 1.5;
            $pdf->SetXY($center_x, $center_y);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(3, 3, '/', 0, 0, 'C');
            $pdf->SetFont('Arial', '', 8);
            // Reset position to continue row
            $pdf->SetXY($cell_x + $equipment_col_width, $cell_y);
        } elseif ($status === 'not_ok') {
            // Draw X mark - centered in cell
            $center_x = $cell_x + ($equipment_col_width / 2) - 1.5;
            $center_y = $cell_y + ($row_height / 2) - 1.5;
            $pdf->SetXY($center_x, $center_y);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(3, 3, 'X', 0, 0, 'C');
            $pdf->SetFont('Arial', '', 8);
            // Reset position to continue row
            $pdf->SetXY($cell_x + $equipment_col_width, $cell_y);
        }
    }

    // Remarks column
    $act_index = array_search($act_key, array_keys($activities));
    $remark_text = ($act_index !== false && isset($remarks[$act_index])) ? $remarks[$act_index] : '';
    // Truncate if too long
    $max_remark_chars = floor($remarks_col_width / 1.1);
    if (strlen($remark_text) > $max_remark_chars) {
        $remark_text = substr($remark_text, 0, $max_remark_chars - 3) . '...';
    }
    $pdf->Cell($remarks_col_width, $row_height, $remark_text, 1, 1);
}

// ---------------------------------------------
// SIGNATURE AREA - Table structure with borders
// Connected directly to activities table above
// ---------------------------------------------
$sig_y_start = $pdf->GetY(); // No gap - connected directly
$sig_row_height = 8; // Height for each signature row

// Calculate column widths for signature table
$sig_label_width = 40;  // "Conducted by:" / "Verified by:"
$sig_name_width = 80;   // Name field
$sig_date_label_width = 20; // "Date:" label
$sig_date_width = 50;   // Date value
$sig_signature_width = 87; // Signature field (remaining width: 277 - 40 - 80 - 20 - 50 = 87)

// ROW 1: Conducted by
$pdf->SetXY(10, $sig_y_start);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($sig_label_width, $sig_row_height, "Conducted by:", 1, 0, "L");

$pdf->SetFont('Arial', '', 9);
$conducted_name = $conducted_by ?: "Ronald C. Tud";
$pdf->Cell($sig_name_width, $sig_row_height, $conducted_name, 1, 0, "L");

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($sig_date_label_width, $sig_row_height, "Date:", 1, 0, "L");

$pdf->SetFont('Arial', '', 9);
$conducted_date_formatted = !empty($conducted_date) ? date('M d, Y', strtotime($conducted_date)) : date('M d, Y');
$pdf->Cell($sig_date_width, $sig_row_height, $conducted_date_formatted, 1, 0, "L");

// Signature field (empty cell for signature)
$pdf->Cell($sig_signature_width, $sig_row_height, "", 1, 1, "C");

// ROW 2: Verified by
$pdf->SetXY(10, $sig_y_start + $sig_row_height);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($sig_label_width, $sig_row_height, "Verified by:", 1, 0, "L");

$pdf->SetFont('Arial', '', 9);
$verified_name = $verified_by ?: "CARLO KRISTAN CATUD";
$pdf->Cell($sig_name_width, $sig_row_height, $verified_name, 1, 0, "L");

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($sig_date_label_width, $sig_row_height, "Date:", 1, 0, "L");

$pdf->SetFont('Arial', '', 9);
$verified_date_formatted = !empty($verified_date) ? date('M d, Y', strtotime($verified_date)) : date('M d, Y');
$pdf->Cell($sig_date_width, $sig_row_height, $verified_date_formatted, 1, 0, "L");

// Signature field (empty cell for signature)
$pdf->Cell($sig_signature_width, $sig_row_height, "", 1, 1, "C");

// ---------------------------------------------
// CORRECTIVE ACTION TABLE - Connected directly to signature section
// ---------------------------------------------
$pdf->SetXY(10, $pdf->GetY()); // No gap - connected directly
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 6, "Date", 1, 0, "C");
$pdf->Cell(70, 6, "Corrective Action", 1, 0, "C");
$pdf->Cell(40, 6, "Office Responsible", 1, 0, "C");
$pdf->Cell(25, 6, "Date Accomplished", 1, 0, "C");
$pdf->Cell(112, 6, "Remarks", 1, 1, "C");

$num_corrective = max(count($corrective_dates), count($corrective_actions), 1);
for ($i = 0; $i < max($num_corrective, 3); $i++) { // Reduced from 4 to 3 rows
    $date = isset($corrective_dates[$i]) && !empty($corrective_dates[$i]) ? date('M d, Y', strtotime($corrective_dates[$i])) : '';
    $action = isset($corrective_actions[$i]) ? substr($corrective_actions[$i], 0, 50) : '';
    $office = isset($corrective_offices[$i]) ? substr($corrective_offices[$i], 0, 40) : '';
    $accomplished = isset($corrective_accomplished[$i]) && !empty($corrective_accomplished[$i]) ? date('M d, Y', strtotime($corrective_accomplished[$i])) : '';
    $remark = isset($corrective_remarks[$i]) ? substr($corrective_remarks[$i], 0, 45) : '';
    
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(30, 8, $date, 1, 0);
    $pdf->Cell(70, 8, $action, 1, 0);
    $pdf->Cell(40, 8, $office, 1, 0);
    $pdf->Cell(25, 8, $accomplished, 1, 0);
    $pdf->Cell(112, 8, $remark, 1, 1);
}

$pdf->Output('I', 'Preventive_Maintenance_Checklist.pdf');
?>
