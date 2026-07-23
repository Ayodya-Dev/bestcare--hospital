<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($page_title)) {
    $page_title = "BestCare Hospital";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=5">
</head>
<body>
    <div class="top-bar">
        <div class="brand">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="BestCare Logo" width="40" height="40">
            BestCare Hospital
        </div>
        <div class="nav-links">
            <a href="/bestcare-hospital/index.php">Home</a>
            <a href="/bestcare-hospital/services.php">Services</a>
            <a href="/bestcare-hospital/doctors.php">Doctors</a>
            <a href="/bestcare-hospital/search.php">Search</a>
            <a href="/bestcare-hospital/contact.php">Contact</a>
            <?php if (isset($_SESSION['user_id'])) { ?>
                <?php if ($_SESSION['role'] == 'admin') { ?>
                    <a href="/bestcare-hospital/admin/dashboard.php">Dashboard</a>
                <?php } elseif ($_SESSION['role'] == 'staff') { ?>
                    <a href="/bestcare-hospital/staff/dashboard.php">Dashboard</a>
                <?php } else { ?>
                    <a href="/bestcare-hospital/patient/dashboard.php">Dashboard</a>
                <?php } ?>
                <a href="/bestcare-hospital/auth/logout.php">Logout</a>
            <?php } else { ?>
                <a href="/bestcare-hospital/auth/login.php">Login</a>
                <a href="/bestcare-hospital/auth/register.php">Register</a>
            <?php } ?>
        </div>
    </div>
