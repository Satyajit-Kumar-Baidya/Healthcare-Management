<?php
session_start();
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_person = isset($_SESSION['person_logged_in']) && $_SESSION['person_logged_in'] === true;
$is_doctor = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'doctor';
$is_patient = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'patient';
$can_edit = $is_admin || $is_doctor;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors - Blood Bank</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="overlay"></div>
<?php include 'navigation.php'; ?>
    <h1>Donor List</h1>
    <table>
        <tr>
            <th>Name</th>
            <th>Address</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Blood Type</th>
            <th>Request</th>
            <?php if ($can_edit) echo '<th>Actions</th>'; ?>
        </tr>
        <?php
        if (file_exists('donors.txt')) {
            $lines = file('donors.txt');
            foreach ($lines as $i => $line) {
                $parts = explode('|', trim($line));
                if (count($parts) === 5) {
                    list($name, $address, $email, $phone, $blood_type) = $parts;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($name) . "</td>";
                    echo "<td>" . htmlspecialchars($address) . "</td>";
                    echo "<td>" . htmlspecialchars($email) . "</td>";
                    echo "<td>" . htmlspecialchars($phone) . "</td>";
                    echo "<td>" . htmlspecialchars($blood_type) . "</td>";
                    echo '<td>';
                    if ($is_person || $is_patient) {
                        echo '<form action="request.php" method="get" style="margin:0;"><input type="hidden" name="name" value="'.htmlspecialchars($name).'">';
                        echo '<input type="hidden" name="blood_type" value="'.htmlspecialchars($blood_type).'">';
                        echo '<input type="hidden" name="email" value="'.htmlspecialchars($email).'">';
                        echo '<input type="submit" value="Request"></form>';
                    } else {
                        echo '<span style="color:#888;">Login to request</span>';
                    }
                    echo '</td>';
                    if ($can_edit) {
                        echo '<td>';
                        echo '<form action="edit_donor.php" method="get" style="display:inline;margin:0;"><input type="hidden" name="index" value="'.$i.'"><input type="submit" value="Edit"></form>';
                        echo ' | ';
                        echo '<form action="delete_donor.php" method="post" style="display:inline;margin:0;" onsubmit="return confirm(\'Are you sure you want to delete this donor?\');">';
                        echo '<input type="hidden" name="index" value="'.$i.'">';
                        echo '<input type="submit" value="Delete" style="color:red;">';
                        echo '</form>';
                        echo '</td>';
                    }
                    echo "</tr>";
                }
            }
        }
        ?>
    </table>
</body>
</html> 