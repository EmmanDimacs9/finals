<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Invalid request.');
}

$stmt = $conn->prepare("SELECT form_type, form_data FROM requests WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();
$stmt->close();

if (!$request) {
    die('Request not found.');
}

$form_data = json_decode($request['form_data'] ?? '[]', true);

function determinePdfFile($formType) {
    $type = strtolower(trim($formType));
    return match ($type) {
        'preventivemaintenanceplan', 'preventive maintenance plan' => '../PDFS/PreventiveMaintenancePlan/preventivePDF.php',
        'preventivemaintenanceplanindexcard', 'preventive maintenance plan index card', 'preventive maintenance index card' => '../PDFS/PreventiveMaintendancePlanIndexCard/preventivePDFindexcard.php',
        'isp evaluation' => '../PDFS/ISPEvaluation/ispEvaluationPDF.php',
        'website posting request', 'website posting' => '../PDFS/WebsitePosting/webpostingPDF.php',
        'ict service request form' => '../PDFS/ICTRequestForm/ictServiceRequestPDF.php',
        'announcement request' => '../PDFS/AnnouncementGreetings/announcementPDF.php',
        'user account request' => '../PDFS/UserAccountForm/userAccountRequestPDF.php',
        'posting request' => '../PDFS/PostingRequestForm/postingRequestPDF.php',
        'system request' => '../PDFS/SystemRequest/systemReqsPDF.php',
        default => '../PDFS/PreventiveMaintenancePlan/preventivePDF.php',
    };
}

function renderHiddenInputs($data, $parentKey = '') {
    foreach ($data as $key => $value) {
        $inputName = $parentKey === '' ? $key : $parentKey . '[' . $key . ']';
        if (is_array($value)) {
            renderHiddenInputs($value, $inputName);
        } else {
            $safeName = htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8');
            $safeValue = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            echo '<input type="hidden" name="' . $safeName . '" value="' . $safeValue . '">';
        }
    }
}

$pdfFile = determinePdfFile($request['form_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request PDF Preview</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f9fa;
        }
        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
    </style>
</head>
<body>
    <form id="pdfForm" method="POST" action="<?= htmlspecialchars($pdfFile) ?>" target="pdfFrame" style="display: none;">
        <?php renderHiddenInputs($form_data); ?>
    </form>
    <iframe id="pdfFrame" name="pdfFrame" title="Request PDF Document"></iframe>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('pdfForm').submit();
        });
    </script>
</body>
</html>

