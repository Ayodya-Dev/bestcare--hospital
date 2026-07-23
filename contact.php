<?php
session_start();
include("includes/db.php");

$page_title = "Contact - BestCare Hospital";
include("includes/header.php");

$error = "";
$success = "";

if (isset($_POST['submit_query'])) {
    // Must be logged in as patient to submit a query linked to patient_id
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
        $error = "Please login as a patient to submit a query. Or use the contact details below.";
    } else {
        $subject = $_POST['subject'];
        $message = $_POST['message'];

        if (strlen($subject) == 0 || strlen($message) == 0) {
            $error = "Need to fill all the fields";
        } else {
            $user_id = $_SESSION['user_id'];

            // Get patient id for this user
            $p_sql = "SELECT id FROM patients WHERE user_id=$user_id";
            $p_result = mysqli_query($conn, $p_sql);

            if ($p_result && mysqli_num_rows($p_result) > 0) {
                $p_row = mysqli_fetch_array($p_result);
                $patient_id = $p_row['id'];

                $safe_subject = mysqli_real_escape_string($conn, $subject);
                $safe_message = mysqli_real_escape_string($conn, $message);

                $sql = "INSERT INTO queries (patient_id, subject, message, status)
                        VALUES ($patient_id, '$safe_subject', '$safe_message', 'Open')";
                $result = mysqli_query($conn, $sql);

                if (!$result) {
                    die("Could not submit query: " . mysqli_error($conn));
                }

                $success = "Your query was submitted successfully. Staff will reply soon.";
            } else {
                $error = "Patient profile not found.";
            }
        }
    }
}
?>

<div class="page-wrap">
    <h2>Contact Us</h2>
    <p>BestCare Hospital, Matara. Reach us for appointments and service questions.</p>

    <div class="contact-grid">
        <div class="card">
            <h3>Hospital Details</h3>
            <p><strong>Address:</strong> Main Street, Matara, Sri Lanka</p>
            <p><strong>Phone:</strong> 041-2223344</p>
            <p><strong>Email:</strong> info@bestcarehospital.lk</p>
            <p><strong>Hours:</strong> Mon–Sat 8:00 AM – 8:00 PM</p>
            <p><strong>Emergency:</strong> Open 24 hours</p>
        </div>

        <div class="card">
            <h3>Submit a Query</h3>
            <p class="field-hint">Patients must login to send a query to the hospital.</p>

            <?php if ($error != "") { ?>
                <div class="error-msg"><span><?php echo $error; ?></span></div>
            <?php } ?>
            <?php if ($success != "") { ?>
                <div class="success-msg"><?php echo $success; ?></div>
            <?php } ?>

            <form method="post" action="" id="contactForm">
                <label for="subject">Subject</label>
                <div class="input-wrap no-icon">
                    <input type="text" name="subject" id="subject" required>
                </div>

                <label for="message">Message</label>
                <div class="input-wrap no-icon textarea-wrap">
                    <textarea name="message" id="message" rows="4" required></textarea>
                </div>

                <button type="submit" name="submit_query" value="1" class="btn-primary">Send Query</button>
            </form>

            <?php if (!isset($_SESSION['user_id'])) { ?>
                <p class="form-bottom-link">
                    <a href="/bestcare-hospital/auth/login.php">Login</a> or
                    <a href="/bestcare-hospital/auth/register.php">Register</a> as patient
                </p>
            <?php } ?>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
include("includes/footer.php");
?>
