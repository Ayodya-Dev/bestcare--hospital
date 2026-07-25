<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'staff') {
    echo "Access denied. Doctors only.";
    exit();
}

include("../includes/staff_profile.php");
$active_page = 'appointments';
$notice = "";
$error = "";

// Confirm / Cancel / Complete
if (isset($_POST['action_id']) && isset($_POST['new_status'])) {
    $action_id = (int)$_POST['action_id'];
    $new_status = $_POST['new_status'];

    if ($new_status == 'Confirmed' || $new_status == 'Cancelled' || $new_status == 'Completed') {
        // Only allow updates on THIS doctor's appointments
        $check = mysqli_query($conn, "SELECT status FROM appointments WHERE id=$action_id AND staff_id=$staff_id");
        if ($check && mysqli_num_rows($check) > 0) {
            $cur = mysqli_fetch_array($check);
            $ok = false;

            if ($new_status == 'Confirmed' && $cur['status'] == 'Pending') {
                $ok = true;
            }
            if ($new_status == 'Cancelled' && ($cur['status'] == 'Pending' || $cur['status'] == 'Confirmed')) {
                $ok = true;
            }
            if ($new_status == 'Completed' && $cur['status'] == 'Confirmed') {
                $ok = true;
            }

            if ($ok) {
                mysqli_query($conn, "UPDATE appointments SET status='$new_status' WHERE id=$action_id AND staff_id=$staff_id");
                $notice = "Appointment updated to $new_status.";
            } else {
                $error = "That status change is not allowed for this appointment.";
            }
        } else {
            $error = "Appointment not found.";
        }
    }
}

$sql = "SELECT a.*, p.full_name AS patient_name, p.contact AS patient_contact, s.name AS service_name
        FROM appointments a, patients p, services s
        WHERE a.patient_id = p.id
        AND a.service_id = s.id
        AND a.staff_id = $staff_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=1">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Appointments</h1>
        <span class="role-tag">Doctor Portal</span>
    </header>

    <main class="sd-content">
        <?php if ($notice != "") { ?>
            <div class="sd-alert ok"><?php echo $notice; ?></div>
        <?php } ?>
        <?php if ($error != "") { ?>
            <div class="sd-alert err"><?php echo $error; ?></div>
        <?php } ?>

        <section class="sd-panel">
            <h2>Patient Appointments</h2>
            <p class="sub">Confirm, complete, or cancel bookings assigned to you.</p>

            <?php if ($result && mysqli_num_rows($result) > 0) { ?>
                <div class="sd-table-wrap">
                    <table class="sd-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Contact</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($result)) {
                                $status = $row['status'];
                            ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_contact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                    <td><span class="sd-status <?php echo strtolower($status); ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                    <td>
                                        <div class="sd-actions">
                                            <?php if ($status == 'Pending') { ?>
                                                <form method="post" action="">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Confirmed">
                                                    <button type="submit" class="sd-btn-outline">Confirm</button>
                                                </form>
                                                <form method="post" action="" onsubmit="return confirm('Cancel this appointment?');">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Cancelled">
                                                    <button type="submit" class="sd-btn-danger">Cancel</button>
                                                </form>
                                            <?php } elseif ($status == 'Confirmed') { ?>
                                                <form method="post" action="">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Completed">
                                                    <button type="submit" class="sd-btn-blue">Complete</button>
                                                </form>
                                                <form method="post" action="" onsubmit="return confirm('Cancel this appointment?');">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Cancelled">
                                                    <button type="submit" class="sd-btn-danger">Cancel</button>
                                                </form>
                                            <?php } else { ?>
                                                <span style="color:#d1d5db;">—</span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="sd-empty">No appointments assigned to you yet.</div>
            <?php } ?>
        </section>
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
