<?php
require_once 'includes/config.php';
requireLogin();
if (isAdmin()) redirect(SITE_URL . '/dashboard.php');
$page_title = 'My Reports';
$uid = $_SESSION['user_id'];

$status_filter = sanitize($conn, $_GET['status'] ?? '');
$where = "WHERE r.user_id=$uid";
if ($status_filter) $where .= " AND r.status='$status_filter'";

$reports = $conn->query("SELECT r.*, c.category_name FROM reports r JOIN categories c ON r.category_id=c.category_id $where ORDER BY r.created_at DESC");

include 'includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-list mr-2" style="color:var(--primary)"></i>My Reports</h4>
        <a href="submit_report.php" class="btn btn-gerims btn-sm"><i class="fas fa-plus mr-1"></i>New Report</a>
    </div>

    <!-- Filter -->
    <div class="gerims-card">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="font-weight-bold mr-2">Filter:</span>
                <?php foreach([''=>'All','pending'=>'Pending','under_review'=>'Under Review','resolved'=>'Resolved','dismissed'=>'Dismissed'] as $val=>$lbl): ?>
                <a href="my_reports.php?status=<?= $val ?>" class="btn btn-sm <?= $status_filter===$val ? 'btn-gerims' : 'btn-outline-secondary' ?> mr-1 mb-1"><?= $lbl ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="gerims-card">
        <div class="card-body p-0">
            <?php if ($reports->num_rows === 0): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>No reports found</h5>
                <a href="submit_report.php" class="btn btn-gerims mt-2">Submit your first report</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
            <table class="table table-hover mb-0 gerims-table">
                <thead>
                    <tr><th>#</th><th>Title</th><th>Category</th><th>Status</th><th>Priority</th><th>Submitted</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php while ($r = $reports->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['report_id'] ?></td>
                    <td>
                        <a href="view_report.php?id=<?= $r['report_id'] ?>" class="font-weight-bold text-dark">
                            <?= htmlspecialchars(substr($r['title'], 0, 45)) ?>
                        </a>
                        <?php if ($r['is_anonymous']): ?>
                        <span class="badge badge-secondary ml-1">Anonymous</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['category_name']) ?></td>
                    <td><?= statusBadge($r['status']) ?></td>
                    <td><?= priorityBadge($r['priority']) ?></td>
                    <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                    <td>
                        <a href="view_report.php?id=<?= $r['report_id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
