<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'appointments';
$admin_name = $_SESSION['username'];
$notice = "";
$error = "";

if (isset($_GET['id'])) {
    $appointment_id = (int)$_GET['id'];
} else {
    $appointment_id = 0;
}

// Admin status actions
if (isset($_POST['action_id']) && isset($_POST['new_status'])) {
    $action_id = (int)$_POST['action_id'];
    $new_status = $_POST['new_status'];

    if ($new_status == 'Confirmed' || $new_status == 'Cancelled' || $new_status == 'Completed') {
        $check = mysqli_query($conn, "SELECT status FROM appointments WHERE id=$action_id");
        if ($check && mysqli_num_rows($check) > 0) {
            $cur = mysqli_fetch_array($check);
            $ok = false;
            if ($new_status == 'Confirmed' && $cur['status'] == 'Pending') { $ok = true; }
            if ($new_status == 'Cancelled' && ($cur['status'] == 'Pending' || $cur['status'] == 'Confirmed')) { $ok = true; }
            if ($new_status == 'Completed' && $cur['status'] == 'Confirmed') { $ok = true; }

            if ($ok) {
                mysqli_query($conn, "UPDATE appointments SET status='$new_status' WHERE id=$action_id");
                $notice = "Appointment updated to $new_status.";
                $appointment_id = $action_id;
            } else {
                $error = "That status change is not allowed.";
            }
        }
    }
}

$sql = "SELECT a.*, s.name AS service_name, s.description AS service_desc, s.fee,
               st.full_name AS doctor_name, st.specialization, st.contact AS doctor_contact,
               d.name AS department_name,
               p.full_name AS patient_name, p.contact AS patient_contact, p.gender, p.dob
        FROM appointments a, services s, staff st, departments d, patients p
        WHERE a.service_id = s.id
        AND a.staff_id = st.id
        AND st.department_id = d.id
        AND a.patient_id = p.id
        AND a.id = $appointment_id";
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
    <title>Appointment Details - Admin</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=3">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/appointment-details.css?v=2">
</head>
<body class="adm-body">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Appointment Details</h1>
        <span class="role-tag">Administrator</span>
    </header>

    <main class="adm-content ad-content">

        <?php if ($notice != "") { ?>
            <div class="ad-alert"><?php echo $notice; ?></div>
        <?php } ?>
        <?php if ($error != "") { ?>
            <div class="ad-alert error"><?php echo $error; ?></div>
        <?php } ?>

        <?php if ($appointment == false) { ?>
            <div class="ad-alert error">Appointment not found. <a href="appointments.php">Back to Appointments</a></div>
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

            $pat_name = $appointment['patient_name'];
            $pat_parts = explode(' ', $pat_name);
            $pat_initials = strtoupper(substr($pat_parts[0], 0, 1));
            if (isset($pat_parts[1])) {
                $pat_initials .= strtoupper(substr($pat_parts[1], 0, 1));
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
                <a class="ad-back" href="appointments.php" title="Back">
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

                    <div class="ad-meta" style="padding-top:18px;border-top:1px solid #F1F2F4;margin-top:18px;">
                        <div class="ad-meta-item full">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div>
                                <p class="label">Patient</p>
                                <p class="val"><?php echo htmlspecialchars($pat_name); ?> &middot; <?php echo htmlspecialchars($appointment['gender']); ?> &middot; <?php echo htmlspecialchars($appointment['patient_contact']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="ad-meta">
                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <div>
                                <p class="label">Date</p>
                                <p class="val"><?php echo date('l, M d, Y', strtotime($appointment['appointment_date'])); ?></p>
                            </div>
                        </div>

                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 16 14"></polyline></svg>
                            </div>
                            <div>
                                <p class="label">Time Slot</p>
                                <p class="val"><?php echo date('h:i A', $start_time); ?> - <?php echo date('h:i A', $end_time); ?></p>
                            </div>
                        </div>

                        <div class="ad-meta-item full">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </div>
                            <div>
                                <p class="label">Location</p>
                                <p class="val">BestCare Central, <?php echo htmlspecialchars($appointment['department_name']); ?> Unit</p>
                            </div>
                        </div>

                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            </div>
                            <div>
                                <p class="label">Doctor Contact</p>
                                <p class="val"><?php echo htmlspecialchars($appointment['doctor_contact']); ?></p>
                            </div>
                        </div>

                        <div class="ad-meta-item">
                            <div class="ad-meta-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
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
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path><polyline points="14 3 14 8 19 8"></polyline></svg>
                            <span>Reason For Visit</span>
                        </div>
                        <p><?php echo htmlspecialchars($reason); ?></p>
                    </div>
                    <div class="ad-info">
                        <div class="ad-info-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="11" x2="12" y2="16"></line><line x1="12" y1="8" x2="12" y2="8"></line></svg>
                            <span>Patient Info</span>
                        </div>
                        <p>DOB: <?php echo date('M d, Y', strtotime($appointment['dob'])); ?>. Contact: <?php echo htmlspecialchars($appointment['patient_contact']); ?>.</p>
                    </div>
                </div>
            </div>

            <aside>
                <section class="ad-side-card">
                    <h3>Status Journey</h3>
                    <ul class="ad-journey">
                        <li class="ad-step done">
                            <span class="ad-step-dot">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            <p class="title">Appointment Requested</p>
                            <p class="desc">Submitted by <?php echo htmlspecialchars($pat_name); ?></p>
                            <p class="when"><?php echo date('M d, Y', $created); ?> &middot; <?php echo date('h:i A', $created); ?></p>
                        </li>

                        <?php if ($status == 'Cancelled') { ?>
                            <li class="ad-step cancelled">
                                <span class="ad-step-dot">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </span>
                                <p class="title">Appointment Cancelled</p>
                                <p class="desc">This booking is no longer active.</p>
                            </li>
                        <?php } else { ?>
                            <li class="ad-step <?php if ($status == 'Confirmed' || $status == 'Completed') { echo 'done'; } else { echo 'current'; } ?>">
                                <span class="ad-step-dot">
                                    <?php if ($status == 'Confirmed' || $status == 'Completed') { ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php } ?>
                                </span>
                                <p class="title">Booking Confirmed</p>
                                <p class="desc"><?php echo ($status == 'Confirmed' || $status == 'Completed') ? 'Confirmed by hospital staff' : 'Waiting for confirmation'; ?></p>
                            </li>
                            <li class="ad-step <?php if ($status == 'Completed') { echo 'done'; } else { echo 'todo'; } ?>">
                                <span class="ad-step-dot">
                                    <?php if ($status == 'Completed') { ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php } ?>
                                </span>
                                <p class="title">Scheduled Appointment</p>
                                <p class="desc">With <?php echo htmlspecialchars($doc_name); ?></p>
                                <p class="when"><?php echo date('M d, Y', $visit_stamp); ?> &middot; <?php echo date('h:i A', $visit_stamp); ?></p>
                            </li>
                            <li class="ad-step <?php if ($status == 'Completed') { echo 'done'; } else { echo 'todo'; } ?>">
                                <span class="ad-step-dot">
                                    <?php if ($status == 'Completed') { ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php } ?>
                                </span>
                                <p class="title">Visit Completed</p>
                                <p class="desc"><?php echo ($status == 'Completed') ? 'Visit marked as completed' : 'Awaiting completion'; ?></p>
                            </li>
                        <?php } ?>
                    </ul>
                </section>

                <?php if ($status == 'Pending' || $status == 'Confirmed') { ?>
                    <div class="ad-cancel-card" style="display:flex;flex-direction:column;gap:8px;">
                        <?php if ($status == 'Pending') { ?>
                            <form method="post" action="">
                                <input type="hidden" name="action_id" value="<?php echo $appointment['id']; ?>">
                                <input type="hidden" name="new_status" value="Confirmed">
                                <button type="submit" class="adm-btn" style="width:100%;">Confirm Appointment</button>
                            </form>
                        <?php } ?>
                        <?php if ($status == 'Confirmed') { ?>
                            <form method="post" action="">
                                <input type="hidden" name="action_id" value="<?php echo $appointment['id']; ?>">
                                <input type="hidden" name="new_status" value="Completed">
                                <button type="submit" class="adm-btn" style="width:100%;">Mark Completed</button>
                            </form>
                        <?php } ?>
                        <form method="post" action="" onsubmit="return confirm('Cancel this appointment?');">
                            <input type="hidden" name="action_id" value="<?php echo $appointment['id']; ?>">
                            <input type="hidden" name="new_status" value="Cancelled">
                            <button type="submit" class="ad-cancel-btn">Cancel Appointment</button>
                        </form>
                    </div>
                <?php } ?>
            </aside>
        </div>

        <?php } ?>
    </main>
</div>

<script>
document.getElementById('admMenuBtn').addEventListener('click', function () {
    document.getElementById('admNav').classList.toggle('open');
    document.getElementById('admSideFooter').classList.toggle('open');
});
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
