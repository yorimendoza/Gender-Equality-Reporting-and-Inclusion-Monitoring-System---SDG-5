<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$page_title = 'Manage Report';
$rid = (int)($_GET['id'] ?? 0);
$admin_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT r.*, u.full_name, u.email, u.course, u.year_level, u.contact_number, c.category_name FROM reports r JOIN users u ON r.user_id=u.user_id JOIN categories c ON r.category_id=c.category_id WHERE r.report_id=?");
$stmt->bind_param("i", $rid);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
if (!$report) redirect(SITE_URL . '/admin/reports.php');

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $new_status = sanitize($conn, $_POST['status'] ?? '');
        $priority   = sanitize($conn, $_POST['priority'] ?? '');
        $remarks    = sanitize($conn, $_POST['remarks'] ?? '');
        $old_status = $report['status'];

        $conn->prepare("UPDATE reports SET status=?, priority=? WHERE report_id=?")->bind_param("ssi", $new_status, $priority, $rid) && $conn->query("UPDATE reports SET status='$new_status', priority='$priority' WHERE report_id=$rid");

        $stmt2 = $conn->prepare("UPDATE reports SET status=?, priority=? WHERE report_id=?");
        $stmt2->bind_param("ssi", $new_status, $priority, $rid);
        $stmt2->execute();

        // Log status change
        $sl = $conn->prepare("INSERT INTO report_status_logs (report_id, changed_by, old_status, new_status, remarks) VALUES (?,?,?,?,?)");
        $sl->bind_param("iisss", $rid, $admin_id, $old_status, $new_status, $remarks);
        $sl->execute();

        // Notify user
        $msg = "Your report \"" . substr($report['title'], 0, 40) . "\" status changed to: " . ucfirst(str_replace('_', ' ', $new_status));
        $nn = $conn->prepare("INSERT INTO notifications (user_id, report_id, message, notif_type) VALUES (?,?,?,'status_update')");
        $nn->bind_param("iis", $report['user_id'], $rid, $msg);
        $nn->execute();

        logAudit($conn, $admin_id, 'UPDATE_REPORT_STATUS', 'reports', $rid, "New status: $new_status");
        $success = 'Report status updated successfully.';
        $report['status']   = $new_status;
        $report['priority'] = $priority;

    } elseif ($action === 'add_response') {
        $response_text = sanitize($conn, $_POST['response_text'] ?? '');
        $visible       = isset($_POST['is_visible_to_user']) ? 1 : 0;

        if ($response_text) {
            $rs = $conn->prepare("INSERT INTO admin_responses (report_id, admin_id, response_text, is_visible_to_user) VALUES (?,?,?,?)");
            $rs->bind_param("iisi", $rid, $admin_id, $response_text, $visible);
            $rs->execute();

            if ($visible) {
                $msg2 = "Admin responded to your report: \"" . substr($report['title'], 0, 40) . "\"";
                $nn2 = $conn->prepare("INSERT INTO notifications (user_id, report_id, message, notif_type) VALUES (?,?,?,'response')");
                $nn2->bind_param("iis", $report['user_id'], $rid, $msg2);
                $nn2->execute();
            }
            logAudit($conn, $admin_id, 'ADD_RESPONSE', 'admin_responses', $conn->insert_id);
            $success = 'Response added successfully.';
        } else {
            $error = 'Response text cannot be empty.';
        }
    }
}

$logs      = $conn->query("SELECT l.*, u.full_name FROM report_status_logs l JOIN users u ON l.changed_by=u.user_id WHERE l.report_id=$rid ORDER BY l.changed_at DESC");
$responses = $conn->query("SELECT ar.*, u.full_name FROM admin_responses ar JOIN users u ON ar.admin_id=u.user_id WHERE ar.report_id=$rid ORDER BY ar.created_at ASC");

include '../includes/header.php';
?>
<div class="container-fluid px-4">
    <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i><?= $error ?></div>
    <?php endif; ?>

    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 mr-auto"><i class="fas fa-edit mr-2" style="color:var(--primary)"></i>Manage Report #<?= $rid ?></h4>
        <a href="reports.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Back</a>
    </div>

    <div class="row">
        <!-- Left: Report Details -->
        <div class="col-lg-8">
            <div class="gerims-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($report['title']) ?></span>
                    <?= statusBadge($report['status']) ?>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><small class="text-muted">Category</small><div class="font-weight-bold"><?= htmlspecialchars($report['category_name']) ?></div></div>
                        <div class="col-sm-4"><small class="text-muted">Priority</small><div><?= priorityBadge($report['priority']) ?></div></div>
                        <div class="col-sm-4"><small class="text-muted">Submitted</small><div><small><?= date('M d, Y h:i A', strtotime($report['created_at'])) ?></small></div></div>
                        <?php if ($report['location']): ?>
                        <div class="col-sm-4 mt-2"><small class="text-muted">Location</small><div><?= htmlspecialchars($report['location']) ?></div></div>
                        <?php endif; ?>
                        <?php if ($report['incident_date']): ?>
                        <div class="col-sm-4 mt-2"><small class="text-muted">Incident Date</small><div><?= date('F d, Y', strtotime($report['incident_date'])) ?></div></div>
                        <?php endif; ?>
                        <div class="col-sm-4 mt-2"><small class="text-muted">Anonymous</small><div><?= $report['is_anonymous'] ? '<span class="text-success font-weight-bold">Yes</span>' : 'No' ?></div></div>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold">Description</h6>
                    <div class="p-3 bg-light rounded" style="white-space:pre-wrap"><?= nl2br(htmlspecialchars($report['description'])) ?></div>
                </div>
            </div>

            <!-- Add Response -->
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-reply mr-2"></i>Add Response</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_response">
                        <div class="form-group">
                            <label>Response Message</label>
                            <textarea name="response_text" class="form-control" rows="4" maxlength="1000" placeholder="Write your response to this report..."></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="vis" name="is_visible_to_user" checked>
                                <label class="custom-control-label" for="vis">Visible to reporter</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-gerims"><i class="fas fa-paper-plane mr-2"></i>Send Response</button>
                    </form>
                </div>
            </div>

            <!-- Existing Responses -->
            <?php if ($responses->num_rows > 0): ?>
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-comments mr-2"></i>All Responses (<?= $responses->num_rows ?>)</div>
                <div class="card-body">
                    <?php while ($resp = $responses->fetch_assoc()): ?>
                    <div class="media mb-3 pb-3 border-bottom">
                        <div style="width:38px;height:38px;background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;" class="mr-3">
                            <?= strtoupper(substr($resp['full_name'], 0, 1)) ?>
                        </div>
                        <div class="media-body">
                            <strong><?= htmlspecialchars($resp['full_name']) ?></strong>
                            <span class="badge badge-<?= $resp['is_visible_to_user'] ? 'success' : 'secondary' ?> ml-1 badge-sm">
                                <?= $resp['is_visible_to_user'] ? 'Visible' : 'Internal' ?>
                            </span>
                            <small class="text-muted ml-2"><?= timeAgo($resp['created_at']) ?></small>
                            <p class="mt-1 mb-0"><?= nl2br(htmlspecialchars($resp['response_text'])) ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Controls -->
        <div class="col-lg-4">
            <!-- Reporter Info -->
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-user mr-2"></i>Reporter Info</div>
                <div class="card-body">
                    <?php if ($report['is_anonymous']): ?>
                    <div class="text-center text-muted py-2"><i class="fas fa-user-secret fa-2x mb-2"></i><br><em>Anonymous Report</em></div>
                    <?php else: ?>
                    <div class="font-weight-bold"><?= htmlspecialchars($report['full_name']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($report['email']) ?></div>
                    <?php if ($report['course']): ?>
                    <div class="text-muted small"><?= htmlspecialchars($report['course']) ?> – <?= htmlspecialchars($report['year_level']) ?></div>
                    <?php endif; ?>
                    <?php if ($report['contact_number']): ?>
                    <div class="text-muted small"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($report['contact_number']) ?></div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Update Status -->
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-sync mr-2"></i>Update Status</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <?php foreach(['pending'=>'Pending','under_review'=>'Under Review','resolved'=>'Resolved','dismissed'=>'Dismissed'] as $val=>$lbl): ?>
                                <option value="<?=$val?>" <?=$report['status']===$val?'selected':''?>><?=$lbl?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority" class="form-control">
                                <?php foreach(['low','medium','high','critical'] as $p): ?>
                                <option value="<?=$p?>" <?=$report['priority']===$p?'selected':''?>><?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Remarks <small class="text-muted">(optional, internal)</small></label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Internal note on why status changed..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-gerims btn-block"><i class="fas fa-save mr-2"></i>Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="gerims-card">
                <div class="card-header"><i class="fas fa-history mr-2"></i>Status History</div>
                <div class="card-body">
                    <ul class="status-timeline">
                        <li>
                            <div class="tl-dot"><i class="fas fa-plus"></i></div>
                            <div class="tl-body">
                                <div class="font-weight-bold">Submitted</div>
                                <div class="tl-time"><?= date('M d, Y h:i A', strtotime($report['created_at'])) ?></div>
                            </div>
                        </li>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                        <li>
                            <div class="tl-dot" style="background:var(--secondary)"><i class="fas fa-sync"></i></div>
                            <div class="tl-body">
                                <div class="font-weight-bold"><?= ucfirst(str_replace('_',' ',$log['new_status'])) ?></div>
                                <?php if ($log['remarks']): ?>
                                <div class="small text-muted"><?= htmlspecialchars($log['remarks']) ?></div>
                                <?php endif; ?>
                                <div class="tl-time">by <?= htmlspecialchars($log['full_name']) ?><br><?= timeAgo($log['changed_at']) ?></div>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
