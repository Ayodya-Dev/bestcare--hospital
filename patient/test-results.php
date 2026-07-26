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

$sql = "SELECT * FROM test_results
        WHERE patient_id = $patient_id
        ORDER BY result_date DESC";
$result = mysqli_query($conn, $sql);

$rows = array();
if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $rows[] = $row;
    }
}
$count = count($rows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-records.css?v=2">
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
        <a href="dashboard.php">
            <img src="<?php echo $dash; ?>/IMG_2.svg" alt=""> Dashboard
        </a>
        <a href="my-appointments.php">
            <img src="<?php echo $dash; ?>/IMG_3.svg" alt=""> Appointments
        </a>
        <a href="medical-records.php">
            <img src="<?php echo $dash; ?>/IMG_4.svg" alt=""> Medical Records
        </a>
        <a href="prescriptions.php">
            <img src="<?php echo $dash; ?>/IMG_5.svg" alt=""> Prescriptions
        </a>
        <a class="active" href="test-results.php">
            <img src="<?php echo $dash; ?>/IMG_5.svg" alt=""> Test Results
        </a>
        <a href="treatment-plans.php">
            <img src="<?php echo $dash; ?>/IMG_4.svg" alt=""> Treatment Plans
        </a>
        <a href="my-queries.php">
            <img src="<?php echo $dash; ?>/IMG_6.svg" alt=""> Queries
        </a>
        <a href="edit-profile.php">
            <img src="<?php echo $dash; ?>/IMG_8.svg" alt=""> Edit Profile
        </a>
        <a href="change-password.php">
            <img src="<?php echo $dash; ?>/IMG_8.svg" alt=""> Change Password
        </a>
        <a href="../index.php">
            <img src="<?php echo $dash; ?>/IMG_7.svg" alt=""> Website
        </a>
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
        <form class="pd-search" method="get" action="">
            <img src="<?php echo $dash; ?>/IMG_11.svg" alt="">
            <input type="text" id="prSearch" placeholder="Search records..." autocomplete="off">
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
        <div class="pr-breadcrumb">
            <a href="dashboard.php">Dashboard</a> / <span>Test Results</span>
        </div>

        <div class="pr-head">
            <div class="pr-head-left">
                <div class="pr-title-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3v6.5L4.5 17A2 2 0 0 0 6.2 20h11.6a2 2 0 0 0 1.7-3L15 9.5V3"></path><line x1="8" y1="3" x2="16" y2="3"></line><line x1="7" y1="14" x2="17" y2="14"></line></svg>
                </div>
                <div>
                    <h1>Test Results</h1>
                    <p>Lab and diagnostic results added by hospital staff.</p>
                </div>
            </div>
            <div class="pr-head-right">
                <span class="pr-stat"><?php echo $count; ?> result<?php echo $count == 1 ? '' : 's'; ?></span>
            </div>
        </div>

        <div class="pr-alert">
            <span class="ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </span>
            <div>
                <strong>Regarding Your Results</strong>
                <p>Lab and diagnostic results usually take 2–3 business days. Contact the hospital if a result is missing after that time.</p>
            </div>
        </div>

        <?php if ($count > 0) { ?>
            <div class="pr-list" id="prList">
                <?php for ($i = 0; $i < $count; $i++) {
                    $row = $rows[$i];
                    $result_text = $row['result_text'];
                    $is_ok = (stripos($result_text, 'normal') !== false || stripos($result_text, 'no significant') !== false);
                    $search_blob = strtolower($row['test_name'] . ' ' . $result_text);
                    ?>
                    <article class="pr-card" data-search="<?php echo htmlspecialchars($search_blob); ?>">
                        <div class="pr-date">
                            <span class="day"><?php echo date('D', strtotime($row['result_date'])); ?></span>
                            <span class="full"><?php echo date('M d, Y', strtotime($row['result_date'])); ?></span>
                        </div>
                        <div class="pr-body">
                            <span class="pr-badge">Lab / Diagnostic</span>
                            <h3><?php echo htmlspecialchars($row['test_name']); ?></h3>
                            <span class="pr-label">Result</span>
                            <p class="result-text<?php echo $is_ok ? ' ok' : ''; ?>"><?php echo htmlspecialchars($result_text); ?></p>
                        </div>
                        <div class="pr-chevron">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </div>
                    </article>
                <?php } ?>
            </div>

            <div class="pr-footer-actions">
                <a href="book-appointment.php">Request Follow-up</a>
                <a href="my-queries.php">Ask a Question</a>
            </div>
            <p class="pr-secure-note">BestCare Hospital Patient Portal · Secure Medical Information</p>
        <?php } else { ?>
            <div class="pr-empty">
                No test results yet. When staff add a lab result, it will show here.
            </div>
        <?php } ?>
    </main>
</div>

<script>
document.getElementById('pdMenuBtn').addEventListener('click', function () {
    document.getElementById('pdNav').classList.toggle('open');
    document.getElementById('pdSideFooter').classList.toggle('open');
});
var search = document.getElementById('prSearch');
if (search) {
    search.addEventListener('input', function () {
        var term = search.value.toLowerCase().trim();
        var cards = document.querySelectorAll('#prList .pr-card');
        for (var i = 0; i < cards.length; i++) {
            var hay = cards[i].getAttribute('data-search') || '';
            cards[i].style.display = (term === '' || hay.indexOf(term) !== -1) ? '' : 'none';
        }
    });
}
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
