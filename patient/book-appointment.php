<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$error = "";
$success = "";
$dash = "/bestcare-hospital/assets/patient-dash";
$img = "/bestcare-hospital/assets/book";

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

$services_result = mysqli_query($conn, "SELECT * FROM services ORDER BY name");
$services = array();
while ($s = mysqli_fetch_array($services_result)) {
    $services[] = $s;
}

$doctors_result = mysqli_query($conn, "SELECT st.id, st.full_name, st.specialization, d.name AS department_name
                                       FROM staff st, departments d
                                       WHERE st.department_id = d.id
                                       ORDER BY st.full_name");
$doctors = array();
while ($d = mysqli_fetch_array($doctors_result)) {
    $doctors[] = $d;
}

$booked_result = mysqli_query($conn, "SELECT staff_id, appointment_date, appointment_time
                                      FROM appointments
                                      WHERE status != 'Cancelled'");
$booked = array();
if ($booked_result) {
    while ($b = mysqli_fetch_array($booked_result)) {
        $booked[] = array(
            'staff_id' => $b['staff_id'],
            'date' => $b['appointment_date'],
            'time' => substr($b['appointment_time'], 0, 5)
        );
    }
}

if (isset($_POST['book_submit'])) {
    $service_id = $_POST['service_id'];
    $staff_id = $_POST['staff_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    if (strlen($service_id) == 0 || strlen($staff_id) == 0 ||
        strlen($appointment_date) == 0 || strlen($appointment_time) == 0) {
        $error = "Need to fill all the fields";
    } elseif ($appointment_date < date('Y-m-d')) {
        $error = "Appointment date cannot be in the past";
    } else {
        $check_sql = "SELECT * FROM appointments
                      WHERE staff_id=$staff_id
                      AND appointment_date='$appointment_date'
                      AND appointment_time='$appointment_time'
                      AND status != 'Cancelled'";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = "This time slot is already booked. Please choose another time.";
        } else {
            $sql = "INSERT INTO appointments (patient_id, staff_id, service_id, appointment_date, appointment_time, status)
                    VALUES ($patient_id, $staff_id, $service_id, '$appointment_date', '$appointment_time', 'Pending')";
            $result = mysqli_query($conn, $sql);

            if (!$result) {
                die("Could not book appointment: " . mysqli_error($conn));
            }

            $success = "Appointment booked successfully! Status: Pending";
        }
    }
}

$service_charge = 5.00;
$today = date('Y-m-d');
$doctor_photos = array($img . '/IMG_19.webp', $img . '/IMG_20.webp');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=15">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/book-appointment.css?v=15">
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
        <a class="active" href="book-appointment.php">
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
            <input type="text" name="search_term" placeholder="Search doctors, services...">
        </form>

        <div class="pd-top-actions">
            <button class="pd-icon-btn" type="button" title="Notifications">
                <img src="<?php echo $dash; ?>/IMG_12.svg" alt="Notifications">
                <span class="pd-dot"></span>
            </button>
            <a class="ba-my-appts-btn" href="my-appointments.php">My Appointments</a>
            <div class="pd-user-chip">
                <div class="mini"><?php echo htmlspecialchars($initials); ?></div>
                <span><?php echo htmlspecialchars($first_name); ?></span>
            </div>
        </div>
    </header>

    <main class="pd-content ba-page-content">
        <section class="ba-hero">
            <img class="ba-hero-bg" src="<?php echo $img; ?>/IMG_13.webp" alt="Hospital">
            <div class="ba-hero-overlay"></div>
            <div class="ba-hero-text">
                <span class="ba-hero-tag">Secure Booking System</span>
                <h1>Book Your Consultation with BestCare Experts</h1>
                <p>Schedule your visit with our medical specialists in just a few clicks.</p>
            </div>
        </section>

        <?php if ($error != "") { ?>
            <div class="ba-alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="ba-alert success">
                <?php echo htmlspecialchars($success); ?>
                — <a href="my-appointments.php">View My Appointments</a>
            </div>
        <?php } ?>

        <form method="post" action="" id="bookForm">
            <div class="ba-layout">
                <div class="ba-left">
                    <section class="ba-card">
                        <div class="ba-card-head">
                            <div class="ba-card-title">
                                <img src="<?php echo $img; ?>/IMG_14.svg" alt="">
                                <h2>Select Medical Service &amp; Professional</h2>
                            </div>
                        </div>

                        <div class="ba-two-col">
                            <div>
                                <label class="ba-label" for="service_id">Medical Service</label>
                                <select class="ba-select" name="service_id" id="service_id" required>
                                    <option value="">Select a service</option>
                                    <?php foreach ($services as $s) { ?>
                                        <option value="<?php echo (int)$s['id']; ?>"
                                                data-fee="<?php echo htmlspecialchars($s['fee']); ?>"
                                                data-name="<?php echo htmlspecialchars($s['name']); ?>">
                                            <?php echo htmlspecialchars($s['name']); ?> — Rs. <?php echo number_format($s['fee'], 2); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label class="ba-label">Visit Type</label>
                                <div class="ba-visit">
                                    <label class="ba-visit-opt active">
                                        <input type="radio" name="visit_type" value="In-Person" checked>
                                        <img src="<?php echo $img; ?>/IMG_16.svg" alt="">
                                        <span class="title">In-Person</span>
                                    </label>
                                    <label class="ba-visit-opt">
                                        <input type="radio" name="visit_type" value="Virtual">
                                        <img src="<?php echo $img; ?>/IMG_17.svg" alt="">
                                        <span class="title">Virtual</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <label class="ba-label">Choose Your Specialist</label>
                        <div class="ba-docs">
                            <?php
                            $i = 0;
                            foreach ($doctors as $d) {
                                $photo = $doctor_photos[$i % count($doctor_photos)];
                                $active = ($i === 0) ? ' active' : '';
                                $checked = ($i === 0) ? ' checked' : '';
                                echo '<label class="ba-doc' . $active . '" data-name="' . htmlspecialchars($d['full_name']) . '" data-spec="' . htmlspecialchars($d['specialization']) . '" data-photo="' . htmlspecialchars($photo) . '">';
                                echo '<input type="radio" name="staff_id" value="' . (int)$d['id'] . '"' . $checked . ' required>';
                                echo '<span class="badge"><img src="' . $img . '/IMG_18.svg" alt=""></span>';
                                echo '<img class="ba-doc-photo" src="' . htmlspecialchars($photo) . '" alt="">';
                                echo '<div class="ba-doc-info">';
                                echo '<p class="name">' . htmlspecialchars($d['full_name']) . '</p>';
                                echo '<p class="spec">' . htmlspecialchars($d['specialization']) . '</p>';
                                echo '<div class="ba-doc-tags"><span class="exp">' . htmlspecialchars($d['department_name']) . '</span><span class="rate">⭐ 4.9</span></div>';
                                echo '</div></label>';
                                $i++;
                            }
                            ?>
                        </div>
                    </section>

                    <section class="ba-card">
                        <div class="ba-card-head">
                            <div class="ba-card-title">
                                <img src="<?php echo $img; ?>/IMG_9.svg" alt="">
                                <h2>Appointment Schedule</h2>
                            </div>
                        </div>

                        <div class="ba-schedule">
                            <div>
                                <label class="ba-label">Select Preferred Date</label>
                                <div class="ba-cal-box">
                                    <div class="ba-cal-nav">
                                        <button type="button" id="calPrev">‹</button>
                                        <h3 id="calMonthLabel">—</h3>
                                        <button type="button" id="calNext">›</button>
                                    </div>
                                    <div class="ba-cal-grid" id="calGrid"></div>
                                </div>
                                <input type="hidden" name="appointment_date" id="appointment_date" value="" required>
                                <div class="ba-info-banner" id="slotInfo">Select a doctor and date to see available time slots.</div>
                            </div>

                            <div>
                                <div class="ba-slots-head">
                                    <p class="ba-slots-title">Available Time Slots</p>
                                    <span class="ba-duration"><img src="<?php echo $img; ?>/IMG_22.svg" alt=""> Duration: 30m</span>
                                </div>
                                <div class="ba-slots" id="slotGrid"></div>
                                <input type="hidden" name="appointment_time" id="appointment_time" value="" required>
                                <div class="ba-legend">
                                    <span><i class="avail"></i> Available</span>
                                    <span><i class="booked"></i> Booked</span>
                                    <span><i class="sel"></i> Selected</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="ba-card">
                        <div class="ba-card-head">
                            <div class="ba-card-title">
                                <img src="<?php echo $img; ?>/IMG_25.svg" alt="">
                                <h2>Consultation Notes</h2>
                            </div>
                        </div>
                        <label class="ba-label" for="notes">Describe your symptoms or reason for visit (Optional)</label>
                        <textarea class="ba-textarea" name="notes" id="notes" placeholder="Please share any relevant medical history or current concerns..."></textarea>
                        <p class="ba-notes-hint">Your information is for booking reference and shared with staff at your visit.</p>
                    </section>
                </div>

                <aside class="ba-side">
                    <div class="ba-summary">
                        <h3>Booking Summary</h3>
                        <p class="ba-summary-sub">Review your appointment details</p>

                        <div class="ba-sum-doc">
                            <img id="sumPhoto" src="<?php echo $doctor_photos[0]; ?>" alt="">
                            <div>
                                <p class="tiny">Specialist</p>
                                <p class="name" id="sumDoctor">Select a specialist</p>
                                <p class="spec" id="sumSpec">—</p>
                            </div>
                        </div>

                        <ul class="ba-sum-list">
                            <li>
                                <span class="left"><span class="ico green"><img src="<?php echo $img; ?>/IMG_21.svg" alt=""></span> Service</span>
                                <strong id="sumService">—</strong>
                            </li>
                            <li>
                                <span class="left"><span class="ico blue"><img src="<?php echo $img; ?>/IMG_9.svg" alt=""></span> Date</span>
                                <strong id="sumDate">—</strong>
                            </li>
                            <li>
                                <span class="left"><span class="ico grey"><img src="<?php echo $img; ?>/IMG_22.svg" alt=""></span> Time</span>
                                <strong id="sumTime">—</strong>
                            </li>
                        </ul>

                        <div class="ba-price">
                            <div class="ba-price-row">
                                <span>Consultation Fee</span>
                                <span id="sumFee">Rs. 0.00</span>
                            </div>
                            <div class="ba-price-row">
                                <span>Service Charge</span>
                                <span>Rs. <?php echo number_format($service_charge, 2); ?></span>
                            </div>
                            <div class="ba-price-total">
                                <span>Estimated Total</span>
                                <span id="sumTotal">Rs. <?php echo number_format($service_charge, 2); ?></span>
                            </div>
                        </div>

                        <button type="submit" name="book_submit" value="1" class="ba-confirm">
                            Confirm Appointment
                            <img src="<?php echo $img; ?>/IMG_3.svg" alt="">
                        </button>
                        <p class="ba-legal">
                            By confirming, you agree to our <a href="#">Terms of Service</a> and <a href="#">Cancellation Policy</a>.
                        </p>
                    </div>
                </aside>
            </div>
        </form>
    </main>
</div>

<script>
document.getElementById('pdMenuBtn').addEventListener('click', function () {
    document.getElementById('pdNav').classList.toggle('open');
    document.getElementById('pdSideFooter').classList.toggle('open');
});

(function () {
    var serviceCharge = <?php echo json_encode($service_charge); ?>;
    var booked = <?php echo json_encode($booked); ?>;
    var todayStr = <?php echo json_encode($today); ?>;

    var slots = [
        { value: '09:00:00', label: '09:00 AM' },
        { value: '09:30:00', label: '09:30 AM' },
        { value: '10:00:00', label: '10:00 AM' },
        { value: '10:30:00', label: '10:30 AM' },
        { value: '11:00:00', label: '11:00 AM' },
        { value: '11:30:00', label: '11:30 AM' },
        { value: '14:00:00', label: '02:00 PM' },
        { value: '14:30:00', label: '02:30 PM' },
        { value: '15:00:00', label: '03:00 PM' },
        { value: '15:30:00', label: '03:30 PM' },
        { value: '16:00:00', label: '04:00 PM' },
        { value: '16:30:00', label: '04:30 PM' }
    ];

    var calMonth = new Date();
    calMonth.setDate(1);
    calMonth.setHours(0, 0, 0, 0);

    var dateInput = document.getElementById('appointment_date');
    var timeInput = document.getElementById('appointment_time');
    var serviceSelect = document.getElementById('service_id');
    var calGrid = document.getElementById('calGrid');
    var slotGrid = document.getElementById('slotGrid');
    var calMonthLabel = document.getElementById('calMonthLabel');

    document.querySelectorAll('.ba-visit-opt').forEach(function (el) {
        el.addEventListener('click', function () {
            document.querySelectorAll('.ba-visit-opt').forEach(function (x) { x.classList.remove('active'); });
            el.classList.add('active');
        });
    });

    document.querySelectorAll('.ba-doc').forEach(function (el) {
        el.addEventListener('click', function () {
            document.querySelectorAll('.ba-doc').forEach(function (x) { x.classList.remove('active'); });
            el.classList.add('active');
            updateSummaryDoctor();
            renderSlots();
        });
    });

    function selectedDoctorId() {
        var r = document.querySelector('input[name="staff_id"]:checked');
        return r ? r.value : '';
    }

    function updateSummaryDoctor() {
        var card = document.querySelector('.ba-doc.active');
        if (!card) return;
        document.getElementById('sumDoctor').textContent = card.getAttribute('data-name');
        document.getElementById('sumSpec').textContent = card.getAttribute('data-spec');
        document.getElementById('sumPhoto').src = card.getAttribute('data-photo');
        updateSlotInfo();
    }

    function updateSummaryService() {
        var opt = serviceSelect.options[serviceSelect.selectedIndex];
        if (!opt || !opt.value) {
            document.getElementById('sumService').textContent = '—';
            document.getElementById('sumFee').textContent = 'Rs. 0.00';
            document.getElementById('sumTotal').textContent = 'Rs. ' + serviceCharge.toFixed(2);
            return;
        }
        var fee = parseFloat(opt.getAttribute('data-fee') || '0');
        document.getElementById('sumService').textContent = opt.getAttribute('data-name');
        document.getElementById('sumFee').textContent = 'Rs. ' + fee.toFixed(2);
        document.getElementById('sumTotal').textContent = 'Rs. ' + (fee + serviceCharge).toFixed(2);
    }

    serviceSelect.addEventListener('change', updateSummaryService);

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function formatDisplayDate(ymd) {
        if (!ymd) return '—';
        var parts = ymd.split('-');
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var m = parseInt(parts[1], 10) - 1;
        var d = parseInt(parts[2], 10);
        return months[m] + ' ' + d + ', ' + parts[0];
    }

    function renderCalendar() {
        var year = calMonth.getFullYear();
        var month = calMonth.getMonth();
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        calMonthLabel.textContent = monthNames[month] + ' ' + year;

        calGrid.innerHTML = '';
        ['S','M','T','W','T','F','S'].forEach(function (d) {
            var el = document.createElement('div');
            el.className = 'dow';
            el.textContent = d;
            calGrid.appendChild(el);
        });

        var firstDow = new Date(year, month, 1).getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var selected = dateInput.value;

        for (var i = 0; i < firstDow; i++) {
            var empty = document.createElement('button');
            empty.type = 'button';
            empty.className = 'day empty';
            empty.disabled = true;
            calGrid.appendChild(empty);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'day';
            btn.textContent = day;
            var ymd = year + '-' + pad(month + 1) + '-' + pad(day);
            if (ymd < todayStr) btn.disabled = true;
            if (ymd === selected) btn.classList.add('selected');
            btn.addEventListener('click', (function (ymdVal) {
                return function () {
                    dateInput.value = ymdVal;
                    document.getElementById('sumDate').textContent = formatDisplayDate(ymdVal);
                    timeInput.value = '';
                    document.getElementById('sumTime').textContent = '—';
                    renderCalendar();
                    renderSlots();
                };
            })(ymd));
            calGrid.appendChild(btn);
        }
    }

    function isBooked(staffId, date, timeHHMM) {
        for (var i = 0; i < booked.length; i++) {
            if (String(booked[i].staff_id) === String(staffId) &&
                booked[i].date === date &&
                booked[i].time === timeHHMM) {
                return true;
            }
        }
        return false;
    }

    function renderSlots() {
        var staffId = selectedDoctorId();
        var date = dateInput.value;
        slotGrid.innerHTML = '';

        slots.forEach(function (s) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ba-slot';
            btn.textContent = s.label;
            var hhmm = s.value.substring(0, 5);

            if (!staffId || !date || isBooked(staffId, date, hhmm)) {
                btn.disabled = true;
            }
            if (timeInput.value === s.value) btn.classList.add('selected');

            btn.addEventListener('click', function () {
                if (btn.disabled) return;
                timeInput.value = s.value;
                document.getElementById('sumTime').textContent = s.label;
                renderSlots();
            });

            slotGrid.appendChild(btn);
        });

        updateSlotInfo();
    }

    function updateSlotInfo() {
        var card = document.querySelector('.ba-doc.active');
        var name = card ? card.getAttribute('data-name') : 'the doctor';
        var date = dateInput.value;
        var info = document.getElementById('slotInfo');
        if (!date) {
            info.textContent = 'Select a preferred date to see slots for ' + name + '.';
        } else {
            info.textContent = 'Showing available slots for ' + name + ' on ' + date + '. Booked times are disabled.';
        }
    }

    document.getElementById('calPrev').addEventListener('click', function () {
        calMonth.setMonth(calMonth.getMonth() - 1);
        renderCalendar();
    });
    document.getElementById('calNext').addEventListener('click', function () {
        calMonth.setMonth(calMonth.getMonth() + 1);
        renderCalendar();
    });

    document.getElementById('bookForm').addEventListener('submit', function (e) {
        if (!serviceSelect.value || !selectedDoctorId() || !dateInput.value || !timeInput.value) {
            e.preventDefault();
            alert('Please select service, doctor, date and time before confirming.');
        }
    });

    updateSummaryDoctor();
    updateSummaryService();
    renderCalendar();
    renderSlots();
})();
</script>
</body>
</html>
<?php mysqli_close($conn); ?>