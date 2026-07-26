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
$today = date('Y-m-d');

$patients = mysqli_query($conn, "SELECT id, full_name FROM patients ORDER BY full_name");

$mode = 'list';
$current = null;
$focus_id = 0;

if (isset($_GET['new'])) {
    $mode = 'add';
} elseif (isset($_GET['view'])) {
    $focus_id = (int)$_GET['view'];
} elseif (isset($_GET['edit'])) {
    $focus_id = (int)$_GET['edit'];
}

if ($focus_id > 0) {
    $get_sql = "SELECT pr.*, p.full_name AS patient_name
                FROM prescriptions pr, patients p
                WHERE pr.patient_id = p.id AND pr.id = $focus_id AND pr.staff_id = $staff_id";
    $get_res = mysqli_query($conn, $get_sql);
    if ($get_res && mysqli_num_rows($get_res) > 0) {
        $current = mysqli_fetch_array($get_res);
        $mode = isset($_GET['edit']) ? 'edit' : 'view';
    } else {
        $error = "Prescription not found or you do not have access.";
        $mode = 'list';
        $focus_id = 0;
    }
}

if (isset($_POST['add_prescription'])) {
    $patient_id = (int)$_POST['patient_id'];
    $medication = $_POST['medication'];
    $dosage = $_POST['dosage'];
    $issued_date = $_POST['issued_date'];

    if ($patient_id == 0 || strlen($medication) == 0 || strlen($dosage) == 0 || strlen($issued_date) == 0) {
        $error = "Need to fill all the fields.";
        $mode = 'add';
    } else {
        $safe_med = mysqli_real_escape_string($conn, $medication);
        $safe_dosage = mysqli_real_escape_string($conn, $dosage);
        $sql = "INSERT INTO prescriptions (patient_id, staff_id, medication, dosage, issued_date)
                VALUES ($patient_id, $staff_id, '$safe_med', '$safe_dosage', '$issued_date')";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not save prescription. Please try again.");
            $mode = 'add';
        } else {
            header("Location: add-prescription.php?saved=1");
            exit();
        }
    }
}

if (isset($_POST['update_prescription'])) {
    $rx_id = (int)$_POST['rx_id'];
    $patient_id = (int)$_POST['patient_id'];
    $medication = $_POST['medication'];
    $dosage = $_POST['dosage'];
    $issued_date = $_POST['issued_date'];

    if ($rx_id == 0 || $patient_id == 0 || strlen($medication) == 0 || strlen($dosage) == 0 || strlen($issued_date) == 0) {
        $error = "Need to fill all the fields.";
        $mode = 'edit';
        $focus_id = $rx_id;
    } else {
        $safe_med = mysqli_real_escape_string($conn, $medication);
        $safe_dosage = mysqli_real_escape_string($conn, $dosage);
        $sql = "UPDATE prescriptions
                SET patient_id=$patient_id, medication='$safe_med', dosage='$safe_dosage', issued_date='$issued_date'
                WHERE id=$rx_id AND staff_id=$staff_id";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not update prescription. Please try again.");
            $mode = 'edit';
            $focus_id = $rx_id;
        } else {
            header("Location: add-prescription.php?updated=1");
            exit();
        }
    }

    if ($mode == 'edit' && $focus_id > 0) {
        $reload = mysqli_query($conn, "SELECT pr.*, p.full_name AS patient_name
            FROM prescriptions pr, patients p
            WHERE pr.patient_id = p.id AND pr.id=$focus_id AND pr.staff_id=$staff_id");
        if ($reload && mysqli_num_rows($reload) > 0) {
            $current = mysqli_fetch_array($reload);
            $current['medication'] = $medication;
            $current['dosage'] = $dosage;
            $current['issued_date'] = $issued_date;
            $current['patient_id'] = $patient_id;
        }
    }
}

if (isset($_POST['delete_prescription'])) {
    $rx_id = (int)$_POST['rx_id'];
    $del = mysqli_query($conn, "DELETE FROM prescriptions WHERE id=$rx_id AND staff_id=$staff_id");
    if ($del && mysqli_affected_rows($conn) > 0) {
        header("Location: add-prescription.php?deleted=1");
        exit();
    }
    $error = "Could not delete prescription.";
}

if (isset($_GET['saved'])) $success = "Prescription added successfully.";
if (isset($_GET['updated'])) $success = "Prescription updated successfully.";
if (isset($_GET['deleted'])) $success = "Prescription deleted.";

$list = mysqli_query($conn, "SELECT pr.*, p.full_name AS patient_name
    FROM prescriptions pr, patients p
    WHERE pr.patient_id = p.id AND pr.staff_id = $staff_id
    ORDER BY pr.issued_date DESC, pr.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=4">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Prescriptions</h1>
        <span class="role-tag">Doctor Portal</span>
    </header>

    <main class="sd-content">
        <?php if ($error != "") { ?><div class="sd-alert err"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <?php if ($success != "") { ?><div class="sd-alert ok"><?php echo htmlspecialchars($success); ?></div><?php } ?>

        <?php if ($mode == 'add') { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Write Prescription</h2>
                        <p class="sub">Issue medication for a patient.</p>
                    </div>
                    <a class="sd-btn-outline" href="add-prescription.php">Back to list</a>
                </div>
                <form class="sd-form" method="post" action="" style="max-width:560px;">
                    <div class="field">
                        <label for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" required>
                            <option value="">Select patient</option>
                            <?php if ($patients) { mysqli_data_seek($patients, 0); while ($p = mysqli_fetch_array($patients)) {
                                echo '<option value="' . $p['id'] . '">' . htmlspecialchars($p['full_name']) . '</option>';
                            }} ?>
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
                        <input type="date" name="issued_date" id="issued_date" value="<?php echo $today; ?>" required>
                    </div>
                    <button type="submit" name="add_prescription" value="1" class="sd-btn">Save Prescription</button>
                </form>
            </section>

        <?php } elseif ($mode == 'view' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Prescription Details</h2>
                        <p class="sub">Medication you issued for this patient.</p>
                    </div>
                    <div class="sd-actions">
                        <a class="sd-btn-blue" href="add-prescription.php?edit=<?php echo (int)$current['id']; ?>">Edit</a>
                        <a class="sd-btn-outline" href="add-prescription.php">Back to list</a>
                    </div>
                </div>
                <div class="sd-detail" style="max-width:640px;">
                    <div class="sd-detail-row"><span class="label">Patient</span><span class="val"><?php echo htmlspecialchars($current['patient_name']); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Issued Date</span><span class="val"><?php echo date('l, M d, Y', strtotime($current['issued_date'])); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Medication</span><span class="val"><?php echo htmlspecialchars($current['medication']); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Dosage</span><span class="val"><?php echo htmlspecialchars($current['dosage']); ?></span></div>
                </div>
            </section>

        <?php } elseif ($mode == 'edit' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Edit Prescription</h2>
                        <p class="sub">Update medication details.</p>
                    </div>
                    <a class="sd-btn-outline" href="add-prescription.php?view=<?php echo (int)$current['id']; ?>">Cancel</a>
                </div>
                <form class="sd-form" method="post" action="" style="max-width:560px;">
                    <input type="hidden" name="rx_id" value="<?php echo (int)$current['id']; ?>">
                    <div class="field">
                        <label for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" required>
                            <option value="">Select patient</option>
                            <?php if ($patients) { mysqli_data_seek($patients, 0); while ($p = mysqli_fetch_array($patients)) {
                                $sel = ($p['id'] == $current['patient_id']) ? ' selected' : '';
                                echo '<option value="' . $p['id'] . '"' . $sel . '>' . htmlspecialchars($p['full_name']) . '</option>';
                            }} ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="medication">Medication *</label>
                        <input type="text" name="medication" id="medication" required value="<?php echo htmlspecialchars($current['medication']); ?>">
                    </div>
                    <div class="field">
                        <label for="dosage">Dosage *</label>
                        <input type="text" name="dosage" id="dosage" required value="<?php echo htmlspecialchars($current['dosage']); ?>">
                    </div>
                    <div class="field">
                        <label for="issued_date">Issued Date *</label>
                        <input type="date" name="issued_date" id="issued_date" value="<?php echo htmlspecialchars($current['issued_date']); ?>" required>
                    </div>
                    <button type="submit" name="update_prescription" value="1" class="sd-btn">Save Changes</button>
                </form>
            </section>

        <?php } else { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Prescriptions</h2>
                        <p class="sub">Medications you issued for your patients.</p>
                    </div>
                    <a class="sd-btn" href="add-prescription.php?new=1">Add Prescription</a>
                </div>

                <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($list)) { ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['issued_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['medication']); ?></td>
                                        <td><?php echo htmlspecialchars($row['dosage']); ?></td>
                                        <td>
                                            <div class="sd-actions">
                                                <a class="sd-btn-outline" href="add-prescription.php?view=<?php echo (int)$row['id']; ?>">View</a>
                                                <a class="sd-btn-blue" href="add-prescription.php?edit=<?php echo (int)$row['id']; ?>">Edit</a>
                                                <form method="post" action="" onsubmit="return confirm('Delete this prescription?');">
                                                    <input type="hidden" name="rx_id" value="<?php echo (int)$row['id']; ?>">
                                                    <button type="submit" name="delete_prescription" value="1" class="sd-btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="sd-empty">No prescriptions yet. Click Add Prescription to create one.</div>
                <?php } ?>
            </section>
        <?php } ?>
    </main>
</div>

<script>
document.getElementById('sdMenuBtn').addEventListener('click', function () {
    document.getElementById('sdNav').classList.toggle('open');
    document.getElementById('sdSideFooter').classList.toggle('open');
});
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
