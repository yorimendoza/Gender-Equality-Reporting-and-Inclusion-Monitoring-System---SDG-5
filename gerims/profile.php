<?php
require_once 'includes/config.php';
requireLogin();
$page_title = 'My Profile';
$uid = $_SESSION['user_id'];

$user = $conn->query("SELECT * FROM users WHERE user_id=$uid")->fetch_assoc();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = sanitize($conn, $_POST['full_name'] ?? '');
        $gender    = sanitize($conn, $_POST['gender'] ?? '');
        $course    = sanitize($conn, $_POST['course'] ?? '');
        $year      = sanitize($conn, $_POST['year_level'] ?? '');
        $contact   = sanitize($conn, $_POST['contact_number'] ?? '');

        $stmt = $conn->prepare("UPDATE users SET full_name=?, gender=?, course=?, year_level=?, contact_number=? WHERE user_id=?");
        $stmt->bind_param("sssssi", $full_name, $gender, $course, $year, $contact, $uid);
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            logAudit($conn, $uid, 'UPDATE_PROFILE', 'users', $uid);
            $success = 'Profile updated successfully.';
            $user = $conn->query("SELECT * FROM users WHERE user_id=$uid")->fetch_assoc();
        } else { $error = 'Update failed.'; }

    } elseif ($action === 'change_password') {
        $old_pwd = $_POST['old_password'] ?? '';
        $new_pwd = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($old_pwd, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_pwd) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new_pwd !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($new_pwd, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt->bind_param("si", $hash, $uid);
            if ($stmt->execute()) {
                logAudit($conn, $uid, 'CHANGE_PASSWORD', 'users', $uid);
                $success = 'Password changed successfully.';
            } else { $error = 'Failed to change password.'; }
        }
    }
}

include 'includes/header.php';
?>
<div class="container" style="max-width:700px">
    <h4 class="mb-4"><i class="fas fa-user-edit mr-2" style="color:var(--primary)"></i>My Profile</h4>

    <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i><?= $error ?></div>
    <?php endif; ?>

    <!-- Profile Info -->
    <div class="gerims-card">
        <div class="card-header"><i class="fas fa-id-card mr-2"></i>Personal Information</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="text-center mb-4">
                    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;font-size:2rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin:auto;">
                        <?= strtoupper(substr($user['full_name'],0,1)) ?>
                    </div>
                    <div class="mt-2 font-weight-bold"><?= htmlspecialchars($user['full_name']) ?></div>
                    <small class="text-muted"><?= ucfirst($user['role']) ?> &bull; <?= htmlspecialchars($user['email']) ?></small>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <?php foreach(['','Male','Female','Non-binary','Prefer not to say'] as $g): ?>
                                <option value="<?=$g?>" <?=$user['gender']===$g?'selected':''?>><?=$g?:'Select...'?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Course / Department</label>
                            <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($user['course'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Year Level</label>
                            <select name="year_level" class="form-control">
                                <?php foreach(['','1st Year','2nd Year','3rd Year','4th Year','Faculty','Staff'] as $y): ?>
                                <option value="<?=$y?>" <?=$user['year_level']===$y?'selected':''?>><?=$y?:'Select...'?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-gerims"><i class="fas fa-save mr-2"></i>Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="gerims-card">
        <div class="card-header"><i class="fas fa-lock mr-2"></i>Change Password</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="old_password" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-key mr-2"></i>Change Password</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
