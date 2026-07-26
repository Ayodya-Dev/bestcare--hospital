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
$today = date('Y-m-d');

// Admin can update any appointment status
if (isset($_POST['action_id']) && isset($_POST['new_status'])) {
    $action_id = (int)$_POST['action_id'];
    $new_status = $_POST['new_status'];

    if ($new_status == 'Confirmed' || $new_status == 'Cancelled' || $new_status == 'Completed') {
        $check = mysqli_query($conn, "SELECT status FROM appointments WHERE id=$action_id");
        if ($check && mysqli_num_rows($check) > 0) {
            $cur = mysqli_fetch_array($check);
            $ok = false;

            if ($new_status == 'Confirmed' && $cur['status'] == 'Pending') {
                $ok = true;
            }
            if ($new_status == 'Cancelled' && ($cur['status'] == 'Pending' || $cur['status'] == 'Confirmed')) {
                $ok = true;
            }
            if ($new_status == 'Completed' && $cur['status'] == 'Confirmed') {
                $ok = true;
            }

            if ($ok) {
                mysqli_query($conn, "UPDATE appointments SET status='$new_status' WHERE id=$action_id");
                $notice = "Appointment updated to $new_status.";
            } else {
                $error = "That status change is not allowed for this appointment.";
            }
        } else {
            $error = "Appointment not found.";
        }
    }
}

$sql = "SELECT a.*, p.full_name AS patient_name, p.contact AS patient_contact,
               st.full_name AS doctor_name, st.specialization, s.name AS service_name
        FROM appointments a, patients p, staff st, services s
        WHERE a.patient_id = p.id
        AND a.staff_id = st.id
        AND a.service_id = s.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($conn, $sql);

$rows = array();
if ($result) {
    while ($r = mysqli_fetch_array($result)) {
        $rows[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Appointments - Admin</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=3">
</head>
<body class="adm-body">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Appointments</h1>
        <span class="role-tag">Administrator</span>
    </header>

    <main class="adm-content">
        <?php if ($notice != "") { ?>
            <div class="adm-alert ok"><?php echo $notice; ?></div>
        <?php } ?>
        <?php if ($error != "") { ?>
            <div class="adm-alert err"><?php echo $error; ?></div>
        <?php } ?>

        <div class="adm-ms-head">
            <div>
                <h1>All Patient Appointments</h1>
                <p>View and manage every booking across the hospital.</p>
            </div>
        </div>

        <section class="adm-ms-panel">
            <div class="adm-ms-panel-top">
                <div class="adm-ms-tabs">
                    <button class="adm-ms-tab active" type="button" data-filter="all">All</button>
                    <button class="adm-ms-tab" type="button" data-filter="upcoming">Upcoming</button>
                    <button class="adm-ms-tab" type="button" data-filter="past">Past</button>
                    <button class="adm-ms-tab" type="button" data-filter="Pending">Pending</button>
                    <button class="adm-ms-tab" type="button" data-filter="Confirmed">Confirmed</button>
                    <button class="adm-ms-tab" type="button" data-filter="Completed">Completed</button>
                    <button class="adm-ms-tab" type="button" data-filter="Cancelled">Cancelled</button>
                </div>
                <div class="adm-ms-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                    <input type="text" id="apSearch" placeholder="Search patient, doctor, service...">
                </div>
            </div>

            <?php if (count($rows) > 0) { ?>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($rows); $i++) {
                                $row = $rows[$i];
                                $status = $row['status'];

                                if ($row['appointment_date'] >= $today && ($status == 'Pending' || $status == 'Confirmed')) {
                                    $group = 'upcoming';
                                } else {
                                    $group = 'past';
                                }

                                $doc_parts = explode(' ', $row['doctor_name']);
                                $doc_ini = strtoupper(substr($doc_parts[0], 0, 1));
                                if (isset($doc_parts[1])) {
                                    $doc_ini .= strtoupper(substr($doc_parts[1], 0, 1));
                                }

                                $pat_parts = explode(' ', $row['patient_name']);
                                $pat_ini = strtoupper(substr($pat_parts[0], 0, 1));
                                if (isset($pat_parts[1])) {
                                    $pat_ini .= strtoupper(substr($pat_parts[1], 0, 1));
                                }
                            ?>
                                <tr class="ap-row" data-group="<?php echo $group; ?>" data-status="<?php echo htmlspecialchars($status); ?>">
                                    <td>
                                        <span class="ma-date"><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></span>
                                    </td>
                                    <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                    <td>
                                        <p class="name" style="margin:0;font-weight:600;color:#111827;"><?php echo htmlspecialchars($row['service_name']); ?></p>
                                        <p class="spec" style="margin:3px 0 0;font-size:11px;color:#9ca3af;">Ref: BC-<?php echo str_pad($row['id'], 4, "0", STR_PAD_LEFT); ?></p>
                                    </td>
                                    <td>
                                        <div class="adm-staff-cell">
                                            <div class="adm-staff-avatar"><?php echo htmlspecialchars($pat_ini); ?></div>
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($row['patient_name']); ?></p>
                                                <p class="spec"><?php echo htmlspecialchars($row['patient_contact']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="adm-staff-cell">
                                            <div class="adm-staff-avatar"><?php echo htmlspecialchars($doc_ini); ?></div>
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($row['doctor_name']); ?></p>
                                                <p class="spec"><?php echo htmlspecialchars($row['specialization']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="adm-status <?php echo strtolower($status); ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                    <td>
                                        <div class="adm-ms-actions">
                                            <a class="adm-btn-outline" href="appointment-details.php?id=<?php echo $row['id']; ?>">View</a>
                                            <?php if ($status == 'Pending') { ?>
                                                <form method="post" action="">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Confirmed">
                                                    <button type="submit" class="adm-btn-outline">Confirm</button>
                                                </form>
                                                <form method="post" action="" onsubmit="return confirm('Cancel this appointment?');">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Cancelled">
                                                    <button type="submit" class="adm-btn-danger">Cancel</button>
                                                </form>
                                            <?php } elseif ($status == 'Confirmed') { ?>
                                                <form method="post" action="">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Completed">
                                                    <button type="submit" class="adm-btn-blue">Complete</button>
                                                </form>
                                                <form method="post" action="" onsubmit="return confirm('Cancel this appointment?');">
                                                    <input type="hidden" name="action_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="Cancelled">
                                                    <button type="submit" class="adm-btn-danger">Cancel</button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-empty" id="apNoMatch" style="display:none;">No appointments match your search.</div>
            <?php } else { ?>
                <div class="adm-empty">No appointments in the system yet.</div>
            <?php } ?>
        </section>
    </main>
</div>

<script>
document.getElementById('admMenuBtn').addEventListener('click', function () {
    document.getElementById('admNav').classList.toggle('open');
    document.getElementById('admSideFooter').classList.toggle('open');
});

var tabs = document.querySelectorAll('.adm-ms-tab');
var rows = document.querySelectorAll('.ap-row');
var search = document.getElementById('apSearch');
var noMatch = document.getElementById('apNoMatch');
var filter = 'all';

function applyFilter() {
    var term = search ? search.value.toLowerCase() : '';
    var shown = 0;
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var group = row.getAttribute('data-group');
        var status = row.getAttribute('data-status');
        var ok = true;

        if (filter === 'upcoming' || filter === 'past') {
            ok = (group === filter);
        } else if (filter === 'Pending' || filter === 'Confirmed' || filter === 'Completed' || filter === 'Cancelled') {
            ok = (status === filter);
        }

        var okTerm = (term === '' || row.textContent.toLowerCase().indexOf(term) !== -1);
        if (ok && okTerm) {
            row.style.display = '';
            shown++;
        } else {
            row.style.display = 'none';
        }
    }
    if (noMatch) {
        noMatch.style.display = (shown === 0) ? 'block' : 'none';
    }
}

for (var t = 0; t < tabs.length; t++) {
    tabs[t].onclick = function () {
        for (var k = 0; k < tabs.length; k++) {
            tabs[k].className = 'adm-ms-tab';
        }
        this.className = 'adm-ms-tab active';
        filter = this.getAttribute('data-filter');
        applyFilter();
    };
}
if (search) {
    search.onkeyup = applyFilter;
}
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
