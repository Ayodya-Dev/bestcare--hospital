<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'staff') {
    echo "Access denied. Doctors only.";
    exit();
}

include("../includes/staff_profile.php");
$active_page = 'tests';
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
    $get_sql = "SELECT t.*, p.full_name AS patient_name
                FROM test_results t, patients p
                WHERE t.patient_id = p.id AND t.id = $focus_id";
    $get_res = mysqli_query($conn, $get_sql);
    if ($get_res && mysqli_num_rows($get_res) > 0) {
        $current = mysqli_fetch_array($get_res);
        $mode = isset($_GET['edit']) ? 'edit' : 'view';
    } else {
        $error = "Test result not found.";
        $mode = 'list';
        $focus_id = 0;
    }
}

if (isset($_POST['add_test'])) {
    $patient_id = (int)$_POST['patient_id'];
    $test_name = $_POST['test_name'];
    $result_text = $_POST['result_text'];
    $result_date = $_POST['result_date'];

    if ($patient_id == 0 || strlen($test_name) == 0 || strlen($result_text) == 0 || strlen($result_date) == 0) {
        $error = "Need to fill all the fields.";
        $mode = 'add';
    } else {
        $safe_name = mysqli_real_escape_string($conn, $test_name);
        $safe_result = mysqli_real_escape_string($conn, $result_text);
        $sql = "INSERT INTO test_results (patient_id, test_name, result_text, result_date)
                VALUES ($patient_id, '$safe_name', '$safe_result', '$result_date')";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not save test result. Please try again.");
            $mode = 'add';
        } else {
            header("Location: add-test-result.php?saved=1");
            exit();
        }
    }
}

if (isset($_POST['update_test'])) {
    $test_id = (int)$_POST['test_id'];
    $patient_id = (int)$_POST['patient_id'];
    $test_name = $_POST['test_name'];
    $result_text = $_POST['result_text'];
    $result_date = $_POST['result_date'];

    if ($test_id == 0 || $patient_id == 0 || strlen($test_name) == 0 || strlen($result_text) == 0 || strlen($result_date) == 0) {
        $error = "Need to fill all the fields.";
        $mode = 'edit';
        $focus_id = $test_id;
    } else {
        $safe_name = mysqli_real_escape_string($conn, $test_name);
        $safe_result = mysqli_real_escape_string($conn, $result_text);
        $sql = "UPDATE test_results
                SET patient_id=$patient_id, test_name='$safe_name', result_text='$safe_result', result_date='$result_date'
                WHERE id=$test_id";
        if (!mysqli_query($conn, $sql)) {
            $error = bestcare_db_error($conn, "Could not update test result. Please try again.");
            $mode = 'edit';
            $focus_id = $test_id;
        } else {
            header("Location: add-test-result.php?updated=1");
            exit();
        }
    }

    if ($mode == 'edit' && $focus_id > 0) {
        $reload = mysqli_query($conn, "SELECT t.*, p.full_name AS patient_name
            FROM test_results t, patients p
            WHERE t.patient_id = p.id AND t.id=$focus_id");
        if ($reload && mysqli_num_rows($reload) > 0) {
            $current = mysqli_fetch_array($reload);
            $current['test_name'] = $test_name;
            $current['result_text'] = $result_text;
            $current['result_date'] = $result_date;
            $current['patient_id'] = $patient_id;
        }
    }
}

if (isset($_POST['delete_test'])) {
    $test_id = (int)$_POST['test_id'];
    $del = mysqli_query($conn, "DELETE FROM test_results WHERE id=$test_id");
    if ($del && mysqli_affected_rows($conn) > 0) {
        header("Location: add-test-result.php?deleted=1");
        exit();
    }
    $error = "Could not delete test result.";
}

if (isset($_GET['saved'])) $success = "Test result added successfully.";
if (isset($_GET['updated'])) $success = "Test result updated successfully.";
if (isset($_GET['deleted'])) $success = "Test result deleted.";

$list = mysqli_query($conn, "SELECT t.*, p.full_name AS patient_name
    FROM test_results t, patients p
    WHERE t.patient_id = p.id
    ORDER BY t.result_date DESC, t.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=4">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Test Results</h1>
        <span class="role-tag">Doctor Portal</span>
    </header>

    <main class="sd-content">
        <?php if ($error != "") { ?><div class="sd-alert err"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <?php if ($success != "") { ?><div class="sd-alert ok"><?php echo htmlspecialchars($success); ?></div><?php } ?>

        <?php if ($mode == 'add') { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Add Test Result</h2>
                        <p class="sub">Record lab or diagnostic findings for a patient.</p>
                    </div>
                    <a class="sd-btn-outline" href="add-test-result.php">Back to list</a>
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
                        <label for="test_name">Test Name *</label>
                        <input type="text" name="test_name" id="test_name" required placeholder="e.g. Full Blood Count">
                    </div>
                    <div class="field">
                        <label for="result_text">Result *</label>
                        <textarea name="result_text" id="result_text" required placeholder="Enter findings / values"></textarea>
                    </div>
                    <div class="field">
                        <label for="result_date">Result Date *</label>
                        <input type="date" name="result_date" id="result_date" value="<?php echo $today; ?>" required>
                    </div>
                    <button type="submit" name="add_test" value="1" class="sd-btn">Save Result</button>
                </form>
            </section>

        <?php } elseif ($mode == 'view' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Test Result Details</h2>
                        <p class="sub">Full lab / diagnostic findings.</p>
                    </div>
                    <div class="sd-actions">
                        <a class="sd-btn-blue" href="add-test-result.php?edit=<?php echo (int)$current['id']; ?>">Edit</a>
                        <a class="sd-btn-outline" href="add-test-result.php">Back to list</a>
                    </div>
                </div>
                <div class="sd-detail" style="max-width:640px;">
                    <div class="sd-detail-row"><span class="label">Patient</span><span class="val"><?php echo htmlspecialchars($current['patient_name']); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Result Date</span><span class="val"><?php echo date('l, M d, Y', strtotime($current['result_date'])); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Test Name</span><span class="val"><?php echo htmlspecialchars($current['test_name']); ?></span></div>
                    <div class="sd-detail-row"><span class="label">Result</span><span class="val"><?php echo nl2br(htmlspecialchars($current['result_text'])); ?></span></div>
                </div>
            </section>

        <?php } elseif ($mode == 'edit' && $current) { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Edit Test Result</h2>
                        <p class="sub">Update findings for this test.</p>
                    </div>
                    <a class="sd-btn-outline" href="add-test-result.php?view=<?php echo (int)$current['id']; ?>">Cancel</a>
                </div>
                <form class="sd-form" method="post" action="" style="max-width:560px;">
                    <input type="hidden" name="test_id" value="<?php echo (int)$current['id']; ?>">
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
                        <label for="test_name">Test Name *</label>
                        <input type="text" name="test_name" id="test_name" required value="<?php echo htmlspecialchars($current['test_name']); ?>">
                    </div>
                    <div class="field">
                        <label for="result_text">Result *</label>
                        <textarea name="result_text" id="result_text" required><?php echo htmlspecialchars($current['result_text']); ?></textarea>
                    </div>
                    <div class="field">
                        <label for="result_date">Result Date *</label>
                        <input type="date" name="result_date" id="result_date" value="<?php echo htmlspecialchars($current['result_date']); ?>" required>
                    </div>
                    <button type="submit" name="update_test" value="1" class="sd-btn">Save Changes</button>
                </form>
            </section>

        <?php } else { ?>
            <section class="sd-panel">
                <div class="sd-panel-head">
                    <div>
                        <h2>Test Results</h2>
                        <p class="sub">Lab and diagnostic results in the system.</p>
                    </div>
                    <a class="sd-btn" href="add-test-result.php?new=1">Add Result</a>
                </div>

                <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Test</th>
                                    <th>Result</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($list)) {
                                    $res = $row['result_text'];
                                    if (strlen($res) > 40) $res = substr($res, 0, 37) . '...';
                                ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['result_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                                        <td><?php echo htmlspecialchars($res); ?></td>
                                        <td>
                                            <div class="sd-actions">
                                                <a class="sd-btn-outline" href="add-test-result.php?view=<?php echo (int)$row['id']; ?>">View</a>
                                                <a class="sd-btn-blue" href="add-test-result.php?edit=<?php echo (int)$row['id']; ?>">Edit</a>
                                                <form method="post" action="" onsubmit="return confirm('Delete this test result?');">
                                                    <input type="hidden" name="test_id" value="<?php echo (int)$row['id']; ?>">
                                                    <button type="submit" name="delete_test" value="1" class="sd-btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="sd-empty">No test results yet. Click Add Result to create one.</div>
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
