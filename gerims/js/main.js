// GERIMS - Main JavaScript

$(document).ready(function () {

    // Auto-dismiss alerts after 4 seconds
    setTimeout(function () {
        $('.alert-dismissible').fadeOut(600, function () { $(this).remove(); });
    }, 4000);

    // Confirm delete actions
    $('[data-confirm]').on('click', function (e) {
        var msg = $(this).data('confirm') || 'Are you sure you want to do this?';
        if (!confirm(msg)) { e.preventDefault(); }
    });

    // Category selector on report form
    $('.cat-card').on('click', function () {
        $('.cat-card').removeClass('selected');
        $(this).addClass('selected');
        $('#category_id').val($(this).data('cat-id'));
    });

    // Character counter for textareas
    $('textarea[maxlength]').each(function () {
        var max = $(this).attr('maxlength');
        var counter = $('<small class="text-muted char-counter">0 / ' + max + '</small>');
        $(this).after(counter);
        $(this).on('input', function () {
            counter.text($(this).val().length + ' / ' + max);
        });
    });

    // Mark notifications as read when dropdown opens
    $('.nav-link[href*="notifications"]').on('click', function () {
        $.post(window.SITE_URL + '/mark_notif_read.php');
    });

    // Table search filter
    $('#tableSearch').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('table tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    // Tooltip init
    $('[data-toggle="tooltip"]').tooltip();

    // Print report
    $('#btnPrint').on('click', function () { window.print(); });

    // Confirm logout
    $('a[href*="logout"]').on('click', function (e) {
        if (!confirm('Are you sure you want to logout?')) e.preventDefault();
    });
});
