<?php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$rid = (int)($_GET['id'] ?? 0);
redirect(SITE_URL . '/admin/edit_report.php?id=' . $rid);
