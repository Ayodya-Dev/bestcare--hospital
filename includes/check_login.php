<?php
require_once __DIR__ . "/session_init.php";

$portal = bestcare_session_role_from_path();

// Extra fallback from full file path (Windows-safe)
if ($portal == "public" && !empty($_SERVER['SCRIPT_FILENAME'])) {
    $file = str_replace("\\", "/", strtolower($_SERVER['SCRIPT_FILENAME']));
    if (strpos($file, "/staff/") !== false) {
        $portal = "staff";
    } elseif (strpos($file, "/admin/") !== false) {
        $portal = "admin";
    } elseif (strpos($file, "/patient/") !== false) {
        $portal = "patient";
    }
}

if ($portal == "public") {
    header("Location: ../auth/login.php");
    exit();
}

bestcare_start_session($portal);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != $portal) {
    header("Location: ../auth/login.php");
    exit();
}
?>
