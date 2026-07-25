<?php
require_once __DIR__ . "/../includes/session_init.php";
include("../includes/db.php");

$error = "";

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (strlen($username) == 0 || strlen($password) == 0) {
        $error = "Need to fill all the fields";
    } else {
        $safe_username = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT * FROM users WHERE username='$safe_username' AND is_active=1";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Query error: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);

            if (password_verify($password, $row['password_hash'])) {
                $role = $row['role'];

                // Start ONLY the role session (no public session first — that caused refresh logout)
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }

                bestcare_start_session($role);

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $role;

                // Make sure session is written before redirect
                session_write_close();

                if ($role == 'admin') {
                    header("Location: ../admin/dashboard.php");
                    exit();
                } elseif ($role == 'staff') {
                    header("Location: ../staff/dashboard.php");
                    exit();
                } else {
                    header("Location: ../patient/dashboard.php");
                    exit();
                }
            } else {
                $error = "Wrong username or password. Please try again or reset your credentials.";
            }
        } else {
            $error = "Wrong username or password. Please try again or reset your credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff & Patient Login - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=4">
</head>
<body>
    <div class="auth-page">
        <header class="auth-header">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="BestCare Hospital Logo" class="auth-logo" width="96" height="96">
            <h1>Staff & Patient Login</h1>
            <p>Secure access to your healthcare portal</p>
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
                    <p class="role-note">Select Your Role</p>
                    <div class="role-tabs" id="roleTabs">
                        <button type="button" class="role-tab active" data-role="patient" id="tabPatient">
                            <img src="/bestcare-hospital/assets/images/IMG_3.svg" alt="">
                            Patient
                        </button>
                        <button type="button" class="role-tab" data-role="staff" id="tabStaff">
                            <img src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            Staff Member
                        </button>
                    </div>

                    <form method="post" action="" id="loginForm">
                        <label for="username" id="usernameLabel">Username</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_5.svg" alt="">
                            <input type="text" name="username" id="username" placeholder="name@example.com" required>
                        </div>

                        <div class="label-row">
                            <label for="password">Password</label>
                            <a href="forgot-password.php" class="forgot-link" id="forgotLink">Forgot password?</a>
                        </div>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            <input type="password" name="password" id="password" placeholder="••••••••" required>
                            <button type="button" class="password-toggle" id="togglePassword" title="Show password">
                                <img src="/bestcare-hospital/assets/images/IMG_6.svg" alt="Show">
                            </button>
                        </div>

                        <button type="submit" name="submit" value="Login" class="btn-primary">Login</button>
                    </form>
                </div>

                <div class="auth-card-footer">
                    <div class="footer-row">
                        <span>New patient?</span>
                        <a href="register.php">
                            Register as Patient
                            <img src="/bestcare-hospital/assets/images/IMG_7.svg" alt="">
                        </a>
                    </div>
                    <div class="back-home-wrap">
                        <a href="../index.php">Back to Home</a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="auth-legal">
            Authorized personnel and registered patients only. Accessing this system implies agreement to our
            Privacy Policy and Terms of Service.
        </footer>
    </div>

    <script src="/bestcare-hospital/assets/js/main.js?v=4"></script>
</body>
</html>
<?php
if (isset($conn)) {
    mysqli_close($conn);
}
?>
