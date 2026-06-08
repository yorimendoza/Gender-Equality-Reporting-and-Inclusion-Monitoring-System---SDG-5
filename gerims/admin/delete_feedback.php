<?php
// delete_feedback.php
require_once '../includes/config.php';
requireLogin(); requireAdmin();
$id = (int)($_GET['id'] ?? 0);
if ($id) { $conn->query("DELETE FROM feedbacks WHERE feedback_id=$id"); logAudit($conn, $_SESSION['user_id'], 'DELETE_FEEDBACK', 'feedbacks', $id); }
redirect(SITE_URL . '/admin/feedback.php');
