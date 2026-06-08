<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$page_title = 'Manage Users';
$success = $error = '';
$admin_id = $_SESSION['user_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_user') {
        $uid       = (int)$_POST['user_id'];
        $full_name = sanitize($conn, $_POST['full_name'] ?? '');
        $email     = sanitize($conn, $_POST['email'] ?? '');
        $gender    = sanitize($conn, $_POST['gender'] ?? '');
        $course    = sanitize($conn, $_POST['course'] ?? '');
        $year      = sanitize($conn, $_POST['year_level'] ?? '');
        $contact   = sanitize($conn, $_POST['contact_number'] ?? '');
        $role      = sanitize($conn, $_POST['role'] ?? 'user');
        $is_active = (int)($_POST['is_active'] ?? 1);

        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, gender=?, course=?, year_level=?, contact_number=?, role=?, is_active=? WHERE user_id=?");
        $stmt->bind_param("sssssssii", $full_name, $email, $gender, $course, $year, $contact, $role, $is_active, $uid);
        if ($stmt->execute()) {
            logAudit($conn, $admin_id, 'EDIT_USER', 'users', $uid);
            $success = 'User updated successfully.';
        } else { $error = 'Update failed.'; }

    } elseif ($action === 'reset_password') {
        $uid     = (int)$_POST['user_id'];
        $new_pwd = $_POST['new_password'] ?? '';
        if (strlen($new_pwd) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $hash = password_hash($new_pwd, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt->bind_param("si", $hash, $uid);
            $stmt->execute();
            logAudit($conn, $admin_id, 'RESET_PASSWORD', 'users', $uid);
            $success = 'Password reset successfully.';
        }
    }
}

// Toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $conn->query("UPDATE users SET is_active = NOT is_active WHERE user_id=$uid AND user_id!=$admin_id");
    logAudit($conn, $admin_id, 'TOGGLE_USER_STATUS', 'users', $uid);
    redirect(SITE_URL . '/admin/users.php');
}
// Delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if ($uid !== $admin_id) {
        $conn->query("DELETE FROM users WHERE user_id=$uid");
        logAudit($conn, $admin_id, 'DELETE_USER', 'users', $uid);
    }
    redirect(SITE_URL . '/admin/users.php');
}

$search = sanitize($conn, $_GET['search'] ?? '');
$where = $search ? "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR course LIKE '%$search%'" : "";
$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM reports r WHERE r.user_id=u.user_id) as report_count FROM users u $where ORDER BY u.created_at DESC");

include '../includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-users mr-2" style="color:var(--primary)"></i>Manage Users</h4>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Search -->
    <div class="gerims-card">
        <div class="card-body py-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search by name, email, course..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-gerims btn-sm"><i class="fas fa-search"></i></button>
                <?php if ($search): ?><a href="users.php" class="btn btn-outline-secondary btn-sm ml-2">Clear</a><?php endif; ?>
            </form>
        </div>
    </div>

    <div class="gerims-card">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0 gerims-table">
                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Course / Year</th><th>Role</th><th>Reports</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;margin-right:8px">
                                <?= strtoupper(substr($u['full_name'],0,1)) ?>
                            </div>
                            <?= htmlspecialchars($u['full_name']) ?>
                        </div>
                    </td>
                    <td><small><?= htmlspecialchars($u['email']) ?></small></td>
                    <td><small><?= htmlspecialchars($u['course']) ?> <?= $u['year_level'] ? '– '.$u['year_level'] : '' ?></small></td>
                    <td><span class="badge badge-<?= $u['role']==='admin'?'danger':'primary' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><span class="badge badge-secondary"><?= $u['report_count'] ?></span></td>
                    <td>
                        <span class="badge badge-<?= $u['is_active']?'success':'secondary' ?>">
                            <?= $u['is_active']?'Active':'Inactive' ?>
                        </span>
                    </td>
                    <td><small><?= date('M d, Y', strtotime($u['created_at'])) ?></small></td>
                    <td>
                        <!-- Edit Modal Trigger -->
                        <button class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#editModal"
                            data-uid="<?= $u['user_id'] ?>"
                            data-name="<?= htmlspecialchars($u['full_name']) ?>"
                            data-email="<?= htmlspecialchars($u['email']) ?>"
                            data-gender="<?= htmlspecialchars($u['gender']) ?>"
                            data-course="<?= htmlspecialchars($u['course']) ?>"
                            data-year="<?= htmlspecialchars($u['year_level']) ?>"
                            data-contact="<?= htmlspecialchars($u['contact_number']) ?>"
                            data-role="<?= $u['role'] ?>"
                            data-active="<?= $u['is_active'] ?>"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($u['user_id'] !== $admin_id): ?>
                        <a href="users.php?toggle=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-<?= $u['is_active']?'secondary':'success' ?>" title="<?= $u['is_active']?'Deactivate':'Activate' ?>">
                            <i class="fas fa-<?= $u['is_active']?'ban':'check' ?>"></i>
                        </a>
                        <a href="users.php?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-danger" 
                           data-confirm="Delete user <?= htmlspecialchars($u['full_name']) ?>? This will also delete all their reports." title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),#9c27b0);color:#fff">
                <h5 class="modal-title"><i class="fas fa-user-edit mr-2"></i>Edit User</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_uid">
                <div class="modal-body">
                    <div class="form-group"><label>Full Name</label><input type="text" name="full_name" id="edit_name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6"><div class="form-group"><label>Gender</label>
                            <select name="gender" id="edit_gender" class="form-control">
                                <option value="">-</option>
                                <option value="Male">Male</option><option value="Female">Female</option>
                                <option value="Non-binary">Non-binary</option><option value="Prefer not to say">Prefer not to say</option>
                            </select></div></div>
                        <div class="col-6"><div class="form-group"><label>Contact</label><input type="text" name="contact_number" id="edit_contact" class="form-control"></div></div>
                        <div class="col-6"><div class="form-group"><label>Course</label><input type="text" name="course" id="edit_course" class="form-control"></div></div>
                        <div class="col-6"><div class="form-group"><label>Year Level</label>
                            <select name="year_level" id="edit_year" class="form-control">
                                <option value="">-</option>
                                <option>1st Year</option><option>2nd Year</option><option>3rd Year</option>
                                <option>4th Year</option><option>Faculty</option><option>Staff</option>
                            </select></div></div>
                        <div class="col-6"><div class="form-group"><label>Role</label>
                            <select name="role" id="edit_role" class="form-control">
                                <option value="user">User</option><option value="admin">Admin</option>
                            </select></div></div>
                        <div class="col-6"><div class="form-group"><label>Status</label>
                            <select name="is_active" id="edit_active" class="form-control">
                                <option value="1">Active</option><option value="0">Inactive</option>
                            </select></div></div>
                    </div>
                    <hr>
                    <h6>Reset Password <small class="text-muted">(leave blank to keep current)</small></h6>
                    <input type="hidden" name="action" value="edit_user">
                    <input type="text" name="new_password" class="form-control" placeholder="New password (optional, min 6 chars)">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gerims"><i class="fas fa-save mr-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#editModal').on('show.bs.modal', function(e){
    var b = $(e.relatedTarget);
    $('#edit_uid').val(b.data('uid'));
    $('#edit_name').val(b.data('name'));
    $('#edit_email').val(b.data('email'));
    $('#edit_gender').val(b.data('gender'));
    $('#edit_course').val(b.data('course'));
    $('#edit_year').val(b.data('year'));
    $('#edit_contact').val(b.data('contact'));
    $('#edit_role').val(b.data('role'));
    $('#edit_active').val(b.data('active'));
});
</script>
<?php include '../includes/footer.php'; ?>
