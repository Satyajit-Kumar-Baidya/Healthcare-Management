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
$patients = [];
$errors = [];

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

    // Fetch patients associated with this doctor (placeholder)
    // This could be patients they have appointments with or have medical history records for
    $stmt = $pdo->prepare("SELECT DISTINCT p.id as patient_id, u.first_name, u.last_name, p.date_of_birth, p.gender 
                          FROM patients p
                          JOIN users u ON p.user_id = u.id
                          JOIN appointments a ON p.id = a.patient_id WHERE a.doctor_id = ?
                          UNION
                          SELECT DISTINCT p.id as patient_id, u.first_name, u.last_name, p.date_of_birth, p.gender
                          FROM patients p
                          JOIN users u ON p.user_id = u.id
                          JOIN medical_history mh ON p.id = mh.patient_id WHERE mh.doctor_id = ?");
    $stmt->execute([$doctorId, $doctorId]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    $errors[] = "Database Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Patients - Healthcare System</title>
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
                    <a href="patients.php" class="active"><i class="fas fa-procedures"></i> My Patients</a>
                    <a href="prescriptions.php"><i class="fas fa-prescription"></i> Prescriptions</a>
                    <a href="medical-records.php"><i class="fas fa-file-medical"></i> Medical Records</a>
                    <a href="schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a>
                    <a href="../profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="welcome-section">
                    <h2>My Patients</h2>
                    <p>View the list of patients you have attended to.</p>
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

                <!-- Patients List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">My Patients List</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($patients)): ?>
                            <p class="text-center">No patients found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Date of Birth</th>
                                            <th>Gender</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($patients as $patient): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['date_of_birth'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <!-- Action buttons (example) -->
                                                    <a href="view_patient.php?patient_id=<?php echo $patient['patient_id']; ?>" class="btn btn-sm btn-primary">View Profile</a>
                                                    <a href="medical-records.php?patient_id=<?php echo $patient['patient_id']; ?>" class="btn btn-sm btn-info">View Medical History</a>
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
</body>
</html> 