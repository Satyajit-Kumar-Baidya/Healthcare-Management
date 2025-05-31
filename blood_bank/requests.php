<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - Blood Bank</title>
    <link rel="stylesheet" href="style.css">
</head>
<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$is_admin = true;
$is_person = false;
?>
<body>
<div class="overlay"></div>
<div class="topnav">
    <a href="index.php">Home</a>
    <a href="contact.php">Contact Us</a>
    <a href="donors.php">Donor List</a>
    <a href="search.php">Search Donor</a>
    <a href="admin.php">Admin Dashboard</a>
    <a href="persons_list.php">View Persons</a>
    <a href="logout.php">Logout</a>
</div>
    <h1>Blood Requests</h1>
    <table>
        <tr>
            <th>Donor Name</th>
            <th>Blood Type</th>
            <th>Donor Email</th>
            <th>Requester Name</th>
            <th>Requester Email</th>
            <th>Requester Phone</th>
        </tr>
        <?php
        if (file_exists('requests.txt')) {
            $lines = file('requests.txt');
            foreach ($lines as $line) {
                $parts = explode('|', trim($line));
                if (count($parts) === 6) {
                    list($donor_name, $donor_blood, $donor_email, $requester_name, $requester_email, $requester_phone) = $parts;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($donor_name) . "</td>";
                    echo "<td>" . htmlspecialchars($donor_blood) . "</td>";
                    echo "<td>" . htmlspecialchars($donor_email) . "</td>";
                    echo "<td>" . htmlspecialchars($requester_name) . "</td>";
                    echo "<td>" . htmlspecialchars($requester_email) . "</td>";
                    echo "<td>" . htmlspecialchars($requester_phone) . "</td>";
                    echo "</tr>";
                }
            }
        }
        ?>
    </table>
</body>
</html> 