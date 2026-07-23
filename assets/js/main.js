// Client-side form validation (login + register + reset)
document.addEventListener("DOMContentLoaded", function () {

    // Login role tabs (Patient / Staff Member)
    var tabPatient = document.getElementById("tabPatient");
    var tabStaff = document.getElementById("tabStaff");
    var forgotLink = document.getElementById("forgotLink");
    var usernameLabel = document.getElementById("usernameLabel");
    var usernameInput = document.getElementById("username");

    function setRoleTab(role) {
        if (!tabPatient || !tabStaff) return;

        if (role === "patient") {
            tabPatient.classList.add("active");
            tabStaff.classList.remove("active");
            if (forgotLink) forgotLink.classList.remove("hidden");
            if (usernameLabel) usernameLabel.textContent = "Username";
            if (usernameInput) usernameInput.placeholder = "name@example.com";
        } else {
            tabStaff.classList.add("active");
            tabPatient.classList.remove("active");
            if (forgotLink) forgotLink.classList.add("hidden");
            if (usernameLabel) usernameLabel.textContent = "Username";
            if (usernameInput) usernameInput.placeholder = "staff username";
        }
    }

    if (tabPatient) {
        tabPatient.addEventListener("click", function () {
            setRoleTab("patient");
        });
    }
    if (tabStaff) {
        tabStaff.addEventListener("click", function () {
            setRoleTab("staff");
        });
    }

    // Login form
    var loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            var username = document.getElementById("username").value.trim();
            var password = document.getElementById("password").value.trim();

            if (username === "" || password === "") {
                e.preventDefault();
                alert("Please fill all the fields");
            }
        });
    }

    // Show / hide password (login)
    var toggleBtn = document.getElementById("togglePassword");
    var passwordInput = document.getElementById("password");
    if (toggleBtn && passwordInput && document.getElementById("loginForm")) {
        toggleBtn.addEventListener("click", function () {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        });
    }

    // Show / hide password (register)
    var toggleRegBtn = document.getElementById("toggleRegPassword");
    var regPassword = document.getElementById("password");
    if (toggleRegBtn && regPassword) {
        toggleRegBtn.addEventListener("click", function () {
            if (regPassword.type === "password") {
                regPassword.type = "text";
            } else {
                regPassword.type = "password";
            }
        });
    }

    // Register form
    var registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            var username = document.getElementById("username").value.trim();
            var password = document.getElementById("password").value.trim();
            var fullName = document.getElementById("full_name").value.trim();
            var dob = document.getElementById("dob").value.trim();
            var gender = document.getElementById("gender").value.trim();
            var contact = document.getElementById("contact").value.trim();
            var address = document.getElementById("address").value.trim();

            if (username === "" || password === "" || fullName === "" ||
                dob === "" || gender === "" || contact === "" || address === "") {
                e.preventDefault();
                alert("Please fill all the fields");
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert("Password must be at least 8 characters");
                return;
            }

            var hasLetter = /[A-Za-z]/.test(password);
            var hasNumber = /[0-9]/.test(password);
            if (!hasLetter || !hasNumber) {
                e.preventDefault();
                alert("Password must have a mix of letters and numbers");
            }
        });
    }

    // Reset password form
    var resetForm = document.getElementById("resetForm");
    if (resetForm) {
        resetForm.addEventListener("submit", function (e) {
            var newPass = document.getElementById("new_password").value.trim();
            var confirmPass = document.getElementById("confirm_password").value.trim();

            if (newPass === "" || confirmPass === "") {
                e.preventDefault();
                alert("Please fill all the fields");
                return;
            }
            if (newPass.length < 8) {
                e.preventDefault();
                alert("Password must be at least 8 characters");
                return;
            }
            if (!/[A-Za-z]/.test(newPass) || !/[0-9]/.test(newPass)) {
                e.preventDefault();
                alert("Password must have a mix of letters and numbers");
                return;
            }
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert("Passwords do not match");
            }
        });
    }

    // Search form
    var searchForm = document.getElementById("searchForm");
    if (searchForm) {
        searchForm.addEventListener("submit", function (e) {
            var term = document.getElementById("search_term").value.trim();
            if (term === "") {
                e.preventDefault();
                alert("Please type a keyword to search");
            }
        });
    }

    // Contact query form
    var contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", function (e) {
            var subject = document.getElementById("subject").value.trim();
            var message = document.getElementById("message").value.trim();
            if (subject === "" || message === "") {
                e.preventDefault();
                alert("Please fill all the fields");
            }
        });
    }

    // Book appointment form
    var bookForm = document.getElementById("bookForm");
    if (bookForm) {
        bookForm.addEventListener("submit", function (e) {
            var service = document.getElementById("service_id").value;
            var doctor = document.getElementById("staff_id").value;
            var date = document.getElementById("appointment_date").value;
            var time = document.getElementById("appointment_time").value;

            if (service === "" || doctor === "" || date === "" || time === "") {
                e.preventDefault();
                alert("Please fill all the fields");
                return;
            }

            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var selected = new Date(date + "T00:00:00");
            if (selected < today) {
                e.preventDefault();
                alert("Appointment date cannot be in the past");
            }
        });
    }

    // Patient query form
    var patientQueryForm = document.getElementById("patientQueryForm");
    if (patientQueryForm) {
        patientQueryForm.addEventListener("submit", function (e) {
            var subject = document.getElementById("subject").value.trim();
            var message = document.getElementById("message").value.trim();
            if (subject === "" || message === "") {
                e.preventDefault();
                alert("Please fill all the fields");
            }
        });
    }
});
