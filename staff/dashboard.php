<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'staff') {
    echo "Access denied. Doctors only.";
    exit();
}

include("../includes/staff_profile.php");
$active_page = 'dashboard';
$today = date('Y-m-d');

// Counts for this doctor only
$c1 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM appointments WHERE staff_id=$staff_id AND status='Pending'");
$pending = mysqli_fetch_array($c1);
$count_pending = $pending['c'];

$c2 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM appointments
                           WHERE staff_id=$staff_id
                           AND appointment_date='$today'
                           AND status IN ('Pending','Confirmed')");
$today_row = mysqli_fetch_array($c2);
$count_today = $today_row['c'];

$c3 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM appointments
                           WHERE staff_id=$staff_id
                           AND status IN ('Pending','Confirmed')
                           AND appointment_date >= '$today'");
$up_row = mysqli_fetch_array($c3);
$count_upcoming = $up_row['c'];

$c4 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM queries WHERE status='Open'");
$q_row = mysqli_fetch_array($c4);
$count_queries = $q_row['c'];

$upcoming_sql = "SELECT a.*, p.full_name AS patient_name, s.name AS service_name
                 FROM appointments a, patients p, services s
                 WHERE a.patient_id = p.id
                 AND a.service_id = s.id
                 AND a.staff_id = $staff_id
                 AND a.status IN ('Pending','Confirmed')
                 AND a.appointment_date >= '$today'
                 ORDER BY a.appointment_date ASC, a.appointment_time ASC
                 LIMIT 8";
$upcoming = mysqli_query($conn, $upcoming_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=1">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Welcome, <?php echo htmlspecialchars($full_name); ?></h1>
        <span class="role-tag"><?php echo htmlspecialchars($staff['specialization']); ?></span>
    </header>

    <main class="sd-content">
        <section class="sd-stats">
            <div class="sd-stat">
                <p class="label">Pending</p>
                <p class="value"><?php echo $count_pending; ?></p>
            </div>
            <div class="sd-stat">
                <p class="label">Today</p>
                <p class="value"><?php echo $count_today; ?></p>
            </div>
            <div class="sd-stat">
                <p class="label">Upcoming</p>
                <p class="value"><?php echo $count_upcoming; ?></p>
            </div>
            <div class="sd-stat">
                <p class="label">Open Queries</p>
                <p class="value"><?php echo $count_queries; ?></p>
            </div>
        </section>

        <div class="sd-grid-2">
            <section class="sd-panel">
                <h2>Upcoming Appointments</h2>
                <p class="sub">Your next scheduled patient visits.</p>

                <?php if ($upcoming && mysqli_num_rows($upcoming) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($upcoming)) { ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                        <td><span class="sd-status <?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p style="margin-top:14px;"><a class="sd-btn-outline" href="appointments.php">Manage all appointments</a></p>
                <?php } else { ?>
                    <div class="sd-empty">No upcoming appointments.</div>
                <?php } ?>
            </section>

            <section class="sd-panel">
                <h2>Quick Actions</h2>
                <p class="sub">Common tasks for your clinic day.</p>
                <div class="sd-quick">
                    <a href="appointments.php">
                        <strong>Confirm Appointments</strong>
                        <span>Review pending booking requests</span>
                    </a>
                    <a href="add-record.php">
                        <strong>Add Medical Record</strong>
                        <span>Save diagnosis and visit notes</span>
                    </a>
                    <a href="add-prescription.php">
                        <strong>Write Prescription</strong>
                        <span>Issue medication for a patient</span>
                    </a>
                    <a href="add-test-result.php">
                        <strong>Add Test Result</strong>
                        <span>Upload lab or diagnostic findings</span>
                    </a>
                    <a href="queries.php">
                        <strong>Reply to Queries</strong>
                        <span>Answer patient questions</span>
                    </a>
                </div>
            </section>
        </div>
    </main>
</div>

<script>
document.getElementById('sdMenuBtn').addEventListener('click', function () {
    document.getElementById('sdNav').classList.toggle('open');
    document.getElementById('sdSideFooter').classList.toggle('open');
});
</script>
</body>
</html>
<?php mysqli_close($conn); ?>
