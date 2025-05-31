<?php
session_start();
$is_person = isset($_SESSION['person_logged_in']) && $_SESSION['person_logged_in'] === true;
$is_patient = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'patient';

if (!$is_person && !$is_patient) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_name = htmlspecialchars($_POST['donor_name']);
    $donor_blood = htmlspecialchars($_POST['donor_blood']);
    $donor_email = htmlspecialchars($_POST['donor_email']);
    $requester_name = htmlspecialchars($_POST['requester_name']);
    $requester_email = htmlspecialchars($_POST['requester_email']);
    $requester_phone = htmlspecialchars($_POST['requester_phone']);
    
    // Add timestamp to the request
    $timestamp = date('Y-m-d H:i:s');
    $line = "$donor_name|$donor_blood|$donor_email|$requester_name|$requester_email|$requester_phone|$timestamp\n";
    
    if (file_put_contents('requests.txt', $line, FILE_APPEND)) {
        header('Location: request.php?requested=1');
    } else {
        header('Location: request.php?error=1');
    }
    exit();
} else {
    header('Location: donors.php');
    exit();
} 