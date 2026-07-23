<?php
// Shared top bar for patient pages
if (!isset($_SESSION)) {
    // session already started by check_login
}
?>
<div class="top-bar">
    <div class="brand">
        <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="Logo" width="40" height="40">
        BestCare Hospital
    </div>
    <div class="nav-links">
        <a href="/bestcare-hospital/index.php">Home</a>
        <a href="/bestcare-hospital/patient/dashboard.php">Dashboard</a>
        <a href="/bestcare-hospital/patient/book-appointment.php">Book</a>
        <a href="/bestcare-hospital/patient/my-appointments.php">My Appointments</a>
        <a href="/bestcare-hospital/auth/logout.php">Logout</a>
    </div>
</div>
