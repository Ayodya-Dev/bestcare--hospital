<?php
// Shared doctor/staff profile helpers — include after check_login + db.php

if (!isset($staff) || !isset($staff_id)) {
    $user_id = $_SESSION['user_id'];
    $st_sql = "SELECT st.*, d.name AS department_name
               FROM staff st, departments d
               WHERE st.department_id = d.id
               AND st.user_id = $user_id";
    $st_result = mysqli_query($conn, $st_sql);

    if (!$st_result || mysqli_num_rows($st_result) == 0) {
        die("Staff profile not found.");
    }

    $staff = mysqli_fetch_array($st_result);
    $staff_id = $staff['id'];

    $full_name = $staff['full_name'];
    $parts = explode(' ', $full_name);
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (isset($parts[1])) {
        $initials .= strtoupper(substr($parts[1], 0, 1));
    }
    if (isset($parts[2])) {
        $initials = strtoupper(substr($parts[1], 0, 1)) . strtoupper(substr($parts[2], 0, 1));
    }
    $first_name = isset($parts[1]) ? $parts[1] : $parts[0];
}
?>
