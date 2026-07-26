<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'services';
$admin_name = $_SESSION['username'];
$error = "";
$success = "";
$open_create = false;
$open_edit_id = 0;

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'created') {
        $success = "Service created successfully.";
    } elseif ($_GET['msg'] == 'updated') {
        $success = "Service details updated.";
    }
}

// Ensure image_path column exists (for databases created before this update)
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM services LIKE 'image_path'");
if (!$col_check || mysqli_num_rows($col_check) == 0) {
    mysqli_query($conn, "ALTER TABLE services ADD COLUMN image_path VARCHAR(255) NULL");
}

$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY name");
$dept_list = array();
if ($departments) {
    while ($d = mysqli_fetch_array($departments)) {
        $dept_list[] = $d;
    }
}

// Handle image upload; returns saved web path, "" if no file chosen, or false on error
function adm_save_service_image(&$error) {
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

    $upload_dir = __DIR__ . "/../assets/uploads/services";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_name = "service_" . time() . "_" . rand(1000, 9999) . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . "/" . $new_name)) {
        $error = "Could not save the uploaded image.";
        return false;
    }

    return "/bestcare-hospital/assets/uploads/services/" . $new_name;
}

// Add service
if (isset($_POST['add_service'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $department_id = (int)$_POST['department_id'];
    $fee = trim($_POST['fee']);
    $open_create = true;

    if (strlen($name) == 0 || strlen($description) == 0 || $department_id == 0 || strlen($fee) == 0) {
        $error = "Need to fill all the fields.";
    } elseif (!is_numeric($fee) || (float)$fee < 0) {
        $error = "Fee must be a valid number.";
    } else {
        $image_path = adm_save_service_image($error);

        if ($image_path !== false) {
            $safe_name = mysqli_real_escape_string($conn, $name);
            $safe_desc = mysqli_real_escape_string($conn, $description);
            $safe_img = mysqli_real_escape_string($conn, $image_path);
            $fee_val = (float)$fee;

            if ($image_path != "") {
                $sql = "INSERT INTO services (name, description, department_id, fee, image_path)
                        VALUES ('$safe_name', '$safe_desc', $department_id, $fee_val, '$safe_img')";
            } else {
                $sql = "INSERT INTO services (name, description, department_id, fee)
                        VALUES ('$safe_name', '$safe_desc', $department_id, $fee_val)";
            }

            if (!mysqli_query($conn, $sql)) {
                $error = bestcare_db_error($conn, "Could not create service. Please try again.");
            } else {
                header("Location: manage-services.php?msg=created");
                exit();
            }
        }
    }
}

// Edit service
if (isset($_POST['edit_service'])) {
    $service_id = (int)$_POST['service_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $department_id = (int)$_POST['department_id'];
    $fee = trim($_POST['fee']);
    $open_edit_id = $service_id;

    if (strlen($name) == 0 || strlen($description) == 0 || $department_id == 0 || strlen($fee) == 0) {
        $error = "Need to fill all required fields.";
    } elseif (!is_numeric($fee) || (float)$fee < 0) {
        $error = "Fee must be a valid number.";
    } else {
        $image_path = adm_save_service_image($error);

        if ($image_path !== false) {
            $safe_name = mysqli_real_escape_string($conn, $name);
            $safe_desc = mysqli_real_escape_string($conn, $description);
            $fee_val = (float)$fee;

            $sql = "UPDATE services SET name='$safe_name', description='$safe_desc',
                    department_id=$department_id, fee=$fee_val";

            if ($image_path != "") {
                $safe_img = mysqli_real_escape_string($conn, $image_path);
                $sql .= ", image_path='$safe_img'";
            }

            $sql .= " WHERE id=$service_id";

            if (!mysqli_query($conn, $sql)) {
                $error = bestcare_db_error($conn, "Could not update service. Please try again.");
            } else {
                header("Location: manage-services.php?msg=updated");
                exit();
            }
        }
    }
}

$list_sql = "SELECT s.*, d.name AS department_name
             FROM services s, departments d
             WHERE s.department_id = d.id
             ORDER BY s.name";
$list = mysqli_query($conn, $list_sql);

$service_rows = array();
if ($list) {
    while ($row = mysqli_fetch_array($list)) {
        $service_rows[] = $row;
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
    <title>Manage Services - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=3">
</head>
<body class="adm-body<?php if ($open_create || $open_edit_id > 0) echo ' modal-open'; ?>">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Manage Services</h1>
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
                <h1>Hospital Services</h1>
                <p>Create and edit the medical services shown on the public website.</p>
            </div>
            <button type="button" class="adm-ms-create" id="btnOpenCreate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Create Service
            </button>
        </div>

        <section class="adm-ms-panel">
            <div class="adm-ms-panel-top">
                <div class="adm-ms-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                    <input type="text" id="svcSearch" placeholder="Search services...">
                </div>
            </div>

            <?php if (count($service_rows) > 0) { ?>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Service</th>
                                <th>Department</th>
                                <th>Fee</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="svcBody">
                            <?php for ($i = 0; $i < count($service_rows); $i++) {
                                $row = $service_rows[$i];
                                $img = isset($row['image_path']) ? $row['image_path'] : '';
                            ?>
                                <tr class="svc-row">
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
                                    <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                                    <td><span class="adm-svc-fee">Rs. <?php echo number_format($row['fee'], 2); ?></span></td>
                                    <td>
                                        <button type="button" class="adm-btn-outline btn-edit"
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                                            data-desc="<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>"
                                            data-dept="<?php echo $row['department_id']; ?>"
                                            data-fee="<?php echo $row['fee']; ?>"
                                            data-img="<?php echo htmlspecialchars($img, ENT_QUOTES); ?>">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-empty" id="svcNoMatch" style="display:none;">No services match your search.</div>
            <?php } else { ?>
                <div class="adm-empty">No services yet. Click Create Service to add the first one.</div>
            <?php } ?>
        </section>
    </main>
</div>

<!-- CREATE SERVICE MODAL -->
<div class="adm-modal-overlay<?php if ($open_create) echo ' open'; ?>" id="createModal">
    <div class="adm-modal" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseCreate" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>
        <h2>Create Service</h2>
        <p class="modal-sub">This service will appear on the public website and in booking.</p>

        <form class="adm-form" method="post" action="" enctype="multipart/form-data">
            <div class="field">
                <label for="name">Service Name *</label>
                <input type="text" name="name" id="name" required placeholder="e.g. X-Ray Scan" value="<?php echo $open_create && isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            <div class="field">
                <label for="description">Description *</label>
                <textarea name="description" id="description" required placeholder="Short description shown on the service card"><?php echo $open_create && isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
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
                <label for="fee">Fee (Rs.) *</label>
                <input type="number" name="fee" id="fee" required min="0" step="0.01" placeholder="e.g. 2500.00" value="<?php echo $open_create && isset($_POST['fee']) ? htmlspecialchars($_POST['fee']) : ''; ?>">
            </div>
            <div class="field">
                <label for="image">Service Image (optional)</label>
                <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp">
                <span class="adm-file-hint">JPG, PNG or WEBP. Max 2MB. A default image is used if empty.</span>
            </div>

            <div class="adm-modal-actions">
                <button type="button" class="adm-btn-ghost" id="btnCancelCreate">Cancel</button>
                <button type="submit" name="add_service" value="1" class="adm-btn">Create Service</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT SERVICE MODAL -->
<div class="adm-modal-overlay<?php if ($open_edit_id > 0) echo ' open'; ?>" id="editModal">
    <div class="adm-modal" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseEdit" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>
        <h2>Edit Service</h2>
        <p class="modal-sub">Update details for <strong id="editNameLabel"></strong>.</p>

        <form class="adm-form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="service_id" id="editServiceId" value="<?php echo $open_edit_id; ?>">

            <div class="field">
                <label for="edit_name">Service Name *</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="field">
                <label for="edit_description">Description *</label>
                <textarea name="description" id="edit_description" required></textarea>
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
                <label for="edit_fee">Fee (Rs.) *</label>
                <input type="number" name="fee" id="edit_fee" required min="0" step="0.01">
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
                <button type="submit" name="edit_service" value="1" class="adm-btn">Save Changes</button>
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
document.getElementById('btnCancelCreate').onclick = closeCreate;
document.getElementById('btnCloseEdit').onclick = closeEdit;
document.getElementById('btnCancelEdit').onclick = closeEdit;

createModal.addEventListener('click', function (e) {
    if (e.target === createModal) closeCreate();
});
editModal.addEventListener('click', function (e) {
    if (e.target === editModal) closeEdit();
});

var editBtns = document.querySelectorAll('.btn-edit');
for (var e = 0; e < editBtns.length; e++) {
    editBtns[e].onclick = function () {
        document.getElementById('editServiceId').value = this.getAttribute('data-id');
        document.getElementById('editNameLabel').textContent = this.getAttribute('data-name');
        document.getElementById('edit_name').value = this.getAttribute('data-name');
        document.getElementById('edit_description').value = this.getAttribute('data-desc');
        document.getElementById('edit_department_id').value = this.getAttribute('data-dept');
        document.getElementById('edit_fee').value = this.getAttribute('data-fee');
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

// Search
var svcSearch = document.getElementById('svcSearch');
var svcRows = document.querySelectorAll('.svc-row');
var svcNoMatch = document.getElementById('svcNoMatch');

if (svcSearch) {
    svcSearch.onkeyup = function () {
        var term = svcSearch.value.toLowerCase();
        var shown = 0;
        for (var i = 0; i < svcRows.length; i++) {
            var row = svcRows[i];
            if (term === '' || row.textContent.toLowerCase().indexOf(term) !== -1) {
                row.style.display = '';
                shown++;
            } else {
                row.style.display = 'none';
            }
        }
        if (svcNoMatch) {
            svcNoMatch.style.display = (shown === 0) ? 'block' : 'none';
        }
    };
}

// If PHP reopened the edit modal after a validation error, refill values
<?php if ($open_edit_id > 0 && isset($_POST['edit_service'])) { ?>
document.getElementById('edit_name').value = <?php echo json_encode(isset($_POST['name']) ? $_POST['name'] : ''); ?>;
document.getElementById('edit_description').value = <?php echo json_encode(isset($_POST['description']) ? $_POST['description'] : ''); ?>;
document.getElementById('edit_department_id').value = <?php echo json_encode(isset($_POST['department_id']) ? $_POST['department_id'] : ''); ?>;
document.getElementById('edit_fee').value = <?php echo json_encode(isset($_POST['fee']) ? $_POST['fee'] : ''); ?>;
<?php } ?>
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
