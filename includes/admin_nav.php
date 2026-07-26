<?php
// Expects: $active_page, and optionally $admin_name
if (!isset($active_page)) {
    $active_page = '';
}
if (!isset($admin_name)) {
    $admin_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
}
$admin_initial = strtoupper(substr($admin_name, 0, 1));
?>
<aside class="adm-sidebar">
    <div class="adm-side-brand">
        <div class="adm-side-logo">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="Logo">
        </div>
        <div>
            <span>BestCare Hospital</span>
            <small>Admin Portal</small>
        </div>
    </div>

    <button class="adm-menu-btn" id="admMenuBtn" type="button" aria-label="Menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg></button>

    <nav class="adm-nav" id="admNav">
        <a class="<?php if ($active_page == 'dashboard') echo 'active'; ?>" href="dashboard.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Dashboard
        </a>
        <a class="<?php if ($active_page == 'staff') echo 'active'; ?>" href="manage-staff.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Manage Staff
        </a>
        <a class="<?php if ($active_page == 'patients') echo 'active'; ?>" href="manage-patients.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Manage Patients
        </a>
        <a class="<?php if ($active_page == 'services') echo 'active'; ?>" href="manage-services.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            Manage Services
        </a>
        <a class="<?php if ($active_page == 'departments') echo 'active'; ?>" href="manage-departments.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M5 21V7l7-4 7 4v14"></path><path d="M9 21v-6h6v6"></path></svg>
            Manage Departments
        </a>
        <a class="<?php if ($active_page == 'appointments') echo 'active'; ?>" href="appointments.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            Appointments
        </a>
        <a class="<?php if ($active_page == 'queries') echo 'active'; ?>" href="manage-queries.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            Patient Queries
        </a>
        <a class="<?php if ($active_page == 'reports') echo 'active'; ?>" href="reports.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            Reports
        </a>
        <a href="../index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
            Website
        </a>
    </nav>

    <div class="adm-side-footer" id="admSideFooter">
        <div class="adm-profile">
            <div class="adm-avatar"><?php echo htmlspecialchars($admin_initial); ?></div>
            <div>
                <p><?php echo htmlspecialchars($admin_name); ?></p>
                <small>Administrator</small>
            </div>
        </div>
        <a class="adm-logout" href="../auth/logout.php?role=admin">Logout</a>
    </div>
</aside>
