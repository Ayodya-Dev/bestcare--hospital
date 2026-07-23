<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$error = "";
$success = "";

$user_id = $_SESSION['user_id'];
$p_sql = "SELECT id FROM patients WHERE user_id=$user_id";
$p_result = mysqli_query($conn, $p_sql);
$patient = mysqli_fetch_array($p_result);
$patient_id = $patient['id'];

if (isset($_POST['send_query'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    if (strlen($subject) == 0 || strlen($message) == 0) {
        $error = "Need to fill all the fields";
    } else {
        $safe_subject = mysqli_real_escape_string($conn, $subject);
        $safe_message = mysqli_real_escape_string($conn, $message);

        $sql = "INSERT INTO queries (patient_id, subject, message, status)
                VALUES ($patient_id, '$safe_subject', '$safe_message', 'Open')";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Could not send query: " . mysqli_error($conn));
        }

        $success = "Query submitted successfully.";
    }
}

$list_sql = "SELECT * FROM queries WHERE patient_id=$patient_id ORDER BY created_at DESC";
$list = mysqli_query($conn, $list_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Queries - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=6">
</head>
<body>
<?php include("../includes/patient_nav.php"); ?>

<div class="dash-wrap">
    <h2>My Queries</h2>
    <p>Ask the hospital about treatments or services.</p>

    <?php if ($error != "") { ?>
        <div class="error-msg"><span><?php echo $error; ?></span></div>
    <?php } ?>
    <?php if ($success != "") { ?>
        <div class="success-msg"><?php echo $success; ?></div>
    <?php } ?>

    <div class="auth-card" style="max-width:560px;margin:20px 0;">
        <div class="auth-card-body">
            <form method="post" action="" id="patientQueryForm">
                <label for="subject">Subject</label>
                <div class="input-wrap no-icon">
                    <input type="text" name="subject" id="subject" required>
                </div>
                <label for="message">Message</label>
                <div class="input-wrap no-icon textarea-wrap">
                    <textarea name="message" id="message" rows="4" required></textarea>
                </div>
                <button type="submit" name="send_query" value="1" class="btn-primary">Send Query</button>
            </form>
        </div>
    </div>

    <h3 style="color:#01875A;margin:24px 0 12px;">Previous Queries</h3>
    <?php if ($list && mysqli_num_rows($list) > 0) { ?>
        <div class="table-wrap">
            <table class="data-table">
                <tr>
                    <th>Date</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Reply</th>
                </tr>
                <?php while ($row = mysqli_fetch_array($list)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo $row['reply'] ? htmlspecialchars($row['reply']) : '-'; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } else { ?>
        <p>No queries yet.</p>
    <?php } ?>
</div>

<script src="/bestcare-hospital/assets/js/main.js?v=6"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
