<?php
include("includes/db.php");

$page_title = "Services - BestCare Hospital";
include("includes/header.php");

// Get all services with department name (lecture style JOIN)
$sql = "SELECT s.*, d.name AS department_name
        FROM services s, departments d
        WHERE s.department_id = d.id
        ORDER BY s.name";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<div class="page-wrap">
    <h2>Our Medical Services</h2>
    <p>BestCare Hospital offers general consultations, specialist care, laboratory services, and emergency care in Matara.</p>

    <div class="card-grid">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                echo "<div class='card'>";
                echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                echo "<p class='card-meta'>" . htmlspecialchars($row['department_name']) . "</p>";
                echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                echo "<p><strong>Fee: Rs. " . number_format($row['fee'], 2) . "</strong></p>";
                echo "</div>";
            }
        } else {
            echo "<p>No services found.</p>";
        }
        ?>
    </div>
</div>

<?php
mysqli_close($conn);
include("includes/footer.php");
?>
