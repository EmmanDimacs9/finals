<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "Preventive Maintenance of ICT-Related Equipment Index Card");

$items = $_POST['items'] ?? [];

class PDF extends TemplatePDF {
    function Header() {
        parent::Header();
        $this->SetFont('Arial', '', 10);
        $this->Cell(190, 6, 'INFORMATION AND COMMUNICATIONS TECHNOLOGY SERVICES', 1, 1, 'C');
        $this->Cell(190, 6, '{CAMPUS}', 1, 1, 'C');

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(190, 7, 'Equipment No.:', 1, 1, 'L');

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(35, 7, 'Date', 1, 0, 'C');
        $this->Cell(95, 7, 'Repair/Maintenance Task', 1, 0, 'C');
        $this->Cell(60, 7, 'Performed by', 1, 1, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->setTitleText('Preventive Maintenance of ICT-Related Equipment Index Card');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

$rowHeight = 13;   // ✅ reduced height
$maxRows   = 12;   // ✅ fits within A4
$rowsPrinted = 0;

foreach ($items as $item) {
    if (!is_array($item)) continue;

    $date = $item['date'] ?? '';
    $report = $item['report'] ?? ($item['description'] ?? '');
    $perform = $item['perform'] ?? ($item['schedule'] ?? '');

    $pdf->Cell(35, $rowHeight, $date, 1, 0, 'C');
    $pdf->Cell(95, $rowHeight, $report, 1, 0, 'L');
    $pdf->Cell(60, $rowHeight, $perform, 1, 1, 'L');
    $rowsPrinted++;
}

while ($rowsPrinted < $maxRows) {
    $pdf->Cell(35, $rowHeight, '', 1, 0);
    $pdf->Cell(95, $rowHeight, '', 1, 0);
    $pdf->Cell(60, $rowHeight, '', 1, 1);
    $rowsPrinted++;
}

$pdf->Ln(2);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 6, 'End of Record', 0, 1, 'C');

$pdf->Output('I', 'Preventive_Maintenance_IndexCard.pdf');
?>
