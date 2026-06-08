<?php
require_once 'includes/config.php';
requireLogin();
$page_title = 'View Report';
$rid = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch report
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT r.*, u.full_name, u.email, u.course, u.year_level, c.category_name FROM reports r JOIN users u ON r.user_id=u.user_id JOIN categories c ON r.category_id=c.category_id WHERE r.report_id=?");
} else {
    $stmt = $conn->prepare("SELECT r.*, u.full_name, u.email, u.course, u.year_level, c.category_name FROM reports r JOIN users u ON r.user_id=u.user_id JOIN categories c ON r.category_id=c.category_id WHERE r.report_id=? AND r.user_id=?");
}
$stmt->bind_param($role === 'admin' ? "i" : "ii", $rid, $uid);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
if (!$report) { redirect(SITE_URL . '/dashboard.php'); }

// Status logs
$logs = $conn->query("SELECT l.*, u.full_name FROM report_status_logs l JOIN users u ON l.changed_by=u.user_id WHERE l.report_id=$rid ORDER BY l.changed_at DESC");

// Admin responses
$responses = $conn->query("SELECT ar.*, u.full_name FROM admin_responses ar JOIN users u ON ar.admin_id=u.user_id WHERE ar.report_id=$rid AND ar.is_visible_to_user=1 ORDER BY ar.created_at ASC");

include 'includes/header.php';
?>
<div class="container" style="max-width:820px">
    <?php if (isset($_GET['submitted'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> Your report has been submitted successfully. We will review it shortly.</div>
    <?php endif; ?>

    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-file-alt mr-2" style="color:var(--primary)"></i>Report #<?= $rid ?></h4>
        <a href="<?= $role === 'admin' ? 'admin/reports.php' : 'my_reports.php' ?>" class="btn btn-outline-secondary btn-sm mr-2">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
        <?php if ($role === 'admin'): ?>
        <a href="admin/edit_report.php?id=<?= $rid ?>" class="btn btn-gerims btn-sm"><i class="fas fa-edit mr-1"></i>Manage</a>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="gerims-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($report['title']) ?></span>
                    <?= statusBadge($report['status']) ?>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Category</small>
                            <div class="font-weight-bold"><?= htmlspecialchars($report['category_name']) ?></div>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Priority</small>
                            <div><?= priorityBadge($report['priority']) ?></div>
                        </div>
                        <?php if ($report['location']): ?>
                        <div class="col-sm-6 mt-2">
                            <small class="text-muted">Location</small>
                            <div><?= htmlspecialchars($report['location']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($report['incident_date']): ?>
                        <div class="col-sm-6 mt-2">
                            <small class="text-muted">Incident Date</small>
                            <div><?= date('F d, Y', strtotime($report['incident_date'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="col-sm-6 mt-2">
                            <small class="text-muted">Submitted</small>
                            <div><?= date('M d, Y h:i A', strtotime($report['created_at'])) ?></div>
                        </div>
                        <div class="col-sm-6 mt-2">
                            <small class="text-muted">Anonymous</small>
                            <div><?= $report['is_anonymous'] ? '<span class="text-success">Yes</span>' : 'No' ?></div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold">Description</h6>
                    <p style="white-space:pre-wrap"><?= nl2br(htmlspecialchars($report['description'])) ?></p>
                </div>
            </div>

            <!-- Admin Responses -->
            <?php if ($responses->num_rows > 0): ?>
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-reply mr-2"></i>Responses from Admin</div>
                <div class="card-body">
                    <?php while($resp = $responses->fetch_assoc()): ?>
                    <div class="media mb-3 pb-3 border-bottom">
                        <div class="avatar-sm mr-3 bg-gradient-primary text-white d-flex align-items-center justify-content-center rounded-circle" style="width:38px;height:38px;flex-shrink:0">
                            <?= strtoupper(substr($resp['full_name'],0,1)) ?>
                        </div>
                        <div class="media-body">
                            <strong><?= htmlspecialchars($resp['full_name']) ?></strong>
                            <small class="text-muted ml-2"><?= timeAgo($resp['created_at']) ?></small>
                            <p class="mt-1 mb-0"><?= nl2br(htmlspecialchars($resp['response_text'])) ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <?php if ($role === 'admin'): ?>
            <!-- Reporter Info -->
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-user mr-2"></i>Reporter</div>
                <div class="card-body">
                    <strong><?= $report['is_anonymous'] ? 'Anonymous' : htmlspecialchars($report['full_name']) ?></strong>
                    <?php if (!$report['is_anonymous']): ?>
                    <div class="text-muted small"><?= htmlspecialchars($report['email']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($report['course']) ?> – <?= htmlspecialchars($report['year_level']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status Timeline -->
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-history mr-2"></i>Status History</div>
                <div class="card-body">
                    <ul class="status-timeline">
                        <li>
                            <div class="tl-dot"><i class="fas fa-plus"></i></div>
                            <div class="tl-body">
                                <div class="font-weight-bold">Report Submitted</div>
                                <div class="tl-time"><?= date('M d, Y h:i A', strtotime($report['created_at'])) ?></div>
                            </div>
                        </li>
                        <?php while($log = $logs->fetch_assoc()): ?>
                        <li>
                            <div class="tl-dot" style="background:var(--secondary)"><i class="fas fa-sync"></i></div>
                            <div class="tl-body">
                                <div class="font-weight-bold">Status: <?= ucfirst(str_replace('_',' ',$log['new_status'])) ?></div>
                                <?php if ($log['remarks']): ?>
                                <div class="small"><?= htmlspecialchars($log['remarks']) ?></div>
                                <?php endif; ?>
                                <div class="tl-time">by <?= htmlspecialchars($log['full_name']) ?> – <?= timeAgo($log['changed_at']) ?></div>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
