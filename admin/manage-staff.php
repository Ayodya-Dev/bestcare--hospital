<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'staff';
$admin_name = $_SESSION['username'];
$error = "";
$success = "";
$open_create = false;
$open_edit_id = 0;

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deactivated') {
        $success = "Staff account deactivated.";
    } elseif ($_GET['msg'] == 'activated') {
        $success = "Staff account activated.";
    } elseif ($_GET['msg'] == 'created') {
        $success = "Staff account created successfully.";
    } elseif ($_GET['msg'] == 'updated') {
        $success = "Staff details updated.";
    } elseif ($_GET['msg'] == 'deleted') {
        $success = "Staff account permanently deleted.";
    }
}

// Ensure staff_type column exists (for databases created before this update)
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM staff LIKE 'staff_type'");
if (!$col_check || mysqli_num_rows($col_check) == 0) {
    mysqli_query($conn, "ALTER TABLE staff ADD COLUMN staff_type ENUM('Doctor','Hospital Staff','Other') NOT NULL DEFAULT 'Doctor'");
}

// Ensure image_path column exists
$img_col = mysqli_query($conn, "SHOW COLUMNS FROM staff LIKE 'image_path'");
if (!$img_col || mysqli_num_rows($img_col) == 0) {
    mysqli_query($conn, "ALTER TABLE staff ADD COLUMN image_path VARCHAR(255) NULL");
}

$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY name");
$dept_list = array();
if ($departments) {
    while ($d = mysqli_fetch_array($departments)) {
        $dept_list[] = $d;
    }
}

function adm_valid_staff_type($type) {
    return ($type == 'Doctor' || $type == 'Hospital Staff' || $type == 'Other');
}

// Handle staff image upload; returns path, "" if none, or false on error
function adm_save_staff_image(&$error) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        return "";
    }

    $file = $_FILES['image'];

    if ($file['error'] != UPLOAD_ERR_OK) {
        $error = "Image upload failed. Please try again.";
        return false;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $error = "Image is too big. Maximum size is 2MB.";
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'png' && $ext != 'webp') {
        $error = "Only JPG, PNG or WEBP images are allowed.";
        return false;
    }

    $upload_dir = __DIR__ . "/../assets/uploads/doctors";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_name = "doctor_" . time() . "_" . rand(1000, 9999) . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . "/" . $new_name)) {
        $error = "Could not save the uploaded image.";
        return false;
    }

    return "/bestcare-hospital/assets/uploads/doctors/" . $new_name;
}

// Toggle active / inactive — then redirect so refresh does not toggle again
if (isset($_POST['toggle_user_id'])) {
    $toggle_id = (int)$_POST['toggle_user_id'];
    $check = mysqli_query($conn, "SELECT id, is_active FROM users WHERE id=$toggle_id AND role='staff'");
    if ($check && mysqli_num_rows($check) > 0) {
        $u = mysqli_fetch_array($check);
        $new_active = ((int)$u['is_active'] === 1) ? 0 : 1;
        $upd = mysqli_query($conn, "UPDATE users SET is_active=$new_active WHERE id=$toggle_id AND role='staff'");
        if ($upd) {
            $msg = ($new_active == 1) ? 'activated' : 'deactivated';
            header("Location: manage-staff.php?msg=$msg");
            exit();
        }
        $error = "Could not update account status.";
    } else {
        $error = "Staff account not found.";
    }
}

// Hard delete staff — blocked if they still have linked hospital history
if (isset($_POST['delete_staff_id'])) {
    $del_staff_id = (int)$_POST['delete_staff_id'];

    $get = mysqli_query($conn, "SELECT st.id, st.user_id, st.image_path, st.full_name
                                FROM staff st, users u
                                WHERE st.id=$del_staff_id
                                AND st.user_id = u.id
                                AND u.role='staff'");

    if (!$get || mysqli_num_rows($get) == 0) {
        $error = "Staff account not found.";
    } else {
        $srow = mysqli_fetch_array($get);
        $del_user_id = (int)$srow['user_id'];
        $del_img = isset($srow['image_path']) ? $srow['image_path'] : '';

        $c1 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM appointments WHERE staff_id=$del_staff_id");
        $c2 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM medical_records WHERE staff_id=$del_staff_id");
        $c3 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM prescriptions WHERE staff_id=$del_staff_id");
        $c4 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM treatment_plans WHERE staff_id=$del_staff_id");

        $appt_n = ($c1 && ($r = mysqli_fetch_array($c1))) ? (int)$r['c'] : 0;
        $rec_n = ($c2 && ($r = mysqli_fetch_array($c2))) ? (int)$r['c'] : 0;
        $rx_n = ($c3 && ($r = mysqli_fetch_array($c3))) ? (int)$r['c'] : 0;
        $tp_n = ($c4 && ($r = mysqli_fetch_array($c4))) ? (int)$r['c'] : 0;

        if ($appt_n > 0 || $rec_n > 0 || $rx_n > 0 || $tp_n > 0) {
            $error = "Cannot delete this staff member because they have linked records "
                   . "($appt_n appointment(s), $rec_n medical record(s), $rx_n prescription(s), $tp_n treatment plan(s)). "
                   . "Use Deactivate instead to hide their login.";
        } else {
            mysqli_begin_transaction($conn);

            // Clear query replies credited to this user (FK on users)
            $ok1 = mysqli_query($conn, "UPDATE queries SET replied_by=NULL WHERE replied_by=$del_user_id");
            $ok2 = mysqli_query($conn, "DELETE FROM staff WHERE id=$del_staff_id");
            $ok3 = mysqli_query($conn, "DELETE FROM users WHERE id=$del_user_id AND role='staff'");

            if ($ok1 && $ok2 && $ok3) {
                mysqli_commit($conn);

                // Remove uploaded photo file if it is under our doctors upload folder
                if ($del_img != "" && strpos($del_img, "/assets/uploads/doctors/") !== false) {
                    $base = basename($del_img);
                    $file_path = __DIR__ . "/../assets/uploads/doctors/" . $base;
                    if (is_file($file_path)) {
                        @unlink($file_path);
                    }
                }

                header("Location: manage-staff.php?msg=deleted");
                exit();
            }

            mysqli_rollback($conn);
            $error = bestcare_db_error($conn, "Could not delete staff account. Please try again.");
        }
    }
}

// Add staff
if (isset($_POST['add_staff'])) {
    $staff_type = isset($_POST['staff_type']) ? $_POST['staff_type'] : '';
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $specialization = trim($_POST['specialization']);
    $department_id = (int)$_POST['department_id'];
    $contact = trim($_POST['contact']);
    $open_create = true;

    if (!adm_valid_staff_type($staff_type)) {
        $error = "Please choose a staff type.";
    } elseif (strlen($username) == 0 || strlen($password) == 0 || strlen($full_name) == 0 ||
        strlen($specialization) == 0 || $department_id == 0 || strlen($contact) == 0) {
        $error = "Need to fill all the fields.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $image_path = adm_save_staff_image($error);

        if ($image_path === false) {
            // $error already set by upload helper
        } else {
        $safe_username = mysqli_real_escape_string($conn, $username);
        $safe_name = mysqli_real_escape_string($conn, $full_name);
        $safe_spec = mysqli_real_escape_string($conn, $specialization);
        $safe_contact = mysqli_real_escape_string($conn, $contact);
        $safe_type = mysqli_real_escape_string($conn, $staff_type);

        $exists = mysqli_query($conn, "SELECT id FROM users WHERE username='$safe_username'");
        if ($exists && mysqli_num_rows($exists) > 0) {
            $error = "Username already exists. Choose another.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $safe_hash = mysqli_real_escape_string($conn, $password_hash);

            $sql1 = "INSERT INTO users (username, password_hash, role, is_active)
                     VALUES ('$safe_username', '$safe_hash', 'staff', 1)";
            $result1 = mysqli_query($conn, $sql1);

            if (!$result1) {
                $error = bestcare_db_error($conn, "Could not create staff login. Please try again.");
            } else {
                $user_id = mysqli_insert_id($conn);

                if ($image_path != "") {
                    $safe_img = mysqli_real_escape_string($conn, $image_path);
                    $sql2 = "INSERT INTO staff (user_id, full_name, specialization, department_id, contact, staff_type, image_path)
                             VALUES ($user_id, '$safe_name', '$safe_spec', $department_id, '$safe_contact', '$safe_type', '$safe_img')";
                } else {
                    $sql2 = "INSERT INTO staff (user_id, full_name, specialization, department_id, contact, staff_type)
                             VALUES ($user_id, '$safe_name', '$safe_spec', $department_id, '$safe_contact', '$safe_type')";
                }
                $result2 = mysqli_query($conn, $sql2);

                if (!$result2) {
                    mysqli_query($conn, "DELETE FROM users WHERE id=$user_id AND role='staff'");
                    $error = bestcare_db_error($conn, "Could not create staff profile. Please try again.");
                } else {
                    $success = "Staff account created ($staff_type). Username: $username";
                    $open_create = false;
                    header("Location: manage-staff.php?msg=created");
                    exit();
                }
            }
        }
        }
    }
}

// Edit staff
if (isset($_POST['edit_staff'])) {
    $staff_id = (int)$_POST['staff_id'];
    $staff_type = isset($_POST['staff_type']) ? $_POST['staff_type'] : '';
    $full_name = trim($_POST['full_name']);
    $specialization = trim($_POST['specialization']);
    $department_id = (int)$_POST['department_id'];
    $contact = trim($_POST['contact']);
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $open_edit_id = $staff_id;

    if (!adm_valid_staff_type($staff_type)) {
        $error = "Please choose a valid staff type.";
    } elseif (strlen($full_name) == 0 || strlen($specialization) == 0 || $department_id == 0 || strlen($contact) == 0) {
        $error = "Need to fill all required fields.";
    } else {
        $image_path = adm_save_staff_image($error);

        if ($image_path === false) {
            // $error already set
        } else {
        $safe_name = mysqli_real_escape_string($conn, $full_name);
        $safe_spec = mysqli_real_escape_string($conn, $specialization);
        $safe_contact = mysqli_real_escape_string($conn, $contact);
        $safe_type = mysqli_real_escape_string($conn, $staff_type);

        $upd = "UPDATE staff SET full_name='$safe_name', specialization='$safe_spec',
                department_id=$department_id, contact='$safe_contact', staff_type='$safe_type'";
        if ($image_path != "") {
            $safe_img = mysqli_real_escape_string($conn, $image_path);
            $upd .= ", image_path='$safe_img'";
        }
        $upd .= " WHERE id=$staff_id";
        $ok = mysqli_query($conn, $upd);

        if (!$ok) {
            $error = bestcare_db_error($conn, "Could not update staff. Please try again.");
        } elseif (strlen($new_password) > 0) {
            if (strlen($new_password) < 8) {
                $error = "New password must be at least 8 characters.";
            } else {
                $get = mysqli_query($conn, "SELECT user_id FROM staff WHERE id=$staff_id");
                if ($get && mysqli_num_rows($get) > 0) {
                    $srow = mysqli_fetch_array($get);
                    $uid = (int)$srow['user_id'];
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $safe_hash = mysqli_real_escape_string($conn, $hash);
                    mysqli_query($conn, "UPDATE users SET password_hash='$safe_hash' WHERE id=$uid AND role='staff'");
                }
                $success = "Staff details and password updated.";
                $open_edit_id = 0;
                header("Location: manage-staff.php?msg=updated");
                exit();
            }
        } else {
            $success = "Staff details updated.";
            $open_edit_id = 0;
            header("Location: manage-staff.php?msg=updated");
            exit();
        }
        }
    }
}

$list_sql = "SELECT st.id, st.user_id, st.full_name, st.specialization, st.department_id,
                    st.contact, st.staff_type, st.image_path, u.username, u.is_active, u.created_at,
                    d.name AS department_name
             FROM staff st, users u, departments d
             WHERE st.user_id = u.id
             AND st.department_id = d.id
             AND u.role = 'staff'
             ORDER BY st.full_name";
$list = mysqli_query($conn, $list_sql);

$staff_rows = array();
if ($list) {
    while ($row = mysqli_fetch_array($list)) {
        $staff_rows[] = $row;
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
    <title>Manage Staff - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=2">
</head>
<body class="adm-body<?php if ($open_create || $open_edit_id > 0) echo ' modal-open'; ?>">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Manage Staff</h1>
        <span class="role-tag">Administrator</span>
    </header>

    <main class="adm-content">
        <?php if ($error != "") { ?>
            <div class="adm-alert err"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="adm-alert ok"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

        <div class="adm-ms-head">
            <div>
                <h1>Staff Directory</h1>
                <p>View, create, and edit hospital staff accounts.</p>
            </div>
            <button type="button" class="adm-ms-create" id="btnOpenCreate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Create Staff
            </button>
        </div>

        <section class="adm-ms-panel">
            <div class="adm-ms-panel-top">
                <div class="adm-ms-tabs">
                    <button class="adm-ms-tab active" type="button" data-filter="all">All</button>
                    <button class="adm-ms-tab" type="button" data-filter="Doctor">Doctors</button>
                    <button class="adm-ms-tab" type="button" data-filter="Hospital Staff">Hospital Staff</button>
                    <button class="adm-ms-tab" type="button" data-filter="Other">Other</button>
                </div>
                <div class="adm-ms-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                    <input type="text" id="msSearch" placeholder="Search staff...">
                </div>
            </div>

            <?php if (count($staff_rows) > 0) { ?>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Staff</th>
                                <th>Username</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="msBody">
                            <?php for ($i = 0; $i < count($staff_rows); $i++) {
                                $row = $staff_rows[$i];
                                $stype = isset($row['staff_type']) && strlen($row['staff_type']) > 0 ? $row['staff_type'] : 'Doctor';
                                $type_class = 'doctor';
                                if ($stype == 'Hospital Staff') { $type_class = 'hospital'; }
                                if ($stype == 'Other') { $type_class = 'other'; }

                                $parts = explode(' ', $row['full_name']);
                                $ini = strtoupper(substr($parts[0], 0, 1));
                                if (isset($parts[1])) {
                                    $ini .= strtoupper(substr($parts[1], 0, 1));
                                }
                                if (isset($parts[2])) {
                                    $ini = strtoupper(substr($parts[1], 0, 1)) . strtoupper(substr($parts[2], 0, 1));
                                }
                            ?>
                                <tr class="ms-row" data-type="<?php echo htmlspecialchars($stype); ?>">
                                    <td>
                                        <?php
                                        $simg = isset($row['image_path']) ? $row['image_path'] : '';
                                        if ($simg != '') {
                                            echo '<img class="adm-svc-thumb" src="' . htmlspecialchars($simg) . '" alt="">';
                                        } else {
                                            echo '<div class="adm-svc-thumb-empty">No photo</div>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="adm-staff-cell">
                                            <div class="adm-staff-avatar"><?php echo htmlspecialchars($ini); ?></div>
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($row['full_name']); ?></p>
                                                <p class="spec"><?php echo htmlspecialchars($row['specialization']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><span class="adm-type <?php echo $type_class; ?>"><?php echo htmlspecialchars($stype); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                    <td>
                                        <?php if ($row['is_active'] == 1) { ?>
                                            <span class="adm-status active">Active</span>
                                        <?php } else { ?>
                                            <span class="adm-status inactive">Inactive</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="adm-ms-actions">
                                            <button type="button" class="adm-btn-outline btn-edit"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>"
                                                data-spec="<?php echo htmlspecialchars($row['specialization'], ENT_QUOTES); ?>"
                                                data-dept="<?php echo $row['department_id']; ?>"
                                                data-contact="<?php echo htmlspecialchars($row['contact'], ENT_QUOTES); ?>"
                                                data-type="<?php echo htmlspecialchars($stype, ENT_QUOTES); ?>"
                                                data-user="<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>"
                                                data-img="<?php echo htmlspecialchars($simg, ENT_QUOTES); ?>">
                                                Edit
                                            </button>
                                            <form method="post" action="">
                                                <input type="hidden" name="toggle_user_id" value="<?php echo $row['user_id']; ?>">
                                                <?php if ($row['is_active'] == 1) { ?>
                                                    <button type="submit" class="adm-btn-danger" onclick="return confirm('Deactivate this staff login? They will be hidden from booking until activated again.');">Deactivate</button>
                                                <?php } else { ?>
                                                    <button type="submit" class="adm-btn-outline">Activate</button>
                                                <?php } ?>
                                            </form>
                                            <form method="post" action="">
                                                <input type="hidden" name="delete_staff_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="adm-btn-delete"
                                                    onclick="return confirm('Permanently DELETE <?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>?\n\nThis cannot be undone. If they have appointments or medical history, delete will be blocked — use Deactivate instead.');">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-empty" id="msNoMatch" style="display:none;">No staff match your search.</div>
            <?php } else { ?>
                <div class="adm-empty">No staff yet. Click Create Staff to add the first account.</div>
            <?php } ?>
        </section>
    </main>
</div>

<!-- CREATE STAFF MODAL -->
<div class="adm-modal-overlay<?php if ($open_create) echo ' open'; ?>" id="createModal">
    <div class="adm-modal adm-modal-wide" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseCreate" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>

        <div class="adm-modal-step<?php if ($open_create) { echo ''; } else { echo ' active'; } ?>" id="createStep1" style="<?php if ($open_create) echo 'display:none;'; ?>">
            <h2>Create Staff</h2>
            <p class="modal-sub">Choose the type of staff account to create.</p>

            <div class="adm-type-cards">
                <button type="button" class="adm-type-card" data-pick="Doctor">
                    <div class="icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                    </div>
                    <strong>Doctor</strong>
                    <span>Clinical doctor with appointments</span>
                </button>
                <button type="button" class="adm-type-card" data-pick="Hospital Staff">
                    <div class="icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M5 21V7l7-4 7 4v14"></path><path d="M9 21v-6h6v6"></path></svg>
                    </div>
                    <strong>Hospital Staff</strong>
                    <span>Nurses, reception, support</span>
                </button>
                <button type="button" class="adm-type-card" data-pick="Other">
                    <div class="icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <strong>Other Staff</strong>
                    <span>Lab, admin assistants, etc.</span>
                </button>
            </div>

            <div class="adm-modal-actions">
                <button type="button" class="adm-btn-ghost" id="btnCancelCreate1">Cancel</button>
                <button type="button" class="adm-btn" id="btnNextCreate" disabled>Continue</button>
            </div>
        </div>

        <div class="adm-modal-step<?php if ($open_create) echo ' active'; ?>" id="createStep2">
            <h2>Staff Details</h2>
            <p class="modal-sub">Fill in login and profile information.</p>
            <div class="adm-chosen-type" id="chosenTypeLabel">Doctor</div>

            <form class="adm-form" method="post" action="" id="createForm" enctype="multipart/form-data">
                <input type="hidden" name="staff_type" id="createStaffType" value="<?php echo $open_create && isset($_POST['staff_type']) ? htmlspecialchars($_POST['staff_type']) : 'Doctor'; ?>">

                <div class="field">
                    <label for="full_name">Full Name *</label>
                    <input type="text" name="full_name" id="full_name" required placeholder="Full name" value="<?php echo $open_create && isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
                <div class="field">
                    <label for="username">Username *</label>
                    <input type="text" name="username" id="username" required placeholder="login username" value="<?php echo $open_create && isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="field">
                    <label for="password">Password *</label>
                    <input type="password" name="password" id="password" required placeholder="Min 8 characters">
                </div>
                <div class="field">
                    <label for="specialization">Role / Specialization *</label>
                    <input type="text" name="specialization" id="specialization" required placeholder="e.g. Cardiologist / Nurse / Reception" value="<?php echo $open_create && isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>">
                </div>
                <div class="field">
                    <label for="department_id">Department *</label>
                    <select name="department_id" id="department_id" required>
                        <option value="">Select department</option>
                        <?php for ($i = 0; $i < count($dept_list); $i++) {
                            $d = $dept_list[$i];
                            $sel = ($open_create && isset($_POST['department_id']) && (int)$_POST['department_id'] == (int)$d['id']) ? ' selected' : '';
                            echo '<option value="' . $d['id'] . '"' . $sel . '>' . htmlspecialchars($d['name']) . '</option>';
                        } ?>
                    </select>
                </div>
                <div class="field">
                    <label for="contact">Contact *</label>
                    <input type="text" name="contact" id="contact" required placeholder="07XXXXXXXX" value="<?php echo $open_create && isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>">
                </div>
                <div class="field">
                    <label for="image">Profile Photo (optional)</label>
                    <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp">
                    <span class="adm-file-hint">JPG, PNG or WEBP. Max 2MB. Shows on Doctors page and booking form.</span>
                </div>

                <div class="adm-modal-actions">
                    <button type="button" class="adm-btn-ghost" id="btnBackCreate">Back</button>
                    <button type="submit" name="add_staff" value="1" class="adm-btn">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT STAFF MODAL -->
<div class="adm-modal-overlay<?php if ($open_edit_id > 0) echo ' open'; ?>" id="editModal">
    <div class="adm-modal" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseEdit" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>
        <h2>Edit Staff</h2>
        <p class="modal-sub">Update profile details for <strong id="editUsernameLabel"></strong>.</p>

        <form class="adm-form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="staff_id" id="editStaffId" value="<?php echo $open_edit_id; ?>">

            <div class="field">
                <label for="edit_staff_type">Staff Type *</label>
                <select name="staff_type" id="edit_staff_type" required>
                    <option value="Doctor">Doctor</option>
                    <option value="Hospital Staff">Hospital Staff</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="field">
                <label for="edit_full_name">Full Name *</label>
                <input type="text" name="full_name" id="edit_full_name" required>
            </div>
            <div class="field">
                <label for="edit_specialization">Role / Specialization *</label>
                <input type="text" name="specialization" id="edit_specialization" required>
            </div>
            <div class="field">
                <label for="edit_department_id">Department *</label>
                <select name="department_id" id="edit_department_id" required>
                    <?php for ($i = 0; $i < count($dept_list); $i++) {
                        $d = $dept_list[$i];
                        echo '<option value="' . $d['id'] . '">' . htmlspecialchars($d['name']) . '</option>';
                    } ?>
                </select>
            </div>
            <div class="field">
                <label for="edit_contact">Contact *</label>
                <input type="text" name="contact" id="edit_contact" required>
            </div>
            <div class="field">
                <label for="edit_image">Replace Photo (optional)</label>
                <div class="adm-current-img" id="editCurrentImg" style="display:none;">
                    <img class="adm-svc-thumb" id="editCurrentThumb" src="" alt="">
                    <span class="adm-file-hint">Current photo</span>
                </div>
                <input type="file" name="image" id="edit_image" accept=".jpg,.jpeg,.png,.webp">
                <span class="adm-file-hint">Leave empty to keep the current photo. JPG, PNG or WEBP, max 2MB.</span>
            </div>
            <div class="field">
                <label for="new_password">New Password (optional)</label>
                <input type="password" name="new_password" id="new_password" placeholder="Leave blank to keep current">
            </div>

            <div class="adm-modal-actions">
                <button type="button" class="adm-btn-ghost" id="btnCancelEdit">Cancel</button>
                <button type="submit" name="edit_staff" value="1" class="adm-btn">Save Changes</button>
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
var createModal = document.getElementById('createModal');
var editModal = document.getElementById('editModal');
var createStep1 = document.getElementById('createStep1');
var createStep2 = document.getElementById('createStep2');
var pickedType = '';
var btnNext = document.getElementById('btnNextCreate');

function openCreate() {
    body.classList.add('modal-open');
    createModal.classList.add('open');
    createStep1.style.display = 'block';
    createStep1.classList.add('active');
    createStep2.classList.remove('active');
    pickedType = '';
    btnNext.disabled = true;
    var cards = document.querySelectorAll('.adm-type-card');
    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove('selected');
    }
}

function closeCreate() {
    createModal.classList.remove('open');
    if (!editModal.classList.contains('open')) {
        body.classList.remove('modal-open');
    }
}

function openEdit() {
    body.classList.add('modal-open');
    editModal.classList.add('open');
}

function closeEdit() {
    editModal.classList.remove('open');
    if (!createModal.classList.contains('open')) {
        body.classList.remove('modal-open');
    }
}

document.getElementById('btnOpenCreate').onclick = openCreate;
document.getElementById('btnCloseCreate').onclick = closeCreate;
document.getElementById('btnCancelCreate1').onclick = closeCreate;
document.getElementById('btnCloseEdit').onclick = closeEdit;
document.getElementById('btnCancelEdit').onclick = closeEdit;

createModal.addEventListener('click', function (e) {
    if (e.target === createModal) closeCreate();
});
editModal.addEventListener('click', function (e) {
    if (e.target === editModal) closeEdit();
});

var typeCards = document.querySelectorAll('.adm-type-card');
for (var t = 0; t < typeCards.length; t++) {
    typeCards[t].onclick = function () {
        for (var k = 0; k < typeCards.length; k++) {
            typeCards[k].classList.remove('selected');
        }
        this.classList.add('selected');
        pickedType = this.getAttribute('data-pick');
        btnNext.disabled = false;
    };
}

btnNext.onclick = function () {
    if (!pickedType) return;
    document.getElementById('createStaffType').value = pickedType;
    document.getElementById('chosenTypeLabel').textContent = pickedType;
    createStep1.style.display = 'none';
    createStep1.classList.remove('active');
    createStep2.classList.add('active');
};

document.getElementById('btnBackCreate').onclick = function () {
    createStep2.classList.remove('active');
    createStep1.style.display = 'block';
    createStep1.classList.add('active');
};

var editBtns = document.querySelectorAll('.btn-edit');
for (var e = 0; e < editBtns.length; e++) {
    editBtns[e].onclick = function () {
        document.getElementById('editStaffId').value = this.getAttribute('data-id');
        document.getElementById('editUsernameLabel').textContent = this.getAttribute('data-user');
        document.getElementById('edit_full_name').value = this.getAttribute('data-name');
        document.getElementById('edit_specialization').value = this.getAttribute('data-spec');
        document.getElementById('edit_department_id').value = this.getAttribute('data-dept');
        document.getElementById('edit_contact').value = this.getAttribute('data-contact');
        document.getElementById('edit_staff_type').value = this.getAttribute('data-type');
        document.getElementById('new_password').value = '';
        document.getElementById('edit_image').value = '';

        var img = this.getAttribute('data-img');
        var wrap = document.getElementById('editCurrentImg');
        if (img) {
            document.getElementById('editCurrentThumb').src = img;
            wrap.style.display = 'flex';
        } else {
            wrap.style.display = 'none';
        }
        openEdit();
    };
}

// Filters + search
var msTabs = document.querySelectorAll('.adm-ms-tab');
var msRows = document.querySelectorAll('.ms-row');
var msSearch = document.getElementById('msSearch');
var msNoMatch = document.getElementById('msNoMatch');
var msFilter = 'all';

function msApply() {
    var term = msSearch ? msSearch.value.toLowerCase() : '';
    var shown = 0;
    for (var i = 0; i < msRows.length; i++) {
        var row = msRows[i];
        var type = row.getAttribute('data-type');
        var okType = (msFilter === 'all' || type === msFilter);
        var okTerm = (term === '' || row.textContent.toLowerCase().indexOf(term) !== -1);
        if (okType && okTerm) {
            row.style.display = '';
            shown++;
        } else {
            row.style.display = 'none';
        }
    }
    if (msNoMatch) {
        msNoMatch.style.display = (shown === 0) ? 'block' : 'none';
    }
}

for (var m = 0; m < msTabs.length; m++) {
    msTabs[m].onclick = function () {
        for (var x = 0; x < msTabs.length; x++) {
            msTabs[x].className = 'adm-ms-tab';
        }
        this.className = 'adm-ms-tab active';
        msFilter = this.getAttribute('data-filter');
        msApply();
    };
}
if (msSearch) {
    msSearch.onkeyup = msApply;
}

// If PHP reopened create modal after validation error, jump to step 2
<?php if ($open_create) { ?>
createStep1.style.display = 'none';
createStep2.classList.add('active');
document.getElementById('chosenTypeLabel').textContent = document.getElementById('createStaffType').value;
<?php } ?>

<?php if ($open_edit_id > 0 && isset($_POST['edit_staff'])) { ?>
document.getElementById('edit_full_name').value = <?php echo json_encode(isset($_POST['full_name']) ? $_POST['full_name'] : ''); ?>;
document.getElementById('edit_specialization').value = <?php echo json_encode(isset($_POST['specialization']) ? $_POST['specialization'] : ''); ?>;
document.getElementById('edit_department_id').value = <?php echo json_encode(isset($_POST['department_id']) ? $_POST['department_id'] : ''); ?>;
document.getElementById('edit_contact').value = <?php echo json_encode(isset($_POST['contact']) ? $_POST['contact'] : ''); ?>;
document.getElementById('edit_staff_type').value = <?php echo json_encode(isset($_POST['staff_type']) ? $_POST['staff_type'] : 'Doctor'); ?>;
<?php } ?>
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
