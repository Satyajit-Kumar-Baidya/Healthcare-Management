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
            // Get the blood group before removing
            $parts = explode('|', trim($lines[$index]));
            $blood_type = isset($parts[4]) ? trim($parts[4]) : '';
            // Remove the line
            unset($lines[$index]);
            // Save back to file
            file_put_contents('donors.txt', implode('', $lines));
            // Update blood_stock.txt
            if ($blood_type !== '') {
                $stock_file = 'blood_stock.txt';
                $blood_stock = [];
                if (file_exists($stock_file)) {
                    $stock_lines = file($stock_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($stock_lines as $i => $line) {
                        if ($i === 0) continue; // skip header
                        list($bg, $qty, $place) = array_pad(explode('|', $line), 3, '');
                        $blood_stock[$bg] = ['qty' => (int)$qty, 'place' => $place];
                    }
                }
                if (isset($blood_stock[$blood_type])) {
                    $blood_stock[$blood_type]['qty'] -= 1;
                    if ($blood_stock[$blood_type]['qty'] <= 0) {
                        unset($blood_stock[$blood_type]);
                    }
                }
                // Write back to file
                $out = "Blood_group|Quantity|Place\n";
                foreach ($blood_stock as $bg => $info) {
                    $out .= "$bg|{$info['qty']}|{$info['place']}\n";
                }
                file_put_contents($stock_file, $out);
            }
        }
    }
}

// Redirect back to donors list
header("Location: donors.php");
exit(); 