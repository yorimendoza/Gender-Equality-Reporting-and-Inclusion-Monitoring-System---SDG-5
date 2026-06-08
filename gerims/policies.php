<?php
require_once 'includes/config.php';
requireLogin();
$page_title = 'Policies';
$policies = $conn->query("SELECT p.*, u.full_name FROM policies p LEFT JOIN users u ON p.created_by=u.user_id WHERE p.is_published=1 ORDER BY p.created_at DESC");
include 'includes/header.php';
?>
<div class="container" style="max-width:800px">
    <h4 class="mb-4"><i class="fas fa-scroll mr-2" style="color:var(--primary)"></i>Gender Equality Policies</h4>
    <p class="text-muted">The following policies have been established to promote gender equality and protect all members of our community.</p>

    <?php if ($policies->num_rows === 0): ?>
    <div class="alert alert-info">No policies published yet.</div>
    <?php else: while($p = $policies->fetch_assoc()): ?>
    <div class="gerims-card">
        <div class="card-header"><?= htmlspecialchars($p['title']) ?></div>
        <div class="card-body">
            <?php if ($p['category']): ?>
            <span class="badge badge-primary mb-2"><?= htmlspecialchars($p['category']) ?></span>
            <?php endif; ?>
            <p style="white-space:pre-wrap"><?= nl2br(htmlspecialchars($p['content'])) ?></p>
            <small class="text-muted">Published <?= date('M d, Y', strtotime($p['created_at'])) ?></small>
        </div>
    </div>
    <?php endwhile; endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
