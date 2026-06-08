<?php
require_once 'includes/config.php';
requireLogin();
if (isAdmin()) redirect(SITE_URL . '/dashboard.php');
$page_title = 'Give Feedback';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type      = sanitize($conn, $_POST['feedback_type'] ?? 'general');
    $subject   = sanitize($conn, $_POST['subject'] ?? '');
    $message   = sanitize($conn, $_POST['message'] ?? '');
    $rating    = (int)($_POST['rating'] ?? 0);
    $anon      = isset($_POST['is_anonymous']) ? 1 : 0;
    $uid       = $_SESSION['user_id'];

    if (!$message) {
        $error = 'Message is required.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating.';
    } else {
        $stmt = $conn->prepare("INSERT INTO feedbacks (user_id, feedback_type, subject, message, rating, is_anonymous) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("isssii", $uid, $type, $subject, $message, $rating, $anon);
        if ($stmt->execute()) {
            logAudit($conn, $uid, 'SUBMIT_FEEDBACK', 'feedbacks', $conn->insert_id);
            $success = 'Thank you! Your feedback has been submitted successfully.';
        } else {
            $error = 'Failed to submit feedback.';
        }
    }
}

include 'includes/header.php';
?>
<div class="container" style="max-width:680px">
    <h4 class="mb-4"><i class="fas fa-comment-dots mr-2" style="color:var(--secondary)"></i>Share Your Feedback</h4>

    <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i><?= $error ?></div>
    <?php endif; ?>

    <div class="gerims-card">
        <div class="card-header"><i class="fas fa-star mr-2"></i>Feedback Form</div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Feedback Type</label>
                            <select name="feedback_type" class="form-control">
                                <option value="general">General</option>
                                <option value="system">System / Platform</option>
                                <option value="policy">Policy Suggestion</option>
                                <option value="report">About a Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rating <span class="text-danger">*</span></label>
                            <div class="star-rating">
                                <?php for($i=5;$i>=1;$i--): ?>
                                    <input type="radio"
                                        name="rating"
                                        id="star<?=$i?>"
                                        value="<?=$i?>">
                                    <label for="star<?=$i?>" class="star-lbl">
                                        &#9733;
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Briefly describe your feedback topic" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Message <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control" rows="5" maxlength="1500"
                        placeholder="Share your thoughts, suggestions, or experiences..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous">
                        <label class="custom-control-label" for="is_anonymous">Submit anonymously</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-gerims-pink px-4">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Feedback
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.star-lbl { transition: color 0.1s; }
.star-lbl:hover, input[type=radio]:checked ~ label { color: #f59e0b; }
</style>
<script>
// Star rating interactivity
$(document).ready(function(){
    $('.star-lbl').on('mouseover', function(){
        $(this).css('color','#f59e0b');
        $(this).prevAll('.star-lbl').css('color','#f59e0b');
        $(this).nextAll('.star-lbl').css('color','#ddd');
    }).on('click', function(){
        var val = $(this).prev('input').val();
        $('#star'+val).prop('checked',true);
    });
    $('.star-rating').on('mouseleave', function(){
        var checked = $('input[name=rating]:checked').val();
        $('.star-lbl').css('color','#ddd');
        if(checked){ for(var i=1;i<=checked;i++) $('#star'+i).next('label').css('color','#f59e0b'); }
    });
});
</script>
<?php include 'includes/footer.php'; ?>
