<?php
/**
 * prevent_submission_by_refresh
 *
 * توابع: prevent_submission_by_refresh
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6110-6116)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function prevent_submission_by_refresh() { ?>
    <script type="text/javascript">
        if ( window.history.replaceState )
            window.history.replaceState( null, null, window.location.href );
    </script>
    <?php
}
