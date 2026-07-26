<?php
include("../includes/check_login.php");
include("../includes/db.php");

if ($_SESSION['role'] != 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

$active_page = 'queries';
$admin_name = $_SESSION['username'];
$error = "";
$success = "";
$user_id = $_SESSION['user_id'];

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'replied') {
        $success = "Reply sent to the patient.";
    } elseif ($_GET['msg'] == 'closed') {
        $success = "Query marked as closed.";
    }
}

// Send a reply to a patient query
if (isset($_POST['send_reply'])) {
    $reply_id = (int)$_POST['reply_id'];
    $reply = trim($_POST['reply']);

    if (strlen($reply) == 0) {
        $error = "Reply cannot be empty.";
    } else {
        $safe_reply = mysqli_real_escape_string($conn, $reply);

        $sql = "UPDATE queries
                SET reply='$safe_reply', status='Replied', replied_by=$user_id
                WHERE id=$reply_id";
        $ok = mysqli_query($conn, $sql);

        if (!$ok) {
            $error = bestcare_db_error($conn, "Could not save reply. Please try again.");
        } else {
            header("Location: manage-queries.php?msg=replied");
            exit();
        }
    }
}

// Close a query - then redirect so refresh does not repeat the action
if (isset($_GET['close_id'])) {
    $close_id = (int)$_GET['close_id'];
    mysqli_query($conn, "UPDATE queries SET status='Closed' WHERE id=$close_id");
    header("Location: manage-queries.php?msg=closed");
    exit();
}

$list_sql = "SELECT q.*, p.full_name AS patient_name, p.contact AS patient_contact,
                    u.username AS patient_username
             FROM queries q, patients p, users u
             WHERE q.patient_id = p.id
             AND p.user_id = u.id
             ORDER BY
                CASE WHEN q.status='Open' THEN 0 ELSE 1 END,
                q.created_at DESC";
$list = mysqli_query($conn, $list_sql);

$queries = array();
if ($list) {
    while ($row = mysqli_fetch_array($list)) {
        // reply column is NULL until answered - coerce to string for PHP 8.1+
        $row['reply'] = isset($row['reply']) ? $row['reply'] : '';
        $row['patient_contact'] = isset($row['patient_contact']) ? $row['patient_contact'] : '';
        $queries[] = $row;
    }
}

$total = count($queries);
$open_count = 0;
$replied_count = 0;
$closed_count = 0;
for ($i = 0; $i < $total; $i++) {
    if ($queries[$i]['status'] == 'Open') {
        $open_count++;
    } elseif ($queries[$i]['status'] == 'Replied') {
        $replied_count++;
    } else {
        $closed_count++;
    }
}

// Never show a leftover ?msg= success banner together with a new error
if ($error != "") {
    $success = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Queries - BestCare Hospital</title>
    <link rel="stylesheet" href="/bestcare-hospital/assets/css/admin.css?v=3">
</head>
<body class="adm-body">

<?php include("../includes/admin_nav.php"); ?>

<div class="adm-main">
    <header class="adm-topbar">
        <h1>Patient Queries</h1>
        <span class="role-tag">Administrator</span>
    </header>

    <main class="adm-content">
        <?php if ($error != "") { ?>
            <div class="adm-alert err"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="adm-alert ok" id="admFlash"><?php echo $success; ?></div>
        <?php } ?>

        <div class="adm-ms-head">
            <div>
                <h1>Query Inbox</h1>
                <p>Questions submitted by patients to the hospital administration.</p>
            </div>
        </div>

        <section class="adm-stats" style="margin-bottom:22px;">
            <div class="adm-stat">
                <p class="label">Total</p>
                <p class="value"><?php echo $total; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Open</p>
                <p class="value"><?php echo $open_count; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Replied</p>
                <p class="value"><?php echo $replied_count; ?></p>
            </div>
            <div class="adm-stat">
                <p class="label">Closed</p>
                <p class="value"><?php echo $closed_count; ?></p>
            </div>
        </section>

        <section class="adm-ms-panel">
            <div class="adm-ms-panel-top">
                <div class="adm-ms-tabs">
                    <button class="adm-ms-tab active" type="button" data-filter="all">All</button>
                    <button class="adm-ms-tab" type="button" data-filter="Open">Open</button>
                    <button class="adm-ms-tab" type="button" data-filter="Replied">Replied</button>
                    <button class="adm-ms-tab" type="button" data-filter="Closed">Closed</button>
                </div>
                <div class="adm-ms-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                    <input type="text" id="mqSearch" placeholder="Search queries...">
                </div>
            </div>

            <?php if ($total > 0) { ?>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Subject / Message</th>
                                <th>Reply</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="mqBody">
                            <?php for ($i = 0; $i < $total; $i++) {
                                $row = $queries[$i];

                                $parts = explode(' ', $row['patient_name']);
                                $ini = strtoupper(substr($parts[0], 0, 1));
                                if (isset($parts[1])) {
                                    $ini .= strtoupper(substr($parts[1], 0, 1));
                                }
                            ?>
                                <tr class="mq-row" data-status="<?php echo htmlspecialchars($row['status']); ?>">
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="adm-staff-cell">
                                            <div class="adm-staff-avatar"><?php echo htmlspecialchars($ini); ?></div>
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($row['patient_name']); ?></p>
                                                <p class="spec"><?php echo htmlspecialchars($row['patient_contact']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['subject']); ?></strong>
                                        <p class="adm-msg"><?php echo htmlspecialchars($row['message']); ?></p>
                                    </td>
                                    <td>
                                        <?php if ($row['reply'] != "") { ?>
                                            <p class="adm-msg"><?php echo htmlspecialchars($row['reply']); ?></p>
                                        <?php } else { ?>
                                            <span style="color:#9CA3AF;font-size:13px;">Not replied yet</span>
                                        <?php } ?>
                                    </td>
                                    <td><span class="adm-status <?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                    <td>
                                        <div class="adm-ms-actions">
                                            <?php if ($row['status'] != 'Closed') { ?>
                                                <button type="button" class="adm-btn-outline btn-reply"
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-patient="<?php echo htmlspecialchars($row['patient_name'], ENT_QUOTES); ?>"
                                                    data-subject="<?php echo htmlspecialchars($row['subject'], ENT_QUOTES); ?>"
                                                    data-message="<?php echo htmlspecialchars($row['message'], ENT_QUOTES); ?>"
                                                    data-reply="<?php echo htmlspecialchars($row['reply'], ENT_QUOTES); ?>">
                                                    <?php echo ($row['status'] == 'Open') ? 'Reply' : 'Edit Reply'; ?>
                                                </button>
                                                <a class="adm-btn-danger" href="manage-queries.php?close_id=<?php echo $row['id']; ?>">Close</a>
                                            <?php } else { ?>
                                                <span style="color:#9CA3AF;font-size:13px;">Closed</span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-empty" id="mqNoMatch" style="display:none;">No queries match your search.</div>
            <?php } else { ?>
                <div class="adm-empty">No patient queries yet.</div>
            <?php } ?>
        </section>
    </main>
</div>

<!-- REPLY MODAL -->
<div class="adm-modal-overlay" id="replyModal">
    <div class="adm-modal" role="dialog" aria-modal="true">
        <button type="button" class="adm-modal-close" id="btnCloseReply" title="Close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg></button>
        <h2>Reply to Query</h2>
        <p class="modal-sub">From <strong id="replyPatientLabel"></strong></p>

        <div class="adm-quote">
            <strong id="replySubjectLabel"></strong>
            <p id="replyMessageLabel"></p>
        </div>

        <form class="adm-form" method="post" action="">
            <input type="hidden" name="reply_id" id="replyId" value="0">

            <div class="field">
                <label for="reply">Your Reply *</label>
                <textarea name="reply" id="reply" rows="5" required placeholder="Type the hospital's response to this patient..."></textarea>
            </div>

            <div class="adm-modal-actions">
                <button type="button" class="adm-btn-ghost" id="btnCancelReply">Cancel</button>
                <button type="submit" name="send_reply" value="1" class="adm-btn">Send Reply</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('admMenuBtn').addEventListener('click', function () {
    document.getElementById('admNav').classList.toggle('open');
    document.getElementById('admSideFooter').classList.toggle('open');
});

var body = document.body;
var replyModal = document.getElementById('replyModal');

function openReply() {
    body.classList.add('modal-open');
    replyModal.classList.add('open');
}

function closeReply() {
    replyModal.classList.remove('open');
    body.classList.remove('modal-open');
}

document.getElementById('btnCloseReply').onclick = closeReply;
document.getElementById('btnCancelReply').onclick = closeReply;
replyModal.addEventListener('click', function (e) {
    if (e.target === replyModal) closeReply();
});

var replyBtns = document.querySelectorAll('.btn-reply');
for (var i = 0; i < replyBtns.length; i++) {
    replyBtns[i].onclick = function () {
        document.getElementById('replyId').value = this.getAttribute('data-id');
        document.getElementById('replyPatientLabel').textContent = this.getAttribute('data-patient');
        document.getElementById('replySubjectLabel').textContent = this.getAttribute('data-subject');
        document.getElementById('replyMessageLabel').textContent = this.getAttribute('data-message');
        document.getElementById('reply').value = this.getAttribute('data-reply');
        openReply();
    };
}

var mqTabs = document.querySelectorAll('.adm-ms-tab');
var mqRows = document.querySelectorAll('.mq-row');
var mqSearch = document.getElementById('mqSearch');
var mqNoMatch = document.getElementById('mqNoMatch');
var mqFilter = 'all';

function mqApply() {
    var term = mqSearch ? mqSearch.value.toLowerCase() : '';
    var shown = 0;
    for (var i = 0; i < mqRows.length; i++) {
        var row = mqRows[i];
        var okFilter = (mqFilter === 'all' || row.getAttribute('data-status') === mqFilter);
        var okTerm = (term === '' || row.textContent.toLowerCase().indexOf(term) !== -1);

        if (okFilter && okTerm) {
            row.style.display = '';
            shown++;
        } else {
            row.style.display = 'none';
        }
    }
    if (mqNoMatch) {
        mqNoMatch.style.display = (shown === 0) ? 'block' : 'none';
    }
}

for (var t = 0; t < mqTabs.length; t++) {
    mqTabs[t].onclick = function () {
        for (var k = 0; k < mqTabs.length; k++) {
            mqTabs[k].className = 'adm-ms-tab';
        }
        this.className = 'adm-ms-tab active';
        mqFilter = this.getAttribute('data-filter');
        mqApply();
    };
}
if (mqSearch) {
    mqSearch.onkeyup = mqApply;
}
</script>
<script src="/bestcare-hospital/assets/js/flash.js?v=1"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
