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

$sql = "SELECT * FROM test_results
        WHERE patient_id = $patient_id
        ORDER BY result_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=6">
</head>
<body>
<?php include("../includes/patient_nav.php"); ?>

<div class="dash-wrap">
    <h2>Test Results</h2>
    <p>Lab and diagnostic results added by hospital staff.</p>

    <?php if ($result && mysqli_num_rows($result) > 0) { ?>
        <div class="table-wrap">
            <table class="data-table">
                <tr>
                    <th>Date</th>
                    <th>Test Name</th>
                    <th>Result</th>
                </tr>
                <?php while ($row = mysqli_fetch_array($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['result_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['result_text']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } else { ?>
        <p>No test results yet.</p>
    <?php } ?>
</div>

<script src="/bestcare-hospital/assets/js/main.js?v=6"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
