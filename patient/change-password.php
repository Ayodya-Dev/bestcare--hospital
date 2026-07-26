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
$p_sql = "SELECT * FROM patients WHERE user_id=$user_id";
$p_result = mysqli_query($conn, $p_sql);
if (!$p_result || mysqli_num_rows($p_result) == 0) {
    bestcare_fail_page("Patient profile not found. Please contact the hospital.");
}
$patient = mysqli_fetch_array($p_result);

$full_name = $patient['full_name'];
$parts = explode(' ', $full_name);
$initials = strtoupper(substr($parts[0], 0, 1));
if (isset($parts[1])) {
    $initials .= strtoupper(substr($parts[1], 0, 1));
}
$first_name = $parts[0];

if (isset($_GET['msg']) && $_GET['msg'] == 'updated') {
    $success = "Password changed successfully.";
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($current_password) == 0 || strlen($new_password) == 0 || strlen($confirm_password) == 0) {
        $error = "Need to fill all the fields";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters";
    } elseif (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error = "New password must have a mix of letters and numbers";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match";
    } elseif ($new_password == $current_password) {
        $error = "New password must be different from your current password";
    } else {
        $u_sql = "SELECT password_hash FROM users WHERE id=$user_id AND role='patient' AND is_active=1";
        $u_result = mysqli_query($conn, $u_sql);

        if (!$u_result || mysqli_num_rows($u_result) == 0) {
            $error = "Account not found. Please try logging in again.";
        } else {
            $u_row = mysqli_fetch_array($u_result);

            if (!password_verify($current_password, $u_row['password_hash'])) {
                $error = "Current password is incorrect.";
            } else {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $safe_hash = mysqli_real_escape_string($conn, $password_hash);
                $upd = mysqli_query($conn, "UPDATE users SET password_hash='$safe_hash' WHERE id=$user_id AND role='patient'");

                if (!$upd) {
                    $error = bestcare_db_error($conn, "Could not update password. Please try again.");
                } else {
                    header("Location: change-password.php?msg=updated");
                    exit();
                }
            }
        }
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
    <title>Change Password - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-dashboard.css?v=16">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/patient-records.css?v=2">
    <style>
        .cp-page { width: 100%; max-width: 520px; }
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
        .cp-form label {
            display: block;
            font-family: "Inter", sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .cp-form input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 11px 12px;
            font-family: "Inter", sans-serif;
            font-size: 14px;
            outline: none;
        }
        .cp-form input:focus { border-color: #016450; }
        .cp-hint {
            margin: -8px 0 16px;
            font-family: "Inter", sans-serif;
            font-size: 12px;
            color: #9CA3AF;
        }
        .cp-submit {
            display: block;
            width: auto;
            min-width: 180px;
            margin: 8px auto 0;
            border: none;
            background: #016450;
            color: #fff;
            border-radius: 10px;
            padding: 12px 28px;
            font-family: "Inter", sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
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
        <a href="edit-profile.php">
            <img src="<?php echo $dash; ?>/IMG_8.svg" alt=""> Edit Profile
        </a>
        <a class="active" href="change-password.php">
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
                <strong>Change Password</strong>
            </div>

            <div class="cp-card">
                <h2>Change Password</h2>
                <p class="sub">Enter your current password, then choose a new one. Anyone without your current password cannot change it.</p>

                <?php if ($error != "") { ?>
                    <div class="cp-alert err"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>
                <?php if ($success != "") { ?>
                    <div class="cp-alert ok"><?php echo htmlspecialchars($success); ?></div>
                <?php } ?>

                <form class="cp-form" method="post" action="">
                    <div class="field">
                        <label for="current_password">Current Password *</label>
                        <input type="password" name="current_password" id="current_password" required autocomplete="current-password">
                    </div>
                    <div class="field">
                        <label for="new_password">New Password *</label>
                        <input type="password" name="new_password" id="new_password" required autocomplete="new-password">
                    </div>
                    <p class="cp-hint">At least 8 characters with a mix of letters and numbers.</p>
                    <div class="field">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" name="confirm_password" id="confirm_password" required autocomplete="new-password">
                    </div>
                    <button type="submit" name="change_password" value="1" class="cp-submit">Update Password</button>
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
