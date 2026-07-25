<?php
require_once __DIR__ . "/../includes/session_init.php";

// Log out only the portal you came from so the other tab can stay logged in.
$roles = array();

if (isset($_GET['role']) && ($_GET['role'] == 'patient' || $_GET['role'] == 'staff' || $_GET['role'] == 'admin')) {
    $roles[] = $_GET['role'];
} else {
    $ref = "";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $ref = str_replace("\\", "/", strtolower($_SERVER['HTTP_REFERER']));
    }

    if (preg_match('#(^|/)patient(/|$)#', $ref)) {
        $roles[] = "patient";
    } elseif (preg_match('#(^|/)staff(/|$)#', $ref)) {
        $roles[] = "staff";
    } elseif (preg_match('#(^|/)admin(/|$)#', $ref)) {
        $roles[] = "admin";
    } else {
        $roles = array("patient", "staff", "admin");
    }
}

foreach ($roles as $role) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ));
    } else {
        session_set_cookie_params(0, '/');
    }

    session_name(bestcare_session_name_for_role($role));
    session_start();
    $_SESSION = array();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), "", time() - 3600, "/");
    }

    session_destroy();
}

header("Location: ../index.php");
exit();
?>
