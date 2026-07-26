<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$dash = "/bestcare-hospital/assets/patient-dash";
$user_id = $_SESSION['user_id'];

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

$plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT tp.*, st.full_name AS doctor_name, st.specialization, st.contact AS doctor_contact,
               d.name AS department_name
        FROM treatment_plans tp, staff st, departments d
        WHERE tp.staff_id = st.id
        AND st.department_id = d.id
        AND tp.id = $plan_id
        AND tp.patient_id = $patient_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    bestcare_fail_page("Could not load treatment plan. Please try again.");
}

$plan = false;
if (mysqli_num_rows($result) > 0) {
    $plan = mysqli_fetch_array($result);
}

function patient_tp_badge($status) {
    if ($status == 'Active') return 'tp-active';
    if ($status == 'Completed') return 'tp-done';
    return 'tp-hold';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan Details - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-records.css?v=4">
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
        <a href="test-results.php">
            <img src="<?php echo $dash; ?>/IMG_5.svg" alt=""> Test Results
        </a>
        <a class="active" href="treatment-plans.php">
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
        <form class="pd-search" method="get" action="treatment-plans.php">
            <img src="<?php echo $dash; ?>/IMG_11.svg" alt="">
            <input type="text" name="q" placeholder="Search treatment plans..." autocomplete="off">
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
        <?php if ($plan == false) { ?>
            <div class="pr-empty">
                Treatment plan not found. <a href="treatment-plans.php">Back to Treatment Plans</a>
            </div>
        <?php } else {
            $doc_name = $plan['doctor_name'];
            $doc_parts = explode(' ', $doc_name);
            $doc_initials = strtoupper(substr($doc_parts[0], 0, 1));
            if (isset($doc_parts[1])) {
                $doc_initials .= strtoupper(substr($doc_parts[1], 0, 1));
            }
        ?>
            <div class="pr-breadcrumb">
                <a href="dashboard.php">Dashboard</a> /
                <a href="treatment-plans.php">Treatment Plans</a> /
                <span>Details</span>
            </div>

            <div class="prd-head">
                <a class="prd-back" href="treatment-plans.php" title="Back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h1>Treatment Plan</h1>
                    <p>Follow this care plan as advised by your doctor.</p>
                </div>
            </div>

            <div class="prd-card">
                <div class="prd-top">
                    <span class="pr-badge <?php echo patient_tp_badge($plan['status']); ?>"><?php echo htmlspecialchars($plan['status']); ?></span>
                    <span class="prd-date">
                        Started <?php echo date('M d, Y', strtotime($plan['start_date'])); ?>
                        <?php if (!empty($plan['end_date'])) { ?>
                            · Until <?php echo date('M d, Y', strtotime($plan['end_date'])); ?>
                        <?php } ?>
                    </span>
                </div>

                <h2 class="prd-title"><?php echo htmlspecialchars($plan['title']); ?></h2>

                <div class="prd-doctor">
                    <div class="prd-avatar"><?php echo htmlspecialchars($doc_initials); ?></div>
                    <div>
                        <h2><?php echo htmlspecialchars($doc_name); ?></h2>
                        <p>
                            <?php echo htmlspecialchars($plan['specialization']); ?>
                            <?php if (!empty($plan['department_name'])) { ?>
                                · <?php echo htmlspecialchars($plan['department_name']); ?>
                            <?php } ?>
                        </p>
                    </div>
                </div>

                <div class="prd-block">
                    <span class="prd-label">Plan Details</span>
                    <p class="prd-text"><?php echo nl2br(htmlspecialchars($plan['plan_details'])); ?></p>
                </div>

                <?php if (!empty($plan['doctor_contact'])) { ?>
                    <div class="prd-block">
                        <span class="prd-label">Doctor Contact</span>
                        <p class="prd-text"><?php echo htmlspecialchars($plan['doctor_contact']); ?></p>
                    </div>
                <?php } ?>
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
