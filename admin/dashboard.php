<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'dashboard';
$admin_name = $_SESSION['username'];

function adm_count($conn, $sql) {
    $r = mysqli_query($conn, $sql);
    if (!$r) {
        return 0;
    }
    $row = mysqli_fetch_array($r);
    return (int)$row['c'];
}

$count_patients = adm_count($conn, "SELECT COUNT(*) AS c FROM patients");
$count_staff = adm_count($conn, "SELECT COUNT(*) AS c FROM staff");
$count_appts = adm_count($conn, "SELECT COUNT(*) AS c FROM appointments");
$count_pending = adm_count($conn, "SELECT COUNT(*) AS c FROM appointments WHERE status='Pending'");
$count_open_q = adm_count($conn, "SELECT COUNT(*) AS c FROM queries WHERE status='Open'");
$count_active_staff = adm_count($conn, "SELECT COUNT(*) AS c FROM users WHERE role='staff' AND is_active=1");

$recent_sql = "SELECT a.*, p.full_name AS patient_name, st.full_name AS doctor_name, s.name AS service_name
               FROM appointments a, patients p, staff st, services s
               WHERE a.patient_id = p.id
               AND a.staff_id = st.id
               AND a.service_id = s.id
               ORDER BY a.created_at DESC
               LIMIT 8";
$recent = mysqli_query($conn, $recent_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=1">
</head>
<body class="adm-body">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Admin Dashboard</h1>
        <span class="role-tag">Administrator</span>
    </header>

    <main class="adm-content">
        <section class="adm-stats">
            <div class="adm-stat">
                <p class="label">Patients</p>
                <p class="value"><?php echo $count_patients; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Doctors</p>
                <p class="value"><?php echo $count_staff; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Appointments</p>
                <p class="value"><?php echo $count_appts; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Pending</p>
                <p class="value"><?php echo $count_pending; ?></p>
            </div>
        </section>

        <div class="adm-grid-2">
            <section class="adm-panel">
                <h2>Recent Appointments</h2>
                <p class="sub">Latest bookings across all doctors.</p>

                <?php if ($recent && mysqli_num_rows($recent) > 0) { ?>
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($recent)) { ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                        <td><span class="adm-status <?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="adm-empty">No appointments yet.</div>
                <?php } ?>
            </section>

            <section class="adm-panel">
                <h2>Quick Actions</h2>
                <p class="sub">Hospital administration shortcuts.</p>
                <div class="adm-quick">
                    <a href="appointments.php">
                        <strong>All Appointments</strong>
                        <span>View every patient booking</span>
                    </a>
                    <a href="manage-patients.php">
                        <strong>Manage Patients</strong>
                        <span><?php echo $count_patients; ?> registered patient(s)</span>
                    </a>
                    <a href="manage-queries.php">
                        <strong>Patient Queries</strong>
                        <span><?php echo $count_open_q; ?> query(s) waiting for a reply</span>
                    </a>
                    <a href="reports.php">
                        <strong>View Reports</strong>
                        <span>Appointment and patient summaries</span>
                    </a>
                    <a href="manage-staff.php">
                        <strong>Manage Staff</strong>
                        <span><?php echo $count_active_staff; ?> active doctor account(s)</span>
                    </a>
                </div>
            </section>
        </div>
    </main>
</div>

<script>
document.getElementById('admMenuBtn').addEventListener('click', function () {
    document.getElementById('admNav').classList.toggle('open');
    document.getElementById('admSideFooter').classList.toggle('open');
});
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
