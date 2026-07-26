<?php
// Database configuration
require_once __DIR__ . "/error_helpers.php";

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "bestcare_hospital";
$port = 3308;  // same MySQL/MariaDB server as phpMyAdmin (foodcare_db)

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

// Check connection — do not show raw MySQL details to users
if (!$conn) {
    error_log("BestCare connection failed: " . mysqli_connect_error());
    bestcare_fail_page("The hospital system is temporarily unavailable. Please try again later.");
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
?>
