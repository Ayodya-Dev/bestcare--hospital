<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'patient') {
    echo "Access denied. Patients only.";
    exit();
}

$error = "";
$success = "";

// Get patient id for logged-in user
$user_id = $_SESSION['user_id'];
$p_sql = "SELECT * FROM patients WHERE user_id=$user_id";
$p_result = mysqli_query($conn, $p_sql);

if (!$p_result || mysqli_num_rows($p_result) == 0) {
    die("Patient profile not found.");
}

$patient = mysqli_fetch_array($p_result);
$patient_id = $patient['id'];

// Load services and doctors for dropdowns
$services = mysqli_query($conn, "SELECT * FROM services ORDER BY name");
$doctors = mysqli_query($conn, "SELECT st.id, st.full_name, st.specialization, d.name AS department_name
                                FROM staff st, departments d
                                WHERE st.department_id = d.id
                                ORDER BY st.full_name");

if (isset($_POST['book_submit'])) {
    $service_id = $_POST['service_id'];
    $staff_id = $_POST['staff_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    if (strlen($service_id) == 0 || strlen($staff_id) == 0 ||
        strlen($appointment_date) == 0 || strlen($appointment_time) == 0) {
        $error = "Need to fill all the fields";
    } elseif ($appointment_date < date('Y-m-d')) {
        $error = "Appointment date cannot be in the past";
    } else {
        // Check if same doctor already booked at that date/time
        $check_sql = "SELECT * FROM appointments
                      WHERE staff_id=$staff_id
                      AND appointment_date='$appointment_date'
                      AND appointment_time='$appointment_time'
                      AND status != 'Cancelled'";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = "This time slot is already booked. Please choose another time.";
        } else {
            $sql = "INSERT INTO appointments (patient_id, staff_id, service_id, appointment_date, appointment_time, status)
                    VALUES ($patient_id, $staff_id, $service_id, '$appointment_date', '$appointment_time', 'Pending')";
            $result = mysqli_query($conn, $sql);

            if (!$result) {
                die("Could not book appointment: " . mysqli_error($conn));
            }

            $success = "Appointment booked successfully! Status: Pending";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/style.css?v=6">
</head>
<body>
<?php include("../includes/patient_nav.php"); ?>

<div class="dash-wrap">
    <h2>Book Appointment</h2>
    <p>Hello <?php echo htmlspecialchars($patient['full_name']); ?>, choose a service and preferred time.</p>

    <?php if ($error != "") { ?>
        <div class="error-msg"><span><?php echo $error; ?></span></div>
    <?php } ?>
    <?php if ($success != "") { ?>
        <div class="success-msg"><?php echo $success; ?></div>
        <p><a href="my-appointments.php">View My Appointments</a></p>
    <?php } ?>

    <div class="auth-card" style="max-width:560px;margin-top:20px;">
        <div class="auth-card-body">
            <form method="post" action="" id="bookForm">
                <label for="service_id">Service</label>
                <div class="input-wrap no-icon">
                    <select name="service_id" id="service_id" required>
                        <option value="">Select service</option>
                        <?php
                        while ($s = mysqli_fetch_array($services)) {
                            echo '<option value="' . $s['id'] . '">' .
                                 htmlspecialchars($s['name']) . ' - Rs. ' . number_format($s['fee'], 2) .
                                 '</option>';
                        }
                        ?>
                    </select>
                </div>

                <label for="staff_id">Doctor</label>
                <div class="input-wrap no-icon">
                    <select name="staff_id" id="staff_id" required>
                        <option value="">Select doctor</option>
                        <?php
                        while ($d = mysqli_fetch_array($doctors)) {
                            echo '<option value="' . $d['id'] . '">' .
                                 htmlspecialchars($d['full_name']) . ' (' . htmlspecialchars($d['specialization']) . ')' .
                                 '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment_date">Date</label>
                        <div class="input-wrap no-icon">
                            <input type="date" name="appointment_date" id="appointment_date"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="appointment_time">Time</label>
                        <div class="input-wrap no-icon">
                            <select name="appointment_time" id="appointment_time" required>
                                <option value="">Select time</option>
                                <option value="09:00:00">09:00 AM</option>
                                <option value="09:30:00">09:30 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="10:30:00">10:30 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="11:30:00">11:30 AM</option>
                                <option value="14:00:00">02:00 PM</option>
                                <option value="14:30:00">02:30 PM</option>
                                <option value="15:00:00">03:00 PM</option>
                                <option value="15:30:00">03:30 PM</option>
                                <option value="16:00:00">04:00 PM</option>
                                <option value="16:30:00">04:30 PM</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" name="book_submit" value="1" class="btn-primary">Book Appointment</button>
            </form>
        </div>
    </div>
</div>

<script src="/bestcare-hospital/assets/js/main.js?v=6"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
