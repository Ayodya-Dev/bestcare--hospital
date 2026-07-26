<?php
include("includes/db.php");
include("includes/public_session.php");

$sv_img = "/bestcare-hospital/assets/services";
$home_img = "/bestcare-hospital/assets/home";

$col_check = mysqli_query($conn, "SHOW COLUMNS FROM departments LIKE 'image_path'");
if (!$col_check || mysqli_num_rows($col_check) == 0) {
    mysqli_query($conn, "ALTER TABLE departments ADD COLUMN image_path VARCHAR(255) NULL");
}

$sql = "SELECT * FROM departments ORDER BY name";
$result = mysqli_query($conn, $sql);

if (!$result) {
    bestcare_fail_page("Could not load departments right now. Please try again later.");
}

$departments = array();
while ($row = mysqli_fetch_array($result)) {
    $departments[] = $row;
}

$fallback_images = array(
    $sv_img . "/IMG_12.webp",
    $sv_img . "/IMG_8.webp",
    $sv_img . "/IMG_10.webp",
    $sv_img . "/IMG_5.webp",
    $sv_img . "/IMG_14.webp"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/home.css?v=1">
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/departments.css?v=1">
</head>
<body class="home-body">

<?php include __DIR__ . "/includes/public_header.php"; ?>

<section class="dp-hero">
    <div class="dp-hero-bg">
        <img src="<?php echo $sv_img; ?>/IMG_2.webp" alt="Hospital departments">
        <div class="dp-hero-overlay"></div>
    </div>
    <div class="dp-hero-content">
        <div class="dp-hero-badge">
            <img src="<?php echo $sv_img; ?>/IMG_3.svg" alt="">
            <span>Specialist Care Units</span>
        </div>
        <h1>Our Hospital<br><span class="accent">Departments</span></h1>
        <p class="dp-hero-text">
            Explore the clinical departments at BestCare Hospital Matara — from general medicine to emergency and specialist care.
        </p>
    </div>
</section>

<section class="dp-section">
    <div class="home-wrap">
        <div class="dp-section-head">
            <span class="dp-eyebrow">Care Units</span>
            <h2>Departments at BestCare</h2>
            <p>Each department is staffed by experienced professionals and linked to the services patients can book online.</p>
        </div>

        <?php if (count($departments) > 0) { ?>
            <div class="dp-grid">
                <?php for ($i = 0; $i < count($departments); $i++) {
                    $d = $departments[$i];
                    if (isset($d['image_path']) && $d['image_path'] != '') {
                        $photo = $d['image_path'];
                    } else {
                        $photo = $fallback_images[$i % count($fallback_images)];
                    }
                    $desc = $d['description'];
                    if (strlen($desc) > 160) {
                        $desc = substr($desc, 0, 157) . "...";
                    }
                ?>
                    <article class="dp-card">
                        <div class="dp-card-img">
                            <img class="cover" src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($d['name']); ?>">
                        </div>
                        <div class="dp-card-body">
                            <h3><?php echo htmlspecialchars($d['name']); ?></h3>
                            <p><?php echo htmlspecialchars($desc); ?></p>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="dp-empty">No departments published yet.</div>
        <?php } ?>
    </div>
</section>

<?php include __DIR__ . "/includes/public_footer.php"; ?>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
