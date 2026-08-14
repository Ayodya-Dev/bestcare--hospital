<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$dash = "/bestcare-hospital/assets/patient-dash";
$user_id = $_SESSION['user_id'];

$p_sql = "SELECT * FROM patients WHERE user_id=$user_id";
$p_result = mysqli_query($conn, $p_sql);
if (!$p_result || mysqli_num_rows($p_result) == 0) {
    bestcare_fail_page("Patient profile not found. Please contact the hospital.");
}
$patient = mysqli_fetch_array($p_result);
$patient_id = $patient['id'];

$full_name = $patient['full_name'];
$parts = explode(' ', $full_name);
$initials = strtoupper(substr($parts[0], 0, 1));
if (isset($parts[1])) {
    $initials .= strtoupper(substr($parts[1], 0, 1));
}
$first_name = $parts[0];

$query_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM queries
        WHERE id = $query_id AND patient_id = $patient_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    bestcare_fail_page("Could not load query. Please try again.");
}

$query = false;
if (mysqli_num_rows($result) > 0) {
    $query = mysqli_fetch_array($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Details - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-records.css?v=4">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-queries.css?v=2">
</head>
<body class="pd-body">

<aside class="pd-sidebar">
    <div class="pd-side-brand">
        <div class="pd-side-logo">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="Logo">
        </div>
        <span>BestCare Hospital</span>
    </div>

    <button class="pd-menu-btn" id="pdMenuBtn" type="button" aria-label="Menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg></button>

    <nav class="pd-nav" id="pdNav">
        <a href="dashboard.php"><img src="<?php echo $dash; ?>/IMG_2.svg" alt=""> Dashboard</a>
        <a href="my-appointments.php"><img src="<?php echo $dash; ?>/IMG_3.svg" alt=""> Appointments</a>
        <a href="medical-records.php"><img src="<?php echo $dash; ?>/IMG_4.svg" alt=""> Medical Records</a>
        <a href="prescriptions.php"><img src="<?php echo $dash; ?>/IMG_5.svg" alt=""> Prescriptions</a>
        <a href="test-results.php"><img src="<?php echo $dash; ?>/IMG_5.svg" alt=""> Test Results</a>
        <a href="treatment-plans.php"><img src="<?php echo $dash; ?>/IMG_4.svg" alt=""> Treatment Plans</a>
        <a class="active" href="my-queries.php"><img src="<?php echo $dash; ?>/IMG_6.svg" alt=""> Queries</a>
        <a href="edit-profile.php"><img src="<?php echo $dash; ?>/IMG_8.svg" alt=""> Edit Profile</a>
        <a href="change-password.php"><img src="<?php echo $dash; ?>/IMG_8.svg" alt=""> Change Password</a>
        <a href="../index.php"><img src="<?php echo $dash; ?>/IMG_7.svg" alt=""> Website</a>
    </nav>

    <div class="pd-side-footer" id="pdSideFooter">
        <div class="pd-profile">
            <div class="pd-avatar"><?php echo htmlspecialchars($initials); ?></div>
            <div>
                <p><?php echo htmlspecialchars($full_name); ?></p>
                <small>Patient ID: <?php echo (int)$patient_id; ?></small>
            </div>
        </div>
        <a class="pd-logout" href="../auth/logout.php?role=patient">
            <img src="<?php echo $dash; ?>/IMG_9.svg" alt=""> Logout
        </a>
    </div>
</aside>

<div class="pd-main">
    <header class="pd-topbar">
        <form class="pd-search" method="get" action="my-queries.php">
            <img src="<?php echo $dash; ?>/IMG_11.svg" alt="">
            <input type="text" name="q" placeholder="Search queries..." autocomplete="off">
        </form>
        <div class="pd-top-actions">
            <button class="pd-icon-btn" type="button" title="Notifications">
                <img src="<?php echo $dash; ?>/IMG_12.svg" alt="Notifications">
                <span class="pd-dot"></span>
            </button>
            <div class="pd-user-chip">
                <div class="mini"><?php echo htmlspecialchars($initials); ?></div>
                <span><?php echo htmlspecialchars($first_name); ?></span>
            </div>
        </div>
    </header>

    <main class="pd-content pr-page">
        <?php if ($query == false) { ?>
            <div class="pr-empty">
                Query not found. <a href="my-queries.php">Back to Queries</a>
            </div>
        <?php } else {
            $status = $query['status'];
            $status_class = 'tp-hold';
            $status_label = $status;
            if ($status == 'Replied') {
                $status_class = 'tp-active';
                $status_label = 'Resolved';
            } elseif ($status == 'Closed') {
                $status_class = 'tp-done';
                $status_label = 'Closed';
            } else {
                $status_label = 'Open';
            }
            $reply = isset($query['reply']) ? $query['reply'] : '';
            $qid = 'Q-' . str_pad($query['id'], 4, '0', STR_PAD_LEFT);
        ?>
            <div class="pr-breadcrumb">
                <a href="dashboard.php">Dashboard</a> /
                <a href="my-queries.php">Queries</a> /
                <span>Details</span>
            </div>

            <div class="prd-head">
                <a class="prd-back" href="my-queries.php" title="Back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h1>Query Details</h1>
                    <p>Your message and any reply from hospital administration.</p>
                </div>
            </div>

            <div class="prd-card">
                <div class="prd-top">
                    <span class="pr-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status_label); ?></span>
                    <span class="prd-date"><?php echo date('l, M d, Y · h:i A', strtotime($query['created_at'])); ?></span>
                </div>

                <p class="pq-detail-id"><?php echo $qid; ?></p>
                <h2 class="prd-title"><?php echo htmlspecialchars($query['subject']); ?></h2>

                <div class="prd-block">
                    <span class="prd-label">Your Message</span>
                    <p class="prd-text"><?php echo nl2br(htmlspecialchars($query['message'])); ?></p>
                </div>

                <div class="prd-block">
                    <span class="prd-label">Hospital Reply</span>
                    <p class="prd-text"><?php
                        echo strlen(trim($reply)) > 0
                            ? nl2br(htmlspecialchars($reply))
                            : 'No reply yet. The hospital administration will respond soon.';
                    ?></p>
                </div>
            </div>
        <?php } ?>
    </main>
</div>

<script>
document.getElementById('pdMenuBtn').addEventListener('click', function () {
    document.getElementById('pdNav').classList.toggle('open');
    document.getElementById('pdSideFooter').classList.toggle('open');
});
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
