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
    $get_sql = "SELECT m.*, p.full_name AS patient_name
                FROM medical_records m, patients p
                WHERE m.patient_id = p.id
                AND m.id = $focus_id
                AND m.staff_id = $staff_id";
    $get_res = mysqli_query($conn, $get_sql);
    if ($get_res && mysqli_num_rows($get_res) > 0) {
        $current = mysqli_fetch_array($get_res);
        $mode = isset($_GET['edit']) ? 'edit' : 'view';
    } else {
        $error = "Record not found or you do not have access.";
        $mode = 'list';
        $focus_id = 0;
    }
}

if (isset($_POST['add_record'])) {
    $patient_id = (int)$_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'];
    $notes = $_POST['notes'];
    $visit_date = $_POST['visit_date'];

    if ($patient_id == 0 || strlen($diagnosis) == 0 || strlen($visit_date) == 0) {
        $error = "Need to fill all required fields.";
        $mode = 'add';
    } else {
        $safe_diagnosis = mysqli_real_escape_string($conn, $diagnosis);
        $safe_notes = mysqli_real_escape_string($conn, $notes);
        $sql = "INSERT INTO medical_records (patient_id, staff_id, diagnosis, notes, visit_date)
                VALUES ($patient_id, $staff_id, '$safe_diagnosis', '$safe_notes', '$visit_date')";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not save medical record. Please try again.");
            $mode = 'add';
        } else {
            header("Location: add-record.php?saved=1");
            exit();
        }
    }
}

if (isset($_POST['update_record'])) {
    $record_id = (int)$_POST['record_id'];
    $patient_id = (int)$_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'];
    $notes = $_POST['notes'];
    $visit_date = $_POST['visit_date'];

    if ($record_id == 0 || $patient_id == 0 || strlen($diagnosis) == 0 || strlen($visit_date) == 0) {
        $error = "Need to fill all required fields.";
        $mode = 'edit';
        $focus_id = $record_id;
    } else {
        $safe_diagnosis = mysqli_real_escape_string($conn, $diagnosis);
        $safe_notes = mysqli_real_escape_string($conn, $notes);
        $sql = "UPDATE medical_records
                SET patient_id=$patient_id, diagnosis='$safe_diagnosis', notes='$safe_notes', visit_date='$visit_date'
                WHERE id=$record_id AND staff_id=$staff_id";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not update medical record. Please try again.");
            $mode = 'edit';
            $focus_id = $record_id;
        } else {
            header("Location: add-record.php?updated=1");
            exit();
        }
    }

    if ($mode == 'edit' && $focus_id > 0) {
        $reload = mysqli_query($conn, "SELECT m.*, p.full_name AS patient_name
            FROM medical_records m, patients p
            WHERE m.patient_id = p.id AND m.id=$focus_id AND m.staff_id=$staff_id");
        if ($reload && mysqli_num_rows($reload) > 0) {
            $current = mysqli_fetch_array($reload);
            $current['diagnosis'] = $diagnosis;
            $current['notes'] = $notes;
            $current['visit_date'] = $visit_date;
            $current['patient_id'] = $patient_id;
        }
    }
}

if (isset($_GET['saved'])) $success = "Medical record added successfully.";
if (isset($_GET['updated'])) $success = "Medical record updated successfully.";

$list = mysqli_query($conn, "SELECT m.*, p.full_name AS patient_name
    FROM medical_records m, patients p
    WHERE m.patient_id = p.id AND m.staff_id = $staff_id
    ORDER BY m.visit_date DESC, m.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=4">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Medical Records</h1>
        <span class="role-tag">Doctor Portal</span>
    </header>

    <main class="sd-content">
        <?php if ($error != "") { ?><div class="sd-alert err"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <?php if ($success != "") { ?><div class="sd-alert ok"><?php echo htmlspecialchars($success); ?></div><?php } ?>

        <?php if ($mode == 'add') { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Add Medical Record</h2>
                        <p class="sub">Save diagnosis and notes after a patient visit.</p>
                    </div>
                    <a class="sd-btn-outline" href="add-record.php">Back to list</a>
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
                        <label for="visit_date">Visit Date *</label>
                        <input type="date" name="visit_date" id="visit_date" value="<?php echo $today; ?>" required>
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

        <?php } elseif ($mode == 'view' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Record Details</h2>
                        <p class="sub">Full visit record you previously saved.</p>
                    </div>
                    <div class="sd-actions">
                        <a class="sd-btn-blue" href="add-record.php?edit=<?php echo (int)$current['id']; ?>">Edit</a>
                        <a class="sd-btn-outline" href="add-record.php">Back to list</a>
                    </div>
                </div>
                <div class="sd-detail" style="max-width:640px;">
                    <div class="sd-detail-row"><span class="label">Patient</span><span class="val"><?php echo htmlspecialchars($current['patient_name']); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Visit Date</span><span class="val"><?php echo date('l, M d, Y', strtotime($current['visit_date'])); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Diagnosis</span><span class="val"><?php echo nl2br(htmlspecialchars($current['diagnosis'])); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Notes</span><span class="val"><?php
                        $notes = isset($current['notes']) ? $current['notes'] : '';
                        echo strlen(trim($notes)) > 0 ? nl2br(htmlspecialchars($notes)) : '—';
                    ?></span></div>
                </div>
            </section>

        <?php } elseif ($mode == 'edit' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Edit Medical Record</h2>
                        <p class="sub">Update diagnosis and notes for this visit.</p>
                    </div>
                    <a class="sd-btn-outline" href="add-record.php?view=<?php echo (int)$current['id']; ?>">Cancel</a>
                </div>
                <form class="sd-form" method="post" action="" style="max-width:560px;">
                    <input type="hidden" name="record_id" value="<?php echo (int)$current['id']; ?>">
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
                        <label for="visit_date">Visit Date *</label>
                        <input type="date" name="visit_date" id="visit_date" value="<?php echo htmlspecialchars($current['visit_date']); ?>" required>
                    </div>
                    <div class="field">
                        <label for="diagnosis">Diagnosis *</label>
                        <textarea name="diagnosis" id="diagnosis" required><?php echo htmlspecialchars($current['diagnosis']); ?></textarea>
                    </div>
                    <div class="field">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes"><?php echo htmlspecialchars(isset($current['notes']) ? $current['notes'] : ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_record" value="1" class="sd-btn">Save Changes</button>
                </form>
            </section>

        <?php } else { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Medical Records</h2>
                        <p class="sub">Records you added for your patients.</p>
                    </div>
                    <a class="sd-btn" href="add-record.php?new=1">Add Record</a>
                </div>

                <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Diagnosis</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($list)) {
                                    $diag = $row['diagnosis'];
                                    if (strlen($diag) > 50) $diag = substr($diag, 0, 47) . '...';
                                ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['visit_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($diag); ?></td>
                                        <td>
                                            <div class="sd-actions">
                                                <a class="sd-btn-outline" href="add-record.php?view=<?php echo (int)$row['id']; ?>">View</a>
                                                <a class="sd-btn-blue" href="add-record.php?edit=<?php echo (int)$row['id']; ?>">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="sd-empty">No records yet. Click Add Record to create one.</div>
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
