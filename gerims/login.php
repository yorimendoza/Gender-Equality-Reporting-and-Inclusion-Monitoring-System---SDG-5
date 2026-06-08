<?php
require_once 'includes/config.php';
if (isLoggedIn()) redirect(SITE_URL . '/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        // Support both hashed and plain-text passwords (dev convenience)
        $valid = $user && (password_verify($password, $user['password']) || $password === $user['password']);

        if ($valid) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['email']     = $user['email'];
            logAudit($conn, $user['user_id'], 'LOGIN');
            redirect(SITE_URL . '/dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login – GERIMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body class="auth-wrapper">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card">
                <div class="auth-logo">
                    <div class="logo-circle"><i class="fas fa-venus-mars"></i></div>
                    <h4>GERIMS</h4>
                    <p>Gender Equality Reporting &amp; Inclusion Monitoring System</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle mr-1"></i> <?= $error ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-envelope mr-1 text-muted"></i> Email Address</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="Enter your email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock mr-1 text-muted"></i> Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control" placeholder="Enter your password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gerims btn-block btn-lg mt-3">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                </form>

                <hr>
                <p class="text-center mb-0 small">
                    Don't have an account? <a href="<?= SITE_URL ?>/register.php" class="font-weight-bold" style="color:var(--primary)">Register here</a>
                </p>
                <p class="text-center mt-2 mb-0">
                    <small class="text-muted">Default Admin: <code>admin@gerims.edu</code> / <code>password</code></small>
                </p>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$('#togglePwd').click(function(){
    var t = $('#password');
    t.attr('type', t.attr('type')==='password' ? 'text' : 'password');
    $(this).find('i').toggleClass('fa-eye fa-eye-slash');
});
setTimeout(function(){$('.alert').fadeOut();},4000);
</script>
</body>
</html>
