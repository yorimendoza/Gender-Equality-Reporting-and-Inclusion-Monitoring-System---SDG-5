<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$page_title = 'Feedback';

$list = $conn->query("SELECT f.*, u.full_name, u.email FROM feedbacks f LEFT JOIN users u ON f.user_id=u.user_id ORDER BY f.created_at DESC");
$avg  = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM feedbacks")->fetch_assoc();

include '../includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-comment-dots mr-2" style="color:var(--secondary)"></i>User Feedback</h4>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="stat-card bg-gradient-pink">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div><div class="stat-num"><?= number_format($avg['avg_rating'] ?? 0, 1) ?></div><div class="stat-label">Avg. Rating</div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-gradient-primary">
                <div class="stat-icon"><i class="fas fa-comments"></i></div>
                <div><div class="stat-num"><?= $avg['total'] ?></div><div class="stat-label">Total Feedback</div></div>
            </div>
        </div>
    </div>

    <?php
    $list->data_seek(0);
    while ($f = $list->fetch_assoc()):
    $stars = str_repeat('★', $f['rating']) . str_repeat('☆', 5 - $f['rating']);
    ?>
    <div class="gerims-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="d-flex align-items-center mb-1">
                        <span style="color:#f59e0b;font-size:1.1rem"><?= $stars ?></span>
                        <span class="badge badge-info ml-2"><?= ucfirst($f['feedback_type']) ?></span>
                        <?php if ($f['is_anonymous']): ?>
                        <span class="badge badge-secondary ml-1">Anonymous</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($f['subject']): ?>
                    <strong><?= htmlspecialchars($f['subject']) ?></strong><br>
                    <?php endif; ?>
                    <p class="mt-1 mb-1"><?= nl2br(htmlspecialchars($f['message'])) ?></p>
                    <small class="text-muted">
                        By <?= $f['is_anonymous'] ? '<em>Anonymous</em>' : htmlspecialchars($f['full_name'] ?? 'Unknown') ?>
                        &bull; <?= date('M d, Y h:i A', strtotime($f['created_at'])) ?>
                    </small>
                </div>
                <a href="delete_feedback.php?id=<?= $f['feedback_id'] ?>" class="btn btn-sm btn-outline-danger ml-3"
                   data-confirm="Delete this feedback?"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php include '../includes/footer.php'; ?>
