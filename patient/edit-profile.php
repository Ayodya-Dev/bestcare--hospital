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

$user_id = (int)$_SESSION['user_id'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

$p_sql = "SELECT * FROM patients WHERE user_id=$user_id";
$p_result = mysqli_query($conn, $p_sql);
if (!$p_result || mysqli_num_rows($p_result) == 0) {
    bestcare_fail_page("Patient profile not found. Please contact the hospital.");
}
$patient = mysqli_fetch_array($p_result);
$patient_id = (int)$patient['id'];

if (isset($_GET['msg']) && $_GET['msg'] == 'updated') {
    $success = "Your profile was updated successfully.";
}

if (isset($_POST['save_profile'])) {
    $full_name = trim($_POST['full_name']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if (strlen($full_name) == 0 || strlen($dob) == 0 || strlen($gender) == 0 ||
        strlen($contact) == 0 || strlen($address) == 0) {
        $error = "Need to fill all the fields";
    } elseif ($gender != 'Male' && $gender != 'Female' && $gender != 'Other') {
        $error = "Please select a valid gender.";
    } else {
        $safe_name = mysqli_real_escape_string($conn, $full_name);
        $safe_dob = mysqli_real_escape_string($conn, $dob);
        $safe_gender = mysqli_real_escape_string($conn, $gender);
        $safe_contact = mysqli_real_escape_string($conn, $contact);
        $safe_address = mysqli_real_escape_string($conn, $address);

        $sql = "UPDATE patients SET full_name='$safe_name', dob='$safe_dob', gender='$safe_gender',
                contact='$safe_contact', address='$safe_address'
                WHERE id=$patient_id AND user_id=$user_id";
        $ok = mysqli_query($conn, $sql);

        if (!$ok) {
            $error = bestcare_db_error($conn, "Could not update your profile. Please try again.");
        } else {
            header("Location: edit-profile.php?msg=updated");
            exit();
        }
    }

    // Keep typed values on validation error
    $patient['full_name'] = $full_name;
    $patient['dob'] = $dob;
    $patient['gender'] = $gender;
    $patient['contact'] = $contact;
    $patient['address'] = $address;
}

$full_name = $patient['full_name'];
$parts = explode(' ', $full_name);
$initials = strtoupper(substr($parts[0], 0, 1));
if (isset($parts[1])) {
    $initials .= strtoupper(substr($parts[1], 0, 1));
}
$first_name = $parts[0];

if ($error != "") {
    $success = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-records.css?v=2">
    <style>
        .cp-page { width: 100%; max-width: 560px; }
        .cp-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        }
        .cp-card h2 {
            margin: 0 0 6px;
            font-family: "Inter", sans-serif;
            font-size: 1.15rem;
            color: #1D1F23;
        }
        .cp-card .sub {
            margin: 0 0 20px;
            font-family: "Inter", sans-serif;
            font-size: 13px;
            color: #6B7280;
            line-height: 1.45;
        }
        .cp-form .field { margin-bottom: 16px; }
        .cp-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        @media (max-width: 560px) {
            .cp-form .form-row { grid-template-columns: 1fr; }
        }
        .cp-form label {
            display: block;
            font-family: "Inter", sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .cp-form input,
        .cp-form select,
        .cp-form textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 11px 12px;
            font-family: "Inter", sans-serif;
            font-size: 14px;
            outline: none;
            background: #fff;
            color: #1D1F23;
        }
        .cp-form input:focus,
        .cp-form select:focus,
        .cp-form textarea:focus { border-color: #016450; }
        .cp-form input[readonly] {
            background: #F9FAFB;
            color: #6B7280;
        }
        .cp-form textarea { min-height: 90px; resize: vertical; }
        .cp-hint {
            margin: -6px 0 16px;
            font-family: "Inter", sans-serif;
            font-size: 12px;
            color: #9CA3AF;
        }
        .cp-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
            justify-content: center;
        }
        .cp-submit {
            border: none;
            background: #016450;
            color: #fff;
            border-radius: 10px;
            padding: 12px 28px;
            font-family: "Inter", sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            min-width: 160px;
        }
        .cp-submit:hover { background: #014d3d; }
        .cp-alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-family: "Inter", sans-serif;
            font-size: 13px;
        }
        .cp-alert.err { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
        .cp-alert.ok { background: #E9FCF5; color: #016450; border: 1px solid #B0D6C9; }
    </style>
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
        <a href="my-queries.php">
            <img src="<?php echo $dash; ?>/IMG_6.svg" alt=""> Queries
        </a>
        <a class="active" href="edit-profile.php">
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
        <form class="pd-search" method="get" action="">
            <img src="<?php echo $dash; ?>/IMG_11.svg" alt="">
            <input type="text" placeholder="Search..." disabled>
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

    <main class="pd-content">
        <div class="cp-page">
            <div class="pr-crumbs" style="margin-bottom:16px;">
                <a href="dashboard.php">Dashboard</a>
                <span>/</span>
                <strong>Edit Profile</strong>
            </div>

            <div class="cp-card">
                <h2>Edit Profile</h2>
                <p class="sub">Update your personal details. Your username stays the same.</p>

                <?php if ($error != "") { ?>
                    <div class="cp-alert err"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>
                <?php if ($success != "") { ?>
                    <div class="cp-alert ok"><?php echo htmlspecialchars($success); ?></div>
                <?php } ?>

                <form class="cp-form" method="post" action="">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($username); ?>" readonly>
                    </div>
                    <p class="cp-hint">Username cannot be changed here.</p>

                    <div class="field">
                        <label for="full_name">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" required
                               value="<?php echo htmlspecialchars($patient['full_name']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="dob">Date of Birth *</label>
                            <input type="date" name="dob" id="dob" required
                                   value="<?php echo htmlspecialchars($patient['dob']); ?>">
                        </div>
                        <div class="field">
                            <label for="gender">Gender *</label>
                            <select name="gender" id="gender" required>
                                <option value="Male"<?php if ($patient['gender'] == 'Male') echo ' selected'; ?>>Male</option>
                                <option value="Female"<?php if ($patient['gender'] == 'Female') echo ' selected'; ?>>Female</option>
                                <option value="Other"<?php if ($patient['gender'] == 'Other') echo ' selected'; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label for="contact">Contact Number *</label>
                        <input type="text" name="contact" id="contact" required
                               value="<?php echo htmlspecialchars($patient['contact']); ?>">
                    </div>

                    <div class="field">
                        <label for="address">Residential Address *</label>
                        <textarea name="address" id="address" required><?php echo htmlspecialchars($patient['address']); ?></textarea>
                    </div>

                    <div class="cp-actions">
                        <button type="submit" name="save_profile" value="1" class="cp-submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
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
