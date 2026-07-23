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

$sql = "SELECT m.*, st.full_name AS doctor_name
        FROM medical_records m, staff st
        WHERE m.staff_id = st.id
        AND m.patient_id = $patient_id
        ORDER BY m.visit_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=6">
</head>
<body>
<?php include("../includes/patient_nav.php"); ?>

<div class="dash-wrap">
    <h2>Medical Records</h2>
    <p>Your visit history and diagnoses.</p>

    <?php if ($result && mysqli_num_rows($result) > 0) { ?>
        <div class="table-wrap">
            <table class="data-table">
                <tr>
                    <th>Visit Date</th>
                    <th>Doctor</th>
                    <th>Diagnosis</th>
                    <th>Notes</th>
                </tr>
                <?php while ($row = mysqli_fetch_array($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['visit_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                        <td><?php echo htmlspecialchars($row['notes']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } else { ?>
        <p>No medical records yet.</p>
    <?php } ?>
</div>

<script src="/bestcare-hospital/assets/js/main.js?v=6"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
