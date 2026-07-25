<?php
// Separate sessions so patient + doctor can stay logged in in two tabs.
// Cookie path is "/" so refresh keeps the login.

if (!function_exists('bestcare_session_role_from_path')) {

function bestcare_session_role_from_path() {
    $parts = array();

    if (!empty($_SERVER['SCRIPT_FILENAME'])) {
        $parts[] = str_replace("\\", "/", $_SERVER['SCRIPT_FILENAME']);
    }
    if (!empty($_SERVER['SCRIPT_NAME'])) {
        $parts[] = str_replace("\\", "/", $_SERVER['SCRIPT_NAME']);
    }
    if (!empty($_SERVER['PHP_SELF'])) {
        $parts[] = str_replace("\\", "/", $_SERVER['PHP_SELF']);
    }
    if (!empty($_SERVER['REQUEST_URI'])) {
        $parts[] = str_replace("\\", "/", $_SERVER['REQUEST_URI']);
    }

    $haystack = strtolower(implode(" ", $parts));

    // Match folder name as a path segment: /patient/ or /patient at end
    if (preg_match('#(^|/)patient(/|$)#', $haystack)) {
        return "patient";
    }
    if (preg_match('#(^|/)staff(/|$)#', $haystack)) {
        return "staff";
    }
    if (preg_match('#(^|/)admin(/|$)#', $haystack)) {
        return "admin";
    }

    return "public";
}

function bestcare_session_name_for_role($role) {
    if ($role == "patient") {
        return "BC_PATIENT";
    }
    if ($role == "staff") {
        return "BC_STAFF";
    }
    if ($role == "admin") {
        return "BC_ADMIN";
    }
    return "BC_PUBLIC";
}

function bestcare_start_session($role = "") {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (strlen($role) == 0) {
        $role = bestcare_session_role_from_path();
    }

    // Important: cookie available for whole site so refresh keeps login
    if (function_exists('session_set_cookie_params')) {
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params(array(
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ));
        } else {
            session_set_cookie_params(0, '/');
        }
    }

    session_name(bestcare_session_name_for_role($role));
    session_start();
}

} // function_exists guard
?>
