<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$dash = "/bestcare-hospital/assets/patient-dash";
$error = "";
$success = "";

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

if (isset($_POST['send_query'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (strlen($subject) == 0 || strlen($message) == 0) {
        $error = "Need to fill all the fields";
    } else {
        $safe_subject = mysqli_real_escape_string($conn, $subject);
        $safe_message = mysqli_real_escape_string($conn, $message);

        $sql = "INSERT INTO queries (patient_id, subject, message, status)
                VALUES ($patient_id, '$safe_subject', '$safe_message', 'Open')";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            $error = bestcare_db_error($conn, "Could not send query. Please try again.");
        } else {
            header("Location: my-queries.php?msg=sent");
            exit();
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'sent') {
    $success = "Query submitted successfully. The hospital administration will reply soon.";
}

$list_sql = "SELECT * FROM queries WHERE patient_id=$patient_id ORDER BY created_at DESC";
$list = mysqli_query($conn, $list_sql);

$rows = array();
if ($list) {
    while ($row = mysqli_fetch_array($list)) {
        $rows[] = $row;
    }
}
$count = count($rows);

// Never show a leftover ?msg= success banner together with a new error
if ($error != "") {
    $success = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Queries - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-queries.css?v=1">
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
        <a href="treatment-plans.php">
            <img src="<?php echo $dash; ?>/IMG_4.svg" alt=""> Treatment Plans
        </a>
        <a class="active" href="my-queries.php">
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
        <form class="pd-search" method="get" action="../search.php">
            <img src="<?php echo $dash; ?>/IMG_11.svg" alt="">
            <input type="text" name="search_term" placeholder="Search...">
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

    <main class="pd-content pq-page">
        <div class="pq-head">
            <h1>My Queries</h1>
            <p>Have a question about your treatment, appointments, or medical records? Submit a query below and our medical staff will assist you.</p>
        </div>

        <div class="pq-grid">
            <!-- Left: send query -->
            <div>
                <div class="pq-card">
                    <div class="pq-card-head">
                        <div class="icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        </div>
                        <div>
                            <h2>Send New Query</h2>
                            <p class="sub">Estimated response time: 24–48 hours.</p>
                        </div>
                    </div>

                    <?php if ($error != "") { ?>
                        <div class="pq-alert err"><?php echo $error; ?></div>
                    <?php } ?>
                    <?php if ($success != "") { ?>
                        <div class="pq-alert ok"><?php echo $success; ?></div>
                    <?php } ?>

                    <form class="pq-form" method="post" action="" id="patientQueryForm">
                        <div class="field">
                            <label for="subject">Subject</label>
                            <input type="text" name="subject" id="subject" required
                                   placeholder="Briefly describe your inquiry"
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                        </div>
                        <div class="field">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" rows="5" required
                                      placeholder="Please provide as much detail as possible to help us assist you better..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        <button type="submit" name="send_query" value="1" class="pq-submit">Send Query</button>
                    </form>

                    <div class="pq-emergency">
                        <span class="dot">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </span>
                        <span>For medical emergencies, please call 041-2223344 or visit the hospital emergency unit immediately.</span>
                    </div>
                </div>

                <!-- Only Clear Subjects tip (no Check History / Secure Communication) -->
                <div class="pq-tip">
                    <div class="tip-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div>
                        <strong>Clear Subjects</strong>
                        <p>Use specific subjects like “Billing Error” or “Medication Question” for faster routing.</p>
                    </div>
                </div>
            </div>

            <!-- Right: previous queries -->
            <div class="pq-card">
                <div class="pq-list-head">
                    <h2>
                        <span class="icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>
                        </span>
                        Previous Queries
                    </h2>
                    <div class="pq-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                        <input type="text" id="pqSearch" placeholder="Search queries..." autocomplete="off">
                    </div>
                </div>

                <?php if ($count > 0) { ?>
                    <div class="pq-table-wrap">
                        <table class="pq-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Query Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="pqBody">
                                <?php for ($i = 0; $i < $count; $i++) {
                                    $row = $rows[$i];
                                    $status = $row['status'];
                                    $status_class = 'open';
                                    $status_label = $status;
                                    if ($status == 'Replied') {
                                        $status_class = 'replied';
                                        $status_label = 'Resolved';
                                    } elseif ($status == 'Closed') {
                                        $status_class = 'closed';
                                        $status_label = 'Closed';
                                    } else {
                                        $status_class = 'open';
                                        $status_label = 'Open';
                                    }

                                    $reply = isset($row['reply']) ? $row['reply'] : '';
                                    $snippet = $reply != '' ? $reply : $row['message'];
                                    if (strlen($snippet) > 90) {
                                        $snippet = substr($snippet, 0, 87) . '...';
                                    }

                                    $created = $row['created_at'];
                                    $date_part = date('Y-m-d', strtotime($created));
                                    $time_part = date('h:i A', strtotime($created));
                                    $qid = 'Q-' . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
                                    ?>
                                    <tr class="pq-row" data-search="<?php echo htmlspecialchars(strtolower($row['subject'] . ' ' . $row['message'] . ' ' . $status_label)); ?>">
                                        <td>
                                            <div class="pq-date">
                                                <?php echo $date_part; ?><br>
                                                <?php echo $time_part; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="pq-qid"><?php echo $qid; ?></span>
                                            <span class="pq-subject"><?php echo htmlspecialchars($row['subject']); ?></span>
                                            <span class="pq-snippet"><?php echo htmlspecialchars($snippet); ?></span>
                                        </td>
                                        <td>
                                            <span class="pq-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="pq-foot" id="pqFoot">Showing <?php echo $count; ?> of <?php echo $count; ?> queries</p>
                <?php } else { ?>
                    <div class="pq-empty">No queries yet. Send your first question using the form.</div>
                <?php } ?>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('pdMenuBtn').addEventListener('click', function () {
    document.getElementById('pdNav').classList.toggle('open');
    document.getElementById('pdSideFooter').classList.toggle('open');
});

var search = document.getElementById('pqSearch');
var rows = document.querySelectorAll('.pq-row');
var foot = document.getElementById('pqFoot');
var total = rows.length;

if (search) {
    search.addEventListener('input', function () {
        var term = search.value.toLowerCase().trim();
        var shown = 0;
        for (var i = 0; i < rows.length; i++) {
            var hay = rows[i].getAttribute('data-search') || '';
            if (term === '' || hay.indexOf(term) !== -1) {
                rows[i].style.display = '';
                shown++;
            } else {
                rows[i].style.display = 'none';
            }
        }
        if (foot) {
            foot.textContent = 'Showing ' + shown + ' of ' + total + ' queries';
        }
    });
}
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
