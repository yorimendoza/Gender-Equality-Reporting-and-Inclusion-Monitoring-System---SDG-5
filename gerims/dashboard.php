<?php
require_once 'includes/config.php';
requireLogin();
$page_title = 'Dashboard';
$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Stats
if ($role === 'admin') {
    $total_reports   = $conn->query("SELECT COUNT(*) as c FROM reports")->fetch_assoc()['c'];
    $pending         = $conn->query("SELECT COUNT(*) as c FROM reports WHERE status='pending'")->fetch_assoc()['c'];
    $under_review    = $conn->query("SELECT COUNT(*) as c FROM reports WHERE status='under_review'")->fetch_assoc()['c'];
    $resolved        = $conn->query("SELECT COUNT(*) as c FROM reports WHERE status='resolved'")->fetch_assoc()['c'];
    $total_users     = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
    $total_feedback  = $conn->query("SELECT COUNT(*) as c FROM feedbacks")->fetch_assoc()['c'];

    // Category breakdown
    $cat_data = $conn->query("SELECT c.category_name, COUNT(r.report_id) as cnt 
        FROM categories c LEFT JOIN reports r ON c.category_id = r.category_id 
        GROUP BY c.category_id ORDER BY cnt DESC");

    // Recent reports
    $recent_reports = $conn->query("SELECT r.*, u.full_name, c.category_name 
        FROM reports r JOIN users u ON r.user_id=u.user_id 
        JOIN categories c ON r.category_id=c.category_id 
        ORDER BY r.created_at DESC LIMIT 8");
} else {
    $my_total    = $conn->query("SELECT COUNT(*) as c FROM reports WHERE user_id=$uid")->fetch_assoc()['c'];
    $my_pending  = $conn->query("SELECT COUNT(*) as c FROM reports WHERE user_id=$uid AND status='pending'")->fetch_assoc()['c'];
    $my_resolved = $conn->query("SELECT COUNT(*) as c FROM reports WHERE user_id=$uid AND status='resolved'")->fetch_assoc()['c'];
    $my_review   = $conn->query("SELECT COUNT(*) as c FROM reports WHERE user_id=$uid AND status='under_review'")->fetch_assoc()['c'];

    $recent_reports = $conn->query("SELECT r.*, c.category_name 
        FROM reports r JOIN categories c ON r.category_id=c.category_id 
        WHERE r.user_id=$uid ORDER BY r.created_at DESC LIMIT 5");

    $announcements = $conn->query("SELECT * FROM announcements WHERE is_active=1 AND (audience='all' OR audience='users') AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC LIMIT 3");
}

include 'includes/header.php';
?>
<div class="container-fluid px-4">

    <!-- SDG Banner -->
    <div class="sdg-banner">
        <div class="sdg-badge">SDG 5</div>
        <div>
            <strong>Gender Equality</strong>
            <div style="font-size:0.82rem;opacity:0.9">Achieve gender equality and empower all women and girls.</div>
        </div>
        <div class="ml-auto text-right d-none d-md-block">
            <i class="fas fa-venus-mars fa-2x" style="opacity:0.4"></i>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
    <!-- ADMIN DASHBOARD -->
    <div class="row">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card bg-gradient-primary">
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                <div><div class="stat-num"><?= $total_reports ?></div><div class="stat-label">Total Reports</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card bg-gradient-orange">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div><div class="stat-num"><?= $pending ?></div><div class="stat-label">Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card bg-gradient-teal">
                <div class="stat-icon"><i class="fas fa-search"></i></div>
                <div><div class="stat-num"><?= $under_review ?></div><div class="stat-label">Under Review</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card bg-gradient-green">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div><div class="stat-num"><?= $resolved ?></div><div class="stat-label">Resolved</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card bg-gradient-pink">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div><div class="stat-num"><?= $total_users ?></div><div class="stat-label">Users</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#607d8b,#455a64)">
                <div class="stat-icon"><i class="fas fa-comment-dots"></i></div>
                <div><div class="stat-num"><?= $total_feedback ?></div><div class="stat-label">Feedback</div></div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <!-- Reports by Category -->
        <div class="col-md-4">
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-chart-pie mr-2"></i>Reports by Category</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <?php while($c = $cat_data->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['category_name']) ?></td>
                            <td><span class="badge badge-primary"><?= $c['cnt'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="col-md-8">
            <div class="gerims-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list mr-2"></i>Recent Reports</span>
                    <a href="admin/reports.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover mb-0 gerims-table">
                        <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Reporter</th><th>Status</th><th>Priority</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php while($r = $recent_reports->fetch_assoc()): ?>
                        <tr onclick="location.href='admin/view_report.php?id=<?= $r['report_id'] ?>'" style="cursor:pointer">
                            <td><?= $r['report_id'] ?></td>
                            <td><?= $r['is_anonymous'] ? '<em class="text-muted">Anonymous</em>' : htmlspecialchars(substr($r['title'],0,30)) ?></td>
                            <td><?= htmlspecialchars($r['category_name']) ?></td>
                            <td><?= $r['is_anonymous'] ? '—' : htmlspecialchars($r['full_name']) ?></td>
                            <td><?= statusBadge($r['status']) ?></td>
                            <td><?= priorityBadge($r['priority']) ?></td>
                            <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- USER DASHBOARD -->

    <!-- Announcements -->
    <?php if ($announcements->num_rows > 0): ?>
    <div class="gerims-card">
        <div class="card-header"><i class="fas fa-bullhorn mr-2"></i>Announcements</div>
        <div class="card-body">
            <?php while($a = $announcements->fetch_assoc()): ?>
            <div class="announcement-alert">
                <strong><?= htmlspecialchars($a['title']) ?></strong>
                <p class="mb-0 small mt-1"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row">
        <div class="col-6 col-md-3">
            <div class="stat-card bg-gradient-primary">
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                <div><div class="stat-num"><?= $my_total ?></div><div class="stat-label">My Reports</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-gradient-orange">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div><div class="stat-num"><?= $my_pending ?></div><div class="stat-label">Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-gradient-teal">
                <div class="stat-icon"><i class="fas fa-search"></i></div>
                <div><div class="stat-num"><?= $my_review ?></div><div class="stat-label">Under Review</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-gradient-green">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div><div class="stat-num"><?= $my_resolved ?></div><div class="stat-label">Resolved</div></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="gerims-card quick-action-card text-center p-4">
                    <div class="quick-action-icon" style="color:var(--primary)">
                        <i class="fas fa-plus-circle fa-3x mb-3"></i>
                    </div>
                    <h5>Submit a Report</h5>
                    <p class="text-muted small mb-3">Report gender-related concerns safely and confidentially.</p>
                    <a href="submit_report.php" class="btn btn-gerims">Submit Now</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="gerims-card quick-action-card text-center p-4">
                    <div class="quick-action-icon" style="color:var(--secondary)">
                        <i class="fas fa-comment-dots fa-3x mb-3"></i>
                    </div>
                    <h5>Share Feedback</h5>
                    <p class="text-muted small mb-3">Help improve inclusivity policies through your feedback.</p>
                    <a href="feedback.php" class="btn btn-gerims">Give Feedback</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="gerims-card quick-action-card text-center p-4">
                    <div class="quick-action-icon" style="color:var(--accent)">
                        <i class="fas fa-scroll fa-3x mb-3"></i>
                    </div>
                    <h5>View Policies</h5>
                    <p class="text-muted small mb-3">Read institutional gender equality policies.</p>
                    <a href="policies.php" class="btn btn-gerims">View Policies</a>
                </div>
            </div>
        </div>
        <br>

    <!-- Recent My Reports -->
    <div class="gerims-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list mr-2"></i>My Recent Reports</span>
            <a href="my_reports.php" class="btn btn-sm btn-light">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if ($recent_reports->num_rows === 0): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i><br>No reports yet.
            </div>
            <?php else: ?>
            <div class="table-responsive">
            <table class="table table-hover mb-0 gerims-table">
                <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Status</th><th>Priority</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php while($r = $recent_reports->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['report_id'] ?></td>
                    <td><?= htmlspecialchars(substr($r['title'],0,35)) ?></td>
                    <td><?= htmlspecialchars($r['category_name']) ?></td>
                    <td><?= statusBadge($r['status']) ?></td>
                    <td><?= priorityBadge($r['priority']) ?></td>
                    <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                    <td><a href="view_report.php?id=<?= $r['report_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
