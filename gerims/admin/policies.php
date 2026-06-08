<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$page_title = 'Manage Policies';
$success = $error = '';
$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $title       = sanitize($conn, $_POST['title'] ?? '');
        $content     = sanitize($conn, $_POST['content'] ?? '');
        $category    = sanitize($conn, $_POST['category'] ?? '');
        $is_published = (int)($_POST['is_published'] ?? 0);

        if (!$title || !$content) { $error = 'Title and content are required.'; }
        else {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO policies (title, content, category, created_by, is_published) VALUES (?,?,?,?,?)");
                $stmt->bind_param("sssii", $title, $content, $category, $admin_id, $is_published);
                $stmt->execute();
                logAudit($conn, $admin_id, 'ADD_POLICY', 'policies', $conn->insert_id);
                $success = 'Policy added successfully.';
            } else {
                $pid = (int)$_POST['policy_id'];
                $stmt = $conn->prepare("UPDATE policies SET title=?, content=?, category=?, is_published=? WHERE policy_id=?");
                $stmt->bind_param("sssii", $title, $content, $category, $is_published, $pid);
                $stmt->execute();
                logAudit($conn, $admin_id, 'EDIT_POLICY', 'policies', $pid);
                $success = 'Policy updated successfully.';
            }
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $conn->query("DELETE FROM policies WHERE policy_id=".(int)$_GET['delete']);
    redirect(SITE_URL . '/admin/policies.php');
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $conn->query("UPDATE policies SET is_published = NOT is_published WHERE policy_id=".(int)$_GET['toggle']);
    redirect(SITE_URL . '/admin/policies.php');
}

$policies = $conn->query("SELECT p.*, u.full_name FROM policies p LEFT JOIN users u ON p.created_by=u.user_id ORDER BY p.created_at DESC");
include '../includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-scroll mr-2" style="color:var(--primary)"></i>Manage Policies</h4>
        <button class="btn btn-gerims btn-sm" data-toggle="modal" data-target="#policyModal" data-action="add">
            <i class="fas fa-plus mr-1"></i>Add Policy
        </button>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="gerims-card">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0 gerims-table">
                <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Created By</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while ($p = $policies->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['policy_id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                    <td><small><?= htmlspecialchars($p['category']) ?></small></td>
                    <td><small><?= htmlspecialchars($p['full_name'] ?? 'System') ?></small></td>
                    <td><span class="badge badge-<?= $p['is_published']?'success':'secondary' ?>"><?= $p['is_published']?'Published':'Draft' ?></span></td>
                    <td><small><?= date('M d, Y', strtotime($p['created_at'])) ?></small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#policyModal"
                            data-action="edit"
                            data-pid="<?= $p['policy_id'] ?>"
                            data-title="<?= htmlspecialchars($p['title']) ?>"
                            data-content="<?= htmlspecialchars($p['content']) ?>"
                            data-category="<?= htmlspecialchars($p['category']) ?>"
                            data-published="<?= $p['is_published'] ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="policies.php?toggle=<?= $p['policy_id'] ?>" class="btn btn-sm btn-outline-<?= $p['is_published']?'secondary':'success' ?>" title="Toggle">
                            <i class="fas fa-<?= $p['is_published']?'eye-slash':'eye' ?>"></i>
                        </a>
                        <a href="policies.php?delete=<?= $p['policy_id'] ?>" class="btn btn-sm btn-outline-danger"
                           data-confirm="Delete this policy?"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- Policy Modal -->
<div class="modal fade" id="policyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),#9c27b0);color:#fff">
                <h5 class="modal-title" id="policyModalTitle"><i class="fas fa-scroll mr-2"></i>Add Policy</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="policy_action" value="add">
                <input type="hidden" name="policy_id" id="policy_id">
                <div class="modal-body">
                    <div class="form-group"><label>Title <span class="text-danger">*</span></label><input type="text" name="title" id="policy_title" class="form-control" required></div>
                    <div class="form-group"><label>Category</label><input type="text" name="category" id="policy_category" class="form-control" placeholder="e.g. Discrimination, Harassment..."></div>
                    <div class="form-group"><label>Content <span class="text-danger">*</span></label><textarea name="content" id="policy_content" class="form-control" rows="6" required></textarea></div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="policy_published" name="is_published" value="1">
                            <label class="custom-control-label" for="policy_published">Publish immediately (visible to users)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gerims"><i class="fas fa-save mr-1"></i>Save Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#policyModal').on('show.bs.modal', function(e){
    var b = $(e.relatedTarget);
    var action = b.data('action');
    $('#policy_action').val(action);
    if (action === 'edit') {
        $('#policyModalTitle').html('<i class="fas fa-edit mr-2"></i>Edit Policy');
        $('#policy_id').val(b.data('pid'));
        $('#policy_title').val(b.data('title'));
        $('#policy_content').val(b.data('content'));
        $('#policy_category').val(b.data('category'));
        $('#policy_published').prop('checked', b.data('published') == 1);
    } else {
        $('#policyModalTitle').html('<i class="fas fa-plus mr-2"></i>Add Policy');
        $('#policy_id, #policy_title, #policy_content, #policy_category').val('');
        $('#policy_published').prop('checked', false);
    }
});
</script>
<?php include '../includes/footer.php'; ?>
