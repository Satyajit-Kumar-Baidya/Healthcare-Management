<?php
session_start();
require_once '../dbConnect.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$patientId = null;

// Get patient ID
try {
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $patientId = $stmt->fetchColumn();

    if (!$patientId) {
        // If patient record not found, log out user
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
} catch (PDOException $e) {
    // If database error, log out user
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Fetch patient's prescriptions
$prescriptions = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               d.id as doctor_id,
               u.first_name as doctor_first_name, 
               u.last_name as doctor_last_name 
        FROM prescriptions p 
        JOIN doctors d ON p.doctor_id = d.id 
        JOIN users u ON d.user_id = u.id 
        WHERE p.patient_id = ? 
        ORDER BY p.prescription_date DESC
    ");
    $stmt->execute([$patientId]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database error
    echo "Database Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h3 class="text-center mb-4">Healthcare</h3>
                <nav>
                    <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    <a href="appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a>
                    <a href="medical-records.php"><i class="fas fa-file-medical"></i> Medical Records</a>
                    <a href="prescriptions.php" class="active"><i class="fas fa-prescription"></i> Prescriptions</a>
                    <a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a>
                    <a href="health-log.php"><i class="fas fa-heartbeat"></i> Health Log</a>
                    <a href="../profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="welcome-section">
                    <h2>Prescriptions</h2>
                    <p>View your prescriptions.</p>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Prescriptions List</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($prescriptions)): ?>
                                    <div class="alert alert-info">No prescriptions found.</div>
                                <?php else: ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Doctor</th>
                                                <th>Medication</th>
                                                <th>Dosage</th>
                                                <th>Instructions</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($prescriptions as $prescription): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($prescription['prescription_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($prescription['doctor_first_name'] . ' ' . $prescription['doctor_last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($prescription['medication']); ?></td>
                                                    <td><?php echo htmlspecialchars($prescription['dosage']); ?></td>
                                                    <td><?php echo nl2br(htmlspecialchars($prescription['instructions'] ?? 'N/A')); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $prescription['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                                            <?php echo htmlspecialchars($prescription['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="view_prescription.php?id=<?php echo $prescription['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script.js"></script>
</body>
</html> 