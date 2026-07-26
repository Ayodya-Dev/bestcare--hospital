<?php
session_start();
include("../includes/db.php");

$error = "";
$success = "";

if (isset($_POST['change_submit'])) {
    $username = trim($_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($username) == 0 || strlen($current_password) == 0 ||
        strlen($new_password) == 0 || strlen($confirm_password) == 0) {
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
        $safe_username = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT * FROM users WHERE username='$safe_username' AND role='patient' AND is_active=1";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            $error = bestcare_db_error($conn, "Could not look up account. Please try again.");
        } elseif (mysqli_num_rows($result) == 0) {
            // Same message as a wrong password so usernames cannot be guessed
            $error = "Wrong username or current password.";
        } else {
            $row = mysqli_fetch_array($result);

            if (!password_verify($current_password, $row['password_hash'])) {
                $error = "Wrong username or current password.";
            } else {
                $user_id = (int)$row['id'];
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $safe_hash = mysqli_real_escape_string($conn, $password_hash);
                $upd = mysqli_query($conn, "UPDATE users SET password_hash='$safe_hash' WHERE id=$user_id AND role='patient'");

                if (!$upd) {
                    $error = bestcare_db_error($conn, "Could not update password. Please try again.");
                } else {
                    $success = "Password updated successfully! You can now login.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=4">
</head>
<body>
    <div class="auth-page">
        <header class="auth-header">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="BestCare Hospital Logo" class="auth-logo" width="96" height="96">
            <h1>Change Password</h1>
            <p>Enter your current password to set a new one</p>
        </header>

        <main class="auth-main">
            <?php if ($error != "") { ?>
                <div class="error-msg">
                    <img src="/bestcare-hospital/assets/images/IMG_2.svg" alt="Alert">
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php } ?>

            <?php if ($success != "") { ?>
                <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
            <?php } ?>

            <div class="auth-card">
                <div class="auth-card-body">
                    <p class="field-hint" style="margin-top:0;margin-bottom:20px;">
                        For patient accounts only. You must know your current password.
                        If you forgot it completely, please contact the hospital administration.
                    </p>

                    <form method="post" action="" id="changePasswordForm">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_5.svg" alt="">
                            <input type="text" name="username" id="username" placeholder="name@example.com" required
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>

                        <label for="current_password">Current Password</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            <input type="password" name="current_password" id="current_password" placeholder="••••••••" required>
                        </div>

                        <label for="new_password">New Password</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            <input type="password" name="new_password" id="new_password" placeholder="••••••••" required>
                        </div>
                        <p class="field-hint">Must be at least 8 characters with a mix of letters and numbers.</p>

                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" required>
                        </div>

                        <button type="submit" name="change_submit" value="1" class="btn-primary">Update Password</button>

                        <p class="form-bottom-link">
                            Remember password? <a href="login.php">Back to Login</a>
                        </p>
                    </form>
                </div>
            </div>

            <p class="back-center"><a href="../index.php">Back to Home</a></p>
        </main>
    </div>

    <script src="/bestcare-hospital/assets/js/main.js?v=4"></script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
