<?php
session_start();
include("../includes/db.php");

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    if (strlen($username) == 0 || strlen($password) == 0 || strlen($full_name) == 0 ||
        strlen($dob) == 0 || strlen($gender) == 0 || strlen($contact) == 0 || strlen($address) == 0) {
        $error = "Need to fill all the fields";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Password must have a mix of letters and numbers";
    } else {
        $check_sql = "SELECT * FROM users WHERE username='$username'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username already exists. Please choose another.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql1 = "INSERT INTO users (username, password_hash, role)
                     VALUES ('$username', '$password_hash', 'patient')";
            $result1 = mysqli_query($conn, $sql1);

            if (!$result1) {
                die("Could not enter user data: " . mysqli_error($conn));
            }

            $user_id = mysqli_insert_id($conn);

            $sql2 = "INSERT INTO patients (user_id, full_name, dob, gender, contact, address)
                     VALUES ($user_id, '$full_name', '$dob', '$gender', '$contact', '$address')";
            $result2 = mysqli_query($conn, $sql2);

            if (!$result2) {
                die("Could not enter patient data: " . mysqli_error($conn));
            }

            $success = "Registered successfully! You can now login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=4">
</head>
<body>
    <div class="auth-page">
        <header class="auth-header">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="BestCare Hospital Logo" class="auth-logo" width="96" height="96">
            <h1>Patient Registration</h1>
            <p>Create your BestCare Hospital account</p>
        </header>

        <main class="auth-main" style="max-width: 480px;">
            <?php if ($error != "") { ?>
                <div class="error-msg">
                    <img src="/bestcare-hospital/assets/images/IMG_2.svg" alt="Alert">
                    <span><?php echo $error; ?></span>
                </div>
            <?php } ?>

            <?php if ($success != "") { ?>
                <div class="success-msg"><?php echo $success; ?></div>
            <?php } ?>

            <div class="auth-card register-card">
                <div class="auth-card-body">
                    <form method="post" action="" id="registerForm">

                        <!-- Account credentials -->
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_5.svg" alt="">
                            <input type="text" name="username" id="username" placeholder="name@example.com" required>
                        </div>

                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_4.svg" alt="">
                            <input type="password" name="password" id="password" placeholder="••••••••" required>
                            <button type="button" class="password-toggle" id="toggleRegPassword" title="Show password">
                                <img src="/bestcare-hospital/assets/images/IMG_6.svg" alt="Show">
                            </button>
                        </div>
                        <p class="field-hint">Must be at least 8 characters with a mix of letters and numbers.</p>

                        <hr class="form-divider">

                        <!-- Personal details -->
                        <h3 class="section-title">Personal Details</h3>

                        <label for="full_name">Full Name</label>
                        <div class="input-wrap">
                            <img class="input-icon" src="/bestcare-hospital/assets/images/IMG_3.svg" alt="">
                            <input type="text" name="full_name" id="full_name" placeholder="John Doe" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="dob">Date of Birth</label>
                                <div class="input-wrap no-icon">
                                    <input type="date" name="dob" id="dob" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <div class="input-wrap no-icon">
                                    <select name="gender" id="gender" required>
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <label for="contact">Contact Number</label>
                        <div class="input-wrap no-icon">
                            <input type="text" name="contact" id="contact" placeholder="+94 77 123 4567" required>
                        </div>

                        <label for="address">Residential Address</label>
                        <div class="input-wrap no-icon textarea-wrap">
                            <textarea name="address" id="address" rows="3" placeholder="Street, City, State, ZIP" required></textarea>
                        </div>

                        <button type="submit" name="submit" value="Register" class="btn-primary">Register</button>

                        <p class="form-bottom-link">
                            Already have an account? <a href="login.php">Login</a>
                        </p>
                    </form>
                </div>
            </div>

            <p class="back-center"><a href="../index.php">Back to Home</a></p>
        </main>

        <footer class="auth-legal">
            Registration is for patients only. Staff and admin accounts are created by the hospital.
        </footer>
    </div>

    <script src="/bestcare-hospital/assets/js/main.js?v=4"></script>
</body>
</html>
