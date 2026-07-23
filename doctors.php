<?php
include("includes/db.php");

$page_title = "Doctors - BestCare Hospital";
include("includes/header.php");

// Get doctors/staff with department
$sql = "SELECT st.*, d.name AS department_name
        FROM staff st, departments d
        WHERE st.department_id = d.id
        ORDER BY st.full_name";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<div class="page-wrap">
    <h2>Our Doctors</h2>
    <p>Meet our experienced medical staff at BestCare Hospital, Matara.</p>

    <div class="card-grid">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                echo "<div class='card'>";
                echo "<h3>" . htmlspecialchars($row['full_name']) . "</h3>";
                echo "<p class='card-meta'>" . htmlspecialchars($row['specialization']) . "</p>";
                echo "<p>Department: " . htmlspecialchars($row['department_name']) . "</p>";
                echo "<p>Contact: " . htmlspecialchars($row['contact']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No doctors found.</p>";
        }
        ?>
    </div>
</div>

<?php
mysqli_close($conn);
include("includes/footer.php");
?>
