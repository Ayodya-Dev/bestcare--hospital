<?php
session_start();
include("../includes/db.php");

$error = "";
$success = "";

// Must come from forgot-password.php first
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot-password.php");
    exit();
}

$reset_user_id = $_SESSION['reset_user_id'];
$reset_username = $_SESSION['reset_username'];

if (isset($_POST['reset_submit'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) == 0 || strlen($confirm_password) == 0) {
        $error = "Need to fill all the fields";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error = "Password must have a mix of letters and numbers";
    } elseif ($new_password != $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check again that this user is a patient
        $check_sql = "SELECT * FROM users WHERE id=$reset_user_id AND role='patient' AND is_active=1";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) == 0) {
            $error = "Invalid reset request";
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_username']);
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash='$password_hash' WHERE id=$reset_user_id AND role='patient'";
            $result = mysqli_query($conn, $sql);

            if (!$result) {
                die("Could not update password: " . mysqli_error($conn));
            }

            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_username']);

            $success = "Password updated successfully! You can now login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=4">
</head>
<body>
    <div class="auth-page">
        <header class="auth-header">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="BestCare Hospital Logo" class="auth-logo" width="96" height="96">
            <h1>Reset Password</h1>
            <p>Account: <?php echo htmlspecialchars($reset_username); ?></p>
        </header>

        <main class="auth-main">
            <?php if ($error != "") { ?>
                <div class="error-msg">
                    <img src="/bestcare-hospital/assets/images/IMG_2.svg" alt="Alert">
                    <span><?php echo $error; ?></span>
                </div>
            <?php } ?>

            <?php if ($success != "") { ?>
                <div class="success-msg"><?php echo $success; ?></div>
                <p class="form-bottom-link"><a href="login.php">Go to Login</a></p>
            <?php } else { ?>
            <div class="auth-card">
                <div class="auth-card-body">
                    <form method="post" action="" id="resetForm">
                        <label for="new_password">New Password</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            <input type="password" name="new_password" id="new_password" placeholder="••••••••" required>
                        </div>
                        <p class="field-hint">Must be at least 8 characters with a mix of letters and numbers.</p>

                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" required>
                        </div>

                        <button type="submit" name="reset_submit" value="1" class="btn-primary">Update Password</button>

                        <p class="form-bottom-link">
                            <a href="login.php">Cancel and go to Login</a>
                        </p>
                    </form>
                </div>
            </div>
            <?php } ?>
        </main>
    </div>

    <script src="/bestcare-hospital/assets/js/main.js?v=4"></script>
</body>
</html>
