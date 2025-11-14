<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "REQUEST FOR SYSTEM USER ACCOUNT FORM");

class PDF extends TemplatePDF {

    // ✅ Checkbox with check mark
    function Checkbox($label, $checked = false, $w = 63, $h = 6) {
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Cell($w, $h, '', 1, 0);
        $this->Rect($x + 2, $y + 1.5, 3, 3);
        if ($checked) {
            $this->SetLineWidth(0.3);
            $this->Line($x + 2.2, $y + 3, $x + 3, $y + 4.5);
            $this->Line($x + 3, $y + 4.5, $x + 4.8, $y + 2);
        }
        $this->SetXY($x + 7, $y);
        $this->Cell($w - 7, $h, $label, 0, 0, 'L');
    }

    // Auto-adjusting wrapped table rows
    function FancyRow($data, $widths, $height = 10, $align = 'C') {
        $nb = 0;
        foreach ($data as $i => $txt) {
            $nb = max($nb, $this->NbLines($widths[$i], $txt));
        }
        $h = 5 * $nb;
        $x = $this->GetX();
        $y = $this->GetY();
        foreach ($data as $i => $txt) {
            $w = $widths[$i];
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 5, $txt, 0, $align);
            $x += $w;
            $this->SetXY($x, $y);
        }
        $this->Ln($h);
    }

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) { if ($i == $j) $i++; }
                else $i = $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        }
        return $nl;
    }
}

// Create PDF (A4)
$pdf = new PDF('P', 'mm', 'A4');
$pdf->setTitleText('REQUEST FOR SYSTEM USER ACCOUNT FORM');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);
$pdf->SetAutoPageBreak(false);

// --- Requested Services ---
$pdf->Cell(190, 6, "Please check the requested service:", 1, 1, 'L');
$pdf->Checkbox("Account Creation", in_array("Account Creation", $_POST['services'] ?? []), 63, 6);
$pdf->Checkbox("Account Modification", in_array("Account Modification", $_POST['services'] ?? []), 63, 6);
$pdf->Checkbox("Account Deletion", in_array("Account Deletion", $_POST['services'] ?? []), 64, 6);
$pdf->Ln(7);

// --- Reason ---
$pdf->Cell(190, 6, "Reason for Request:", 1, 1);
$pdf->MultiCell(190, 6, $_POST['reason'] ?? '', 1);

// --- Application ---
$pdf->Cell(190, 6, "Name of the Application or System: " . ($_POST['application'] ?? ''), 1, 1);

// --- Requested User Info ---
$pdf->Cell(190, 6, "Requested User's Information", 1, 1);
$pdf->Cell(190, 6, "For Individual Employee Requests", 1, 1);

$pdf->SetFont('Arial', 'B', 7);
$pdf->FancyRow(
    [
        "Full Name\n(Last Name, First Name M.I.)",
        "ID No.",
        "Username\n(For modification/deletion only)",
        "Position/\nDesignation",
        "Employment\nStatus",
        "Access Details\n(Include info such as: same access as role, specific access, etc.)"
    ],
    [35, 18, 27, 28, 27, 55]
);

$pdf->SetFont('Arial', '', 7.5);
foreach ($_POST['individual'] ?? [] as $row) {
    $pdf->Cell(35, 6, $row['name'], 1, 0);
    $pdf->Cell(18, 6, $row['id'], 1, 0);
    $pdf->Cell(27, 6, $row['username'], 1, 0);
    $pdf->Cell(28, 6, $row['position'], 1, 0);
    $pdf->Cell(27, 6, $row['status'], 1, 0);
    $pdf->Cell(55, 6, $row['access'], 1, 1);
}
$pdf->Cell(190, 5, "", 1, 1);

// --- Department Requests ---
$pdf->Cell(190, 6, "For Office/Department Requests", 1, 1);
$pdf->SetFont('Arial', 'B', 7);
$pdf->FancyRow(
    [
        "Name of Office/Department",
        "Username\n(For modification/deletion only)",
        "Access Details\n(Include info such as: same access as role, specific access, etc.)"
    ],
    [75, 55, 60]
);
$pdf->SetFont('Arial', '', 7.5);
foreach ($_POST['department'] ?? [] as $row) {
    $pdf->Cell(75, 6, $row['office'], 1, 0);
    $pdf->Cell(55, 6, $row['username'], 1, 0);
    $pdf->Cell(60, 6, $row['access'], 1, 1);
}
$pdf->Cell(190, 5, "", 1, 1);

// --- Additional Permissions ---
$pdf->SetFont('Arial', 'I', 7);
$pdf->Cell(190, 4, "*Kindly use additional sheet if necessary", 0, 1);
$pdf->SetFont('Arial', '', 8.5);
$pdf->Cell(190, 6, "Additional permissions or needs (if any):", 1, 1);
$pdf->MultiCell(190, 6, $_POST['permissions'] ?? '', 1);

// --- Requested / Approved ---
$pdf->SetFont('Arial', '', 8.5);
$pdf->Cell(95, 16, "Requested by:\n" . ($_POST['requested_by'] ?? '') . "\n" . ($_POST['requested_designation'] ?? '') . "\nDate: ___________", 1, 0, 'L');
$pdf->Cell(95, 16, "Reviewed and Approved by:\n" . ($_POST['reviewed_by'] ?? 'Engr. JONNAH R. MELO') . "\n" . ($_POST['reviewed_designation'] ?? 'Head, ICT Services') . "\nDate: ___________", 1, 1, 'L');

// --- Remarks ---
$pdf->Cell(190, 6, "Remarks:", 1, 1);
$pdf->MultiCell(190, 6, $_POST['remarks'] ?? '', 1);

// --- ICT Services Section ---
$pdf->Cell(190, 6, "------------------ To be completed by the ICT Services ------------------", 1, 1, 'C');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(47.5, 6, "Name", 1, 0, 'C');
$pdf->Cell(47.5, 6, "Username", 1, 0, 'C');
$pdf->Cell(47.5, 6, "Default Password", 1, 0, 'C');
$pdf->Cell(47.5, 6, "Access Details", 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);
for ($i = 0; $i < 2; $i++) { // reduced rows
    $pdf->Cell(47.5, 6, "", 1, 0);
    $pdf->Cell(47.5, 6, "", 1, 0);
    $pdf->Cell(47.5, 6, "", 1, 0);
    $pdf->Cell(47.5, 6, "", 1, 1);
}

// --- Assigned / Conforme ---
$pdf->SetFont('Arial', '', 8.5);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->MultiCell(95, 5, "Assigned to:\n\n__________________________\nSignature over Printed Name\nDesignation: __________________\nDate: ____________", 1, 'L');
$pdf->SetXY($x + 95, $y);
$pdf->MultiCell(95, 5, "Conforme:\n\n__________________________\nSignature over Printed Name\nDesignation: __________________\nDate: ____________", 1, 'L');

// --- Footer ---
$pdf->SetFont('Arial', 'I', 7);
$pdf->MultiCell(190, 4, "Note: The user is required to create a new password or change the default password given for security reasons.", 0, 'L');
$pdf->SetFont('Arial', '', 8.5);
$pdf->Cell(190, 6, "Tracking No.: ___________________", 0, 1, 'R');

$pdf->Output('I', "SystemUserAccountForm.pdf");
?>
