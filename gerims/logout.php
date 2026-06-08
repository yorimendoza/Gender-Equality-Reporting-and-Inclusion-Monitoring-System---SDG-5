<?php
require_once 'includes/config.php';
if (isLoggedIn()) {
    logAudit($conn, $_SESSION['user_id'], 'LOGOUT');
}
session_destroy();
redirect(SITE_URL . '/login.php');
