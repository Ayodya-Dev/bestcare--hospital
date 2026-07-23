<?php
session_start();
include("../includes/db.php");

$error = "";
$success = "";

// Step 1: patient enters username to start reset
if (isset($_POST['find_account'])) {
    $username = $_POST['username'];

    if (strlen($username) == 0) {
        $error = "Please enter your username";
    } else {
        $sql = "SELECT * FROM users WHERE username='$username' AND is_active=1";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Query error: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);

            // Password reset is for patients only
            if ($row['role'] != 'patient') {
                $error = "Password reset is only available for patient accounts. Staff/Admin should contact the administrator.";
            } else {
                $_SESSION['reset_user_id'] = $row['id'];
                $_SESSION['reset_username'] = $row['username'];
                header("Location: reset-password.php");
                exit();
            }
        } else {
            $error = "No patient account found with that username";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=4">
</head>
<body>
    <div class="auth-page">
        <header class="auth-header">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="BestCare Hospital Logo" class="auth-logo" width="96" height="96">
            <h1>Forgot Password</h1>
            <p>Password reset for patients only</p>
        </header>

        <main class="auth-main">
            <?php if ($error != "") { ?>
                <div class="error-msg">
                    <img src="/bestcare-hospital/assets/images/IMG_2.svg" alt="Alert">
                    <span><?php echo $error; ?></span>
                </div>
            <?php } ?>

            <div class="auth-card">
                <div class="auth-card-body">
                    <p class="field-hint" style="margin-top:0;margin-bottom:20px;">
                        Enter your patient username. Staff and admin cannot reset passwords here.
                    </p>

                    <form method="post" action="" id="forgotForm">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_5.svg" alt="">
                            <input type="text" name="username" id="username" placeholder="name@example.com" required>
                        </div>

                        <button type="submit" name="find_account" value="1" class="btn-primary">Continue</button>

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
</body>
</html>
