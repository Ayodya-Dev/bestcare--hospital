<?php
include("../includes/check_login.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=2">
</head>
<body>
    <div class="top-bar">
        <div class="brand">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="Logo" width="40" height="40">
            BestCare Hospital
        </div>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="dash-wrap">
        <h2>Admin Dashboard</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?></p>
    </div>

    <script src="/bestcare-hospital/assets/js/main.js?v=2"></script>
</body>
</html>
