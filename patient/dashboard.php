<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$img = "/bestcare-hospital/assets/patient-dash";
$user_id = $_SESSION['user_id'];

$p_sql = "SELECT * FROM patients WHERE user_id=$user_id";
$p_result = mysqli_query($conn, $p_sql);
if (!$p_result || mysqli_num_rows($p_result) == 0) {
    bestcare_fail_page("Patient profile not found. Please contact the hospital.");
}
$patient = mysqli_fetch_array($p_result);
$patient_id = $patient['id'];

$full_name = $patient['full_name'];
$first_name = explode(' ', $full_name)[0];
$parts = explode(' ', $full_name);
$initials = strtoupper(substr($parts[0], 0, 1));
if (isset($parts[1])) {
    $initials .= strtoupper(substr($parts[1], 0, 1));
}

// Upcoming appointments
$appt_sql = "SELECT a.*, s.name AS service_name, st.full_name AS doctor_name, st.specialization
             FROM appointments a, services s, staff st
             WHERE a.service_id = s.id
             AND a.staff_id = st.id
             AND a.patient_id = $patient_id
             AND a.status IN ('Pending','Confirmed')
             AND a.appointment_date >= CURDATE()
             ORDER BY a.appointment_date ASC, a.appointment_time ASC
             LIMIT 3";
$appt_result = mysqli_query($conn, $appt_sql);
$upcoming_count = ($appt_result) ? mysqli_num_rows($appt_result) : 0;

// Recent medical records
$rec_sql = "SELECT m.*, st.full_name AS doctor_name
            FROM medical_records m, staff st
            WHERE m.staff_id = st.id
            AND m.patient_id = $patient_id
            ORDER BY m.visit_date DESC
            LIMIT 4";
$rec_result = mysqli_query($conn, $rec_sql);

// Pending test results count
$test_sql = "SELECT COUNT(*) AS total FROM test_results WHERE patient_id=$patient_id";
$test_result = mysqli_query($conn, $test_sql);
$test_row = mysqli_fetch_array($test_result);
$test_count = $test_row['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
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
        <a class="active" href="dashboard.php">
            <img src="<?php echo $img; ?>/IMG_2.svg" alt=""> Dashboard
        </a>
        <a href="book-appointment.php">
            <img src="<?php echo $img; ?>/IMG_3.svg" alt=""> Appointments
        </a>
        <a href="medical-records.php">
            <img src="<?php echo $img; ?>/IMG_4.svg" alt=""> Medical Records
        </a>
        <a href="prescriptions.php">
            <img src="<?php echo $img; ?>/IMG_5.svg" alt=""> Prescriptions
        </a>
        <a href="test-results.php">
            <img src="<?php echo $img; ?>/IMG_5.svg" alt=""> Test Results
        </a>
        <a href="treatment-plans.php">
            <img src="<?php echo $img; ?>/IMG_4.svg" alt=""> Treatment Plans
        </a>
        <a href="my-queries.php">
            <img src="<?php echo $img; ?>/IMG_6.svg" alt=""> Queries
        </a>
        <a href="edit-profile.php">
            <img src="<?php echo $img; ?>/IMG_8.svg" alt=""> Edit Profile
        </a>
        <a href="change-password.php">
            <img src="<?php echo $img; ?>/IMG_8.svg" alt=""> Change Password
        </a>
        <a href="../index.php">
            <img src="<?php echo $img; ?>/IMG_7.svg" alt=""> Website
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
            <img src="<?php echo $img; ?>/IMG_9.svg" alt=""> Logout
        </a>
    </div>
</aside>

<div class="pd-main">
    <header class="pd-topbar">
        <form class="pd-search" method="get" action="../search.php">
            <img src="<?php echo $img; ?>/IMG_11.svg" alt="">
            <input type="text" name="search_term" placeholder="Search records or services...">
        </form>

        <div class="pd-top-actions">
            <button class="pd-icon-btn" type="button" title="Help">
                <img src="<?php echo $img; ?>/IMG_10.svg" alt="Help">
            </button>
            <button class="pd-icon-btn" type="button" title="Notifications">
                <img src="<?php echo $img; ?>/IMG_12.svg" alt="Notifications">
                <span class="pd-dot"></span>
            </button>
            <div class="pd-user-chip">
                <div class="mini"><?php echo htmlspecialchars($initials); ?></div>
                <span><?php echo htmlspecialchars($first_name); ?></span>
            </div>
        </div>
    </header>

    <main class="pd-content">
        <!-- Hero -->
        <section class="pd-hero">
            <div class="pd-hero-left">
                <div>
                    <h1>Welcome back, <?php echo htmlspecialchars($first_name); ?>!</h1>
                    <p class="pd-hero-text">
                        Your health journey is looking great this week.
                        You have <?php echo (int)$upcoming_count; ?> upcoming appointment<?php echo $upcoming_count == 1 ? '' : 's'; ?>
                        and your vitals are within normal range.
                    </p>
                    <div class="pd-hero-actions">
                        <a class="pd-btn-green" href="book-appointment.php">
                            <img src="<?php echo $img; ?>/IMG_14.svg" alt=""> Book New Appointment
                        </a>
                        <a class="pd-btn-outline" href="medical-records.php">
                            <img src="<?php echo $img; ?>/IMG_4.svg" alt=""> Download Health Summary
                        </a>
                    </div>
                </div>

                <div class="pd-hero-meta">
                    <div>
                        <p class="label">Gender</p>
                        <p class="value"><?php echo htmlspecialchars($patient['gender']); ?></p>
                    </div>
                    <div>
                        <p class="label">Date of Birth</p>
                        <p class="value"><?php echo htmlspecialchars($patient['dob']); ?></p>
                    </div>
                    <div>
                        <p class="label">Patient ID</p>
                        <p class="value">#BC-<?php echo str_pad($patient_id, 5, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </div>
            </div>
            <div class="pd-hero-right">
                <div class="pd-hero-fade"></div>
                <img src="<?php echo $img; ?>/IMG_13.webp" alt="Doctor">
            </div>
        </section>

        <!-- Vitals (demo sample values for UI) -->
        <section class="pd-vitals">
            <div class="pd-vital">
                <div class="pd-vital-top">
                    <div class="pd-vital-icon heart"><img src="<?php echo $img; ?>/IMG_15.svg" alt=""></div>
                    <span class="pd-chip">Last 24h</span>
                </div>
                <div class="pd-vital-mid">
                    <p class="name">Heart Rate</p>
                    <div><span class="num">72</span><span class="unit">bpm</span></div>
                </div>
                <div class="pd-vital-trend">
                    <img src="<?php echo $img; ?>/IMG_16.svg" alt="">
                    <span>0.5%</span><span class="muted">vs yesterday</span>
                </div>
            </div>

            <div class="pd-vital">
                <div class="pd-vital-top">
                    <div class="pd-vital-icon glucose"><img src="<?php echo $img; ?>/IMG_17.svg" alt=""></div>
                    <span class="pd-chip">Last 24h</span>
                </div>
                <div class="pd-vital-mid">
                    <p class="name">Blood Glucose</p>
                    <div><span class="num">94</span><span class="unit">mg/dL</span></div>
                </div>
                <div class="pd-vital-trend warn">
                    <img src="<?php echo $img; ?>/IMG_16.svg" alt="" style="transform:rotate(180deg);">
                    <span>1.2%</span><span class="muted">vs yesterday</span>
                </div>
            </div>

            <div class="pd-vital">
                <div class="pd-vital-top">
                    <div class="pd-vital-icon temp"><img src="<?php echo $img; ?>/IMG_18.svg" alt=""></div>
                    <span class="pd-chip">Last 24h</span>
                </div>
                <div class="pd-vital-mid">
                    <p class="name">Body Temp</p>
                    <div><span class="num">36.6</span><span class="unit">°C</span></div>
                </div>
                <div class="pd-vital-trend">
                    <img src="<?php echo $img; ?>/IMG_16.svg" alt="">
                    <span>0.1%</span><span class="muted">vs yesterday</span>
                </div>
            </div>

            <div class="pd-vital">
                <div class="pd-vital-top">
                    <div class="pd-vital-icon oxygen"><img src="<?php echo $img; ?>/IMG_19.svg" alt=""></div>
                    <span class="pd-chip">Last 24h</span>
                </div>
                <div class="pd-vital-mid">
                    <p class="name">Oxygen Level</p>
                    <div><span class="num">98</span><span class="unit">%</span></div>
                </div>
                <div class="pd-vital-trend">
                    <img src="<?php echo $img; ?>/IMG_16.svg" alt="">
                    <span>2.1%</span><span class="muted">vs yesterday</span>
                </div>
            </div>
        </section>

        <!-- Chart + Upcoming -->
        <section class="pd-mid">
            <div class="pd-panel pd-panel-pad">
                <div class="pd-panel-head">
                    <div>
                        <h3>Weekly Health Overview</h3>
                        <p>Monitoring your heart rate and sleep patterns</p>
                    </div>
                    <a class="pd-link-green" href="medical-records.php">
                        Detailed View <img src="<?php echo $img; ?>/IMG_20.svg" alt="">
                    </a>
                </div>
                <div class="pd-chart">
                    <img src="<?php echo $img; ?>/IMG_21.svg" alt="Health Chart">
                </div>
                <div class="pd-legend">
                    <span><i class="hr"></i> Heart Rate (bpm)</span>
                    <span><i class="sleep"></i> Sleep (hrs)</span>
                </div>
            </div>

            <div class="pd-panel pd-upcoming">
                <div class="pd-upcoming-top">
                    <div class="row">
                        <h3>Upcoming</h3>
                        <a class="pd-cal-btn" href="my-appointments.php">View Calendar</a>
                    </div>
                    <p style="margin:0;font-size:14px;color:#6b7280;">Next scheduled visits</p>
                </div>

                <div class="pd-appt-list">
                    <?php
                    if ($appt_result && mysqli_num_rows($appt_result) > 0) {
                        while ($a = mysqli_fetch_array($appt_result)) {
                            $mo = strtoupper(date('M', strtotime($a['appointment_date'])));
                            $dy = date('d', strtotime($a['appointment_date']));
                            $tm = date('h:i A', strtotime($a['appointment_time']));
                            echo '<div class="pd-appt">';
                            echo '<div class="pd-date-box"><span class="mo">' . $mo . '</span><span class="dy">' . $dy . '</span></div>';
                            echo '<div class="pd-appt-info">';
                            echo '<p class="name">' . htmlspecialchars($a['doctor_name']) . '</p>';
                            echo '<p class="meta">' . htmlspecialchars($a['specialization']) . ' • ' . htmlspecialchars($a['service_name']) . '</p>';
                            echo '</div>';
                            echo '<div class="pd-appt-right">';
                            echo '<div class="time"><img src="' . $img . '/IMG_22.svg" alt=""> ' . $tm . '</div>';
                            echo '<a href="my-appointments.php">Manage</a>';
                            echo '</div></div>';
                        }
                    } else {
                        echo '<p style="font-size:14px;color:#6b7280;">No upcoming appointments. <a href="book-appointment.php" style="color:#007A41;font-weight:600;">Book one</a>.</p>';
                    }
                    ?>

                    <div class="pd-lab-alert">
                        <img src="<?php echo $img; ?>/IMG_19.svg" alt="">
                        <div>
                            <p class="title">Lab Tests</p>
                            <p class="desc">
                                <?php if ($test_count > 0) { ?>
                                    You have <?php echo (int)$test_count; ?> test result(s) available in Test Results.
                                <?php } else { ?>
                                    No new lab results yet. Results will appear here when staff add them.
                                <?php } ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pd-upcoming-foot">
                    <a class="pd-btn-soft" href="my-appointments.php">View All Appointments</a>
                </div>
            </div>
        </section>

        <!-- Records -->
        <section>
            <div class="pd-records-head">
                <div>
                    <h2>Recent Medical Records</h2>
                    <p>Access your latest reports and test results</p>
                </div>
                <a class="pd-link-green" href="medical-records.php">
                    Go to Records <img src="<?php echo $img; ?>/IMG_23.svg" alt="">
                </a>
            </div>

            <div class="pd-records">
                <?php
                if ($rec_result && mysqli_num_rows($rec_result) > 0) {
                    while ($r = mysqli_fetch_array($rec_result)) {
                        echo '<div class="pd-record">';
                        echo '<div class="pd-record-top">';
                        echo '<div class="pd-record-icon"><img src="' . $img . '/IMG_4.svg" alt=""></div>';
                        echo '<span class="pd-tag">Visit</span>';
                        echo '</div>';
                        echo '<h4>' . htmlspecialchars($r['diagnosis']) . '</h4>';
                        echo '<p class="doc">' . htmlspecialchars($r['doctor_name']) . '</p>';
                        echo '<div class="pd-record-foot">';
                        echo '<span class="date">' . htmlspecialchars($r['visit_date']) . '</span>';
                        echo '<a href="record-details.php?id=' . (int)$r['id'] . '">View Report <img src="' . $img . '/IMG_20.svg" alt=""></a>';
                        echo '</div></div>';
                    }
                } else {
                    // Keep design look with sample empty-state cards style
                    echo '<div class="pd-empty">No medical records yet. Records will appear after your doctor visits.</div>';
                }
                ?>
            </div>
        </section>
    </main>

    <footer class="pd-footer">
        <p>© 2026 BestCare Hospital. All health data is encrypted and handled with care.</p>
        <div class="pd-footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="../contact.php">Support</a>
        </div>
    </footer>
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
