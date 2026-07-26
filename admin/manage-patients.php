<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'patients';
$admin_name = $_SESSION['username'];
$error = "";
$success = "";
$open_edit_id = 0;

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'updated') {
        $success = "Patient details updated.";
    }
}

// Edit patient details
if (isset($_POST['edit_patient'])) {
    $patient_id = (int)$_POST['patient_id'];
    $full_name = trim($_POST['full_name']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $open_edit_id = $patient_id;

    if (strlen($full_name) == 0 || strlen($dob) == 0 || strlen($gender) == 0 ||
        strlen($contact) == 0 || strlen($address) == 0) {
        $error = "Need to fill all the fields.";
    } elseif ($gender != 'Male' && $gender != 'Female' && $gender != 'Other') {
        $error = "Invalid gender selected.";
    } else {
        $safe_name = mysqli_real_escape_string($conn, $full_name);
        $safe_contact = mysqli_real_escape_string($conn, $contact);
        $safe_address = mysqli_real_escape_string($conn, $address);
        $safe_gender = mysqli_real_escape_string($conn, $gender);
        $safe_dob = mysqli_real_escape_string($conn, $dob);

        $sql = "UPDATE patients SET full_name='$safe_name', dob='$safe_dob', gender='$safe_gender',
                contact='$safe_contact', address='$safe_address'
                WHERE id=$patient_id";
        $ok = mysqli_query($conn, $sql);

        if (!$ok) {
            $error = bestcare_db_error($conn, "Could not update patient. Please try again.");
        } else {
            $success = "Patient details updated.";
            $open_edit_id = 0;
            header("Location: manage-patients.php?msg=updated");
            exit();
        }
    }
}

$list_sql = "SELECT p.*, u.username, u.is_active, u.created_at,
                    (SELECT COUNT(*) FROM appointments a WHERE a.patient_id = p.id) AS appt_count
             FROM patients p, users u
             WHERE p.user_id = u.id
             AND u.role = 'patient'
             ORDER BY p.full_name";
$list = mysqli_query($conn, $list_sql);

$patients = array();
if ($list) {
    while ($row = mysqli_fetch_array($list)) {
        $patients[] = $row;
    }
}

$total = count($patients);
$active_count = 0;
$inactive_count = 0;
for ($i = 0; $i < $total; $i++) {
    if ($patients[$i]['is_active'] == 1) {
        $active_count++;
    } else {
        $inactive_count++;
    }
}

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
    <title>Manage Patients - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=2">
</head>
<body class="adm-body<?php if ($open_edit_id > 0) echo ' modal-open'; ?>">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Manage Patients</h1>
        <span class="role-tag">Administrator</span>
    </header>

    <main class="adm-content">
        <?php if ($error != "") { ?>
            <div class="adm-alert err"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="adm-alert ok"><?php echo $success; ?></div>
        <?php } ?>

        <div class="adm-ms-head">
            <div>
                <h1>Registered Patients</h1>
                <p>All patients who registered through the hospital portal.</p>
            </div>
        </div>

        <section class="adm-stats" style="margin-bottom:22px;">
            <div class="adm-stat">
                <p class="label">Total</p>
                <p class="value"><?php echo $total; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Active</p>
                <p class="value"><?php echo $active_count; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Inactive</p>
                <p class="value"><?php echo $inactive_count; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Accounts</p>
                <p class="value"><?php echo $total; ?></p>
            </div>
        </section>

        <section class="adm-ms-panel">
            <div class="adm-ms-panel-top">
                <div class="adm-ms-tabs">
                    <button class="adm-ms-tab active" type="button" data-filter="all">All</button>
                    <button class="adm-ms-tab" type="button" data-filter="active">Active</button>
                    <button class="adm-ms-tab" type="button" data-filter="inactive">Inactive</button>
                    <button class="adm-ms-tab" type="button" data-filter="Male">Male</button>
                    <button class="adm-ms-tab" type="button" data-filter="Female">Female</button>
                </div>
                <div class="adm-ms-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                    <input type="text" id="mpSearch" placeholder="Search patients...">
                </div>
            </div>

            <?php if ($total > 0) { ?>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Username</th>
                                <th>Gender</th>
                                <th>Contact</th>
                                <th>Registered</th>
                                <th>Appointments</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="mpBody">
                            <?php for ($i = 0; $i < $total; $i++) {
                                $row = $patients[$i];
                                $status_key = ($row['is_active'] == 1) ? 'active' : 'inactive';

                                $parts = explode(' ', $row['full_name']);
                                $ini = strtoupper(substr($parts[0], 0, 1));
                                if (isset($parts[1])) {
                                    $ini .= strtoupper(substr($parts[1], 0, 1));
                                }
                            ?>
                                <tr class="mp-row"
                                    data-status="<?php echo $status_key; ?>"
                                    data-gender="<?php echo htmlspecialchars($row['gender']); ?>">
                                    <td>
                                        <div class="adm-staff-cell">
                                            <div class="adm-staff-avatar"><?php echo htmlspecialchars($ini); ?></div>
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($row['full_name']); ?></p>
                                                <p class="spec"><?php echo date('M d, Y', strtotime($row['dob'])); ?> &middot; DOB</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo (int)$row['appt_count']; ?></td>
                                    <td>
                                        <?php if ($row['is_active'] == 1) { ?>
                                            <span class="adm-status active">Active</span>
                                        <?php } else { ?>
                                            <span class="adm-status inactive">Inactive</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="adm-ms-actions">
                                            <button type="button" class="adm-btn-outline btn-edit-patient"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-user="<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>"
                                                data-name="<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>"
                                                data-dob="<?php echo htmlspecialchars($row['dob'], ENT_QUOTES); ?>"
                                                data-gender="<?php echo htmlspecialchars($row['gender'], ENT_QUOTES); ?>"
                                                data-contact="<?php echo htmlspecialchars($row['contact'], ENT_QUOTES); ?>"
                                                data-address="<?php echo htmlspecialchars($row['address'], ENT_QUOTES); ?>">
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-empty" id="mpNoMatch" style="display:none;">No patients match your search.</div>
            <?php } else { ?>
                <div class="adm-empty">No registered patients yet.</div>
            <?php } ?>
        </section>
    </main>
</div>

<!-- EDIT PATIENT MODAL -->
<div class="adm-modal-overlay<?php if ($open_edit_id > 0) echo ' open'; ?>" id="editPatientModal">
    <div class="adm-modal" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseEditPatient" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>
        <h2>Edit Patient</h2>
        <p class="modal-sub">Update details for <strong id="editPatientUserLabel"></strong>.</p>

        <form class="adm-form" method="post" action="">
            <input type="hidden" name="patient_id" id="editPatientId" value="<?php echo $open_edit_id; ?>">

            <div class="field">
                <label for="edit_full_name">Full Name *</label>
                <input type="text" name="full_name" id="edit_full_name" required>
            </div>
            <div class="field">
                <label for="edit_dob">Date of Birth *</label>
                <input type="date" name="dob" id="edit_dob" required>
            </div>
            <div class="field">
                <label for="edit_gender">Gender *</label>
                <select name="gender" id="edit_gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="field">
                <label for="edit_contact">Contact *</label>
                <input type="text" name="contact" id="edit_contact" required>
            </div>
            <div class="field">
                <label for="edit_address">Address *</label>
                <textarea name="address" id="edit_address" required></textarea>
            </div>

            <div class="adm-modal-actions">
                <button type="button" class="adm-btn-ghost" id="btnCancelEditPatient">Cancel</button>
                <button type="submit" name="edit_patient" value="1" class="adm-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('admMenuBtn').addEventListener('click', function () {
    document.getElementById('admNav').classList.toggle('open');
    document.getElementById('admSideFooter').classList.toggle('open');
});

var body = document.body;
var editModal = document.getElementById('editPatientModal');

function openEdit() {
    body.classList.add('modal-open');
    editModal.classList.add('open');
}

function closeEdit() {
    editModal.classList.remove('open');
    body.classList.remove('modal-open');
}

document.getElementById('btnCloseEditPatient').onclick = closeEdit;
document.getElementById('btnCancelEditPatient').onclick = closeEdit;
editModal.addEventListener('click', function (e) {
    if (e.target === editModal) closeEdit();
});

var editBtns = document.querySelectorAll('.btn-edit-patient');
for (var i = 0; i < editBtns.length; i++) {
    editBtns[i].onclick = function () {
        document.getElementById('editPatientId').value = this.getAttribute('data-id');
        document.getElementById('editPatientUserLabel').textContent = this.getAttribute('data-user');
        document.getElementById('edit_full_name').value = this.getAttribute('data-name');
        document.getElementById('edit_dob').value = this.getAttribute('data-dob');
        document.getElementById('edit_gender').value = this.getAttribute('data-gender');
        document.getElementById('edit_contact').value = this.getAttribute('data-contact');
        document.getElementById('edit_address').value = this.getAttribute('data-address');
        openEdit();
    };
}

var mpTabs = document.querySelectorAll('.adm-ms-tab');
var mpRows = document.querySelectorAll('.mp-row');
var mpSearch = document.getElementById('mpSearch');
var mpNoMatch = document.getElementById('mpNoMatch');
var mpFilter = 'all';

function mpApply() {
    var term = mpSearch ? mpSearch.value.toLowerCase() : '';
    var shown = 0;
    for (var i = 0; i < mpRows.length; i++) {
        var row = mpRows[i];
        var status = row.getAttribute('data-status');
        var gender = row.getAttribute('data-gender');
        var okFilter = true;

        if (mpFilter === 'active' || mpFilter === 'inactive') {
            okFilter = (status === mpFilter);
        } else if (mpFilter === 'Male' || mpFilter === 'Female') {
            okFilter = (gender === mpFilter);
        }

        var okTerm = (term === '' || row.textContent.toLowerCase().indexOf(term) !== -1);

        if (okFilter && okTerm) {
            row.style.display = '';
            shown++;
        } else {
            row.style.display = 'none';
        }
    }
    if (mpNoMatch) {
        mpNoMatch.style.display = (shown === 0) ? 'block' : 'none';
    }
}

for (var t = 0; t < mpTabs.length; t++) {
    mpTabs[t].onclick = function () {
        for (var k = 0; k < mpTabs.length; k++) {
            mpTabs[k].className = 'adm-ms-tab';
        }
        this.className = 'adm-ms-tab active';
        mpFilter = this.getAttribute('data-filter');
        mpApply();
    };
}
if (mpSearch) {
    mpSearch.onkeyup = mpApply;
}

<?php if ($open_edit_id > 0 && isset($_POST['edit_patient'])) { ?>
document.getElementById('edit_full_name').value = <?php echo json_encode(isset($_POST['full_name']) ? $_POST['full_name'] : ''); ?>;
document.getElementById('edit_dob').value = <?php echo json_encode(isset($_POST['dob']) ? $_POST['dob'] : ''); ?>;
document.getElementById('edit_gender').value = <?php echo json_encode(isset($_POST['gender']) ? $_POST['gender'] : 'Male'); ?>;
document.getElementById('edit_contact').value = <?php echo json_encode(isset($_POST['contact']) ? $_POST['contact'] : ''); ?>;
document.getElementById('edit_address').value = <?php echo json_encode(isset($_POST['address']) ? $_POST['address'] : ''); ?>;
<?php } ?>
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
