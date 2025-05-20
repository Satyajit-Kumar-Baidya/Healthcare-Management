<?php
session_start();
require_once 'dbConnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

// Ensure role is set
if (!isset($user['role'])) {
    // Try to get role from database
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $role = $stmt->fetchColumn();
        
        if ($role) {
            $user['role'] = $role;
            $_SESSION['user']['role'] = $role;
        } else {
            // If role not found, log out user
            session_destroy();
            header("Location: index.php");
            exit();
        }
    } catch (PDOException $e) {
        // If database error, log out user
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

$role = $user['role'];
$stats = [];

if ($role === 'admin') {
    // Admin statistics
    $stats = [
        'patients' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
        'doctors' => $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
        'appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetchColumn(),
        'revenue' => $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid' AND DATE(payment_date) = CURDATE()")->fetchColumn()
    ];
} elseif ($role === 'doctor') {
    // Get doctor ID
    $doctorId = null;
    try {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $doctorId = $stmt->fetchColumn();

        if ($doctorId) {
            $stats = [
                'today_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctorId AND DATE(appointment_date) = CURDATE()")->fetchColumn(),
                'total_patients' => $pdo->query("SELECT COUNT(DISTINCT patient_id) FROM medical_history WHERE doctor_id = $doctorId")->fetchColumn(), // Count unique patients seen by this doctor
                'pending_prescriptions' => $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE doctor_id = $doctorId AND status = 'Active'")->fetchColumn() // Assuming 'Active' means pending for the doctor to review/issue
            ];
        }
    } catch (PDOException $e) {
        // Handle database error
        echo "Database Error: " . $e->getMessage();
    }
} else { // Patient statistics
    // Get patient ID
    $patientId = null;
    try {
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $patientId = $stmt->fetchColumn();

         if ($patientId) {
            $stats = [
                'upcoming_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE patient_id = $patientId AND appointment_date >= CURDATE()")->fetchColumn(),
                'medical_records' => $pdo->query("SELECT COUNT(*) FROM medical_records WHERE patient_id = $patientId")->fetchColumn(),
                'prescriptions' => $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE patient_id = $patientId")->fetchColumn()
            ];
        }
    } catch (PDOException $e) {
        // Handle database error
        echo "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            padding: 20px;
        }
        .welcome-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h3 class="text-center mb-4">Healthcare</h3>
                <nav>
                    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    <?php if ($role === 'admin'): ?>
                        <a href="admin/users.php"><i class="fas fa-users"></i> User Management</a>
                        <a href="admin/doctors.php"><i class="fas fa-user-md"></i> Doctors</a>
                        <a href="admin/patients.php"><i class="fas fa-procedures"></i> Patients</a>
                        <a href="admin/appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
                        <a href="admin/medicines.php"><i class="fas fa-pills"></i> Medicines</a>
                        <a href="admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                    <?php elseif ($role === 'doctor'): ?>
                        <a href="doctor/appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a>
                        <a href="doctor/patients.php"><i class="fas fa-procedures"></i> My Patients</a>
                        <a href="doctor/prescriptions.php"><i class="fas fa-prescription"></i> Prescriptions</a>
                        <a href="doctor/medical-records.php"><i class="fas fa-file-medical"></i> Medical Records</a>
                        <a href="doctor/schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a>
                    <?php else: // patient ?>
                        <a href="patient/appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a>
                        <a href="patient/medical-records.php"><i class="fas fa-file-medical"></i> Medical Records</a>
                        <a href="patient/prescriptions.php"><i class="fas fa-prescription"></i> Prescriptions</a>
                        <a href="patient/payments.php"><i class="fas fa-credit-card"></i> Payments</a>
                        <a href="patient/health-log.php"><i class="fas fa-heartbeat"></i> Health Log</a>
                    <?php endif; ?>
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="welcome-section">
                    <h2>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
                    <p>Here's your healthcare dashboard overview.</p>
                </div>

                <div class="row">
                    <?php if ($role === 'admin'): ?>
                        <!-- Admin Statistics -->
                        <div class="col-md-3">
                            <div class="stat-card bg-primary">
                                <i class="fas fa-users"></i>
                                <h3><?php echo $stats['patients'] ?? 0; ?></h3>
                                <p>Total Patients</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-success">
                                <i class="fas fa-user-md"></i>
                                <h3><?php echo $stats['doctors'] ?? 0; ?></h3>
                                <p>Total Doctors</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-info">
                                <i class="fas fa-calendar-check"></i>
                                <h3><?php echo $stats['appointments'] ?? 0; ?></h3>
                                <p>Today's Appointments</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-warning">
                                <i class="fas fa-dollar-sign"></i>
                                <h3>$<?php echo number_format($stats['revenue'] ?? 0, 2); ?></h3>
                                <p>Today's Revenue</p>
                            </div>
                        </div>
                    <?php elseif ($role === 'doctor'): ?>
                        <!-- Doctor Statistics -->
                        <div class="col-md-4">
                            <div class="stat-card bg-primary">
                                <i class="fas fa-calendar-check"></i>
                                <h3><?php echo $stats['today_appointments'] ?? 0; ?></h3>
                                <p>Today's Appointments</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-success">
                                <i class="fas fa-procedures"></i>
                                <h3><?php echo $stats['total_patients'] ?? 0; ?></h3>
                                <p>Total Patients</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-warning">
                                <i class="fas fa-prescription"></i>
                                <h3><?php echo $stats['pending_prescriptions'] ?? 0; ?></h3>
                                <p>Pending Prescriptions</p>
                            </div>
                        </div>
                    <?php else: // Patient Statistics ?>
                        <div class="col-md-4">
                            <div class="stat-card bg-primary">
                                <i class="fas fa-calendar-check"></i>
                                <h3><?php echo $stats['upcoming_appointments'] ?? 0; ?></h3>
                                <p>Upcoming Appointments</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-success">
                                <i class="fas fa-file-medical"></i>
                                <h3><?php echo $stats['medical_records'] ?? 0; ?></h3>
                                <p>Medical Records</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-info">
                                <i class="fas fa-prescription"></i>
                                <h3><?php echo $stats['prescriptions'] ?? 0; ?></h3>
                                <p>Prescriptions</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if ($role === 'admin'): ?>
                                        <div class="col-md-3 mb-3">
                                            <a href="admin/users.php?action=add" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-user-plus"></i> Add User
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="admin/appointments.php?action=add" class="btn btn-success btn-lg w-100">
                                                <i class="fas fa-calendar-plus"></i> Schedule Appointment
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="admin/medicines.php?action=add" class="btn btn-info btn-lg w-100">
                                                <i class="fas fa-pills"></i> Add Medicine
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="admin/reports.php" class="btn btn-warning btn-lg w-100">
                                                <i class="fas fa-chart-bar"></i> View Reports
                                            </a>
                                        </div>
                                    <?php elseif ($role === 'doctor'): ?>
                                        <div class="col-md-4 mb-3">
                                            <a href="doctor/appointments.php?action=schedule" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-calendar-plus"></i> Schedule Appointment
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="doctor/prescriptions.php?action=write" class="btn btn-success btn-lg w-100">
                                                <i class="fas fa-prescription"></i> Write Prescription
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="doctor/medical-records.php?action=add" class="btn btn-info btn-lg w-100">
                                                <i class="fas fa-file-medical"></i> Add Medical Record
                                            </a>
                                        </div>
                                    <?php else: // Patient Quick Actions ?>
                                        <div class="col-md-4 mb-3">
                                            <a href="patient/appointments.php?action=book" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-calendar-plus"></i> Book Appointment
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="patient/health-log.php?action=add" class="btn btn-success btn-lg w-100">
                                                <i class="fas fa-heartbeat"></i> Log Health Data
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="patient/payments.php" class="btn btn-info btn-lg w-100">
                                                <i class="fas fa-credit-card"></i> Make Payment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html> 