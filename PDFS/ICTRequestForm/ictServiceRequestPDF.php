<?php

require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';

include '../../logger.php';
$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "ICT SERVICE REQUEST FORM");

// Check if service_request_id is provided to fetch from database
$service_request_id = $_POST['service_request_id'] ?? $_GET['service_request_id'] ?? 0;
$surveys = [];
$serviceRequestData = null;

// If service_request_id is provided, fetch data from database
if ($service_request_id > 0) {
    // Fetch service request data
    $stmt = $conn->prepare("
        SELECT sr.*, 
               u.full_name as technician_name,
               tu.full_name as client_full_name
        FROM service_requests sr
        LEFT JOIN users u ON sr.technician_id = u.id
        LEFT JOIN users tu ON sr.user_id = tu.id
        WHERE sr.id = ?
    ");
    $stmt->bind_param("i", $service_request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $serviceRequestData = $result->fetch_assoc();
        
        // Fetch all surveys for this service request
        $surveyStmt = $conn->prepare("
            SELECT ss.*, u.full_name as user_name
            FROM service_surveys ss
            LEFT JOIN users u ON ss.user_id = u.id
            WHERE ss.service_request_id = ?
            ORDER BY ss.submitted_at DESC
        ");
        $surveyStmt->bind_param("i", $service_request_id);
        $surveyStmt->execute();
        $surveyResult = $surveyStmt->get_result();
        
        while ($survey = $surveyResult->fetch_assoc()) {
            $surveys[] = $survey;
        }
        $surveyStmt->close();
    }
    $stmt->close();
}

// Use database data if available, otherwise use POST data
if ($serviceRequestData) {
    $campus         = $serviceRequestData['campus'] ?? '';
    $ict_srf_no     = $serviceRequestData['ict_srf_no'] ?? '';
    $client_name    = $serviceRequestData['client_full_name'] ?? $serviceRequestData['client_name'] ?? '';
    $technician     = $serviceRequestData['technician_name'] ?? $serviceRequestData['technician'] ?? '';
    $office         = $serviceRequestData['office'] ?? '';
    $date_time_call = $serviceRequestData['date_time_call'] ?? '';
    $response_time  = $serviceRequestData['response_time'] ?? '';
    $requirements   = $serviceRequestData['requirements'] ?? '';
    $accomplishment = $serviceRequestData['accomplishment'] ?? '';
    $remarks        = $serviceRequestData['remarks'] ?? '';
} else {
    $campus         = $_POST['campus'] ?? '';
    $ict_srf_no     = $_POST['ict_srf_no'] ?? '';
    $client_name    = $_POST['client_name'] ?? '';
    $technician     = $_POST['technician'] ?? '';
    $office         = $_POST['office'] ?? '';
    $date_time_call = $_POST['date_time_call'] ?? '';
    $response_time  = $_POST['response_time'] ?? '';
    $requirements   = $_POST['requirements'] ?? '';
    $accomplishment = $_POST['accomplishment'] ?? '';
    $remarks        = $_POST['remarks'] ?? '';
}

// Evaluation data from POST (for direct form submission) - not used when fetching from DB
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
$pdf->Cell(65, 7, $technician, 1, 1);

$pdf->Cell(30, 7, 'Client\'s Name', 1, 0);
$pdf->Cell(65, 7, $client_name, 1, 0);
$pdf->Cell(30, 7, 'Signature', 1, 0);
$pdf->Cell(65, 7, '', 1, 1);

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

// Rows - only show if evaluation data is provided (for direct form submission)
if ($eval_response || $eval_quality || $eval_courtesy || $eval_overall) {
    $pdf->evaluationRow('Response time to your initial call for service', $eval_response);
    $pdf->evaluationRow('Quality of service provided to resolve the problem', $eval_quality);
    $pdf->evaluationRow('Courtesy and professionalism of the attending ICT staff', $eval_courtesy);
    $pdf->evaluationRow('Overall satisfaction with the assistance/service provided', $eval_overall);
}

// Conforme Section
$pdf->Ln(4);
$pdf->Cell(190, 7, 'Conforme:', 1, 1);
$pdf->Ln(8);
$pdf->Cell(190, 7, "Client's Signature Over Printed Name", 0, 1, 'C');
$pdf->Ln(6);
$pdf->Cell(190, 7, "Office/Building", 0, 1, 'C');
$pdf->Ln(6);
$pdf->Cell(190, 7, "Date Signed", 0, 1, 'C');

// Display Survey Data if available
if (count($surveys) > 0) {
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(190, 10, 'SERVICE EVALUATION SURVEYS', 1, 1, 'C');
    $pdf->Ln(2);
    
    foreach ($surveys as $index => $survey) {
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(190, 7, 'Survey #' . ($index + 1) . ' - Submitted by: ' . ($survey['user_name'] ?? 'Unknown'), 1, 1, 'L');
        
        $pdf->SetFont('Arial','',8);
        $submittedDate = date('M d, Y H:i', strtotime($survey['submitted_at']));
        $pdf->Cell(190, 6, 'Date Submitted: ' . $submittedDate, 0, 1, 'L');
        $pdf->Ln(1);
        
        // Survey evaluation table
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(80, 7, 'Evaluation Statements', 1, 0, 'C');
        $pdf->Cell(22, 7, 'Very Satisfied 5', 1, 0, 'C');
        $pdf->Cell(22, 7, 'Satisfied 4', 1, 0, 'C');
        $pdf->Cell(22, 7, 'Neutral 3', 1, 0, 'C');
        $pdf->Cell(22, 7, 'Dissatisfied 2', 1, 0, 'C');
        $pdf->Cell(22, 7, 'Very Dissatisfied 1', 1, 1, 'C');
        
        // Survey rows
        $pdf->evaluationRow('Response time to your initial call for service', $survey['eval_response'] ?? 0);
        $pdf->evaluationRow('Quality of service provided to resolve the problem', $survey['eval_quality'] ?? 0);
        $pdf->evaluationRow('Courtesy and professionalism of the attending ICT staff', $survey['eval_courtesy'] ?? 0);
        $pdf->evaluationRow('Overall satisfaction with the assistance/service provided', $survey['eval_overall'] ?? 0);
        
        // Comments if available
        if (!empty($survey['comments'])) {
            $pdf->Ln(2);
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(190, 6, 'Comments:', 0, 1, 'L');
            $pdf->SetFont('Arial','',8);
            $pdf->MultiCell(190, 6, $survey['comments'], 0, 'L');
        }
        
        // Add spacing between surveys
        if ($index < count($surveys) - 1) {
            $pdf->Ln(3);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(3);
        }
    }
    
    // Calculate and display average rating
    if (count($surveys) > 0) {
        $pdf->Ln(3);
        $pdf->SetFont('Arial','B',9);
        $total = 0;
        $count = 0;
        foreach ($surveys as $survey) {
            $avg = (intval($survey['eval_response']) + intval($survey['eval_quality']) + 
                   intval($survey['eval_courtesy']) + intval($survey['eval_overall'])) / 4;
            $total += $avg;
            $count++;
        }
        $overallAverage = $count > 0 ? $total / $count : 0;
        $pdf->Cell(190, 8, 'Overall Average Rating: ' . number_format($overallAverage, 2) . ' / 5.00', 1, 1, 'C');
    }
}

$pdf->Output('I','ICT_Service_Request_Form.pdf');
