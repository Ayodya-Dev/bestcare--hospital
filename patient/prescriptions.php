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
    die("Patient profile not found.");
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

$sql = "SELECT pr.*, st.full_name AS doctor_name, st.specialization
        FROM prescriptions pr, staff st
        WHERE pr.staff_id = st.id
        AND pr.patient_id = $patient_id
        ORDER BY pr.issued_date DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=15">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/my-appointments.css?v=2">
</head>
<body class="pd-body">

<aside class="pd-sidebar">
    <div class="pd-side-brand">
        <div class="pd-side-logo">
            <img src="<?php echo $dash; ?>/IMG_1.svg" alt="Logo">
        </div>
        <span>BestCare Hospital</span>
    </div>

    <button class="pd-menu-btn" id="pdMenuBtn" type="button">☰</button>

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
        <a class="active" href="prescriptions.php">
            <img src="<?php echo $dash; ?>/IMG_5.svg" alt=""> Prescriptions
        </a>
        <a href="test-results.php">
            <img src="<?php echo $dash; ?>/IMG_5.svg" alt=""> Test Results
        </a>
        <a href="my-queries.php">
            <img src="<?php echo $dash; ?>/IMG_6.svg" alt=""> Queries
        </a>
        <a href="../index.php">
            <img src="<?php echo $dash; ?>/IMG_7.svg" alt=""> Website
        </a>
    </nav>

    <div class="pd-side-footer" id="pdSideFooter">
        <a class="pd-settings" href="dashboard.php">
            <img src="<?php echo $dash; ?>/IMG_8.svg" alt=""> Settings
        </a>
        <div class="pd-profile">
            <div class="pd-avatar"><?php echo htmlspecialchars($initials); ?></div>
            <div>
                <p><?php echo htmlspecialchars($full_name); ?></p>
                <small>Patient</small>
            </div>
        </div>
        <a class="pd-logout" href="../auth/logout.php?role=patient">
            <img src="<?php echo $dash; ?>/IMG_9.svg" alt=""> Logout
        </a>
    </div>
</aside>

<div class="pd-main">
    <header class="pd-topbar">
        <form class="pd-search" method="get" action="../search.php">
            <img src="<?php echo $dash; ?>/IMG_11.svg" alt="">
            <input type="text" name="search_term" placeholder="Search prescriptions...">
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

    <main class="pd-content ma-content">
        <div class="ma-head">
            <div>
                <h1>My Prescriptions</h1>
                <p>Medications prescribed by your doctors.</p>
            </div>
        </div>

        <section class="ma-panel">
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <div class="ma-table-wrap">
                    <table class="ma-table">
                        <thead>
                            <tr>
                                <th>Issued Date</th>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Doctor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td>
                                        <span class="ma-date"><?php echo date('M d, Y', strtotime($row['issued_date'])); ?></span>
                                    </td>
                                    <td>
                                        <p class="ma-service"><?php echo htmlspecialchars($row['medication']); ?></p>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['dosage']); ?></td>
                                    <td>
                                        <div class="ma-doc">
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($row['doctor_name']); ?></p>
                                                <p class="spec"><?php echo htmlspecialchars($row['specialization']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="ma-empty">
                    No prescriptions yet. After your doctor issues one, it will appear here.
                </div>
            <?php } ?>
        </section>
    </main>
</div>

<script>
document.getElementById('pdMenuBtn').addEventListener('click', function () {
    document.getElementById('pdNav').classList.toggle('open');
    document.getElementById('pdSideFooter').classList.toggle('open');
});
</script>
</body>
</html>
<?php mysqli_close($conn); ?>
