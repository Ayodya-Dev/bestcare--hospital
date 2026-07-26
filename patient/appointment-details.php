<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$dash = "/bestcare-hospital/assets/patient-dash";
$notice = "";

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

// Which appointment?
if (isset($_GET['id'])) {
    $appointment_id = (int)$_GET['id'];
} else {
    $appointment_id = 0;
}

// Cancel from this page
if (isset($_POST['cancel_id'])) {
    $cancel_id = (int)$_POST['cancel_id'];
    $cancel_sql = "UPDATE appointments SET status='Cancelled'
                   WHERE id=$cancel_id AND patient_id=$patient_id AND status='Pending'";
    mysqli_query($conn, $cancel_sql);

    if (mysqli_affected_rows($conn) > 0) {
        $notice = "Appointment cancelled successfully.";
    }
}

$sql = "SELECT a.*, s.name AS service_name, s.description AS service_desc, s.fee,
               st.full_name AS doctor_name, st.specialization, st.contact AS doctor_contact,
               d.name AS department_name
        FROM appointments a, services s, staff st, departments d
        WHERE a.service_id = s.id
        AND a.staff_id = st.id
        AND st.department_id = d.id
        AND a.id = $appointment_id
        AND a.patient_id = $patient_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    bestcare_fail_page("Could not load appointment details. Please try again.");
}

if (mysqli_num_rows($result) == 0) {
    $appointment = false;
} else {
    $appointment = mysqli_fetch_array($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/appointment-details.css?v=1">
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
        <a class="active" href="my-appointments.php">
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
            <input type="text" name="search_term" placeholder="Search appointments, doctors, or departments...">
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

    <main class="pd-content ad-content">

        <?php if (strlen($notice) > 0) { ?>
            <div class="ad-alert"><?php echo $notice; ?></div>
        <?php } ?>

        <?php if ($appointment == false) { ?>

            <div class="ad-alert error">Appointment not found. <a href="my-appointments.php">Back to My Appointments</a></div>

        <?php } else {
            $status = $appointment['status'];
            $status_class = strtolower($status);

            $doc_name = $appointment['doctor_name'];
            $doc_parts = explode(' ', $doc_name);
            $doc_initials = strtoupper(substr($doc_parts[0], 0, 1));
            if (isset($doc_parts[1])) {
                $doc_initials .= strtoupper(substr($doc_parts[1], 0, 1));
            }
            if (isset($doc_parts[2])) {
                $doc_initials = strtoupper(substr($doc_parts[1], 0, 1)) . strtoupper(substr($doc_parts[2], 0, 1));
            }

            $start_time = strtotime($appointment['appointment_time']);
            $end_time = $start_time + (30 * 60);

            $reason = $appointment['service_desc'];
            if (strlen(trim($reason)) == 0) {
                $reason = "Consultation booked for " . $appointment['service_name'] . ".";
            }

            $created = strtotime($appointment['created_at']);
            $visit_stamp = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
        ?>

        <div class="ad-head">
            <div class="ad-head-left">
                <a class="ad-back" href="my-appointments.php" title="Back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h1>Appointment Details</h1>
                    <p class="ad-ref">Booking Reference: <b>BC-<?php echo str_pad($appointment['id'], 4, "0", STR_PAD_LEFT); ?></b></p>
                </div>
            </div>
        </div>

        <div class="ad-layout">
            <div>
                <section class="ad-card">
                    <div class="ad-doc">
                        <div class="ad-doc-avatar"><?php echo htmlspecialchars($doc_initials); ?></div>
                        <div>
                            <div class="ad-doc-name">
                                <h2><?php echo htmlspecialchars($doc_name); ?></h2>
                                <span class="ad-pill <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </div>
                            <p class="role"><?php echo htmlspecialchars($appointment['specialization']); ?></p>
                            <p class="dept"><?php echo htmlspecialchars($appointment['department_name']); ?> Department &middot; <?php echo htmlspecialchars($appointment['service_name']); ?></p>
                        </div>
                    </div>

                    <div class="ad-meta">
                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <div>
                                <p class="label">Date</p>
                                <p class="val"><?php echo date('l, M d, Y', strtotime($appointment['appointment_date'])); ?></p>
                            </div>
                        </div>

                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 16 14"></polyline></svg>
                            </div>
                            <div>
                                <p class="label">Time Slot</p>
                                <p class="val"><?php echo date('h:i A', $start_time); ?> - <?php echo date('h:i A', $end_time); ?></p>
                            </div>
                        </div>

                        <div class="ad-meta-item full">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </div>
                            <div>
                                <p class="label">Location</p>
                                <p class="val">BestCare Central, <?php echo htmlspecialchars($appointment['department_name']); ?> Unit</p>
                            </div>
                        </div>

                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            </div>
                            <div>
                                <p class="label">Contact Doctor's Desk</p>
                                <p class="val"><?php echo htmlspecialchars($appointment['doctor_contact']); ?></p>
                            </div>
                        </div>

                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            </div>
                            <div>
                                <p class="label">Service Fee</p>
                                <p class="val">Rs. <?php echo number_format($appointment['fee'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="ad-info-row">
                    <div class="ad-info">
                        <div class="ad-info-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path><polyline points="14 3 14 8 19 8"></polyline><line x1="9" y1="14" x2="15" y2="14"></line></svg>
                            <span>Reason For Visit</span>
                        </div>
                        <p><?php echo htmlspecialchars($reason); ?></p>
                    </div>

                    <div class="ad-info">
                        <div class="ad-info-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="11" x2="12" y2="16"></line><line x1="12" y1="8" x2="12" y2="8"></line></svg>
                            <span>Preparation</span>
                        </div>
                        <p>Please arrive 15 minutes early with your NIC and patient ID. Bring any current medications and previous reports related to this visit.</p>
                    </div>
                </div>
            </div>

            <aside>
                <section class="ad-side-card">
                    <h3>Status Journey</h3>
                    <ul class="ad-journey">
                        <li class="ad-step done">
                            <span class="ad-step-dot">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            <p class="title">Appointment Requested</p>
                            <p class="desc">Request submitted via patient portal</p>
                            <p class="when"><?php echo date('M d, Y', $created); ?> &middot; <?php echo date('h:i A', $created); ?></p>
                        </li>

                        <?php if ($status == 'Cancelled') { ?>
                            <li class="ad-step cancelled">
                                <span class="ad-step-dot">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </span>
                                <p class="title">Appointment Cancelled</p>
                                <p class="desc">This booking is no longer active. You can book a new appointment any time.</p>
                            </li>
                        <?php } else { ?>
                            <li class="ad-step <?php if ($status == 'Confirmed' || $status == 'Completed') { echo 'done'; } else { echo 'current'; } ?>">
                                <span class="ad-step-dot">
                                    <?php if ($status == 'Confirmed' || $status == 'Completed') { ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php } ?>
                                </span>
                                <p class="title">Booking Confirmed</p>
                                <p class="desc">
                                    <?php if ($status == 'Confirmed' || $status == 'Completed') {
                                        echo "Confirmed by hospital scheduling desk";
                                    } else {
                                        echo "Waiting for the scheduling desk to confirm your slot";
                                    } ?>
                                </p>
                            </li>

                            <li class="ad-step <?php if ($status == 'Completed') { echo 'done'; } else { echo 'todo'; } ?>">
                                <span class="ad-step-dot">
                                    <?php if ($status == 'Completed') { ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php } ?>
                                </span>
                                <p class="title">Scheduled Appointment</p>
                                <p class="desc">Consultation with <?php echo htmlspecialchars($doc_name); ?></p>
                                <p class="when"><?php echo date('M d, Y', $visit_stamp); ?> &middot; <?php echo date('h:i A', $visit_stamp); ?></p>
                            </li>

                            <li class="ad-step <?php if ($status == 'Completed') { echo 'done'; } else { echo 'todo'; } ?>">
                                <span class="ad-step-dot">
                                    <?php if ($status == 'Completed') { ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php } ?>
                                </span>
                                <p class="title">Visit Completed</p>
                                <p class="desc">
                                    <?php if ($status == 'Completed') {
                                        echo "Medical records and prescriptions are available in your portal";
                                    } else {
                                        echo "Records will appear here after your visit";
                                    } ?>
                                </p>
                            </li>
                        <?php } ?>
                    </ul>
                </section>

                <?php if ($status == 'Pending') { ?>
                    <div class="ad-cancel-card">
                        <form method="post" action="" onsubmit="return confirm('Cancel this appointment?');">
                            <input type="hidden" name="cancel_id" value="<?php echo $appointment['id']; ?>">
                            <button type="submit" class="ad-cancel-btn">Cancel Appointment</button>
                        </form>
                        <p class="ad-cancel-note">Only pending appointments can be cancelled online.</p>
                    </div>
                <?php } ?>
            </aside>
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
