<?php
// Shared public website footer (used by Home, Services, etc.)
if (!isset($pub_img)) {
    $pub_img = "/bestcare-hospital/assets/home";
}
if (!isset($book_link)) {
    $book_link = "/bestcare-hospital/auth/login.php";
}
?>
<!-- Footer -->
<footer class="vh-footer">
    <div class="home-wrap">
        <div class="vh-footer-grid">
            <div>
                <h4>Contact Us</h4>
                <ul class="vh-footer-list">
                    <li>
                        <img src="<?php echo $pub_img; ?>/IMG_21.svg" alt="">
                        <span>Main Street, Matara, Sri Lanka</span>
                    </li>
                    <li>
                        <img src="<?php echo $pub_img; ?>/IMG_26.svg" alt="">
                        <span>041-2223344</span>
                    </li>
                    <li>
                        <img src="<?php echo $pub_img; ?>/IMG_27.svg" alt="">
                        <span>info@bestcarehospital.lk</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4>Quick Links</h4>
                <div class="vh-footer-links">
                    <a href="<?php echo $book_link; ?>">Appointments</a>
                    <a href="/bestcare-hospital/auth/login.php">Patient Portal</a>
                    <a href="/bestcare-hospital/departments.php">Departments</a>
                    <a href="/bestcare-hospital/doctors.php">Find a Doctor</a>
                    <a href="/bestcare-hospital/contact.php">Contact</a>
                </div>
            </div>

            <div>
                <h4>Hours</h4>
                <div class="vh-hours-row"><span>Mon - Fri</span><span>8:00 AM - 8:00 PM</span></div>
                <div class="vh-hours-row"><span>Sat - Sun</span><span>9:00 AM - 5:00 PM</span></div>
                <div class="vh-hours-note">
                    <img src="<?php echo $pub_img; ?>/IMG_16.svg" alt="">
                    <span>Emergency services available 24/7</span>
                </div>
            </div>

            <div>
                <h4>Follow Us</h4>
                <div class="vh-social">
                    <a href="#"><img src="<?php echo $pub_img; ?>/IMG_22.svg" alt=""></a>
                    <a href="#"><img src="<?php echo $pub_img; ?>/IMG_23.svg" alt=""></a>
                    <a href="#"><img src="<?php echo $pub_img; ?>/IMG_24.svg" alt=""></a>
                    <a href="#"><img src="<?php echo $pub_img; ?>/IMG_25.svg" alt=""></a>
                </div>
            </div>
        </div>

        <div class="vh-footer-bottom">
            <p>&copy; 2026 BestCare Hospital. All rights reserved. | Privacy Policy | Terms of Service</p>
        </div>
    </div>
</footer>
