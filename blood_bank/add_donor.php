<?php
session_start();
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_doctor = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'doctor';

// Check if user has permission to add donors
if (!$is_admin && !$is_doctor) {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '');

    if ($name && $address && $email && $phone && $blood_type) {
        $donor_data = "$name|$address|$email|$phone|$blood_type\n";
        if (file_put_contents('donors.txt', $donor_data, FILE_APPEND)) {
            $message = '<div class="success">Donor added successfully!</div>';
        } else {
            $message = '<div class="error">Error adding donor. Please try again.</div>';
        }
    } else {
        $message = '<div class="error">All fields are required.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Donor - Blood Bank</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            background: #e53935;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #c62828;
        }
        .success {
            color: green;
            padding: 10px;
            margin-bottom: 15px;
            background: #e8f5e9;
            border-radius: 4px;
        }
        .error {
            color: red;
            padding: 10px;
            margin-bottom: 15px;
            background: #ffebee;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="overlay"></div>
<?php include 'navigation.php'; ?>

<div class="form-container">
    <h1>Add New Donor</h1>
    <?php echo $message; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        
        <div class="form-group">
            <label for="blood_type">Blood Type:</label>
            <select id="blood_type" name="blood_type" required>
                <option value="">Select Blood Type</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>
        
        <button type="submit" class="submit-btn">Add Donor</button>
    </form>
</div>

</body>
</html> 