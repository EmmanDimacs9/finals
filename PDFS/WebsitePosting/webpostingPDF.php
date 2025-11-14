<?php
require_once __DIR__ . '/../../includes/pdf_template.php';
require_once '../../includes/session.php';
require_once '../../includes/db.php';
include '../../logger.php';

$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'SYSTEM';
logAdminAction($uid, $uname, "Generated Report", "RWEBSITE POSTING REQUEST FORM");

class PDF extends TemplatePDF {}

// Collect POST data
$office          = $_POST['office'] ?? '';
$datePosting     = $_POST['datePosting'] ?? '';
$durationPosting = $_POST['durationPosting'] ?? '';
$purpose         = $_POST['purpose'] ?? '';
$content         = $_POST['content'] ?? '';

// Create PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->setTitleText('WEBSITE POSTING REQUEST FORM');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$fullWidth = 190;
$lineHeight = 8;

// --- Requesting Office/Unit ---
$pdf->Cell(50, $lineHeight, 'Requesting Office/Unit:', 1, 0, 'L');
$pdf->Cell($fullWidth - 50, $lineHeight, $office, 1, 1, 'C');

// --- Proposed Date of Posting & Duration ---
$pdf->Cell(50, $lineHeight, 'Proposed Date of Posting:', 1, 0, 'L');
$pdf->Cell(60, $lineHeight, $datePosting, 1, 0, 'C');
$pdf->Cell(40, $lineHeight, 'Duration of Posting:', 1, 0, 'L');
$pdf->Cell(40, $lineHeight, $durationPosting, 1, 1, 'C');

// --- Purpose (vertically aligned label) ---
$purposeHeight = max(20, $pdf->GetStringWidth($purpose) / ($fullWidth - 25) * 10 + 10);
$yBefore = $pdf->GetY();
$pdf->MultiCell($fullWidth - 25, 8, $purpose, 1, 'C');
$yAfter = $pdf->GetY();
$purposeHeight = $yAfter - $yBefore;

$pdf->SetXY(10, $yBefore);
$pdf->Cell(25, $purposeHeight, 'Purpose:', 1, 0, 'C');
$pdf->SetXY(35, $yBefore);
$pdf->MultiCell($fullWidth - 25, 8, $purpose, 1, 'C');

// --- Content (vertically aligned label) ---
$contentHeight = max(25, $pdf->GetStringWidth($content) / ($fullWidth - 25) * 10 + 10);
$yBefore = $pdf->GetY();
$pdf->MultiCell($fullWidth - 25, 8, $content, 1, 'C');
$yAfter = $pdf->GetY();
$contentHeight = $yAfter - $yBefore;

$pdf->SetXY(10, $yBefore);
$pdf->Cell(25, $contentHeight, 'Content:', 1, 0, 'C');
$pdf->SetXY(35, $yBefore);
$pdf->MultiCell($fullWidth - 25, 8, $content, 1, 'C');

$pdf->Ln(4);

// --- Signature Blocks ---
$yStart = $pdf->GetY();
$colWidth = ($pdf->GetPageWidth() - 20) / 2;
$blockHeight = 40;

// Prepared / Requested by
$pdf->Rect(10, $yStart, $colWidth, $blockHeight);
$pdf->SetXY(10, $yStart + 4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($colWidth, 6, 'Prepared by:', 0, 2, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell($colWidth, 5, "\nNAME OF REQUESTING OFFICIAL / PERSONNEL\nPosition/Designation\n\nDate Signed: ___________", 0, 'C');

// Reviewed / Approved by
$pdf->Rect(10 + $colWidth, $yStart, $colWidth, $blockHeight);
$pdf->SetXY(10 + $colWidth, $yStart + 4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($colWidth, 6, 'Reviewed and Approved by:', 0, 2, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell($colWidth, 5, "\nNAME\nDirector for ICT Services /\nVice Chancellor for Development and External Affairs\n\nDate Signed: ___________", 0, 'C');

$pdf->SetY($yStart + $blockHeight + 3);

// --- Remarks ---
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, $lineHeight * 1.5, 'Remarks:', 1, 0, 'L');
$pdf->Cell($fullWidth - 30, $lineHeight * 1.5, '', 1, 1);

$pdf->Ln(4);

// --- Required Attachments ---
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, 'Required Attachments: PDF format of the requested file/s to be posted shall be sent through the email address of ICT Services-Central / ICT Services-Constituent Campus.');

// --- Tracking Number ---
$pdf->Ln(6);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, 'Tracking No.: ____________________', 0, 1, 'L');

$pdf->Output('I', 'Website_Posting_Request.pdf');
?>
