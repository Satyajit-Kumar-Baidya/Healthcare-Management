<?php
session_start();
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_person = isset($_SESSION['person_logged_in']) && $_SESSION['person_logged_in'] === true;
$is_doctor = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'doctor';
$is_patient = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'patient';

// Give doctors admin-like access
if ($is_doctor) {
    $is_admin = true;
}

$can_edit = $is_admin || $is_doctor;

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="topnav">
    <a href="../index.php" style="background: #e53935; color: white;">Back to Healthcare</a>
    <a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a>
    <a href="donors.php" <?php echo $current_page == 'donors.php' ? 'class="active"' : ''; ?>>Donor List</a>
    <a href="search.php" <?php echo $current_page == 'search.php' ? 'class="active"' : ''; ?>>Search Donor</a>
    <?php if ($is_admin || $is_doctor): ?>
        <a href="add_donor.php" <?php echo $current_page == 'add_donor.php' ? 'class="active"' : ''; ?>>Add Donor</a>
        <a href="persons_list.php" <?php echo $current_page == 'persons_list.php' ? 'class="active"' : ''; ?>>View Persons</a>
        <a href="admin.php" <?php echo $current_page == 'admin.php' ? 'class="active"' : ''; ?>>Admin Dashboard</a>
    <?php endif; ?>
    <?php if ($is_person || $is_patient): ?>
        <a href="request.php" <?php echo $current_page == 'request.php' ? 'class="active"' : ''; ?>>Request Blood</a>
    <?php endif; ?>
    <?php if (!$is_admin && !$is_person && !$is_doctor && !$is_patient): ?>
        <a href="login.php" <?php echo $current_page == 'login.php' ? 'class="active"' : ''; ?>>Login</a>
    <?php endif; ?>
</div> 