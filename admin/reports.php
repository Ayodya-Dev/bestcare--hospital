<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'reports';
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
$count_services = adm_count($conn, "SELECT COUNT(*) AS c FROM services");
$count_records = adm_count($conn, "SELECT COUNT(*) AS c FROM medical_records");
$count_tests = adm_count($conn, "SELECT COUNT(*) AS c FROM test_results");
$count_rx = adm_count($conn, "SELECT COUNT(*) AS c FROM prescriptions");
$count_queries = adm_count($conn, "SELECT COUNT(*) AS c FROM queries");
$count_open_q = adm_count($conn, "SELECT COUNT(*) AS c FROM queries WHERE status='Open'");

$count_pending = adm_count($conn, "SELECT COUNT(*) AS c FROM appointments WHERE status='Pending'");
$count_confirmed = adm_count($conn, "SELECT COUNT(*) AS c FROM appointments WHERE status='Confirmed'");
$count_completed = adm_count($conn, "SELECT COUNT(*) AS c FROM appointments WHERE status='Completed'");
$count_cancelled = adm_count($conn, "SELECT COUNT(*) AS c FROM appointments WHERE status='Cancelled'");
$count_total_appts = $count_pending + $count_confirmed + $count_completed + $count_cancelled;

$month = date('Y-m');
$count_month = adm_count($conn, "SELECT COUNT(*) AS c FROM appointments WHERE DATE_FORMAT(appointment_date, '%Y-%m')='$month'");

// Appointments per doctor
$by_doctor = mysqli_query($conn, "SELECT st.full_name, COUNT(a.id) AS total
                                  FROM staff st
                                  LEFT JOIN appointments a ON a.staff_id = st.id
                                  GROUP BY st.id, st.full_name
                                  ORDER BY total DESC");

// Appointments per service
$by_service = mysqli_query($conn, "SELECT s.name, COUNT(a.id) AS total
                                   FROM services s
                                   LEFT JOIN appointments a ON a.service_id = s.id
                                   GROUP BY s.id, s.name
                                   ORDER BY total DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=1">
</head>
<body class="adm-body">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Reports</h1>
        <span class="role-tag">Administrator</span>
    </header>

    <main class="adm-content">
        <section class="adm-stats">
            <div class="adm-stat">
                <p class="label">Total Patients</p>
                <p class="value"><?php echo $count_patients; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Doctors</p>
                <p class="value"><?php echo $count_staff; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">This Month</p>
                <p class="value"><?php echo $count_month; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">All Appointments</p>
                <p class="value"><?php echo $count_total_appts; ?></p>
            </div>
        </section>

        <section class="adm-panel">
            <h2>Appointments by Status</h2>
            <p class="sub">Breakdown of every booking in the system.</p>
            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="adm-status pending">Pending</span></td>
                            <td><?php echo $count_pending; ?></td>
                        </tr>
                        <tr>
                            <td><span class="adm-status confirmed">Confirmed</span></td>
                            <td><?php echo $count_confirmed; ?></td>
                        </tr>
                        <tr>
                            <td><span class="adm-status completed">Completed</span></td>
                            <td><?php echo $count_completed; ?></td>
                        </tr>
                        <tr>
                            <td><span class="adm-status cancelled">Cancelled</span></td>
                            <td><?php echo $count_cancelled; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="adm-grid-2">
            <section class="adm-panel">
                <h2>Appointments per Doctor</h2>
                <p class="sub">How many bookings each doctor has received.</p>
                <?php if ($by_doctor && mysqli_num_rows($by_doctor) > 0) { ?>
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Appointments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($by_doctor)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo (int)$row['total']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="adm-empty">No data yet.</div>
                <?php } ?>
            </section>

            <section class="adm-panel">
                <h2>Appointments per Service</h2>
                <p class="sub">Which hospital services are booked most.</p>
                <?php if ($by_service && mysqli_num_rows($by_service) > 0) { ?>
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($by_service)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo (int)$row['total']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="adm-empty">No data yet.</div>
                <?php } ?>
            </section>
        </div>

        <section class="adm-panel">
            <h2>Other System Totals</h2>
            <p class="sub">Clinical and support activity snapshot.</p>
            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Services offered</td><td><?php echo $count_services; ?></td></tr>
                        <tr><td>Medical records</td><td><?php echo $count_records; ?></td></tr>
                        <tr><td>Test results</td><td><?php echo $count_tests; ?></td></tr>
                        <tr><td>Prescriptions</td><td><?php echo $count_rx; ?></td></tr>
                        <tr><td>Patient queries (all)</td><td><?php echo $count_queries; ?></td></tr>
                        <tr><td>Open queries</td><td><?php echo $count_open_q; ?></td></tr>
                    </tbody>
                </table>
            </div>
        </section>
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
