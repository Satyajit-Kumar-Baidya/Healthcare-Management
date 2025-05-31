<?php
session_start();
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_doctor = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'doctor';

// Check if user has permission to delete
if (!$is_admin && !$is_doctor) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['index'])) {
    $index = (int)$_POST['index'];
    
    if (file_exists('donors.txt')) {
        $lines = file('donors.txt');
        if (isset($lines[$index])) {
            // Remove the line
            unset($lines[$index]);
            // Save back to file
            file_put_contents('donors.txt', implode('', $lines));
        }
    }
}

// Redirect back to donors list
header("Location: donors.php");
exit(); 