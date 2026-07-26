<?php
include("includes/db.php");
include("includes/public_session.php");

$home_img = "/bestcare-hospital/assets/home";
$search_term = "";
$services_result = null;
$doctors_result = null;
$departments_result = null;
$did_search = false;

if (isset($_GET['search_term']) && strlen(trim($_GET['search_term'])) > 0) {
    $did_search = true;
    $search_term = trim($_GET['search_term']);
    $safe = mysqli_real_escape_string($conn, $search_term);

    $sql_services = "SELECT s.*, d.name AS department_name
                     FROM services s, departments d
                     WHERE s.department_id = d.id
                     AND (s.name LIKE '%$safe%' OR s.description LIKE '%$safe%' OR d.name LIKE '%$safe%')";
    $services_result = mysqli_query($conn, $sql_services);

    $sql_doctors = "SELECT st.*, d.name AS department_name
                    FROM staff st, departments d, users u
                    WHERE st.department_id = d.id
                    AND st.user_id = u.id
                    AND u.role = 'staff'
                    AND u.is_active = 1
                    AND (st.full_name LIKE '%$safe%' OR st.specialization LIKE '%$safe%' OR d.name LIKE '%$safe%')";
    $doctors_result = mysqli_query($conn, $sql_doctors);

    $sql_departments = "SELECT * FROM departments
                        WHERE name LIKE '%$safe%' OR description LIKE '%$safe%'";
    $departments_result = mysqli_query($conn, $sql_departments);
}

$popular = array("Cardiology", "Pediatrics", "Emergency", "Laboratory", "General Consultation");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/home.css?v=1">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/search.css?v=1">
</head>
<body class="home-body">

<?php include __DIR__ . "/includes/public_header.php"; ?>

<section class="sr-page">
    <div class="home-wrap">
        <div class="sr-head">
            <h1>Search</h1>
            <p>Search for services, doctors, or departments.</p>
        </div>

        <form method="get" action="search.php" class="sr-form-row" id="searchForm">
            <input class="sr-input" type="text" name="search_term" id="search_term"
                   placeholder="Type a keyword..."
                   value="<?php echo htmlspecialchars($search_term); ?>" required>
            <button type="submit" class="sr-btn">
                <img src="<?php echo $home_img; ?>/IMG_2.svg" alt="">
                Search
            </button>
        </form>

        <div class="sr-popular">
            <span class="sr-popular-label">Popular:</span>
            <?php for ($i = 0; $i < count($popular); $i++) {
                $tag = $popular[$i];
                $href = "search.php?search_term=" . urlencode($tag);
                echo '<a class="sr-tag" href="' . htmlspecialchars($href) . '">' . htmlspecialchars($tag) . '</a>';
            } ?>
        </div>

        <hr class="sr-divider">

        <?php if (!$did_search) { ?>
            <div class="sr-features">
                <a class="sr-feature" href="/bestcare-hospital/doctors.php">
                    <div class="sr-feature-icon"><img src="<?php echo $home_img; ?>/IMG_2.svg" alt=""></div>
                    <h3>Find a Doctor</h3>
                    <p>Browse our network of world-class medical specialists.</p>
                </a>
                <a class="sr-feature" href="/bestcare-hospital/services.php">
                    <div class="sr-feature-icon"><img src="<?php echo $home_img; ?>/IMG_2.svg" alt=""></div>
                    <h3>Our Services</h3>
                    <p>Explore the wide range of treatments and diagnostic tools.</p>
                </a>
                <a class="sr-feature" href="<?php echo $book_link; ?>">
                    <div class="sr-feature-icon"><img src="<?php echo $home_img; ?>/IMG_2.svg" alt=""></div>
                    <h3>Medical Reports</h3>
                    <p>Securely access your records and lab results online.</p>
                </a>
            </div>
        <?php } else { ?>
            <div class="sr-results">
                <p class="sr-summary">Results for <strong><?php echo htmlspecialchars($search_term); ?></strong></p>

                <div class="sr-group">
                    <h2 class="sr-section-title">Services</h2>
                    <div class="sr-cards">
                        <?php
                        if ($services_result && mysqli_num_rows($services_result) > 0) {
                            while ($row = mysqli_fetch_array($services_result)) {
                                echo '<div class="sr-card">';
                                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                                echo '<p class="meta">' . htmlspecialchars($row['department_name']) . '</p>';
                                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                                echo '<p class="fee">Fee: Rs. ' . number_format($row['fee'], 2) . '</p>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="sr-empty">No matching services.</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="sr-group">
                    <h2 class="sr-section-title">Doctors</h2>
                    <div class="sr-cards">
                        <?php
                        if ($doctors_result && mysqli_num_rows($doctors_result) > 0) {
                            while ($row = mysqli_fetch_array($doctors_result)) {
                                echo '<div class="sr-card">';
                                echo '<h3>' . htmlspecialchars($row['full_name']) . '</h3>';
                                echo '<p class="meta">' . htmlspecialchars($row['specialization']) . '</p>';
                                echo '<p>Department: ' . htmlspecialchars($row['department_name']) . '</p>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="sr-empty">No matching doctors.</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="sr-group">
                    <h2 class="sr-section-title">Departments</h2>
                    <div class="sr-cards">
                        <?php
                        if ($departments_result && mysqli_num_rows($departments_result) > 0) {
                            while ($row = mysqli_fetch_array($departments_result)) {
                                echo '<a class="sr-card" href="/bestcare-hospital/departments.php">';
                                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                                echo '</a>';
                            }
                        } else {
                            echo '<p class="sr-empty">No matching departments.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

<?php include __DIR__ . "/includes/public_footer.php"; ?>

<?php mysqli_close($conn); ?>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
