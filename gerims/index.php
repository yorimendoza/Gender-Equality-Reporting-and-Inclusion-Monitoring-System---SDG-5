<?php
require_once 'includes/config.php';
if (isLoggedIn()) redirect(SITE_URL . '/dashboard.php');
redirect(SITE_URL . '/login.php');
