<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'staff') {
    echo "Access denied. Doctors only.";
    exit();
}

include("../includes/staff_profile.php");
$active_page = 'prescriptions';
$error = "";
$success = "";

$patients = mysqli_query($conn, "SELECT id, full_name FROM patients ORDER BY full_name");

if (isset($_POST['add_prescription'])) {
    $patient_id = (int)$_POST['patient_id'];
    $medication = $_POST['medication'];
    $dosage = $_POST['dosage'];
    $issued_date = $_POST['issued_date'];

    if ($patient_id == 0 || strlen($medication) == 0 || strlen($dosage) == 0 || strlen($issued_date) == 0) {
        $error = "Need to fill all the fields.";
    } else {
        $safe_med = mysqli_real_escape_string($conn, $medication);
        $safe_dosage = mysqli_real_escape_string($conn, $dosage);

        $sql = "INSERT INTO prescriptions (patient_id, staff_id, medication, dosage, issued_date)
                VALUES ($patient_id, $staff_id, '$safe_med', '$safe_dosage', '$issued_date')";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Could not save prescription: " . mysqli_error($conn));
        }

        $success = "Prescription added successfully.";
    }
}

$list_sql = "SELECT pr.*, p.full_name AS patient_name
             FROM prescriptions pr, patients p
             WHERE pr.patient_id = p.id
             AND pr.staff_id = $staff_id
             ORDER BY pr.issued_date DESC";
$list = mysqli_query($conn, $list_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=1">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Prescriptions</h1>
        <span class="role-tag">Doctor Portal</span>
    </header>

    <main class="sd-content">
        <?php if ($error != "") { ?>
            <div class="sd-alert err"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="sd-alert ok"><?php echo $success; ?></div>
        <?php } ?>

        <div class="sd-grid-2">
            <section class="sd-panel">
                <h2>Write Prescription</h2>
                <p class="sub">Issue medication for a patient.</p>

                <form class="sd-form" method="post" action="">
                    <div class="field">
                        <label for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" required>
                            <option value="">Select patient</option>
                            <?php
                            if ($patients) {
                                while ($p = mysqli_fetch_array($patients)) {
                                    echo '<option value="' . $p['id'] . '">' . htmlspecialchars($p['full_name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="medication">Medication *</label>
                        <input type="text" name="medication" id="medication" required placeholder="e.g. Amoxicillin">
                    </div>

                    <div class="field">
                        <label for="dosage">Dosage *</label>
                        <input type="text" name="dosage" id="dosage" required placeholder="e.g. 500mg twice daily for 5 days">
                    </div>

                    <div class="field">
                        <label for="issued_date">Issued Date *</label>
                        <input type="date" name="issued_date" id="issued_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" name="add_prescription" value="1" class="sd-btn">Save Prescription</button>
                </form>
            </section>

            <section class="sd-panel">
                <h2>Prescriptions You Issued</h2>
                <p class="sub">Recent prescriptions written by you.</p>

                <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($list)) { ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['issued_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['medication']); ?></td>
                                        <td><?php echo htmlspecialchars($row['dosage']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="sd-empty">No prescriptions yet.</div>
                <?php } ?>
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
