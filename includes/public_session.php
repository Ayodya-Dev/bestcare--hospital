<?php
// Shared public page session + common links.
// Include this at the very top of public pages (before any HTML output).
//
// Logins are stored in named sessions (BC_PATIENT / BC_STAFF / BC_ADMIN) so a
// patient and a doctor can stay logged in at the same time. A plain
// session_start() here would use a different cookie and never see them, so the
// public pages look up those named sessions instead.
require_once __DIR__ . "/session_init.php";

$pub_img = "/bestcare-hospital/assets/home";

$pub_user_id = 0;
$pub_role = "";
$pub_username = "";

// Patient is checked first because the public site (booking, queries) is
// written for patients.
$pub_roles = array("patient", "staff", "admin");

for ($i = 0; $i < count($pub_roles); $i++) {
    $role = $pub_roles[$i];
    $cookie_name = bestcare_session_name_for_role($role);

    if (!isset($_COOKIE[$cookie_name])) {
        continue;
    }
    if (!preg_match('/^[A-Za-z0-9,\-]{1,128}$/', $_COOKIE[$cookie_name])) {
        continue;
    }

    bestcare_start_session($role);

    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == $role) {
        $pub_user_id = $_SESSION['user_id'];
        $pub_role = $_SESSION['role'];
        $pub_username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

        // Keep the patient session open so contact.php can save a query.
        if ($role == "patient") {
            break;
        }
    }

    session_write_close();

    if ($pub_user_id > 0) {
        break;
    }
}

// Visitors (and staff/admin browsing the public site) get the public session.
if (session_status() !== PHP_SESSION_ACTIVE) {
    bestcare_start_session("public");
}

$book_link = "/bestcare-hospital/auth/login.php";
$portal_link = "/bestcare-hospital/auth/login.php";
$pub_dashboard_link = "";
$pub_logout_link = "";

if ($pub_role == "patient") {
    $book_link = "/bestcare-hospital/patient/book-appointment.php";
    $portal_link = "/bestcare-hospital/patient/dashboard.php";
    $pub_dashboard_link = "/bestcare-hospital/patient/dashboard.php";
    $pub_logout_link = "/bestcare-hospital/auth/logout.php?role=patient";
} elseif ($pub_role == "staff") {
    $pub_dashboard_link = "/bestcare-hospital/staff/dashboard.php";
    $pub_logout_link = "/bestcare-hospital/auth/logout.php?role=staff";
} elseif ($pub_role == "admin") {
    $pub_dashboard_link = "/bestcare-hospital/admin/dashboard.php";
    $pub_logout_link = "/bestcare-hospital/auth/logout.php?role=admin";
}
?>
