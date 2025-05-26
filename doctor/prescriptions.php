<?php
session_start();
require_once '../dbConnect.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$doctorId = null;
$prescriptions = [];
$errors = [];
$success = '';

// Get doctor ID
try {
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $doctorId = $stmt->fetchColumn();

    if (!$doctorId) {
        session_destroy();
        header("Location: ../index.php");
        exit();
    }

    // Handle writing a new prescription
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['write_prescription'])) {
        $patientId = $_POST['patient_id'] ?? '';
        $medication = $_POST['medication'] ?? '';
        $dosage = $_POST['dosage'] ?? '';
        $instructions = $_POST['instructions'] ?? '';
        $status = 'Active';

        // Debug information
        echo '<div class="alert alert-info">';
        echo "Debug Info:<br>";
        echo "Doctor ID: " . $doctorId . "<br>";
        echo "Patient ID: " . $patientId . "<br>";
        
        // Basic validation
        if (empty($patientId)) $errors[] = 'Patient is required.';
        if (empty($medication)) $errors[] = 'Medication is required.';

        if (empty($errors)) {
            try {
                // First verify if patient exists
                $checkPatient = $pdo->prepare("SELECT id FROM patients WHERE id = ?");
                $checkPatient->execute([$patientId]);
                $patientExists = $checkPatient->fetch();
                
                echo "Patient check result: " . ($patientExists ? "Found" : "Not found") . "<br>";
                
                if (!$patientExists) {
                    $errors[] = "Invalid patient ID selected.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO prescriptions 
                        (patient_id, doctor_id, medication, dosage, instructions, status, prescription_date) 
                        VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE)
                    ");
                    
                    $result = $stmt->execute([$patientId, $doctorId, $medication, $dosage, $instructions, $status]);
                    
                    if ($result) {
                        $success = 'Prescription written successfully!';
                        echo "Prescription saved successfully. Last Insert ID: " . $pdo->lastInsertId() . "<br>";
                        // Clear form fields after successful submission
                        $_POST = [];
                    } else {
                        $errors[] = "Failed to save prescription.";
                        echo "SQL Error Info: ";
                        print_r($stmt->errorInfo());
                    }
                }
            } catch (PDOException $e) {
                $errors[] = "Database Error: " . $e->getMessage();
                echo "Exception: " . $e->getMessage() . "<br>";
            }
        }
        echo '</div>';
    }

    // Fetch doctor's prescriptions
    $stmt = $pdo->prepare("
        SELECT p.*, u.first_name as patient_first_name, u.last_name as patient_last_name 
        FROM prescriptions p 
        JOIN patients pa ON p.patient_id = pa.id 
        JOIN users u ON pa.user_id = u.id 
        WHERE p.doctor_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$doctorId]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch list of patients for the prescription form (patients associated with this doctor)
     $stmtPatients = $pdo->prepare("SELECT DISTINCT p.id as patient_id, u.first_name, u.last_name 
                          FROM patients p
                          JOIN users u ON p.user_id = u.id
                          JOIN appointments a ON p.id = a.patient_id WHERE a.doctor_id = ?
                          UNION
                          SELECT DISTINCT p.id as patient_id, u.first_name, u.last_name 
                          FROM patients p
                          JOIN users u ON p.user_id = u.id
                          JOIN medical_history mh ON p.id = mh.patient_id WHERE mh.doctor_id = ?");
    $stmtPatients->execute([$doctorId, $doctorId]);
    $patients = $stmtPatients->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    $errors[] = "Database Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Prescriptions - Healthcare System</title>
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
                    <a href="patients.php"><i class="fas fa-procedures"></i> My Patients</a>
                    <a href="prescriptions.php" class="active"><i class="fas fa-prescription"></i> Prescriptions</a>
                    <a href="medical-records.php"><i class="fas fa-file-medical"></i> Medical Records</a>
                    <a href="schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a>
                    <a href="../profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="welcome-section">
                    <h2>Prescriptions</h2>
                    <p>View and write prescriptions.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Write New Prescription Form -->
                 <?php if (isset($_GET['action']) && $_GET['action'] === 'write'): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Write New Prescription</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="prescriptions.php?action=write">
                                <input type="hidden" name="write_prescription" value="1">
                                <?php
                                // Debug information
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    echo '<div class="alert alert-info">';
                                    echo 'POST Data: ';
                                    print_r($_POST);
                                    echo '</div>';
                                }
                                ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="patient_id_search" class="form-label">Search Patient by ID</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="patient_id_search" placeholder="Enter Patient ID">
                                            <button type="button" class="btn btn-primary" onclick="searchPatient()">Search</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="patient_id" class="form-label">Select Patient</label>
                                        <select class="form-select" id="patient_id" name="patient_id" required>
                                            <option value="">-- Select a Patient --</option>
                                            <?php foreach ($patients as $patient): ?>
                                                <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>" 
                                                        data-id="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                                    <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?> 
                                                    (ID: <?php echo htmlspecialchars($patient['patient_id']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="medication" class="form-label">Medication</label>
                                    <input type="text" class="form-control" id="medication" name="medication" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dosage" class="form-label">Dosage</label>
                                    <input type="text" class="form-control" id="dosage" name="dosage" required placeholder="e.g., 500mg twice daily">
                                </div>
                                <div class="mb-3">
                                    <label for="instructions" class="form-label">Instructions</label>
                                    <textarea class="form-control" id="instructions" name="instructions" rows="3" placeholder="Special instructions for the patient"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Save Prescription</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Prescriptions List -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Prescriptions</h5>
                         <a href="prescriptions.php?action=write" class="btn btn-primary btn-sm">
                             <i class="fas fa-plus"></i> Write New Prescription
                         </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($prescriptions)): ?>
                            <p class="text-center">No prescriptions found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
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
                                                <td><?php echo htmlspecialchars($prescription['patient_first_name'] . ' ' . $prescription['patient_last_name']); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($prescription['medication'])); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($prescription['dosage'])); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($prescription['instructions'] ?? 'N/A')); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $prescription['status'] === 'Active' ? 'success' : ($prescription['status'] === 'Completed' ? 'secondary' : 'danger'); ?>">
                                                        <?php echo htmlspecialchars($prescription['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <!-- Action buttons (example) -->
                                                    <a href="#" class="btn btn-sm btn-primary">View Details</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script.js"></script>
    <script>
    function searchPatient() {
        const searchId = document.getElementById('patient_id_search').value;
        const select = document.getElementById('patient_id');
        const options = select.options;
        let found = false;
        
        for (let i = 0; i < options.length; i++) {
            const optionId = options[i].getAttribute('data-id');
            if (optionId === searchId) {
                select.selectedIndex = i;
                found = true;
                break;
            }
        }
        
        if (!found) {
            alert('Patient not found with ID: ' + searchId);
        }
    }
    </script>
</body>
</html> 