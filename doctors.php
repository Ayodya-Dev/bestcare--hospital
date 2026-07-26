<?php
include("includes/db.php");
include("includes/public_session.php");

$doc_img = "/bestcare-hospital/assets/doctors";
$fallback_photos = array(
    $doc_img . "/IMG_3.webp",
    $doc_img . "/IMG_9.webp",
    $doc_img . "/IMG_10.webp"
);

// Active doctors only
$sql = "SELECT st.*, d.name AS department_name, u.username
        FROM staff st, departments d, users u
        WHERE st.department_id = d.id
        AND st.user_id = u.id
        AND u.role = 'staff'
        AND u.is_active = 1
        AND (st.staff_type = 'Doctor' OR st.staff_type IS NULL OR st.staff_type = '')
        ORDER BY st.full_name";
$result = mysqli_query($conn, $sql);

if (!$result) {
    // Fallback if staff_type column missing
    $sql = "SELECT st.*, d.name AS department_name, u.username
            FROM staff st, departments d, users u
            WHERE st.department_id = d.id
            AND st.user_id = u.id
            AND u.role = 'staff'
            AND u.is_active = 1
            ORDER BY st.full_name";
    $result = mysqli_query($conn, $sql);
}

if (!$result) {
    bestcare_fail_page("Could not load doctors right now. Please try again later.");
}

$doctors = array();
while ($row = mysqli_fetch_array($result)) {
    $doctors[] = $row;
}
$total = count($doctors);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - BestCare Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/home.css?v=1">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/doctors.css?v=1">
</head>
<body class="home-body">

<?php include __DIR__ . "/includes/public_header.php"; ?>

<!-- Intro -->
<section class="doc-intro">
    <div class="home-wrap">
        <div class="doc-intro-row">
            <div class="doc-intro-copy">
                <span class="doc-badge">Excellence in Care</span>
                <h1>Our Doctors</h1>
                <p>Meet our experienced medical staff at BestCare Hospital, Matara. Our team of specialists is dedicated to providing high-quality healthcare services tailored to your individual needs.</p>
            </div>
            <div class="doc-search-box">
                <div class="doc-search-label">
                    <img src="<?php echo $doc_img; ?>/IMG_2.svg" alt="">
                    <span>Find a Specialist</span>
                </div>
                <input type="text" id="docSearch" placeholder="Search by name or department..." autocomplete="off">
            </div>
        </div>
    </div>
</section>

<!-- Medical Staff -->
<section class="doc-section">
    <div class="home-wrap">
        <div class="doc-section-head">
            <div class="doc-section-title">
                <h2>Medical Staff</h2>
            </div>
            <span class="doc-count" id="docCount">Showing <?php echo $total; ?> result<?php echo $total == 1 ? '' : 's'; ?></span>
        </div>

        <div class="doc-grid" id="docGrid">
            <?php
            if ($total > 0) {
                for ($i = 0; $i < $total; $i++) {
                    $row = $doctors[$i];

                    if (isset($row['image_path']) && $row['image_path'] != '') {
                        $photo = $row['image_path'];
                    } else {
                        $photo = $fallback_photos[$i % count($fallback_photos)];
                    }

                    $bio = "Specializing in " . $row['specialization'] . " with a focus on compassionate, patient-centred care.";
                    ?>
                    <div class="doc-card"
                         data-name="<?php echo htmlspecialchars(strtolower($row['full_name'])); ?>"
                         data-dept="<?php echo htmlspecialchars(strtolower($row['department_name'])); ?>"
                         data-spec="<?php echo htmlspecialchars(strtolower($row['specialization'])); ?>">
                        <div class="doc-card-photo">
                            <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($row['full_name']); ?>">
                            <span class="doc-card-dept"><?php echo htmlspecialchars($row['department_name']); ?></span>
                        </div>
                        <div class="doc-card-body">
                            <h3><?php echo htmlspecialchars($row['full_name']); ?></h3>
                            <div class="doc-card-spec">
                                <img src="<?php echo $doc_img; ?>/IMG_4.svg" alt="">
                                <span><?php echo htmlspecialchars($row['specialization']); ?></span>
                            </div>
                            <p class="doc-card-bio">"<?php echo htmlspecialchars($bio); ?>"</p>
                        </div>
                        <div class="doc-card-actions">
                            <a class="doc-btn-book" href="<?php echo $book_link; ?>">Book Appointment</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="doc-empty">No doctors available right now.</div>';
            }
            ?>
            <div class="doc-empty" id="docNoMatch" style="display:none;">No doctors match your search.</div>
        </div>

        <div class="doc-trust">
            <div class="doc-trust-item">
                <div class="doc-trust-icon"><img src="<?php echo $doc_img; ?>/IMG_11.svg" alt=""></div>
                <div>
                    <strong>Accredited Experts</strong>
                    <p>All our doctors are board-certified and hold advanced clinical degrees.</p>
                </div>
            </div>
            <div class="doc-trust-item">
                <div class="doc-trust-icon"><img src="<?php echo $doc_img; ?>/IMG_12.svg" alt=""></div>
                <div>
                    <strong>Modern Equipment</strong>
                    <p>Our specialists utilize state-of-the-art diagnostic and surgical technology.</p>
                </div>
            </div>
            <div class="doc-trust-item">
                <div class="doc-trust-icon"><img src="<?php echo $doc_img; ?>/IMG_13.svg" alt=""></div>
                <div>
                    <strong>Accessible Location</strong>
                    <p>Conveniently located in the heart of Matara for easy patient access.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . "/includes/public_footer.php"; ?>

<script>
(function () {
    var input = document.getElementById('docSearch');
    var cards = document.querySelectorAll('.doc-card');
    var countEl = document.getElementById('docCount');
    var noMatch = document.getElementById('docNoMatch');

    function applyFilter() {
        var term = (input.value || '').toLowerCase().trim();
        var shown = 0;
        for (var i = 0; i < cards.length; i++) {
            var c = cards[i];
            var hay = c.getAttribute('data-name') + ' ' + c.getAttribute('data-dept') + ' ' + c.getAttribute('data-spec');
            if (term === '' || hay.indexOf(term) !== -1) {
                c.style.display = '';
                shown++;
            } else {
                c.style.display = 'none';
            }
        }
        if (countEl) {
            countEl.textContent = 'Showing ' + shown + ' result' + (shown === 1 ? '' : 's');
        }
        if (noMatch) {
            noMatch.style.display = (shown === 0 && cards.length > 0) ? 'block' : 'none';
        }
    }

    if (input) {
        input.addEventListener('keyup', applyFilter);
        input.addEventListener('input', applyFilter);
    }
})();
</script>

<?php mysqli_close($conn); ?>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
