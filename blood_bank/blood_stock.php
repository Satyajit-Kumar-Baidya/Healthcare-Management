<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Stock - Blood Bank</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #f5f5f5; color: #00695c; }
    </style>
</head>
<body>
<div class="overlay"></div>
<?php include 'navigation.php'; ?>
<div class="container">
    <h1>Blood Stock</h1>
    <table>
        <tr>
            <th>Blood Group</th>
            <th>Quantity</th>
        </tr>
        <?php
        if (file_exists('blood_stock.txt')) {
            $lines = file('blood_stock.txt');
            foreach ($lines as $i => $line) {
                if ($i === 0) continue; // Skip header
                $parts = explode('|', trim($line));
                if (count($parts) >= 2) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($parts[0]) . '</td>';
                    echo '<td>' . htmlspecialchars($parts[1]) . '</td>';
                    echo '</tr>';
                }
            }
        }
        ?>
    </table>
</div>
</body>
</html> 