<?php
include("includes/db.php");
include("includes/public_session.php");

$sv_img = "/bestcare-hospital/assets/services";

// Get all services with department name (lecture style JOIN)
$sql = "SELECT s.*, d.name AS department_name
        FROM services s, departments d
        WHERE s.department_id = d.id
        ORDER BY s.name";
$result = mysqli_query($conn, $sql);

if (!$result) {
    bestcare_fail_page("Could not load services right now. Please try again later.");
}

// Default photos + icons per department (used when admin has not uploaded an image)
$dept_images = array(
    'Laboratory'       => "IMG_5.webp",
    'Cardiology'       => "IMG_8.webp",
    'Emergency'        => "IMG_10.webp",
    'General Medicine' => "IMG_12.webp"
);
$dept_icons = array(
    'Laboratory'       => "IMG_6.svg",
    'Cardiology'       => "IMG_9.svg",
    'Emergency'        => "IMG_11.svg",
    'General Medicine' => "IMG_13.svg"
);
$fallback_images = array("IMG_14.webp", "IMG_5.webp", "IMG_8.webp", "IMG_10.webp", "IMG_12.webp");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/home.css?v=1">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/services.css?v=1">
</head>
<body class="home-body">

<?php include __DIR__ . "/includes/public_header.php"; ?>

<!-- Hero -->
<section class="sv-hero">
    <div class="sv-hero-bg">
        <img src="<?php echo $sv_img; ?>/IMG_2.webp" alt="Hospital Interior">
        <div class="sv-hero-overlay"></div>
    </div>
    <div class="sv-hero-content">
        <div class="sv-hero-badge">
            <img src="<?php echo $sv_img; ?>/IMG_3.svg" alt="Shield">
            <span>Trusted Healthcare in Matara</span>
        </div>
        <h1>Exceptional Care,<br><span class="accent">Close to Heart.</span></h1>
        <p class="sv-hero-text">
            BestCare Hospital provides comprehensive medical services ranging from routine check-ups to specialized surgical procedures, ensuring premium care for every patient.
        </p>
        <div class="sv-hero-actions">
            <a class="sv-btn-solid" href="<?php echo $book_link; ?>">Book Appointment</a>
            <a class="sv-btn-outline" href="/bestcare-hospital/contact.php">Contact Support</a>
        </div>
    </div>
</section>

<!-- Services -->
<section class="sv-section">
    <div class="home-wrap">
        <div class="sv-section-head">
            <span class="sv-eyebrow">Explore Services</span>
            <h2>Our Medical Services</h2>
            <p>We offer a wide spectrum of healthcare solutions tailored to your individual needs. Our departments are staffed by experienced professionals utilizing the latest medical technology.</p>
        </div>

        <div class="sv-grid">
            <?php
            $i = 0;
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    $dept = $row['department_name'];

                    // Card photo: uploaded image first, then department default, then rotation
                    if (isset($row['image_path']) && $row['image_path'] != '') {
                        $photo = $row['image_path'];
                    } elseif (isset($dept_images[$dept])) {
                        $photo = $sv_img . "/" . $dept_images[$dept];
                    } else {
                        $photo = $sv_img . "/" . $fallback_images[$i % count($fallback_images)];
                    }

                    if (isset($dept_icons[$dept])) {
                        $icon = $sv_img . "/" . $dept_icons[$dept];
                    } else {
                        $icon = $sv_img . "/IMG_15.svg";
                    }

                    $i = $i + 1;
                    ?>
                    <div class="sv-card">
                        <div class="sv-card-img">
                            <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <span class="sv-card-dept"><?php echo htmlspecialchars($dept); ?></span>
                        </div>
                        <div class="sv-card-body">
                            <div class="sv-card-title">
                                <div class="sv-card-icon"><img src="<?php echo $icon; ?>" alt=""></div>
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            </div>
                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                        </div>
                        <div class="sv-card-foot">
                            <div>
                                <span class="sv-fee-label">Standard Fee</span>
                                <span class="sv-fee">Rs. <?php echo number_format($row['fee'], 2); ?></span>
                            </div>
                            <a class="sv-details-btn" href="<?php echo $book_link; ?>">
                                Details <img src="<?php echo $sv_img; ?>/IMG_7.svg" alt="Arrow">
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No services found.</p>";
            }
            ?>

            <!-- More services coming -->
            <div class="sv-card-more">
                <div class="sv-more-icon">
                    <img src="<?php echo $sv_img; ?>/IMG_1.svg" alt="Pulse">
                </div>
                <h3>More Services Coming</h3>
                <p>We are constantly expanding our specialties to better serve the Matara community.</p>
                <a href="/bestcare-hospital/contact.php">Suggest a Service</a>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="sv-features">
    <div class="home-wrap">
        <div class="sv-features-grid">
            <div class="sv-feature">
                <div class="sv-feature-icon"><img src="<?php echo $sv_img; ?>/IMG_3.svg" alt="Certified"></div>
                <h4>Certified Experts</h4>
                <p>Board-certified medical specialists.</p>
            </div>
            <div class="sv-feature">
                <div class="sv-feature-icon"><img src="<?php echo $sv_img; ?>/IMG_11.svg" alt="Response"></div>
                <h4>24/7 Response</h4>
                <p>Round-the-clock emergency assistance.</p>
            </div>
            <div class="sv-feature">
                <div class="sv-feature-icon"><img src="<?php echo $sv_img; ?>/IMG_16.svg" alt="Facilities"></div>
                <h4>Modern Facilities</h4>
                <p>Cutting-edge diagnostic equipment.</p>
            </div>
            <div class="sv-feature">
                <div class="sv-feature-icon"><img src="<?php echo $sv_img; ?>/IMG_15.svg" alt="Centric"></div>
                <h4>Patient Centric</h4>
                <p>Individualized care and attention.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="sv-cta-section">
    <div class="home-wrap">
        <div class="sv-cta">
            <div class="sv-cta-inner">
                <h2>Ready to experience premium healthcare?</h2>
                <p>Book your consultation online today or visit our facility in Matara for expert medical guidance. Our team is dedicated to providing you with the highest standard of clinical excellence.</p>
                <div class="sv-cta-actions">
                    <a class="sv-btn-light" href="<?php echo $book_link; ?>">Schedule Visit</a>
                    <a class="sv-btn-ghost-light" href="/bestcare-hospital/doctors.php">Find a Doctor</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . "/includes/public_footer.php"; ?>

<?php mysqli_close($conn); ?>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
