<?php
require_once 'includes/config.php';
if (isLoggedIn()) {
    $conn->query("UPDATE notifications SET is_read=1 WHERE user_id={$_SESSION['user_id']}");
}
echo json_encode(['ok' => true]);
