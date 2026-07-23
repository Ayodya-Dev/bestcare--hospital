<?php
include("includes/db.php");

$page_title = "Search - BestCare Hospital";
include("includes/header.php");

$search_term = "";
$services_result = null;
$doctors_result = null;
$departments_result = null;

if (isset($_GET['search_term']) && strlen(trim($_GET['search_term'])) > 0) {
    $search_term = trim($_GET['search_term']);
    // Escape for safety in lecture-style query
    $safe = mysqli_real_escape_string($conn, $search_term);

    $sql_services = "SELECT s.*, d.name AS department_name
                     FROM services s, departments d
                     WHERE s.department_id = d.id
                     AND (s.name LIKE '%$safe%' OR s.description LIKE '%$safe%')";
    $services_result = mysqli_query($conn, $sql_services);

    $sql_doctors = "SELECT st.*, d.name AS department_name
                    FROM staff st, departments d
                    WHERE st.department_id = d.id
                    AND (st.full_name LIKE '%$safe%' OR st.specialization LIKE '%$safe%')";
    $doctors_result = mysqli_query($conn, $sql_doctors);

    $sql_departments = "SELECT * FROM departments
                        WHERE name LIKE '%$safe%' OR description LIKE '%$safe%'";
    $departments_result = mysqli_query($conn, $sql_departments);
}
?>

<div class="page-wrap">
    <h2>Search</h2>
    <p>Search for services, doctors, or departments.</p>

    <form method="get" action="search.php" class="search-form" id="searchForm">
        <input type="text" name="search_term" id="search_term"
               placeholder="Type a keyword..."
               value="<?php echo htmlspecialchars($search_term); ?>" required>
        <button type="submit" class="btn-primary search-btn">Search</button>
    </form>

    <?php if ($search_term != "") { ?>

        <h3 class="result-heading">Services</h3>
        <div class="card-grid">
            <?php
            if ($services_result && mysqli_num_rows($services_result) > 0) {
                while ($row = mysqli_fetch_array($services_result)) {
                    echo "<div class='card'>";
                    echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                    echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                    echo "<p><strong>Fee: Rs. " . number_format($row['fee'], 2) . "</strong></p>";
                    echo "</div>";
                }
            } else {
                echo "<p>No matching services.</p>";
            }
            ?>
        </div>

        <h3 class="result-heading">Doctors</h3>
        <div class="card-grid">
            <?php
            if ($doctors_result && mysqli_num_rows($doctors_result) > 0) {
                while ($row = mysqli_fetch_array($doctors_result)) {
                    echo "<div class='card'>";
                    echo "<h3>" . htmlspecialchars($row['full_name']) . "</h3>";
                    echo "<p>" . htmlspecialchars($row['specialization']) . "</p>";
                    echo "<p>Department: " . htmlspecialchars($row['department_name']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>No matching doctors.</p>";
            }
            ?>
        </div>

        <h3 class="result-heading">Departments</h3>
        <div class="card-grid">
            <?php
            if ($departments_result && mysqli_num_rows($departments_result) > 0) {
                while ($row = mysqli_fetch_array($departments_result)) {
                    echo "<div class='card'>";
                    echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                    echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>No matching departments.</p>";
            }
            ?>
        </div>

    <?php } ?>
</div>

<?php
mysqli_close($conn);
include("includes/footer.php");
?>
