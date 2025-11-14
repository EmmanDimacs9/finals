<?php

require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';

include '../../logger.php';
$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "ICT SERVICE REQUEST FORM");

$campus         = $_POST['campus'] ?? '';
$ict_srf_no     = $_POST['ict_srf_no'] ?? '';
$client_name    = $_POST['client_name'] ?? '';
$technician_id  = $_POST['technician'] ?? '';
$technician_name = $_POST['technician_name'] ?? '';
$office         = $_POST['office'] ?? '';
$date_time_call = $_POST['date_time_call'] ?? '';
$response_time  = $_POST['response_time'] ?? '';
$requirements   = $_POST['requirements'] ?? '';
$accomplishment = $_POST['accomplishment'] ?? '';
$remarks        = $_POST['remarks'] ?? '';
$signature      = $_POST['signature'] ?? '';

// If technician_name is not provided, fetch it from database using technician_id
if (empty($technician_name) && !empty($technician_id)) {
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $technician_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $technician_name = $row['full_name'];
    }
    $stmt->close();
}

// If signature is not in POST, try to get it from database using ict_srf_no
if (empty($signature) && !empty($ict_srf_no)) {
    // Get service request ID from ict_srf_no
    $stmt = $conn->prepare("SELECT id FROM service_requests WHERE ict_srf_no = ?");
    $stmt->bind_param("s", $ict_srf_no);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $request_id = $row['id'];
        // Get signature from service_request_signatures table
        $sigStmt = $conn->prepare("SELECT signature_data FROM service_request_signatures WHERE service_request_id = ? ORDER BY created_at DESC LIMIT 1");
        $sigStmt->bind_param("i", $request_id);
        $sigStmt->execute();
        $sigResult = $sigStmt->get_result();
        if ($sigRow = $sigResult->fetch_assoc()) {
            $signature = $sigRow['signature_data'];
        }
        $sigStmt->close();
    }
    $stmt->close();
}

$eval_response  = $_POST['eval_response'] ?? '';
$eval_quality   = $_POST['eval_quality'] ?? '';
$eval_courtesy  = $_POST['eval_courtesy'] ?? '';
$eval_overall   = $_POST['eval_overall'] ?? '';

class PDF extends TemplatePDF {
    function Header() {
        parent::Header();
    }

 function evaluationRow($statement, $selected) {
    $this->SetFont('Arial','',8);
    $this->Cell(80, 8, $statement, 1, 0);

    for ($i = 5; $i >= 1; $i--) {
        $x = $this->GetX();
        $y = $this->GetY();

        // draw empty cell
        $this->Cell(22, 8, '', 1, 0, 'C');

        // draw the check symbol manually using a small line tick
        if ($selected == $i) {
            $tick_x1 = $x + 8;
            $tick_y1 = $y + 5;
            $tick_x2 = $x + 10;
            $tick_y2 = $y + 7;
            $tick_x3 = $x + 14;
            $tick_y3 = $y + 3;

            // draw two small lines to form a ✓
            $this->Line($tick_x1, $tick_y1, $tick_x2, $tick_y2);
            $this->Line($tick_x2, $tick_y2, $tick_x3, $tick_y3);
        }
    }
    $this->Ln();
}


}

$pdf = new PDF('P','mm','A4');
$pdf->setTitleText('ICT SERVICE REQUEST FORM');
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

// --- Form Content ---
$pdf->Cell(30, 7, 'Campus', 1, 0);
$pdf->Cell(65, 7, $campus, 1, 0);
$pdf->Cell(30, 7, 'ICT SRF No.', 1, 0);
$pdf->Cell(65, 7, $ict_srf_no, 1, 1);

$pdf->Cell(30, 7, 'Office/Building', 1, 0);
$pdf->Cell(65, 7, $office, 1, 0);
$pdf->Cell(30, 7, 'Technician assigned', 1, 0);
$pdf->Cell(65, 7, $technician_name, 1, 1);

$pdf->Cell(30, 7, 'Client\'s Name', 1, 0);
$pdf->Cell(65, 7, $client_name, 1, 0);
$pdf->Cell(30, 7, 'Signature', 1, 0);
// Display signature if available
if (!empty($signature)) {
    // Signature is base64 encoded image, decode and display
    $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature));
    if ($signatureData) {
        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
        file_put_contents($tempFile, $signatureData);
        // Get image dimensions
        $imgInfo = getimagesize($tempFile);
        if ($imgInfo) {
            $imgWidth = $imgInfo[0];
            $imgHeight = $imgInfo[1];
            // Calculate size to fit in cell (65mm width, maintain aspect ratio)
            $maxWidth = 60; // mm
            $maxHeight = 20; // mm
            $ratio = min($maxWidth / ($imgWidth * 0.264583), $maxHeight / ($imgHeight * 0.264583)); // Convert px to mm
            $displayWidth = $imgWidth * 0.264583 * $ratio;
            $displayHeight = $imgHeight * 0.264583 * $ratio;
            
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Image($tempFile, $x + 2, $y + 1, $displayWidth, $displayHeight);
            // Draw cell border
            $pdf->Rect($x, $y, 65, max(7, $displayHeight + 2));
            $pdf->SetXY($x + 65, $y);
            $pdf->Cell(0, max(7, $displayHeight + 2), '', 0, 1);
            unlink($tempFile);
        } else {
            $pdf->Cell(65, 7, '[Signature Image]', 1, 1);
        }
    } else {
        $pdf->Cell(65, 7, '[Signature]', 1, 1);
    }
} else {
    $pdf->Cell(65, 7, '', 1, 1);
}

$pdf->Cell(30, 7, 'Date/Time of Call', 1, 0);
$pdf->Cell(65, 7, $date_time_call, 1, 0);

$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->MultiCell(30, 3.5, "Required\nResponse Time", 1, 'C');
$pdf->SetXY($x + 30, $y);
$pdf->Cell(65, 7, $response_time, 1, 1);

$pdf->Cell(190, 7, 'Service Requirements:', 1, 1);
$pdf->MultiCell(190, 7, $requirements, 1, 'L');

$pdf->SetFont('Arial','I',8);
$pdf->Cell(190, 7, 'ACCOMPLISHMENT (to be accomplished by the assigned technician)', 1, 1);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(190, 7, $accomplishment, 1, 'L');

$pdf->Cell(190, 7, 'Remarks:', 1, 1);
$pdf->MultiCell(190, 7, $remarks, 1, 'L');

// --- Evaluation ---
$pdf->Ln(2);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(190, 5, "Thank you for giving us the opportunity to serve you better. Please help us by taking a few minutes to inform us about the technical assistance/service that you have just been provided. Put a ✓ on the column that corresponds to your level of satisfaction.", 0, 'L');

$pdf->Ln(1);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(80, 7, 'Evaluation Statements', 1, 0, 'C');
$pdf->Cell(22, 7, 'Very Satisfied 5', 1, 0, 'C');
$pdf->Cell(22, 7, 'Satisfied 4', 1, 0, 'C');
$pdf->Cell(22, 7, 'Neutral 3', 1, 0, 'C');
$pdf->Cell(22, 7, 'Dissatisfied 2', 1, 0, 'C');
$pdf->Cell(22, 7, 'Very Dissatisfied 1', 1, 1, 'C');

// Rows
$pdf->evaluationRow('Response time to your initial call for service', $eval_response);
$pdf->evaluationRow('Quality of service provided to resolve the problem', $eval_quality);
$pdf->evaluationRow('Courtesy and professionalism of the attending ICT staff', $eval_courtesy);
$pdf->evaluationRow('Overall satisfaction with the assistance/service provided', $eval_overall);

// Conforme Section
$pdf->Ln(4);
$pdf->Cell(190, 7, 'Conforme:', 1, 1);
$pdf->Ln(8);
$pdf->Cell(190, 7, "Client's Signature Over Printed Name", 0, 1, 'C');
$pdf->Ln(6);
$pdf->Cell(190, 7, "Office/Building", 0, 1, 'C');
$pdf->Ln(6);
$pdf->Cell(190, 7, "Date Signed", 0, 1, 'C');

$pdf->Output('I','ICT_Service_Request_Form.pdf');
