<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'departments';
$admin_name = $_SESSION['username'];
$error = "";
$success = "";
$open_create = false;
$open_edit_id = 0;

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'created') {
        $success = "Department created successfully.";
    } elseif ($_GET['msg'] == 'updated') {
        $success = "Department details updated.";
    } elseif ($_GET['msg'] == 'deleted') {
        $success = "Department permanently deleted.";
    }
}

// Ensure image_path column exists
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM departments LIKE 'image_path'");
if (!$col_check || mysqli_num_rows($col_check) == 0) {
    mysqli_query($conn, "ALTER TABLE departments ADD COLUMN image_path VARCHAR(255) NULL");
}

function adm_save_dept_image(&$error) {
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

    $upload_dir = __DIR__ . "/../assets/uploads/departments";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_name = "dept_" . time() . "_" . rand(1000, 9999) . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . "/" . $new_name)) {
        $error = "Could not save the uploaded image.";
        return false;
    }

    return "/bestcare-hospital/assets/uploads/departments/" . $new_name;
}

// Add department
if (isset($_POST['add_department'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $open_create = true;

    if (strlen($name) == 0 || strlen($description) == 0) {
        $error = "Need to fill all the fields.";
    } else {
        $image_path = adm_save_dept_image($error);

        if ($image_path !== false) {
            $safe_name = mysqli_real_escape_string($conn, $name);
            $safe_desc = mysqli_real_escape_string($conn, $description);

            $exists = mysqli_query($conn, "SELECT id FROM departments WHERE name='$safe_name'");
            if ($exists && mysqli_num_rows($exists) > 0) {
                $error = "A department with that name already exists.";
            } else {
                if ($image_path != "") {
                    $safe_img = mysqli_real_escape_string($conn, $image_path);
                    $sql = "INSERT INTO departments (name, description, image_path)
                            VALUES ('$safe_name', '$safe_desc', '$safe_img')";
                } else {
                    $sql = "INSERT INTO departments (name, description)
                            VALUES ('$safe_name', '$safe_desc')";
                }

                if (!mysqli_query($conn, $sql)) {
                    $error = bestcare_db_error($conn, "Could not create department. Please try again.");
                } else {
                    header("Location: manage-departments.php?msg=created");
                    exit();
                }
            }
        }
    }
}

// Edit department
if (isset($_POST['edit_department'])) {
    $department_id = (int)$_POST['department_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $open_edit_id = $department_id;

    if (strlen($name) == 0 || strlen($description) == 0) {
        $error = "Need to fill all required fields.";
    } else {
        $image_path = adm_save_dept_image($error);

        if ($image_path !== false) {
            $safe_name = mysqli_real_escape_string($conn, $name);
            $safe_desc = mysqli_real_escape_string($conn, $description);

            $exists = mysqli_query($conn, "SELECT id FROM departments WHERE name='$safe_name' AND id<>$department_id");
            if ($exists && mysqli_num_rows($exists) > 0) {
                $error = "A department with that name already exists.";
            } else {
                $sql = "UPDATE departments SET name='$safe_name', description='$safe_desc'";
                if ($image_path != "") {
                    $safe_img = mysqli_real_escape_string($conn, $image_path);
                    $sql .= ", image_path='$safe_img'";
                }
                $sql .= " WHERE id=$department_id";

                if (!mysqli_query($conn, $sql)) {
                    $error = bestcare_db_error($conn, "Could not update department. Please try again.");
                } else {
                    header("Location: manage-departments.php?msg=updated");
                    exit();
                }
            }
        }
    }
}

// Delete department — blocked if staff or services still use it
if (isset($_POST['delete_department_id'])) {
    $del_id = (int)$_POST['delete_department_id'];

    $get = mysqli_query($conn, "SELECT id, name, image_path FROM departments WHERE id=$del_id");
    if (!$get || mysqli_num_rows($get) == 0) {
        $error = "Department not found.";
    } else {
        $drow = mysqli_fetch_array($get);
        $del_img = isset($drow['image_path']) ? $drow['image_path'] : '';

        $c1 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM staff WHERE department_id=$del_id");
        $c2 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM services WHERE department_id=$del_id");
        $staff_n = ($c1 && ($r = mysqli_fetch_array($c1))) ? (int)$r['c'] : 0;
        $svc_n = ($c2 && ($r = mysqli_fetch_array($c2))) ? (int)$r['c'] : 0;

        if ($staff_n > 0 || $svc_n > 0) {
            $error = "Cannot delete this department because it is still used by "
                   . "$staff_n staff member(s) and $svc_n service(s). "
                   . "Move or remove those first.";
        } else {
            $ok = mysqli_query($conn, "DELETE FROM departments WHERE id=$del_id");
            if (!$ok) {
                $error = bestcare_db_error($conn, "Could not delete department. Please try again.");
            } else {
                if ($del_img != "" && strpos($del_img, "/assets/uploads/departments/") !== false) {
                    $file_path = __DIR__ . "/../assets/uploads/departments/" . basename($del_img);
                    if (is_file($file_path)) {
                        @unlink($file_path);
                    }
                }
                header("Location: manage-departments.php?msg=deleted");
                exit();
            }
        }
    }
}

$list = mysqli_query($conn, "SELECT d.*,
        (SELECT COUNT(*) FROM staff st WHERE st.department_id = d.id) AS staff_count,
        (SELECT COUNT(*) FROM services s WHERE s.department_id = d.id) AS service_count
        FROM departments d
        ORDER BY d.name");

$dept_rows = array();
if ($list) {
    while ($row = mysqli_fetch_array($list)) {
        $dept_rows[] = $row;
    }
}

if ($error != "") {
    $success = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=4">
</head>
<body class="adm-body<?php if ($open_create || $open_edit_id > 0) echo ' modal-open'; ?>">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Manage Departments</h1>
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
                <h1>Hospital Departments</h1>
                <p>Create, edit, and delete departments shown on the public website and in staff/service forms.</p>
            </div>
            <button type="button" class="adm-ms-create" id="btnOpenCreate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Create Department
            </button>
        </div>

        <section class="adm-ms-panel">
            <div class="adm-ms-panel-top">
                <div class="adm-ms-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                    <input type="text" id="deptSearch" placeholder="Search departments...">
                </div>
            </div>

            <?php if (count($dept_rows) > 0) { ?>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Department</th>
                                <th>Staff</th>
                                <th>Services</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="deptBody">
                            <?php for ($i = 0; $i < count($dept_rows); $i++) {
                                $row = $dept_rows[$i];
                                $img = isset($row['image_path']) ? $row['image_path'] : '';
                            ?>
                                <tr class="dept-row">
                                    <td>
                                        <?php if ($img != '') { ?>
                                            <img class="adm-svc-thumb" src="<?php echo htmlspecialchars($img); ?>" alt="">
                                        <?php } else { ?>
                                            <div class="adm-svc-thumb-empty">Default</div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="adm-svc-cell">
                                            <p class="name"><?php echo htmlspecialchars($row['name']); ?></p>
                                            <p class="desc"><?php echo htmlspecialchars($row['description']); ?></p>
                                        </div>
                                    </td>
                                    <td><?php echo (int)$row['staff_count']; ?></td>
                                    <td><?php echo (int)$row['service_count']; ?></td>
                                    <td>
                                        <div class="adm-ms-actions">
                                            <button type="button" class="adm-btn-outline btn-edit"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                                                data-desc="<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>"
                                                data-img="<?php echo htmlspecialchars($img, ENT_QUOTES); ?>">
                                                Edit
                                            </button>
                                            <form method="post" action="">
                                                <input type="hidden" name="delete_department_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="adm-btn-delete"
                                                    onclick="return confirm('Permanently DELETE <?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>?\n\nThis is blocked if staff or services still use it.');">
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
                <div class="adm-empty" id="deptNoMatch" style="display:none;">No departments match your search.</div>
            <?php } else { ?>
                <div class="adm-empty">No departments yet. Click Create Department to add the first one.</div>
            <?php } ?>
        </section>
    </main>
</div>

<!-- CREATE -->
<div class="adm-modal-overlay<?php if ($open_create) echo ' open'; ?>" id="createModal">
    <div class="adm-modal" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseCreate" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>
        <h2>Create Department</h2>
        <p class="modal-sub">This department will appear on the public website and in doctor/service forms.</p>

        <form class="adm-form" method="post" action="" enctype="multipart/form-data">
            <div class="field">
                <label for="name">Department Name *</label>
                <input type="text" name="name" id="name" required placeholder="e.g. Pediatrics"
                       value="<?php echo $open_create && isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            <div class="field">
                <label for="description">Description *</label>
                <textarea name="description" id="description" required placeholder="Short description shown on the department card"><?php echo $open_create && isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>
            <div class="field">
                <label for="image">Department Image (optional)</label>
                <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp">
                <span class="adm-file-hint">JPG, PNG or WEBP. Max 2MB. A default image is used if empty.</span>
            </div>
            <div class="adm-modal-actions">
                <button type="button" class="adm-btn-ghost" id="btnCancelCreate">Cancel</button>
                <button type="submit" name="add_department" value="1" class="adm-btn">Create Department</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT -->
<div class="adm-modal-overlay<?php if ($open_edit_id > 0) echo ' open'; ?>" id="editModal">
    <div class="adm-modal" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseEdit" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>
        <h2>Edit Department</h2>
        <p class="modal-sub">Update details for <strong id="editNameLabel"></strong>.</p>

        <form class="adm-form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="department_id" id="editDepartmentId" value="<?php echo $open_edit_id; ?>">
            <div class="field">
                <label for="edit_name">Department Name *</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="field">
                <label for="edit_description">Description *</label>
                <textarea name="description" id="edit_description" required></textarea>
            </div>
            <div class="field">
                <label for="edit_image">Replace Image (optional)</label>
                <div class="adm-current-img" id="editCurrentImg" style="display:none;">
                    <img class="adm-svc-thumb" id="editCurrentThumb" src="" alt="">
                    <span class="adm-file-hint">Current image</span>
                </div>
                <input type="file" name="image" id="edit_image" accept=".jpg,.jpeg,.png,.webp">
                <span class="adm-file-hint">Leave empty to keep the current image.</span>
            </div>
            <div class="adm-modal-actions">
                <button type="button" class="adm-btn-ghost" id="btnCancelEdit">Cancel</button>
                <button type="submit" name="edit_department" value="1" class="adm-btn">Save Changes</button>
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

function openCreate() {
    body.classList.add('modal-open');
    createModal.classList.add('open');
}
function closeCreate() {
    createModal.classList.remove('open');
    if (!editModal.classList.contains('open')) body.classList.remove('modal-open');
}
function openEdit() {
    body.classList.add('modal-open');
    editModal.classList.add('open');
}
function closeEdit() {
    editModal.classList.remove('open');
    if (!createModal.classList.contains('open')) body.classList.remove('modal-open');
}

document.getElementById('btnOpenCreate').onclick = openCreate;
document.getElementById('btnCloseCreate').onclick = closeCreate;
document.getElementById('btnCancelCreate').onclick = closeCreate;
document.getElementById('btnCloseEdit').onclick = closeEdit;
document.getElementById('btnCancelEdit').onclick = closeEdit;
createModal.addEventListener('click', function (e) { if (e.target === createModal) closeCreate(); });
editModal.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });

var editBtns = document.querySelectorAll('.btn-edit');
for (var e = 0; e < editBtns.length; e++) {
    editBtns[e].onclick = function () {
        document.getElementById('editDepartmentId').value = this.getAttribute('data-id');
        document.getElementById('editNameLabel').textContent = this.getAttribute('data-name');
        document.getElementById('edit_name').value = this.getAttribute('data-name');
        document.getElementById('edit_description').value = this.getAttribute('data-desc');
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

var deptSearch = document.getElementById('deptSearch');
var deptRows = document.querySelectorAll('.dept-row');
var deptNoMatch = document.getElementById('deptNoMatch');
if (deptSearch) {
    deptSearch.onkeyup = function () {
        var term = deptSearch.value.toLowerCase();
        var shown = 0;
        for (var i = 0; i < deptRows.length; i++) {
            var row = deptRows[i];
            if (term === '' || row.textContent.toLowerCase().indexOf(term) !== -1) {
                row.style.display = '';
                shown++;
            } else {
                row.style.display = 'none';
            }
        }
        if (deptNoMatch) deptNoMatch.style.display = (shown === 0) ? 'block' : 'none';
    };
}

<?php if ($open_edit_id > 0 && isset($_POST['edit_department'])) { ?>
document.getElementById('edit_name').value = <?php echo json_encode(isset($_POST['name']) ? $_POST['name'] : ''); ?>;
document.getElementById('edit_description').value = <?php echo json_encode(isset($_POST['description']) ? $_POST['description'] : ''); ?>;
<?php } ?>
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
