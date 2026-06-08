<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$page_title = 'Announcements';
$success = $error = '';
$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $title    = sanitize($conn, $_POST['title'] ?? '');
    $content  = sanitize($conn, $_POST['content'] ?? '');
    $audience = sanitize($conn, $_POST['audience'] ?? 'all');
    $expires  = $_POST['expires_at'] ?? '';

    // Fixed NULL handling
    if (!empty($expires)) {
        $expires = date('Y-m-d H:i:s', strtotime($expires));
        $expires_val = "'$expires'";
    } else {
        $expires_val = "NULL";
    }

    if (!$title || !$content) { 
        $error = 'Title and content are required.'; 
    }
    elseif ($action === 'add') {
        $sql = "INSERT INTO announcements (admin_id, title, content, audience, expires_at) 
                VALUES ($admin_id, '$title', '$content', '$audience', $expires_val)";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = 'Announcement posted successfully.';
            header("Location: announcements.php");
            exit;
        } else {
            $error = 'Error: ' . $conn->error;
        }
    } elseif ($action === 'edit') {
        $aid = (int)$_POST['announcement_id'];
        $sql = "UPDATE announcements SET title='$title', content='$content', audience='$audience', expires_at=$expires_val WHERE announcement_id=$aid";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = 'Announcement updated.';
            header("Location: announcements.php");
            exit;
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_GET['delete'])) { $conn->query("DELETE FROM announcements WHERE announcement_id=".(int)$_GET['delete']); header("Location: announcements.php"); exit; }
if (isset($_GET['toggle'])) { $conn->query("UPDATE announcements SET is_active = NOT is_active WHERE announcement_id=".(int)$_GET['toggle']); header("Location: announcements.php"); exit; }

$list = $conn->query("SELECT a.*, u.full_name FROM announcements a JOIN users u ON a.admin_id=u.user_id ORDER BY a.created_at DESC");
include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-bullhorn mr-2" style="color:var(--primary)"></i>Announcements</h4>
        <button class="btn btn-gerims btn-sm" data-toggle="modal" data-target="#annModal" data-action="add"><i class="fas fa-plus mr-1"></i>New Announcement</button>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if ($list && $list->num_rows > 0): ?>
        <?php while ($a = $list->fetch_assoc()): ?>
    <div class="gerims-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-1"><?= htmlspecialchars($a['title']) ?></h5>
                    <small class="text-muted">
                        By <?= htmlspecialchars($a['full_name']) ?> &bull; <?= date('M d, Y', strtotime($a['created_at'])) ?> &bull;
                        Audience: <strong><?= ucfirst($a['audience']) ?></strong>
                        <?php if ($a['expires_at']): ?> &bull; Expires: <?= date('M d, Y', strtotime($a['expires_at'])) ?><?php endif; ?>
                    </small>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                </div>
                <div class="d-flex flex-column ml-3" style="gap:4px">
                    <span class="badge badge-<?= $a['is_active']?'success':'secondary' ?>"><?= $a['is_active']?'Active':'Inactive' ?></span>
                    <button class="btn btn-sm btn-outline-warning mt-1" data-toggle="modal" data-target="#annModal"
                        data-action="edit" data-aid="<?= $a['announcement_id'] ?>"
                        data-title="<?= htmlspecialchars($a['title']) ?>" data-content="<?= htmlspecialchars($a['content']) ?>"
                        data-audience="<?= $a['audience'] ?>" data-expires="<?= $a['expires_at'] ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="?toggle=<?= $a['announcement_id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-<?= $a['is_active']?'eye-slash':'eye' ?>"></i></a>
                    <a href="?delete=<?= $a['announcement_id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this announcement?"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        </div>
    </div>
        <?php endwhile; ?>
    <?php else: ?>
    <div class="alert alert-info">No announcements yet. Click "New Announcement" to create one.</div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="annModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),#9c27b0);color:#fff">
                <h5 class="modal-title" id="annModalTitle">New Announcement</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="ann_action" value="add">
                <input type="hidden" name="announcement_id" id="ann_id">
                <div class="modal-body">
                    <div class="form-group"><label>Title</label><input type="text" name="title" id="ann_title" class="form-control" required></div>
                    <div class="form-group"><label>Content</label><textarea name="content" id="ann_content" class="form-control" rows="4" required></textarea></div>
                    <div class="row">
                        <div class="col-6"><div class="form-group"><label>Audience</label>
                            <select name="audience" id="ann_audience" class="form-control">
                                <option value="all">All</option><option value="users">Users Only</option><option value="admins">Admins Only</option>
                            </select></div></div>
                        <div class="col-6"><div class="form-group"><label>Expires At <small class="text-muted">(optional)</small></label>
                            <input type="datetime-local" name="expires_at" id="ann_expires" class="form-control"></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gerims">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$('#annModal').on('show.bs.modal', function(e){
    var b = $(e.relatedTarget), action = b.data('action') || 'add';
    $('#ann_action').val(action);
    if (action === 'edit') {
        $('#annModalTitle').text('Edit Announcement');
        $('#ann_id').val(b.data('aid'));
        $('#ann_title').val(b.data('title'));
        $('#ann_content').val(b.data('content'));
        $('#ann_audience').val(b.data('audience'));
        $('#ann_expires').val(b.data('expires'));
    } else {
        $('#annModalTitle').text('New Announcement');
        $('#ann_id').val('');
        $('#ann_title').val('');
        $('#ann_content').val('');
        $('#ann_expires').val('');
        $('#ann_audience').val('all');
    }
});
</script>
<?php include '../includes/footer.php'; ?>