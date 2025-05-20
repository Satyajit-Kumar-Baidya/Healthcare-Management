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
$doctors = [];
$errors = [];
$success = '';

// Get patient ID
try {
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $patientId = $stmt->fetchColumn();

    if (!$patientId) {
        // If patient record not found, log out user
        session_destroy();
        header("Location: ../index.php");
        exit();
    }

    // Fetch list of doctors for the dropdown
    $stmt = $pdo->query("SELECT d.id, u.first_name, u.last_name, d.specialization FROM doctors d JOIN users u ON d.user_id = u.id ORDER BY u.last_name, u.first_name");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errors[] = "Database Error: " . $e->getMessage();
}

// Handle appointment booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $doctorId = $_POST['doctor'] ?? '';
    $appointmentDate = $_POST['appointment_date'] ?? '';
    $appointmentTime = $_POST['appointment_time'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $status = 'Pending'; // Changed from 'scheduled'

    // Basic validation
    if (empty($doctorId)) $errors[] = 'Please select a doctor.';
    if (empty($appointmentDate)) $errors[] = 'Please select a date.';
    if (empty($appointmentTime)) $errors[] = 'Please select a time.';
    if (empty($reason)) $errors[] = 'Please provide a reason for the appointment.';

    // Combine date and time
    $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;

    // Further validation (e.g., check if the date is in the future, check doctor's actual availability)
    // This is basic validation, more robust checks would be needed in a production system.

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, reason, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$patientId, $doctorId, $appointmentDateTime, $reason, $status]);

            $success = 'Appointment booked successfully!';
            // Clear form fields after successful submission (optional)
            $_POST = []; 

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    } else {
        // If there are validation errors, $errors array is already populated
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Healthcare System</title>
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
                    <a href="prescriptions.php"><i class="fas fa-prescription"></i> Prescriptions</a>
                    <a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a>
                    <a href="health-log.php"><i class="fas fa-heartbeat"></i> Health Log</a>
                    <a href="../profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="welcome-section">
                    <h2>Book New Appointment</h2>
                    <p>Select a doctor and preferred time slot to book an appointment.</p>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Appointment Details</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <ul>
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

                                <form action="book_appointment.php" method="POST">
                                    <div class="mb-3">
                                        <label for="doctor" class="form-label">Select Doctor</label>
                                        <select class="form-select" id="doctor" name="doctor" required>
                                            <option value="">-- Select a Doctor --</option>
                                            <?php foreach ($doctors as $doctor): ?>
                                                <option value="<?php echo htmlspecialchars($doctor['id']); ?>">
                                                    Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?> (<?php echo htmlspecialchars($doctor['specialization']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="appointment_date" class="form-label">Preferred Date</label>
                                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="appointment_time" class="form-label">Preferred Time</label>
                                        <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                                    </div>

                                     <div class="mb-3">
                                        <label for="reason" class="form-label">Reason for Appointment</label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                    </div>

                                    <button type="submit" name="book_appointment" class="btn btn-primary">Book Appointment</button>
                                </form>
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