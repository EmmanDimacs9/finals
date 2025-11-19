<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

// Get the request ID
$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id <= 0) {
    die("Invalid Request ID.");
}

// Fetch request details with user information
$query = "SELECT r.*, u.full_name, u.email FROM requests r 
          LEFT JOIN users u ON r.user_id = u.id 
          WHERE r.id = $id LIMIT 1";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("Request not found.");
}

$request = $result->fetch_assoc();
$form_data = isset($request['form_data']) ? json_decode($request['form_data'], true) : [];

// Function to display form data in readable format
function displayFormData($data, $form_type) {
    $html = '<div class="card">';
    $html .= '<div class="card-header"><h5 class="mb-0"><i class="fas fa-clipboard-list"></i> ' . htmlspecialchars($form_type) . ' - Submitted Data</h5></div>';
    $html .= '<div class="card-body">';
    unset($data['form_type']);
    if (empty($data)) {
        $html .= '<div class="alert alert-info text-center">No form data was submitted.</div>';
    } else {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleanValues = [];
                foreach ($value as $item) {
                    if (is_array($item)) {
                        $cleanValues = array_merge($cleanValues, array_filter($item, function($subItem) {
                            return !empty(trim((string)$subItem));
                        }));
                    } else {
                        $cleanItem = trim((string)$item);
                        if (!empty($cleanItem)) {
                            $cleanValues[] = $cleanItem;
                        }
                    }
                }
                if (empty($cleanValues)) continue;
                $value = implode(', ', $cleanValues);
            } else {
                $value = trim((string)$value);
                if (empty($value)) continue;
            }
            $label = ucwords(str_replace(['_', '-'], ' ', $key));
            $html .= '<div class="row mb-3">';
            $html .= '<div class="col-md-3"><strong>' . htmlspecialchars($label) . ':</strong></div>';
            $html .= '<div class="col-md-9">' . htmlspecialchars($value) . '</div>';
            $html .= '</div>';
        }
    }
    $html .= '</div></div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request - BSU Inventory Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #dc3545; --secondary-color: #343a40; }
        body { background: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .card { border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="request.php">
                <img src="../images/Ict logs.png" alt="Logo" style="height:40px;"> BSU Inventory System
            </a>
            <div class="navbar-nav ms-auto">
                <a href="request.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Back to Requests</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Request Header -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-file-alt text-primary"></i> Request Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Request ID:</strong> #<?= htmlspecialchars($request['id']) ?></p>
                                <p><strong>Submitted By:</strong> <?= htmlspecialchars($request['full_name'] ?? 'Unknown') ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($request['email'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Form Type:</strong> <?= htmlspecialchars($request['form_type']) ?></p>
                                <p><strong>Status:</strong> 
                                    <?php
                                    $statusClass = match ($request['status']) {
                                        'Approved' => 'bg-success',
                                        'Rejected' => 'bg-danger',
                                        default => 'bg-warning text-dark'
                                    };
                                    ?>
                                    <span class="badge status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($request['status']) ?>
                                    </span>
                                </p>
                                <p><strong>Date Submitted:</strong> <?= htmlspecialchars($request['created_at']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Form Display -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-pdf text-danger"></i> PDF Form with Submitted Data</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Determine which PDF file to load based on form type
                        $pdfFile = '';
                        switch (strtolower($request['form_type'])) {
                            case 'preventivemaintenanceplan':
                            case 'preventive maintenance plan':
                                $pdfFile = '../PDFS/PreventiveMaintenancePlan/preventivePDF.php';
                                break;

                            case 'preventivemaintenanceplanindexcard':
                            case 'preventive maintenance plan index card':
                            case 'preventive maintenance index card':
                                $pdfFile = '../PDFS/PreventiveMaintendancePlanIndexCard/preventivePDFindexcard.php';
                                break;

                            case 'isp evaluation':
                                $pdfFile = '../PDFS/ISPEvaluation/ispEvaluationPDF.php';
                                break;
                            case 'website posting request':
                            case 'website posting':
                                $pdfFile = '../PDFS/WebsitePosting/webpostingPDF.php';
                                break;
                            case 'ict service request form':    
                                $pdfFile = '../PDFS/ICTRequestForm/ictServiceRequestPDF.php';
                                break;
                            case 'announcement request':
                                $pdfFile = '../PDFS/AnnouncementGreetings/announcementPDF.php';
                                break;
                            case 'user account request':
                                $pdfFile = '../PDFS/UserAccountForm/userAccountRequestPDF.php';
                                break;
                            case 'posting request':
                                $pdfFile = '../PDFS/PostingRequestForm/postingRequestPDF.php';
                                break;
                            case 'system request':
                                $pdfFile = '../PDFS/SystemRequest/systemReqsPDF.php';
                                break;

                            default:
                                $pdfFile = '../PDFS/PreventiveMaintenancePlan/preventivePDF.php';
                        }
                        ?>
                        
                        <!-- Form to pass data to the PDF generator -->
                        <form id="pdfForm" method="POST" action="<?= htmlspecialchars($pdfFile) ?>" target="pdfFrame" style="display: none;">
                            <?php
                            foreach ($form_data as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $subKey => $subValue) {
                                        if (is_array($subValue)) {
                                            foreach ($subValue as $subSubKey => $subSubValue) {
                                                echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subKey) . '][' . htmlspecialchars($subSubKey) . ']" value="' . htmlspecialchars($subSubValue) . '">';
                                            }
                                        } else {
                                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subKey) . ']" value="' . htmlspecialchars($subValue) . '">';
                                        }
                                    }
                                } else {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                                }
                            }
                            ?>
                        </form>

                        <!-- PDF iframe -->
                        <iframe id="pdfFrame" name="pdfFrame" style="width: 100%; height: 80vh; border: none;" title="PDF Form"></iframe>
                        
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            document.getElementById('pdfForm').submit();
                        });
                        </script>
                    </div>
                </div>

                <!-- Form Data Display -->
                <?php if (!empty($form_data)): ?>
                    <?= displayFormData($form_data, $request['form_type']) ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <h5>No Form Data Available</h5>
                            <p class="text-muted">This request was submitted without form data.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Admin Action Buttons -->
                <?php if ($request['status'] === 'Pending'): ?>
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <h5 class="mb-3">Admin Actions</h5>
                            <a href="approve_request.php?id=<?= $request['id'] ?>" class="btn btn-secondary me-2" onclick="return confirm('Approve this request?');">
                                <i class="fas fa-check"></i> Approve Request
                            </a>
                            <a href="reject_request.php?id=<?= $request['id'] ?>" class="btn btn-danger" onclick="return confirm('Reject this request?');">
                                <i class="fas fa-times"></i> Reject Request
                            </a>
                            <a href="delete_request.php?id=<?= $request['id'] ?>" class="btn btn-outline-danger ms-2" onclick="return confirm('Are you sure you want to delete this request? This action cannot be undone.');">
                                <i class="fas fa-trash"></i> Delete Request
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
