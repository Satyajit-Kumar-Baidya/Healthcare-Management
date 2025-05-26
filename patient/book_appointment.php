<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
require_once '../dbConnect.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

// Check if doctor_id is provided
if (!isset($_GET['doctor_id'])) {
    header("Location: browse_doctors.php");
    exit();
}

$doctor_id = $_GET['doctor_id'];

// Fetch doctor details
$stmt = $pdo->prepare("
    SELECT 
        d.*,
        u.first_name,
        u.last_name,
        u.email,
        u.profile_image
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    header("Location: browse_doctors.php");
    exit();
}

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO appointments (
                doctor_id, 
                patient_id, 
                appointment_date, 
                appointment_time, 
                reason, 
                status
            ) VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $doctor_id,
            $_SESSION['patient_id'],
            $_POST['appointment_date'],
            $_POST['appointment_time'],
            $_POST['reason']
        ]);
        
        $_SESSION['success_message'] = "Appointment request submitted successfully!";
        header("Location: my_appointments.php");
        exit();
    } catch (Exception $e) {
        $error_message = "Error booking appointment: " . $e->getMessage();
    }
}

// Get doctor's available days
$available_days = explode(',', $doctor['available_days']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .doctor-profile {
            background-color: #f8f9fa;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .doctor-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .doctor-avatar i {
            font-size: 60px;
            color: #6c757d;
        }
        .qualification-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        .time-slot {
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .time-slot:hover {
            background-color: #e9ecef;
        }
        .time-slot.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="doctor-profile">
                    <div class="doctor-avatar">
                        <?php if ($doctor['profile_image']): ?>
                            <img src="<?php echo htmlspecialchars($doctor['profile_image']); ?>" alt="Doctor" class="img-fluid">
                        <?php else: ?>
                            <i class="bi bi-person-circle"></i>
                        <?php endif; ?>
                    </div>
                    
                    <h4 class="text-center mb-3">Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></h4>
                    <p class="text-center text-primary mb-3"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    
                    <div class="text-center mb-4">
                        <span class="badge bg-primary">
                            <i class="bi bi-star-fill"></i> <?php echo $doctor['experience']; ?>+ Years Experience
                        </span>
                    </div>

                    <div class="mb-3">
                        <h6>Qualifications</h6>
                        <?php
                        $qualifications = explode(',', $doctor['qualification']);
                        foreach ($qualifications as $qual):
                        ?>
                            <span class="qualification-badge"><?php echo htmlspecialchars(trim($qual)); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <h6>Hospital</h6>
                        <p class="mb-1"><?php echo htmlspecialchars($doctor['hospital']); ?></p>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($doctor['location']); ?>
                        </small>
                    </div>

                    <div class="mb-3">
                        <h6>Consultation Fee</h6>
                        <p class="mb-0">$<?php echo htmlspecialchars($doctor['consultation_fee']); ?></p>
                    </div>

                    <div>
                        <h6>Available Days</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($doctor['available_days']); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Book an Appointment</h4>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="appointmentForm">
                            <div class="mb-3">
                                <label class="form-label">Select Date</label>
                                <input type="date" class="form-control" name="appointment_date" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       onchange="validateDate(this.value)">
                                <div class="form-text">Please select from available days: <?php echo implode(', ', $available_days); ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Time</label>
                                <div class="time-slots d-flex flex-wrap" id="timeSlots">
                                    <?php
                                    $start_time = strtotime('09:00');
                                    $end_time = strtotime('17:00');
                                    $interval = 30 * 60; // 30 minutes

                                    for ($time = $start_time; $time <= $end_time; $time += $interval) {
                                        $formatted_time = date('H:i', $time);
                                        echo '<div class="time-slot" onclick="selectTime(this)" data-time="' . $formatted_time . '">';
                                        echo date('g:i A', $time);
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <input type="hidden" name="appointment_time" id="selectedTime" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason for Visit</label>
                                <textarea class="form-control" name="reason" rows="4" required
                                          placeholder="Please describe your symptoms or reason for consultation"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-calendar-check"></i> Request Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const availableDays = <?php echo json_encode($available_days); ?>;
        
        function validateDate(date) {
            const selectedDate = new Date(date);
            const dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' });
            
            if (!availableDays.includes(dayName)) {
                alert('Doctor is not available on ' + dayName + 's. Please select another day.');
                document.querySelector('input[name="appointment_date"]').value = '';
            }
        }

        function selectTime(element) {
            // Remove selected class from all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selected class to clicked time slot
            element.classList.add('selected');
            
            // Update hidden input
            document.getElementById('selectedTime').value = element.dataset.time;
        }

        // Form validation
        document.getElementById('appointmentForm').onsubmit = function(e) {
            if (!document.getElementById('selectedTime').value) {
                e.preventDefault();
                alert('Please select an appointment time');
            }
        };
    </script>
</body>
</html> 