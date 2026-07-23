<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$user_id = $_SESSION['user_id'];
$p_sql = "SELECT id FROM patients WHERE user_id=$user_id";
$p_result = mysqli_query($conn, $p_sql);
$patient = mysqli_fetch_array($p_result);
$patient_id = $patient['id'];

// Cancel appointment
if (isset($_POST['cancel_id'])) {
    $cancel_id = (int)$_POST['cancel_id'];
    $cancel_sql = "UPDATE appointments SET status='Cancelled'
                   WHERE id=$cancel_id AND patient_id=$patient_id AND status='Pending'";
    mysqli_query($conn, $cancel_sql);
}

$sql = "SELECT a.*, s.name AS service_name, st.full_name AS doctor_name
        FROM appointments a, services s, staff st
        WHERE a.service_id = s.id
        AND a.staff_id = st.id
        AND a.patient_id = $patient_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=6">
</head>
<body>
<?php include("../includes/patient_nav.php"); ?>

<div class="dash-wrap">
    <h2>My Appointments</h2>
    <p><a class="btn-primary" style="display:inline-block;width:auto;padding:10px 18px;text-decoration:none;" href="book-appointment.php">+ Book New</a></p>

    <?php if (mysqli_num_rows($result) > 0) { ?>
        <div class="table-wrap">
            <table class="data-table">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Service</th>
                    <th>Doctor</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = mysqli_fetch_array($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                        <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                        <td><span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td>
                            <?php if ($row['status'] == 'Pending') { ?>
                                <form method="post" action="" style="display:inline;" onsubmit="return confirm('Cancel this appointment?');">
                                    <input type="hidden" name="cancel_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn-cancel">Cancel</button>
                                </form>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } else { ?>
        <p>No appointments yet. <a href="book-appointment.php">Book your first appointment</a>.</p>
    <?php } ?>
</div>

<script src="/bestcare-hospital/assets/js/main.js?v=6"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
