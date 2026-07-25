<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'staff') {
    echo "Access denied. Doctors only.";
    exit();
}

include("../includes/staff_profile.php");
$active_page = 'queries';
$error = "";
$success = "";
$user_id = $_SESSION['user_id'];

if (isset($_POST['reply_id'])) {
    $reply_id = (int)$_POST['reply_id'];
    $reply = $_POST['reply'];

    if (strlen($reply) == 0) {
        $error = "Reply cannot be empty.";
    } else {
        $safe_reply = mysqli_real_escape_string($conn, $reply);
        $sql = "UPDATE queries
                SET reply='$safe_reply', status='Replied', replied_by=$user_id
                WHERE id=$reply_id AND status='Open'";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Could not save reply: " . mysqli_error($conn));
        }

        if (mysqli_affected_rows($conn) > 0) {
            $success = "Reply sent to patient.";
        } else {
            $error = "Query not found or already replied.";
        }
    }
}

$list_sql = "SELECT q.*, p.full_name AS patient_name
             FROM queries q, patients p
             WHERE q.patient_id = p.id
             ORDER BY
                CASE WHEN q.status='Open' THEN 0 ELSE 1 END,
                q.created_at DESC";
$list = mysqli_query($conn, $list_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Queries - Doctor Portal</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/staff.css?v=1">
</head>
<body class="sd-body">

<?php include("../includes/staff_nav.php"); ?>

<div class="sd-main">
    <header class="sd-topbar">
        <h1>Patient Queries</h1>
        <span class="role-tag">Doctor Portal</span>
    </header>

    <main class="sd-content">
        <?php if ($error != "") { ?>
            <div class="sd-alert err"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="sd-alert ok"><?php echo $success; ?></div>
        <?php } ?>

        <section class="sd-panel">
            <h2>Inbox</h2>
            <p class="sub">Answer questions submitted by patients.</p>

            <?php if ($list && mysqli_num_rows($list) > 0) { ?>
                <div class="sd-table-wrap">
                    <table class="sd-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Subject / Message</th>
                                <th>Status</th>
                                <th>Reply</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($list)) { ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['subject']); ?></strong>
                                        <p class="sd-msg"><?php echo htmlspecialchars($row['message']); ?></p>
                                    </td>
                                    <td><span class="sd-status <?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                    <td>
                                        <?php if ($row['status'] == 'Open') { ?>
                                            <form class="sd-form" method="post" action="">
                                                <input type="hidden" name="reply_id" value="<?php echo $row['id']; ?>">
                                                <div class="field">
                                                    <textarea name="reply" rows="3" required placeholder="Type your reply..."></textarea>
                                                </div>
                                                <button type="submit" class="sd-btn">Send Reply</button>
                                            </form>
                                        <?php } else { ?>
                                            <p class="sd-msg"><?php echo htmlspecialchars($row['reply']); ?></p>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="sd-empty">No patient queries yet.</div>
            <?php } ?>
        </section>
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
