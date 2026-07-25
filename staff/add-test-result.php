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

$patients = mysqli_query($conn, "SELECT id, full_name FROM patients ORDER BY full_name");

if (isset($_POST['add_test'])) {
    $patient_id = (int)$_POST['patient_id'];
    $test_name = $_POST['test_name'];
    $result_text = $_POST['result_text'];
    $result_date = $_POST['result_date'];

    if ($patient_id == 0 || strlen($test_name) == 0 || strlen($result_text) == 0 || strlen($result_date) == 0) {
        $error = "Need to fill all the fields.";
    } else {
        $safe_name = mysqli_real_escape_string($conn, $test_name);
        $safe_result = mysqli_real_escape_string($conn, $result_text);

        $sql = "INSERT INTO test_results (patient_id, test_name, result_text, result_date)
                VALUES ($patient_id, '$safe_name', '$safe_result', '$result_date')";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Could not save test result: " . mysqli_error($conn));
        }

        $success = "Test result added successfully.";
    }
}

$list_sql = "SELECT t.*, p.full_name AS patient_name
             FROM test_results t, patients p
             WHERE t.patient_id = p.id
             ORDER BY t.result_date DESC
             LIMIT 20";
$list = mysqli_query($conn, $list_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=1">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Test Results</h1>
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
                <h2>Add Test Result</h2>
                <p class="sub">Record lab or diagnostic findings for a patient.</p>

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
                        <label for="test_name">Test Name *</label>
                        <input type="text" name="test_name" id="test_name" required placeholder="e.g. Full Blood Count">
                    </div>

                    <div class="field">
                        <label for="result_text">Result *</label>
                        <textarea name="result_text" id="result_text" required placeholder="Enter findings / values"></textarea>
                    </div>

                    <div class="field">
                        <label for="result_date">Result Date *</label>
                        <input type="date" name="result_date" id="result_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" name="add_test" value="1" class="sd-btn">Save Result</button>
                </form>
            </section>

            <section class="sd-panel">
                <h2>Recent Results</h2>
                <p class="sub">Latest test results in the system.</p>

                <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                    <div class="sd-table-wrap">
                        <table class="sd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Test</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($list)) { ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['result_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['result_text']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="sd-empty">No test results yet.</div>
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
