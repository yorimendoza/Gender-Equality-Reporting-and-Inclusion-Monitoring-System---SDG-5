<?php
// ============================================================
// GERIMS - Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gerims_db');

define('SITE_NAME', 'GERIMS');
define('SITE_FULL_NAME', 'Gender Equality Reporting & Inclusion Monitoring System');
define('SITE_URL', 'http://localhost/gerims');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:2rem;color:#c0392b;'>
        <h3>Database Connection Failed</h3>
        <p>Please make sure XAMPP MySQL is running and the database <strong>gerims_db</strong> is imported.</p>
        <p>Error: " . $conn->connect_error . "</p>
    </div>");
}
$conn->set_charset("utf8mb4");

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect(SITE_URL . '/dashboard.php');
    }
}

function logAudit($conn, $user_id, $action, $table = null, $target_id = null, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, target_table, target_id, details, ip_address) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ississ", $user_id, $action, $table, $target_id, $details, $ip);
    $stmt->execute();
}

function getUnreadNotifCount($conn, $user_id) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id=$user_id AND is_read=0");
    return $res->fetch_assoc()['cnt'] ?? 0;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    return date('M d, Y', $time);
}

function statusBadge($status) {
    $map = [
        'pending'      => ['warning',  'Pending'],
        'under_review' => ['info',     'Under Review'],
        'resolved'     => ['success',  'Resolved'],
        'dismissed'    => ['secondary','Dismissed'],
    ];
    $s = $map[$status] ?? ['secondary', ucfirst($status)];
    return "<span class='badge badge-{$s[0]}'>{$s[1]}</span>";
}

function priorityBadge($p) {
    $map = ['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'dark'];
    $cl = $map[$p] ?? 'secondary';
    return "<span class='badge badge-{$cl}'>".ucfirst($p)."</span>";
}
?>
