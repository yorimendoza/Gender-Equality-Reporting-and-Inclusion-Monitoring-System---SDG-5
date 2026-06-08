<?php
require_once 'includes/config.php';
requireLogin();
if (isAdmin()) redirect(SITE_URL . '/dashboard.php');
$page_title = 'Submit Report';

$success = $error = '';
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = sanitize($conn, $_POST['title'] ?? '');
    $cat_id        = (int)($_POST['category_id'] ?? 0);
    $description   = sanitize($conn, $_POST['description'] ?? '');
    $location      = sanitize($conn, $_POST['location'] ?? '');
    $incident_date = sanitize($conn, $_POST['incident_date'] ?? '');
    $is_anonymous  = isset($_POST['is_anonymous']) ? 1 : 0;
    $priority      = sanitize($conn, $_POST['priority'] ?? 'medium');
    $uid           = $_SESSION['user_id'];

    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['attachment']['size'] < 5 * 1024 * 1024) {
            $fname = uniqid('rpt_') . '.' . $ext;
            move_uploaded_file($_FILES['attachment']['tmp_name'], __DIR__ . '/uploads/reports/' . $fname);
            $attachment = $fname;
        } else {
            $error = 'Invalid file type or size exceeds 5MB.';
        }
    }

    if (!$error) {
        if (!$title || !$cat_id || !$description) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $conn->prepare("INSERT INTO reports (user_id, category_id, title, description, location, incident_date, is_anonymous, priority) VALUES (?,?,?,?,?,?,?,?)");
            $inc_date = $incident_date ?: null;
            $stmt->bind_param("iissssis", $uid, $cat_id, $title, $description, $location, $inc_date, $is_anonymous, $priority);
            if ($stmt->execute()) {
                $rid = $conn->insert_id;
                logAudit($conn, $uid, 'SUBMIT_REPORT', 'reports', $rid, "Title: $title");
                // Notify admins
                $admins = $conn->query("SELECT user_id FROM users WHERE role='admin'");
                while ($adm = $admins->fetch_assoc()) {
                    $msg = "New report submitted: " . substr($title, 0, 60);
                    $nstmt = $conn->prepare("INSERT INTO notifications (user_id, report_id, message, notif_type) VALUES (?,?,?,'system')");
                    $nstmt->bind_param("iis", $adm['user_id'], $rid, $msg);
                    $nstmt->execute();
                }
                redirect(SITE_URL . '/view_report.php?id=' . $rid . '&submitted=1');
            } else {
                $error = 'Failed to submit report. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>
<div class="container" style="max-width:100%">
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-plus-circle mr-2" style="color:var(--primary)"></i>Submit a Report</h4>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Back</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i><?= $error ?></div>
    <?php endif; ?>

    <div class="gerims-card">
        <div class="card-header"><i class="fas fa-file-alt mr-2"></i>Report Details</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <!-- Category -->
                <div class="form-group">
                    <label>Report Category <span class="text-danger">*</span></label>
                    <input type="hidden" name="category_id" id="category_id" required>
                    <div class="row mt-2">
                        <?php
                        $categories->data_seek(0);
                        while ($cat = $categories->fetch_assoc()):
                        ?>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="cat-card" data-cat-id="<?= $cat['category_id'] ?>">
                                    <i class="fas <?= $cat['icon'] ?> mb-2"></i>
                                    <div style="font-size:0.78rem;font-weight:600"><?= htmlspecialchars($cat['category_name']) ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="form-group text-center">
                    <label>Report Title <span class="text-danger">*</span></label>
                    <input type="text" name="title"
                        class="form-control report-title"
                        placeholder="Brief title of the incident"
                        required maxlength="150"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>


                <div class="form-group">
                    <label>Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5" maxlength="2000"
                        placeholder="Describe the incident in detail. Include who, what, when, and where." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Location / Area</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Classroom 201, Library" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Incident Date</label>
                            <input type="date" name="incident_date" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['incident_date'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority" class="form-control">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous">
                        <label class="custom-control-label" for="is_anonymous">
                            Submit anonymously <small class="text-muted">(your identity will not be shown to admins)</small>
                        </label>
                    </div>
                </div>

                <div class="alert alert-info py-2">
                    <i class="fas fa-shield-alt mr-1"></i> <small>All reports are treated with strict confidentiality in accordance with our reporting policy.</small>
                </div>

                <div class="d-flex">
                    <button type="submit" class="btn btn-gerims px-4">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Report
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>