<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'staff') {
    echo "Access denied. Doctors only.";
    exit();
}

include("../includes/staff_profile.php");
$active_page = 'records';
$error = "";
$success = "";

$patients = mysqli_query($conn, "SELECT id, full_name FROM patients ORDER BY full_name");

if (isset($_POST['add_record'])) {
    $patient_id = (int)$_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'];
    $notes = $_POST['notes'];
    $visit_date = $_POST['visit_date'];

    if ($patient_id == 0 || strlen($diagnosis) == 0 || strlen($visit_date) == 0) {
        $error = "Need to fill all required fields.";
    } else {
        $safe_diagnosis = mysqli_real_escape_string($conn, $diagnosis);
        $safe_notes = mysqli_real_escape_string($conn, $notes);

        $sql = "INSERT INTO medical_records (patient_id, staff_id, diagnosis, notes, visit_date)
                VALUES ($patient_id, $staff_id, '$safe_diagnosis', '$safe_notes', '$visit_date')";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Could not save record: " . mysqli_error($conn));
        }

        $success = "Medical record added successfully.";
    }
}

$list_sql = "SELECT m.*, p.full_name AS patient_name
             FROM medical_records m, patients p
             WHERE m.patient_id = p.id
             AND m.staff_id = $staff_id
             ORDER BY m.visit_date DESC";
$list = mysqli_query($conn, $list_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=1">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Medical Records</h1>
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
                <h2>Add Medical Record</h2>
                <p class="sub">Save diagnosis and notes after a patient visit.</p>

                <form class="sd-form" method="post" action="">
                    <div class="field">
                        <label for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" required>
                            <option value="">Select patient</option>
                            <?php
                            if ($patients) {
                                mysqli_data_seek($patients, 0);
                                while ($p = mysqli_fetch_array($patients)) {
                                    echo '<option value="' . $p['id'] . '">' . htmlspecialchars($p['full_name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="visit_date">Visit Date *</label>
                        <input type="date" name="visit_date" id="visit_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="field">
                        <label for="diagnosis">Diagnosis *</label>
                        <textarea name="diagnosis" id="diagnosis" required placeholder="e.g. Hypertension follow-up"></textarea>
                    </div>

                    <div class="field">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes" placeholder="Treatment plan, advice, etc."></textarea>
                    </div>

                    <button type="submit" name="add_record" value="1" class="sd-btn">Save Record</button>
                </form>
            </section>

            <section class="sd-panel">
                <h2>Records You Added</h2>
                <p class="sub">Recent medical records written by you.</p>

                <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Diagnosis</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($list)) { ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['visit_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="sd-empty">No records added yet.</div>
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
