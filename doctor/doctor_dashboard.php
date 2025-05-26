<?php
session_start();
require_once '../dbConnect.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
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
            background-color: #34495e;
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h3 class="text-center mb-4">Doctor Panel</h3>
                <nav>
                    <a href="doctor_dashboard.php" class="active">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="appointments.php">
                        <i class="fas fa-calendar-check"></i> My Appointments
                    </a>
                    <a href="patients.php">
                        <i class="fas fa-user-injured"></i> My Patients
                    </a>
                    <a href="prescriptions.php">
                        <i class="fas fa-prescription"></i> Prescriptions
                    </a>
                    <a href="schedule.php">
                        <i class="fas fa-clock"></i> My Schedule
                    </a>
                    <a href="profile.php">
                        <i class="fas fa-user-md"></i> My Profile
                    </a>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="alert alert-info">
                    <h2>Welcome Dr. <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
                    <p>This is your doctor dashboard. You can manage your appointments, patients, and prescriptions from here.</p>
                </div>

                <!-- Content will be added here -->
                <div class="alert alert-warning">
                    <i class="fas fa-tools"></i> Doctor dashboard is under construction. More features coming soon!
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 