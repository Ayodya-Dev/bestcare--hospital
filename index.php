<?php
include __DIR__ . "/includes/public_session.php";
include __DIR__ . "/includes/db.php";

$img = "/bestcare-hospital/assets/home";
$sv_img = "/bestcare-hospital/assets/services";
$doc_img = "/bestcare-hospital/assets/doctors";

// Fallback images when no upload is set
$service_fallbacks = array(
    $sv_img . "/IMG_5.webp",
    $sv_img . "/IMG_8.webp",
    $sv_img . "/IMG_10.webp",
    $sv_img . "/IMG_12.webp",
    $sv_img . "/IMG_14.webp"
);
$service_icons = array(
    $img . "/IMG_6.svg",
    $img . "/IMG_9.svg",
    $img . "/IMG_11.svg"
);
$doctor_fallbacks = array(
    $doc_img . "/IMG_3.webp",
    $doc_img . "/IMG_9.webp",
    $doc_img . "/IMG_10.webp"
);

// Latest / first 3 services from DB
$home_services = array();
$svc_sql = "SELECT s.*, d.name AS department_name
            FROM services s, departments d
            WHERE s.department_id = d.id
            ORDER BY s.name
            LIMIT 3";
$svc_result = mysqli_query($conn, $svc_sql);
if ($svc_result) {
    while ($row = mysqli_fetch_array($svc_result)) {
        $home_services[] = $row;
    }
}

// First 3 active doctors from DB
$home_doctors = array();
$doc_sql = "SELECT st.*, d.name AS department_name
            FROM staff st, departments d, users u
            WHERE st.department_id = d.id
            AND st.user_id = u.id
            AND u.role = 'staff'
            AND u.is_active = 1
            AND (st.staff_type = 'Doctor' OR st.staff_type IS NULL OR st.staff_type = '')
            ORDER BY st.full_name
            LIMIT 3";
$doc_result = mysqli_query($conn, $doc_sql);
if (!$doc_result) {
    // Fallback if staff_type column missing
    $doc_sql = "SELECT st.*, d.name AS department_name
                FROM staff st, departments d, users u
                WHERE st.department_id = d.id
                AND st.user_id = u.id
                AND u.role = 'staff'
                AND u.is_active = 1
                ORDER BY st.full_name
                LIMIT 3";
    $doc_result = mysqli_query($conn, $doc_sql);
}
if ($doc_result) {
    while ($row = mysqli_fetch_array($doc_result)) {
        $home_doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BestCare Hospital - Excellence in Healthcare</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/home.css?v=1">
</head>
<body class="home-body">

<?php include __DIR__ . "/includes/public_header.php"; ?>

<!-- Hero -->
<section class="vh-hero">
    <div class="vh-hero-bg">
        <img src="<?php echo $img; ?>/IMG_3.webp" alt="Hospital Interior">
        <div class="vh-hero-overlay"></div>
    </div>

    <div class="vh-hero-content">
        <div class="vh-hero-inner">
            <div class="vh-pill">
                <span>Excellence in Healthcare</span>
            </div>
            <h1>Your Health, Our <span class="accent">Priority</span>.</h1>
            <p class="vh-hero-text">
                Experience world-class medical services delivered with compassion. From routine check-ups to advanced surgeries, we are here for you 24/7.
            </p>
            <div class="vh-hero-actions">
                <a class="vh-btn-primary" href="<?php echo $book_link; ?>">
                    Book Appointment
                    <img src="<?php echo $img; ?>/IMG_4.svg" alt="Calendar">
                </a>
                <a class="vh-btn-outline" href="/bestcare-hospital/services.php">Our Services</a>
            </div>
        </div>
    </div>
</section>

<!-- Services (synced from database) -->
<section class="vh-section vh-section-muted">
    <div class="home-wrap">
        <div class="vh-section-head">
            <h2>Comprehensive Medical Services</h2>
            <p>We provide a wide range of specialized healthcare solutions tailored to meet the unique needs of every patient.</p>
        </div>

        <div class="vh-grid-3">
            <?php
            if (count($home_services) > 0) {
                for ($i = 0; $i < count($home_services); $i++) {
                    $s = $home_services[$i];
                    if (isset($s['image_path']) && $s['image_path'] != '') {
                        $photo = $s['image_path'];
                    } else {
                        $photo = $service_fallbacks[$i % count($service_fallbacks)];
                    }
                    $icon = $service_icons[$i % count($service_icons)];
                    $desc = $s['description'];
                    if (strlen($desc) > 140) {
                        $desc = substr($desc, 0, 137) . "...";
                    }
                    ?>
                    <div class="vh-card">
                        <div class="vh-card-img">
                            <img class="cover" src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($s['name']); ?>">
                            <div class="vh-card-icon"><img src="<?php echo $icon; ?>" alt=""></div>
                        </div>
                        <div class="vh-card-body">
                            <h3><?php echo htmlspecialchars($s['name']); ?></h3>
                            <p><?php echo htmlspecialchars($desc); ?></p>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No services available yet.</p>';
            }
            ?>
        </div>

        <div class="vh-center-link">
            <a href="/bestcare-hospital/services.php">
                View All Specialist Services
                <img src="<?php echo $img; ?>/IMG_7.svg" alt="">
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="vh-section vh-section-soft">
    <div class="home-wrap">
        <div class="vh-why">
            <div class="vh-why-media">
                <div class="vh-why-frame">
                    <img src="<?php echo $img; ?>/IMG_12.webp" alt="Medical Team">
                </div>
                <div class="vh-sat-card">
                    <div class="vh-sat-icon">
                        <img src="<?php echo $img; ?>/IMG_15.svg" alt="Check">
                    </div>
                    <div>
                        <div class="num">98%</div>
                        <div class="label">Patient Satisfaction</div>
                    </div>
                </div>
            </div>

            <div class="vh-why-copy">
                <div class="vh-outline-pill"><span>Why Choose BestCare</span></div>
                <h2>Setting New Standards in Modern Patient Care</h2>
                <p>Our hospital combines the expertise of leading medical professionals with cutting-edge technology to ensure the best possible outcomes for our patients.</p>

                <div class="vh-feature">
                    <div class="vh-feature-icon"><img src="<?php echo $img; ?>/IMG_13.svg" alt=""></div>
                    <div>
                        <h4>Certified Excellence</h4>
                        <p>Recognized globally for high clinical standards and patient safety protocols.</p>
                    </div>
                </div>
                <div class="vh-feature">
                    <div class="vh-feature-icon"><img src="<?php echo $img; ?>/IMG_14.svg" alt=""></div>
                    <div>
                        <h4>Expert Team</h4>
                        <p>Over 200+ specialized doctors and surgeons with decades of combined experience.</p>
                    </div>
                </div>
                <div class="vh-feature">
                    <div class="vh-feature-icon"><img src="<?php echo $img; ?>/IMG_16.svg" alt=""></div>
                    <div>
                        <h4>24/7 Emergency Care</h4>
                        <p>Always open. Our emergency response team is ready to provide immediate life-saving care.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctors (synced from database) -->
<section class="vh-section vh-section-muted">
    <div class="home-wrap">
        <div class="vh-section-head">
            <h2>Meet Our Medical Experts</h2>
            <p>Our world-class doctors bring years of experience and specialized knowledge to ensure you receive the highest quality of care.</p>
        </div>

        <div class="vh-grid-3">
            <?php
            if (count($home_doctors) > 0) {
                for ($i = 0; $i < count($home_doctors); $i++) {
                    $d = $home_doctors[$i];
                    if (isset($d['image_path']) && $d['image_path'] != '') {
                        $photo = $d['image_path'];
                    } else {
                        $photo = $doctor_fallbacks[$i % count($doctor_fallbacks)];
                    }
                    ?>
                    <div class="vh-doc-card">
                        <div class="vh-doc-photo"><img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($d['full_name']); ?>"></div>
                        <div class="vh-doc-badge"><span><?php echo htmlspecialchars($d['specialization']); ?></span></div>
                        <h3><?php echo htmlspecialchars($d['full_name']); ?></h3>
                        <p class="exp"><?php echo htmlspecialchars($d['department_name']); ?></p>
                        <div class="vh-stars">
                            <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                            <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                            <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                            <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                            <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                            <span>4.9</span>
                        </div>
                        <a class="vh-btn-doc" href="/bestcare-hospital/doctors.php">View Profile</a>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No doctors available yet.</p>';
            }
            ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="vh-cta">
    <div class="vh-cta-inner">
        <h2>Ready to Schedule Your Visit?</h2>
        <p>Our specialists are ready to help you on your journey to better health. Book your appointment online today and avoid waiting times.</p>
        <div class="vh-cta-row">
            <a class="vh-btn-white" href="<?php echo $book_link; ?>">Book An Appointment</a>
            <div class="vh-cta-phone">
                <span class="small">Or call us directly:</span>
                <span class="big">041-2223344</span>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . "/includes/public_footer.php"; ?>

<script src="/bestcare-hospital/assets/js/main.js?v=5"></script>
<?php mysqli_close($conn); ?>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
