<?php
include("includes/db.php");
include("includes/public_session.php");

$home_img = "/bestcare-hospital/assets/home";
$error = "";
$success = "";

if (isset($_POST['submit_query'])) {
    if ($pub_user_id == 0 || $pub_role != 'patient') {
        $error = "Please login as a patient to submit a query. Or use the contact details below.";
    } else {
        $subject = $_POST['subject'];
        $message = $_POST['message'];

        if (strlen($subject) == 0 || strlen($message) == 0) {
            $error = "Need to fill all the fields";
        } else {
            $user_id = (int)$pub_user_id;

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
                    $error = bestcare_db_error($conn, "Could not submit query. Please try again.");
                } else {
                    $success = "Your query was submitted successfully. The hospital administration will reply soon.";
                }
            } else {
                $error = "Patient profile not found.";
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
    <title>Contact - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/home.css?v=1">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/contact.css?v=1">
</head>
<body class="home-body">

<?php include __DIR__ . "/includes/public_header.php"; ?>

<!-- Hero -->
<section class="ct-hero">
    <div class="ct-hero-bg">
        <img src="<?php echo $home_img; ?>/IMG_3.webp" alt="Hospital waiting area">
        <div class="ct-hero-overlay"></div>
    </div>
    <div class="ct-hero-content">
        <span class="ct-badge">Get in Touch</span>
        <h1>Your Health, Our Priority.</h1>
        <p class="ct-hero-text">BestCare Hospital Matara is here to provide world-class medical assistance. Reach us for appointments and service questions.</p>
    </div>
</section>

<!-- Care coordinator -->
<div class="ct-coord-wrap">
    <div class="ct-coord">
        <div class="ct-coord-photo">
            <img src="<?php echo $home_img; ?>/IMG_19.webp" alt="Care Coordinator">
        </div>
        <div class="ct-coord-copy">
            <h2>Talk to a Care Coordinator</h2>
            <p>Our front-desk specialists are available 24/7 to help with appointments, directions, and general hospital questions.</p>
            <div class="ct-coord-checks">
                <div class="ct-check">
                    <span class="ct-check-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    Response within 2 hours
                </div>
                <div class="ct-check">
                    <span class="ct-check-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    Verified Medical Advice
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main -->
<section class="ct-main">
    <div class="home-wrap">
        <div class="ct-grid">
            <!-- Hospital details -->
            <div class="ct-details">
                <h2>Hospital Details</h2>
                <p class="lead">Find our address, phone lines, and opening hours for BestCare Hospital Matara.</p>

                <ul class="ct-info-list">
                    <li class="ct-info-item">
                        <div class="ct-info-icon"><img src="<?php echo $home_img; ?>/IMG_21.svg" alt=""></div>
                        <div>
                            <span class="label">Address</span>
                            <span class="value">Main Street, Matara, Sri Lanka</span>
                        </div>
                    </li>
                    <li class="ct-info-item">
                        <div class="ct-info-icon"><img src="<?php echo $home_img; ?>/IMG_26.svg" alt=""></div>
                        <div>
                            <span class="label">Phone</span>
                            <span class="value">041-2223344</span>
                        </div>
                    </li>
                    <li class="ct-info-item">
                        <div class="ct-info-icon"><img src="<?php echo $home_img; ?>/IMG_27.svg" alt=""></div>
                        <div>
                            <span class="label">Email</span>
                            <span class="value">info@bestcarehospital.lk</span>
                        </div>
                    </li>
                    <li class="ct-info-item">
                        <div class="ct-info-icon"><img src="<?php echo $home_img; ?>/IMG_16.svg" alt=""></div>
                        <div>
                            <span class="label">Hours</span>
                            <span class="value">Mon–Sat 8:00 AM – 8:00 PM</span>
                        </div>
                    </li>
                </ul>

                <div class="ct-emergency">
                    <div class="ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </div>
                    <div>
                        <strong>Emergency Availability</strong>
                        <p>Open 24 hours</p>
                    </div>
                </div>

                <div class="ct-portal">
                    <strong>Need urgent medical records?</strong>
                    <a class="ct-portal-btn" href="<?php echo $portal_link; ?>">
                        Go to Patient Portal
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            </div>

            <!-- Submit query -->
            <div class="ct-form-card">
                <h2>Submit a Query</h2>
                <p class="lead">Send a message to our staff. Logged-in patients get tracked replies in their portal.</p>

                <?php if ($pub_role == 'patient') { ?>
                    <div class="ct-notice">
                        <span class="ico">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </span>
                        <div>
                            Signed in as <strong><?php echo htmlspecialchars($pub_username); ?></strong>.
                            Your reply will appear in
                            <a href="/bestcare-hospital/patient/my-queries.php">My Queries</a>.
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="ct-notice">
                        <span class="ico">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </span>
                        <div>
                            Patients must
                            <a href="/bestcare-hospital/auth/login.php">login</a>
                            or
                            <a href="/bestcare-hospital/auth/register.php">register</a>
                            as a patient before submitting a query for medical tracking.
                        </div>
                    </div>
                <?php } ?>

                <?php if ($error != "") { ?>
                    <div class="ct-alert err"><?php echo $error; ?></div>
                <?php } ?>
                <?php if ($success != "") { ?>
                    <div class="ct-alert ok"><?php echo $success; ?></div>
                <?php } ?>

                <form class="ct-form" method="post" action="" id="contactForm">
                    <div class="field">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" required
                               placeholder="e.g. Appointment Inquiry, Laboratory Test, Pharmacy">
                    </div>
                    <div class="field">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" rows="5" required
                                  placeholder="Please describe your query in detail..."></textarea>
                    </div>
                    <button type="submit" name="submit_query" value="1" class="ct-submit">Send Query</button>
                    <p class="ct-form-note">By clicking Send Query, you agree to our Privacy Policy.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . "/includes/public_footer.php"; ?>

<?php mysqli_close($conn); ?>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
