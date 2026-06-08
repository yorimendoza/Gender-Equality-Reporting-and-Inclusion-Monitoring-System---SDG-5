<?php
require_once 'includes/config.php';
if (isLoggedIn()) redirect(SITE_URL . '/dashboard.php');

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($conn, $_POST['full_name'] ?? '');
    $email     = sanitize($conn, $_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $gender    = sanitize($conn, $_POST['gender'] ?? '');
    $course    = sanitize($conn, $_POST['course'] ?? '');
    $year      = sanitize($conn, $_POST['year_level'] ?? '');
    $contact   = sanitize($conn, $_POST['contact_number'] ?? '');

    if (!$full_name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email=?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, gender, course, year_level, contact_number) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $full_name, $email, $hash, $gender, $course, $year, $contact);
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now <a href="login.php">login</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register – GERIMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body class="auth-wrapper">
<div class="container">
    <div class="row justify-content-center py-4">
        <div class="col-md-7">
            <div class="auth-card">
                <div class="auth-logo">
                    <div class="logo-circle"><i class="fas fa-user-plus"></i></div>
                    <h4>Create Account</h4>
                    <p>Join GERIMS – Gender Equality Reporting System</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i> <?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> <?= $success ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="Enter email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" placeholder="09xxxxxxxxx" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Non-binary">Non-binary</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Course / Department</label>
                                <input type="text" name="course" class="form-control" placeholder="e.g. BSIT" value="<?= htmlspecialchars($_POST['course'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Year Level</label>
                                <select name="year_level" class="form-control">
                                    <option value="">Select</option>
                                    <option>1st Year</option>
                                    <option>2nd Year</option>
                                    <option>3rd Year</option>
                                    <option>4th Year</option>
                                    <option>Faculty</option>
                                    <option>Staff</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gerims btn-block btn-lg">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </button>
                </form>
                <p class="text-center mt-3 mb-0 small">
                    Already have an account? <a href="<?= SITE_URL ?>/login.php" style="color:var(--primary);font-weight:700">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>setTimeout(function(){$('.alert').fadeOut();},5000);</script>
</body>
</html>
