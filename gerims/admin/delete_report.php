<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$rid = (int)($_GET['id'] ?? 0);
if ($rid) {
    $conn->query("DELETE FROM reports WHERE report_id=$rid");
    logAudit($conn, $_SESSION['user_id'], 'DELETE_REPORT', 'reports', $rid);
}
redirect(SITE_URL . '/admin/reports.php');
