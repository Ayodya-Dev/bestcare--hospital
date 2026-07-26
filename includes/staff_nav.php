<?php
// Expects: $initials, $full_name, $active_page
if (!isset($active_page)) {
    $active_page = '';
}
?>
<aside class="sd-sidebar">
    <div class="sd-side-brand">
        <div class="sd-side-logo">
            <img src="/bestcare-hospital/assets/images/bestcarelogo.png" alt="Logo">
        </div>
        <div>
            <span>BestCare Hospital</span>
            <small>Doctor Portal</small>
        </div>
    </div>

    <button class="sd-menu-btn" id="sdMenuBtn" type="button" aria-label="Menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg></button>

    <nav class="sd-nav" id="sdNav">
        <a class="<?php if ($active_page == 'dashboard') echo 'active'; ?>" href="dashboard.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Dashboard
        </a>
        <a class="<?php if ($active_page == 'appointments') echo 'active'; ?>" href="appointments.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            Appointments
        </a>
        <a class="<?php if ($active_page == 'records') echo 'active'; ?>" href="add-record.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path><polyline points="14 3 14 8 19 8"></polyline></svg>
            Medical Records
        </a>
        <a class="<?php if ($active_page == 'prescriptions') echo 'active'; ?>" href="add-prescription.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6v4H9z"></path><path d="M8 7h8v14H8z"></path><line x1="10" y1="12" x2="14" y2="12"></line><line x1="12" y1="10" x2="12" y2="14"></line></svg>
            Prescriptions
        </a>
        <a class="<?php if ($active_page == 'treatment_plans') echo 'active'; ?>" href="treatment-plans.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path><rect x="9" y="3" width="6" height="4" rx="1"></rect><line x1="9" y1="12" x2="15" y2="12"></line><line x1="9" y1="16" x2="13" y2="16"></line></svg>
            Treatment Plans
        </a>
        <a class="<?php if ($active_page == 'tests') echo 'active'; ?>" href="add-test-result.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6l1 4H8z"></path><path d="M10 7v10a2 2 0 0 0 4 0V7"></path></svg>
            Test Results
        </a>
    </nav>

    <div class="sd-side-footer" id="sdSideFooter">
        <div class="sd-profile">
            <div class="sd-avatar"><?php echo htmlspecialchars($initials); ?></div>
            <div>
                <p><?php echo htmlspecialchars($full_name); ?></p>
                <small>Doctor</small>
            </div>
        </div>
        <a class="sd-logout" href="../auth/logout.php?role=staff">Logout</a>
    </div>
</aside>
