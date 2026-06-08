<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$page_title = 'Manage Reports';

$status_filter   = sanitize($conn, $_GET['status'] ?? '');
$cat_filter      = (int)($_GET['category'] ?? 0);
$priority_filter = sanitize($conn, $_GET['priority'] ?? '');
$search          = sanitize($conn, $_GET['search'] ?? '');

$where = "WHERE 1";
if ($status_filter)   $where .= " AND r.status='$status_filter'";
if ($cat_filter)      $where .= " AND r.category_id=$cat_filter";
if ($priority_filter) $where .= " AND r.priority='$priority_filter'";
if ($search)          $where .= " AND (r.title LIKE '%$search%' OR r.description LIKE '%$search%' OR u.full_name LIKE '%$search%')";

$reports    = $conn->query("SELECT r.*, u.full_name, c.category_name FROM reports r JOIN users u ON r.user_id=u.user_id JOIN categories c ON r.category_id=c.category_id $where ORDER BY r.created_at DESC");
$categories = $conn->query("SELECT * FROM categories");

include '../includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-file-alt mr-2" style="color:var(--primary)"></i>All Reports</h4>
        <span class="badge badge-primary badge-pill px-3 py-2"><?= $reports->num_rows ?> results</span>
    </div>

    <!-- Filters -->
    <div class="gerims-card">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Title, description, user..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <?php foreach(['pending','under_review','resolved','dismissed'] as $s): ?>
                        <option value="<?=$s?>" <?=$status_filter===$s?'selected':''?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small">Category</label>
                    <select name="category" class="form-control form-control-sm">
                        <option value="">All</option>
                        <?php $categories->data_seek(0); while($c=$categories->fetch_assoc()): ?>
                        <option value="<?=$c['category_id']?>" <?=$cat_filter==$c['category_id']?'selected':''?>><?= htmlspecialchars($c['category_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small">Priority</label>
                    <select name="priority" class="form-control form-control-sm">
                        <option value="">All</option>
                        <?php foreach(['low','medium','high','critical'] as $p): ?>
                        <option value="<?=$p?>" <?=$priority_filter===$p?'selected':''?>><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex">
                    <button type="submit" class="btn btn-gerims btn-sm mr-2"><i class="fas fa-search mr-1"></i>Filter</button>
                    <a href="reports.php" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="gerims-card">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0 gerims-table">
                <thead>
                    <tr><th>#</th><th>Title</th><th>Category</th><th>Reporter</th><th>Status</th><th>Priority</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($reports->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No reports found.</td></tr>
                <?php else: while ($r = $reports->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['report_id'] ?></td>
                    <td>
                        <a href="view_report.php?id=<?= $r['report_id'] ?>" class="font-weight-bold text-dark">
                            <?= htmlspecialchars(substr($r['title'],0,40)) ?>
                        </a>
                    </td>
                    <td><small><?= htmlspecialchars($r['category_name']) ?></small></td>
                    <td><small><?= $r['is_anonymous'] ? '<em class="text-muted">Anonymous</em>' : htmlspecialchars($r['full_name']) ?></small></td>
                    <td><?= statusBadge($r['status']) ?></td>
                    <td><?= priorityBadge($r['priority']) ?></td>
                    <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                    <td>
                        <a href="view_report.php?id=<?= $r['report_id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                        <a href="edit_report.php?id=<?= $r['report_id'] ?>" class="btn btn-sm btn-outline-warning" title="Manage"><i class="fas fa-edit"></i></a>
                        <a href="delete_report.php?id=<?= $r['report_id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                           data-confirm="Are you sure you want to delete this report?"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
