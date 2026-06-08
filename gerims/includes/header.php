<?php
requireLogin();
$notif_count = getUnreadNotifCount($conn, $_SESSION['user_id']);
$user_role = $_SESSION['role'];
$user_name = $_SESSION['full_name'];
$base = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?? SITE_NAME ?> - <?= SITE_FULL_NAME ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/css/style.css?v=<?= time(); ?>">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark gerims-nav fixed-top">
    <a class="navbar-brand d-flex align-items-center" href="<?= $base ?>/dashboard.php">
        <div class="brand-icon mr-2"><i class="fas fa-venus-mars"></i></div>
        <div>
            <span class="brand-name">GERIMS</span>
            <small class="d-block brand-sub" style="font-size:10px;line-height:1">Gender Equality System</small>
        </div>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ml-auto align-items-lg-center">
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/dashboard.php"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
            </li>
            <?php if ($user_role === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/admin/reports.php"><i class="fas fa-file-alt mr-1"></i>All Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/admin/users.php"><i class="fas fa-users mr-1"></i>Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/admin/policies.php"><i class="fas fa-scroll mr-1"></i>Policies</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/admin/announcements.php"><i class="fas fa-bullhorn mr-1"></i>Announcements</a>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/submit_report.php"><i class="fas fa-plus-circle mr-1"></i>Submit Report</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/my_reports.php"><i class="fas fa-list mr-1"></i>My Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/feedback.php"><i class="fas fa-comment-dots mr-1"></i>Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base ?>/policies.php"><i class="fas fa-scroll mr-1"></i>Policies</a>
            </li>
            <?php endif; ?>
            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link position-relative" href="#" data-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <?php if ($notif_count > 0): ?>
                    <span class="notif-badge"><?= $notif_count ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right notif-dropdown">
                    <h6 class="dropdown-header">Notifications</h6>
                    <?php
                    $nq = $conn->query("SELECT n.*, r.title as report_title FROM notifications n 
                        LEFT JOIN reports r ON n.report_id = r.report_id 
                        WHERE n.user_id={$_SESSION['user_id']} ORDER BY n.created_at DESC LIMIT 5");
                    if ($nq->num_rows === 0): ?>
                    <span class="dropdown-item text-muted small">No notifications</span>
                    <?php else: while($n = $nq->fetch_assoc()): ?>
                    <a class="dropdown-item <?= $n['is_read']?'':'font-weight-bold' ?>" 
                       href="<?= $base ?>/notifications.php">
                        <small><?= htmlspecialchars($n['message']) ?></small>
                        <br><small class="text-muted"><?= timeAgo($n['created_at']) ?></small>
                    </a>
                    <?php endwhile; endif; ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center small" href="<?= $base ?>/notifications.php">View all</a>
                </div>
            </li>
            <!-- User Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" href="#" data-toggle="dropdown">
                    <div class="avatar-sm mr-2"><?= strtoupper(substr($user_name,0,1)) ?></div>
                    <span class="d-none d-lg-inline"><?= htmlspecialchars(explode(' ',$user_name)[0]) ?></span>
                    <i class="fas fa-caret-down ml-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header">
                        <strong><?= htmlspecialchars($user_name) ?></strong>
                        <br><small class="text-muted"><?= ucfirst($user_role) ?></small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= $base ?>/profile.php"><i class="fas fa-user-edit mr-2"></i>Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="<?= $base ?>/logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<div class="content-wrapper">
