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

// Cancel appointment
if (isset($_POST['cancel_id'])) {
    $cancel_id = (int)$_POST['cancel_id'];
    $cancel_sql = "UPDATE appointments SET status='Cancelled'
                   WHERE id=$cancel_id AND patient_id=$patient_id AND status='Pending'";
    mysqli_query($conn, $cancel_sql);

    if (mysqli_affected_rows($conn) > 0) {
        $notice = "Appointment cancelled successfully.";
    }
}

$sql = "SELECT a.*, s.name AS service_name, st.full_name AS doctor_name, st.specialization
        FROM appointments a, services s, staff st
        WHERE a.service_id = s.id
        AND a.staff_id = st.id
        AND a.patient_id = $patient_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    bestcare_fail_page("Could not load your appointments. Please try again.");
}

$appointments = array();
while ($row = mysqli_fetch_array($result)) {
    $appointments[] = $row;
}

$today = date('Y-m-d');
$count_upcoming = 0;
$count_pending = 0;
$doctor_ids = array();

for ($i = 0; $i < count($appointments); $i++) {
    $a = $appointments[$i];

    if ($a['status'] == 'Pending') {
        $count_pending++;
    }

    if ($a['appointment_date'] >= $today && ($a['status'] == 'Pending' || $a['status'] == 'Confirmed')) {
        $count_upcoming++;
    }

    if (!in_array($a['staff_id'], $doctor_ids)) {
        $doctor_ids[] = $a['staff_id'];
    }
}

$count_total = count($appointments);
$count_doctors = count($doctor_ids);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/my-appointments.css?v=1">
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

    <main class="pd-content ma-content">

        <?php if (strlen($notice) > 0) { ?>
            <div class="ma-alert"><?php echo $notice; ?></div>
        <?php } ?>

        <div class="ma-head">
            <div>
                <h1>My Appointments</h1>
                <p>Manage your healthcare schedule and visit history.</p>
            </div>
            <div class="ma-head-actions">
                <a class="ma-btn-green" href="book-appointment.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Book Appointment
                </a>
            </div>
        </div>

        <section class="ma-stats">
            <div class="ma-stat">
                <div class="ma-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#016450" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="9 15 11 17 15 13"></polyline></svg>
                </div>
                <div>
                    <p class="label">Upcoming</p>
                    <p class="value"><?php echo $count_upcoming; ?></p>
                </div>
            </div>

            <div class="ma-stat">
                <div class="ma-stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 16 14"></polyline></svg>
                </div>
                <div>
                    <p class="label">Pending</p>
                    <p class="value"><?php echo $count_pending; ?></p>
                </div>
            </div>

            <div class="ma-stat">
                <div class="ma-stat-icon grey">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path><polyline points="14 3 14 8 19 8"></polyline><line x1="9" y1="13" x2="15" y2="13"></line><line x1="9" y1="17" x2="13" y2="17"></line></svg>
                </div>
                <div>
                    <p class="label">Total Visits</p>
                    <p class="value"><?php echo $count_total; ?></p>
                </div>
            </div>

            <div class="ma-stat">
                <div class="ma-stat-icon amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#B45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.5 10 19 14 21 14 12.5 22 3"></polygon></svg>
                </div>
                <div>
                    <p class="label">Specialists</p>
                    <p class="value"><?php echo $count_doctors; ?></p>
                </div>
            </div>
        </section>

        <section class="ma-panel">
            <div class="ma-panel-top">
                <div class="ma-tabs">
                    <button class="ma-tab active" type="button" data-filter="all">All</button>
                    <button class="ma-tab" type="button" data-filter="upcoming">Upcoming</button>
                    <button class="ma-tab" type="button" data-filter="past">Past Visits</button>
                </div>

                <div class="ma-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                    <input type="text" id="maSearch" placeholder="Search appointments...">
                </div>
            </div>

            <?php if ($count_total > 0) { ?>
                <div class="ma-table-wrap">
                    <table class="ma-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service</th>
                                <th>Doctor</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="maBody">
                            <?php for ($i = 0; $i < count($appointments); $i++) {
                                $row = $appointments[$i];
                                $status = $row['status'];

                                if ($row['appointment_date'] >= $today && ($status == 'Pending' || $status == 'Confirmed')) {
                                    $group = "upcoming";
                                } else {
                                    $group = "past";
                                }

                                $doc_name = $row['doctor_name'];
                                $doc_parts = explode(' ', $doc_name);
                                $doc_initials = strtoupper(substr($doc_parts[0], 0, 1));
                                if (isset($doc_parts[1])) {
                                    $doc_initials .= strtoupper(substr($doc_parts[1], 0, 1));
                                }
                            ?>
                                <tr class="ma-row" data-group="<?php echo $group; ?>">
                                    <td>
                                        <span class="ma-cell-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                            <span class="ma-date"><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ma-cell-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 16 14"></polyline></svg>
                                            <span class="ma-time"><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="ma-service" href="appointment-details.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['service_name']); ?></a>
                                        <p class="ma-ref">Ref: BC-<?php echo str_pad($row['id'], 4, "0", STR_PAD_LEFT); ?></p>
                                    </td>
                                    <td>
                                        <div class="ma-doc">
                                            <div class="ma-doc-avatar"><?php echo htmlspecialchars($doc_initials); ?></div>
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($doc_name); ?></p>
                                                <p class="spec"><?php echo htmlspecialchars($row['specialization']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ma-status <?php echo strtolower($status); ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td>
                                        <div class="ma-actions">
                                            <a class="ma-view-btn" href="appointment-details.php?id=<?php echo $row['id']; ?>">View</a>
                                            <?php if ($status == 'Pending') { ?>
                                                <form method="post" action="" onsubmit="return confirm('Cancel this appointment?');">
                                                    <input type="hidden" name="cancel_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" class="ma-cancel-btn">Cancel</button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="ma-empty" id="maNoMatch" style="display:none;">No appointments match your search.</div>
            <?php } else { ?>
                <div class="ma-empty">
                    You have no appointments yet. <a href="book-appointment.php">Book your first appointment</a>.
                </div>
            <?php } ?>
        </section>

    </main>
</div>

<script>
var maTabs = document.querySelectorAll('.ma-tab');
var maRows = document.querySelectorAll('.ma-row');
var maSearch = document.getElementById('maSearch');
var maNoMatch = document.getElementById('maNoMatch');
var maFilter = 'all';

function maApply() {
    var term = maSearch ? maSearch.value.toLowerCase() : '';
    var shown = 0;

    for (var i = 0; i < maRows.length; i++) {
        var row = maRows[i];
        var okGroup = (maFilter === 'all' || row.getAttribute('data-group') === maFilter);
        var okTerm = (term === '' || row.textContent.toLowerCase().indexOf(term) !== -1);

        if (okGroup && okTerm) {
            row.style.display = '';
            shown++;
        } else {
            row.style.display = 'none';
        }
    }

    if (maNoMatch) {
        maNoMatch.style.display = (shown === 0) ? 'block' : 'none';
    }
}

for (var t = 0; t < maTabs.length; t++) {
    maTabs[t].onclick = function () {
        for (var k = 0; k < maTabs.length; k++) {
            maTabs[k].className = 'ma-tab';
        }
        this.className = 'ma-tab active';
        maFilter = this.getAttribute('data-filter');
        maApply();
    };
}

if (maSearch) {
    maSearch.onkeyup = maApply;
}

document.getElementById('pdMenuBtn').addEventListener('click', function () {
    document.getElementById('pdNav').classList.toggle('open');
    document.getElementById('pdSideFooter').classList.toggle('open');
});
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
