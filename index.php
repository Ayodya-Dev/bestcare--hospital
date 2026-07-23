<?php
session_start();

$img = "/bestcare-hospital/assets/home";

$book_link = "/bestcare-hospital/auth/login.php";
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'patient') {
    $book_link = "/bestcare-hospital/patient/book-appointment.php";
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

<!-- Header -->
<header class="vh-header">
    <div class="vh-header-inner">
        <div class="vh-brand">
            <div class="vh-brand-icon">
                <img src="<?php echo $img; ?>/IMG_1.svg" alt="Logo">
            </div>
            <span class="vh-brand-name">BestCare Hospital</span>
        </div>

        <nav class="vh-nav">
            <a href="/bestcare-hospital/index.php">Home</a>
            <a href="/bestcare-hospital/services.php">Services</a>
            <a href="/bestcare-hospital/doctors.php">Doctors</a>
            <a class="vh-nav-search" href="/bestcare-hospital/search.php">
                <img src="<?php echo $img; ?>/IMG_2.svg" alt="Search">
                <span>Search</span>
            </a>
            <a href="/bestcare-hospital/contact.php">Contact</a>
            <?php if (isset($_SESSION['user_id'])) { ?>
                <?php if ($_SESSION['role'] == 'admin') { ?>
                    <a href="/bestcare-hospital/admin/dashboard.php">Dashboard</a>
                <?php } elseif ($_SESSION['role'] == 'staff') { ?>
                    <a href="/bestcare-hospital/staff/dashboard.php">Dashboard</a>
                <?php } else { ?>
                    <a href="/bestcare-hospital/patient/dashboard.php">Dashboard</a>
                <?php } ?>
                <a href="/bestcare-hospital/auth/logout.php">Logout</a>
            <?php } else { ?>
                <a href="/bestcare-hospital/auth/login.php">Login</a>
                <a href="/bestcare-hospital/auth/register.php">Register</a>
            <?php } ?>
        </nav>
    </div>
</header>

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

<!-- Services -->
<section class="vh-section vh-section-muted">
    <div class="home-wrap">
        <div class="vh-section-head">
            <h2>Comprehensive Medical Services</h2>
            <p>We provide a wide range of specialized healthcare solutions tailored to meet the unique needs of every patient.</p>
        </div>

        <div class="vh-grid-3">
            <div class="vh-card">
                <div class="vh-card-img">
                    <img class="cover" src="<?php echo $img; ?>/IMG_5.webp" alt="Cardiology">
                    <div class="vh-card-icon"><img src="<?php echo $img; ?>/IMG_6.svg" alt=""></div>
                </div>
                <div class="vh-card-body">
                    <h3>Cardiology</h3>
                    <p>Advanced heart care including diagnostics, non-invasive treatments, and surgical interventions by top specialists.</p>
                    <a class="vh-btn-ghost" href="/bestcare-hospital/services.php">Learn More <img src="<?php echo $img; ?>/IMG_7.svg" alt=""></a>
                </div>
            </div>

            <div class="vh-card">
                <div class="vh-card-img">
                    <img class="cover" src="<?php echo $img; ?>/IMG_8.webp" alt="Pediatrics">
                    <div class="vh-card-icon"><img src="<?php echo $img; ?>/IMG_9.svg" alt=""></div>
                </div>
                <div class="vh-card-body">
                    <h3>Pediatrics</h3>
                    <p>Dedicated care for your little ones in a warm, friendly environment designed to make healthcare stress-free for kids.</p>
                    <a class="vh-btn-ghost" href="/bestcare-hospital/services.php">Learn More <img src="<?php echo $img; ?>/IMG_7.svg" alt=""></a>
                </div>
            </div>

            <div class="vh-card">
                <div class="vh-card-img">
                    <img class="cover" src="<?php echo $img; ?>/IMG_10.webp" alt="Surgery">
                    <div class="vh-card-icon"><img src="<?php echo $img; ?>/IMG_11.svg" alt=""></div>
                </div>
                <div class="vh-card-body">
                    <h3>Advanced Surgery</h3>
                    <p>State-of-the-art surgical suites equipped with the latest robotic and minimally invasive medical technology.</p>
                    <a class="vh-btn-ghost" href="/bestcare-hospital/services.php">Learn More <img src="<?php echo $img; ?>/IMG_7.svg" alt=""></a>
                </div>
            </div>
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

<!-- Doctors -->
<section class="vh-section vh-section-muted">
    <div class="home-wrap">
        <div class="vh-section-head">
            <h2>Meet Our Medical Experts</h2>
            <p>Our world-class doctors bring years of experience and specialized knowledge to ensure you receive the highest quality of care.</p>
        </div>

        <div class="vh-grid-3">
            <div class="vh-doc-card">
                <div class="vh-doc-photo"><img src="<?php echo $img; ?>/IMG_17.webp" alt="Dr. James Wilson"></div>
                <div class="vh-doc-badge"><span>Senior Cardiologist</span></div>
                <h3>Dr. James Wilson</h3>
                <p class="exp">15+ Years Experience</p>
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

            <div class="vh-doc-card">
                <div class="vh-doc-photo"><img src="<?php echo $img; ?>/IMG_19.webp" alt="Dr. Sarah Mitchell"></div>
                <div class="vh-doc-badge"><span>Pediatric Surgeon</span></div>
                <h3>Dr. Sarah Mitchell</h3>
                <p class="exp">12+ Years Experience</p>
                <div class="vh-stars">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <span>4.8</span>
                </div>
                <a class="vh-btn-doc" href="/bestcare-hospital/doctors.php">View Profile</a>
            </div>

            <div class="vh-doc-card">
                <div class="vh-doc-photo"><img src="<?php echo $img; ?>/IMG_20.webp" alt="Dr. David Chen"></div>
                <div class="vh-doc-badge"><span>Neurologist</span></div>
                <h3>Dr. David Chen</h3>
                <p class="exp">18+ Years Experience</p>
                <div class="vh-stars">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <img src="<?php echo $img; ?>/IMG_18.svg" alt="">
                    <span>5</span>
                </div>
                <a class="vh-btn-doc" href="/bestcare-hospital/doctors.php">View Profile</a>
            </div>
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

<!-- Footer -->
<footer class="vh-footer">
    <div class="home-wrap">
        <div class="vh-footer-grid">
            <div>
                <h4>Contact Us</h4>
                <ul class="vh-footer-list">
                    <li>
                        <img src="<?php echo $img; ?>/IMG_21.svg" alt="">
                        <span>Main Street, Matara, Sri Lanka</span>
                    </li>
                    <li>
                        <img src="<?php echo $img; ?>/IMG_26.svg" alt="">
                        <span>041-2223344</span>
                    </li>
                    <li>
                        <img src="<?php echo $img; ?>/IMG_27.svg" alt="">
                        <span>info@bestcarehospital.lk</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4>Quick Links</h4>
                <div class="vh-footer-links">
                    <a href="<?php echo $book_link; ?>">Appointments</a>
                    <a href="/bestcare-hospital/auth/login.php">Patient Portal</a>
                    <a href="/bestcare-hospital/doctors.php">Find a Doctor</a>
                    <a href="/bestcare-hospital/contact.php">Contact</a>
                </div>
            </div>

            <div>
                <h4>Hours</h4>
                <div class="vh-hours-row"><span>Mon - Fri</span><span>8:00 AM - 8:00 PM</span></div>
                <div class="vh-hours-row"><span>Sat - Sun</span><span>9:00 AM - 5:00 PM</span></div>
                <div class="vh-hours-note">
                    <img src="<?php echo $img; ?>/IMG_16.svg" alt="">
                    <span>Emergency services available 24/7</span>
                </div>
            </div>

            <div>
                <h4>Follow Us</h4>
                <div class="vh-social">
                    <a href="#"><img src="<?php echo $img; ?>/IMG_22.svg" alt=""></a>
                    <a href="#"><img src="<?php echo $img; ?>/IMG_23.svg" alt=""></a>
                    <a href="#"><img src="<?php echo $img; ?>/IMG_24.svg" alt=""></a>
                    <a href="#"><img src="<?php echo $img; ?>/IMG_25.svg" alt=""></a>
                </div>
            </div>
        </div>

        <div class="vh-footer-bottom">
            <p>&copy; 2026 BestCare Hospital. All rights reserved. | Privacy Policy | Terms of Service</p>
        </div>
    </div>
</footer>

<script src="/bestcare-hospital/assets/js/main.js?v=5"></script>
</body>
</html>
