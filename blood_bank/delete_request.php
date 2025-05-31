<?php
session_start();
$is_person = isset($_SESSION['person_logged_in']) && $_SESSION['person_logged_in'] === true;
$is_patient = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'patient';

if (!$is_person && !$is_patient) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_index'])) {
    $index = (int)$_POST['request_index'];
    
    if (file_exists('requests.txt')) {
        $lines = file('requests.txt');
        $user_email = '';
        
        // Get user email based on role
        if ($is_person) {
            $username = $_SESSION['person_username'];
            $person_lines = file('persons.txt');
            foreach ($person_lines as $line) {
                $parts = explode('|', trim($line));
                if (count($parts) === 5 && $parts[0] === $username) {
                    $user_email = $parts[1];
                    break;
                }
            }
        } elseif ($is_patient && isset($_SESSION['user'])) {
            $user_email = $_SESSION['user']['email'];
        }
        
        // Only delete if the request belongs to the user
        if (isset($lines[$index])) {
            $request_parts = explode('|', trim($lines[$index]));
            if (count($request_parts) >= 6 && $request_parts[4] === $user_email) {
                unset($lines[$index]);
                file_put_contents('requests.txt', implode('', $lines));
            }
        }
    }
}

// Redirect back to request page
header('Location: request.php');
exit(); 