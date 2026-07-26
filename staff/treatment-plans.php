<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'staff') {
    echo "Access denied. Doctors only.";
    exit();
}

include("../includes/staff_profile.php");
$active_page = 'treatment_plans';
$error = "";
$success = "";
$today = date('Y-m-d');

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS treatment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    staff_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    plan_details TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('Active', 'Completed', 'On Hold') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (staff_id) REFERENCES staff(id)
)");

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
    $get_sql = "SELECT tp.*, p.full_name AS patient_name
                FROM treatment_plans tp, patients p
                WHERE tp.patient_id = p.id AND tp.id = $focus_id AND tp.staff_id = $staff_id";
    $get_res = mysqli_query($conn, $get_sql);
    if ($get_res && mysqli_num_rows($get_res) > 0) {
        $current = mysqli_fetch_array($get_res);
        $mode = isset($_GET['edit']) ? 'edit' : 'view';
    } else {
        $error = "Treatment plan not found or you do not have access.";
        $mode = 'list';
        $focus_id = 0;
    }
}

if (isset($_POST['add_plan'])) {
    $patient_id = (int)$_POST['patient_id'];
    $title = trim($_POST['title']);
    $plan_details = trim($_POST['plan_details']);
    $start_date = $_POST['start_date'];
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $status = $_POST['status'];
    $allowed_status = array('Active', 'Completed', 'On Hold');
    if (!in_array($status, $allowed_status)) $status = 'Active';

    if ($patient_id == 0 || strlen($title) == 0 || strlen($plan_details) == 0 || strlen($start_date) == 0) {
        $error = "Need to fill all required fields.";
        $mode = 'add';
    } else {
        $safe_title = mysqli_real_escape_string($conn, $title);
        $safe_details = mysqli_real_escape_string($conn, $plan_details);
        $safe_status = mysqli_real_escape_string($conn, $status);
        $end_sql = (strlen($end_date) > 0) ? "'" . mysqli_real_escape_string($conn, $end_date) . "'" : "NULL";
        $sql = "INSERT INTO treatment_plans (patient_id, staff_id, title, plan_details, start_date, end_date, status)
                VALUES ($patient_id, $staff_id, '$safe_title', '$safe_details', '$start_date', $end_sql, '$safe_status')";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not save treatment plan. Please try again.");
            $mode = 'add';
        } else {
            header("Location: treatment-plans.php?saved=1");
            exit();
        }
    }
}

if (isset($_POST['update_plan'])) {
    $plan_id = (int)$_POST['plan_id'];
    $patient_id = (int)$_POST['patient_id'];
    $title = trim($_POST['title']);
    $plan_details = trim($_POST['plan_details']);
    $start_date = $_POST['start_date'];
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $status = $_POST['status'];
    $allowed_status = array('Active', 'Completed', 'On Hold');
    if (!in_array($status, $allowed_status)) $status = 'Active';

    if ($plan_id == 0 || $patient_id == 0 || strlen($title) == 0 || strlen($plan_details) == 0 || strlen($start_date) == 0) {
        $error = "Need to fill all required fields.";
        $mode = 'edit';
        $focus_id = $plan_id;
    } else {
        $safe_title = mysqli_real_escape_string($conn, $title);
        $safe_details = mysqli_real_escape_string($conn, $plan_details);
        $safe_status = mysqli_real_escape_string($conn, $status);
        $end_sql = (strlen($end_date) > 0) ? "'" . mysqli_real_escape_string($conn, $end_date) . "'" : "NULL";
        $sql = "UPDATE treatment_plans
                SET patient_id=$patient_id, title='$safe_title', plan_details='$safe_details',
                    start_date='$start_date', end_date=$end_sql, status='$safe_status'
                WHERE id=$plan_id AND staff_id=$staff_id";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not update treatment plan. Please try again.");
            $mode = 'edit';
            $focus_id = $plan_id;
        } else {
            header("Location: treatment-plans.php?updated=1");
            exit();
        }
    }

    if ($mode == 'edit' && $focus_id > 0) {
        $reload = mysqli_query($conn, "SELECT tp.*, p.full_name AS patient_name
            FROM treatment_plans tp, patients p
            WHERE tp.patient_id = p.id AND tp.id=$focus_id AND tp.staff_id=$staff_id");
        if ($reload && mysqli_num_rows($reload) > 0) {
            $current = mysqli_fetch_array($reload);
            $current['title'] = $title;
            $current['plan_details'] = $plan_details;
            $current['start_date'] = $start_date;
            $current['end_date'] = $end_date;
            $current['status'] = $status;
            $current['patient_id'] = $patient_id;
        }
    }
}

if (isset($_POST['delete_plan'])) {
    $plan_id = (int)$_POST['plan_id'];
    $del = mysqli_query($conn, "DELETE FROM treatment_plans WHERE id=$plan_id AND staff_id=$staff_id");
    if ($del && mysqli_affected_rows($conn) > 0) {
        header("Location: treatment-plans.php?deleted=1");
        exit();
    }
    $error = "Could not delete treatment plan.";
}

if (isset($_GET['saved'])) $success = "Treatment plan added successfully.";
if (isset($_GET['updated'])) $success = "Treatment plan updated successfully.";
if (isset($_GET['deleted'])) $success = "Treatment plan deleted.";

$list = mysqli_query($conn, "SELECT tp.*, p.full_name AS patient_name
    FROM treatment_plans tp, patients p
    WHERE tp.patient_id = p.id AND tp.staff_id = $staff_id
    ORDER BY tp.start_date DESC, tp.id DESC");

function tp_status_class($status) {
    if ($status == 'Active') return 'confirmed';
    if ($status == 'Completed') return 'completed';
    return 'pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plans - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=4">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Treatment Plans</h1>
        <span class="role-tag">Doctor Portal</span>
    </header>

    <main class="sd-content">
        <?php if ($error != "") { ?><div class="sd-alert err"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <?php if ($success != "") { ?><div class="sd-alert ok"><?php echo htmlspecialchars($success); ?></div><?php } ?>

        <?php if ($mode == 'add') { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>New Treatment Plan</h2>
                        <p class="sub">Create a care plan the patient can follow.</p>
                    </div>
                    <a class="sd-btn-outline" href="treatment-plans.php">Back to list</a>
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
                        <label for="title">Plan Title *</label>
                        <input type="text" name="title" id="title" required placeholder="e.g. Post-surgery recovery plan">
                    </div>
                    <div class="field">
                        <label for="start_date">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" value="<?php echo $today; ?>" required>
                    </div>
                    <div class="field">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date">
                    </div>
                    <div class="field">
                        <label for="status">Status *</label>
                        <select name="status" id="status" required>
                            <option value="Active" selected>Active</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="plan_details">Plan Details *</label>
                        <textarea name="plan_details" id="plan_details" required placeholder="Goals, exercises, diet, follow-up schedule, etc."></textarea>
                    </div>
                    <button type="submit" name="add_plan" value="1" class="sd-btn">Save Plan</button>
                </form>
            </section>

        <?php } elseif ($mode == 'view' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Plan Details</h2>
                        <p class="sub">Full treatment plan for this patient.</p>
                    </div>
                    <div class="sd-actions">
                        <a class="sd-btn-blue" href="treatment-plans.php?edit=<?php echo (int)$current['id']; ?>">Edit</a>
                        <a class="sd-btn-outline" href="treatment-plans.php">Back to list</a>
                    </div>
                </div>
                <div class="sd-detail" style="max-width:640px;">
                    <div class="sd-detail-row"><span class="label">Patient</span><span class="val"><?php echo htmlspecialchars($current['patient_name']); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Title</span><span class="val"><?php echo htmlspecialchars($current['title']); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Status</span><span class="val"><span class="sd-status <?php echo tp_status_class($current['status']); ?>"><?php echo htmlspecialchars($current['status']); ?></span></span></div>
                    <div class="sd-detail-row"><span class="label">Start Date</span><span class="val"><?php echo date('M d, Y', strtotime($current['start_date'])); ?></span></div>
                    <div class="sd-detail-row"><span class="label">End Date</span><span class="val"><?php echo !empty($current['end_date']) ? date('M d, Y', strtotime($current['end_date'])) : '—'; ?></span></div>
                    <div class="sd-detail-row"><span class="label">Plan Details</span><span class="val"><?php echo nl2br(htmlspecialchars($current['plan_details'])); ?></span></div>
                </div>
            </section>

        <?php } elseif ($mode == 'edit' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Edit Treatment Plan</h2>
                        <p class="sub">Update this patient's care plan.</p>
                    </div>
                    <a class="sd-btn-outline" href="treatment-plans.php?view=<?php echo (int)$current['id']; ?>">Cancel</a>
                </div>
                <form class="sd-form" method="post" action="" style="max-width:560px;">
                    <input type="hidden" name="plan_id" value="<?php echo (int)$current['id']; ?>">
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
                        <label for="title">Plan Title *</label>
                        <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($current['title']); ?>">
                    </div>
                    <div class="field">
                        <label for="start_date">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" required value="<?php echo htmlspecialchars($current['start_date']); ?>">
                    </div>
                    <div class="field">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars(isset($current['end_date']) ? $current['end_date'] : ''); ?>">
                    </div>
                    <div class="field">
                        <label for="status">Status *</label>
                        <select name="status" id="status" required>
                            <?php foreach (array('Active', 'On Hold', 'Completed') as $st) {
                                $sel = ($current['status'] == $st) ? ' selected' : '';
                                echo '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="plan_details">Plan Details *</label>
                        <textarea name="plan_details" id="plan_details" required><?php echo htmlspecialchars($current['plan_details']); ?></textarea>
                    </div>
                    <button type="submit" name="update_plan" value="1" class="sd-btn">Save Changes</button>
                </form>
            </section>

        <?php } else { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Treatment Plans</h2>
                        <p class="sub">Care plans you created for your patients.</p>
                    </div>
                    <a class="sd-btn" href="treatment-plans.php?new=1">Add Plan</a>
                </div>

                <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Start</th>
                                    <th>Patient</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($list)) {
                                    $title_short = $row['title'];
                                    if (strlen($title_short) > 40) $title_short = substr($title_short, 0, 37) . '...';
                                ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['start_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($title_short); ?></td>
                                        <td><span class="sd-status <?php echo tp_status_class($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                        <td>
                                            <div class="sd-actions">
                                                <a class="sd-btn-outline" href="treatment-plans.php?view=<?php echo (int)$row['id']; ?>">View</a>
                                                <a class="sd-btn-blue" href="treatment-plans.php?edit=<?php echo (int)$row['id']; ?>">Edit</a>
                                                <form method="post" action="" onsubmit="return confirm('Delete this treatment plan permanently?');">
                                                    <input type="hidden" name="plan_id" value="<?php echo (int)$row['id']; ?>">
                                                    <button type="submit" name="delete_plan" value="1" class="sd-btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="sd-empty">No treatment plans yet. Click Add Plan to create one.</div>
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
