<?php
// Shared public website header (same nav on Home, Services, etc.)
// Pages must include includes/public_session.php at the top first.
if (!isset($pub_img)) {
    $pub_img = "/bestcare-hospital/assets/home";
}
if (!isset($pub_user_id)) {
    $pub_user_id = 0;
}
if (!isset($pub_dashboard_link)) {
    $pub_dashboard_link = "/bestcare-hospital/auth/login.php";
}
if (!isset($pub_logout_link)) {
    $pub_logout_link = "/bestcare-hospital/auth/logout.php";
}
?>
<!-- Header -->
<header class="vh-header">
    <div class="vh-header-inner">
        <div class="vh-brand">
            <div class="vh-brand-icon">
                <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="BestCare Logo">
            </div>
            <span class="vh-brand-name">BestCare Hospital</span>
        </div>

        <nav class="vh-nav">
            <a href="/bestcare-hospital/index.php">Home</a>
            <a href="/bestcare-hospital/services.php">Services</a>
            <a href="/bestcare-hospital/departments.php">Departments</a>
            <a href="/bestcare-hospital/doctors.php">Doctors</a>
            <a href="/bestcare-hospital/contact.php">Contact</a>
            <a class="vh-nav-search" href="/bestcare-hospital/search.php">
                <img src="<?php echo $pub_img; ?>/IMG_2.svg" alt="Search">
                <span>Search</span>
            </a>
            <?php if ($pub_user_id > 0) { ?>
                <a href="<?php echo $pub_dashboard_link; ?>">Dashboard</a>
                <a href="<?php echo $pub_logout_link; ?>">Logout</a>
            <?php } else { ?>
                <a href="/bestcare-hospital/auth/login.php">Login</a>
                <a href="/bestcare-hospital/auth/register.php">Register</a>
            <?php } ?>
        </nav>
    </div>
</header>
