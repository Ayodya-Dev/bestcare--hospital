<?php
// Shared error helpers — keep SQL details off the screen, log them instead.

if (!function_exists('bestcare_db_error')) {

function bestcare_db_error($conn, $user_message = "Something went wrong. Please try again.") {
    if ($conn) {
        $detail = mysqli_error($conn);
        if ($detail != "") {
            error_log("BestCare DB error: " . $detail);
        }
    }
    return $user_message;
}

function bestcare_fail_page($message) {
    $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Error - BestCare Hospital</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#F8FAF9;margin:0;padding:40px;color:#1D1F23;}';
    echo '.box{max-width:480px;margin:60px auto;background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:28px;}';
    echo 'h1{font-size:1.25rem;margin:0 0 10px;color:#B91C1C;}p{margin:0 0 18px;line-height:1.5;color:#4B5563;}';
    echo 'a{color:#016450;font-weight:600;text-decoration:none;}a:hover{text-decoration:underline;}</style></head><body>';
    echo '<div class="box"><h1>Unable to continue</h1><p>' . $safe . '</p>';
    echo '<p><a href="/bestcare-hospital/index.php">Back to Home</a></p></div></body></html>';
    exit();
}

} // function_exists
?>
