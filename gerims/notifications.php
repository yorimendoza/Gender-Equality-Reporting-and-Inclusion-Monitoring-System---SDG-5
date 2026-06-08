<?php
require_once 'includes/config.php';
requireLogin();
$page_title = 'Notifications';
$uid = $_SESSION['user_id'];

// Mark all as read
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid");

$notifs = $conn->query("SELECT n.*, r.title as report_title FROM notifications n LEFT JOIN reports r ON n.report_id=r.report_id WHERE n.user_id=$uid ORDER BY n.created_at DESC LIMIT 50");

include 'includes/header.php';
?>
<div class="container" style="max-width:700px">
    <h4 class="mb-4"><i class="fas fa-bell mr-2" style="color:var(--primary)"></i>Notifications</h4>

    <div class="gerims-card">
        <div class="card-body p-0">
            <?php if ($notifs->num_rows === 0): ?>
            <div class="text-center py-5 text-muted"><i class="fas fa-bell-slash fa-2x mb-2"></i><br>No notifications yet.</div>
            <?php else: while($n = $notifs->fetch_assoc()): ?>
            <div class="d-flex align-items-start p-3 border-bottom">
                <div class="mr-3" style="width:36px;height:36px;background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0">
                    <i class="fas fa-<?= $n['notif_type']==='status_update'?'sync':($n['notif_type']==='response'?'reply':'bell') ?> fa-sm"></i>
                </div>
                <div class="flex-grow-1">
                    <div><?= htmlspecialchars($n['message']) ?></div>
                    <?php if ($n['report_title']): ?>
                    <small class="text-muted">Report: <?= htmlspecialchars(substr($n['report_title'],0,50)) ?></small><br>
                    <?php endif; ?>
                    <small class="text-muted"><?= timeAgo($n['created_at']) ?></small>
                </div>
                <?php if ($n['report_id']): ?>
                <a href="view_report.php?id=<?= $n['report_id'] ?>" class="btn btn-sm btn-outline-primary ml-2">View</a>
                <?php endif; ?>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
