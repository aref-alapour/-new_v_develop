<?php
/**
 * rating_in_details_admin_html
 *
 * توابع: rating_in_details_admin_html
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3562-3636)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rating_in_details_admin_html($comment) {
    $comment_id = $comment->comment_ID;

    $product_rates = get_comment_meta($comment_id, "comment_rating", true);

    defined('ABSPATH') or die('No script kiddies please!!');
    wp_nonce_field('cld_metabox_nonce1', 'cld_metabox_nonce1_field'); ?>

    <div class="cld-field-wrap">
        <label>فضاسازی</label>
        <div class="cld-field">
            <select name="fazasazi">
                <?php
                for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <option <?php echo $product_rates[1094] / 20 == $i ? 'selected="selected"' : ''; ?> value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php
                endfor;?>
            </select>
        </div>
    </div>

    <div class="cld-field-wrap">
        <label>کیفیت معما</label>
        <div class="cld-field">
            <select name="moama">
                <?php
                for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <option <?php echo $product_rates[1095] / 20 == $i ? 'selected="selected"' : ''; ?> value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php
                endfor;?>
            </select>
        </div>
    </div>

    <div class="cld-field-wrap">
        <label>تازگی و خلاقیت</label>
        <div class="cld-field">
            <select name="tazegi">
                <?php
                for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <option <?php echo $product_rates[1098] / 20 == $i ? 'selected="selected"' : ''; ?> value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php
                endfor;?>
            </select>
        </div>
    </div>

    <div class="cld-field-wrap">
        <label>بازیگردانی و اکت</label>
        <div class="cld-field">
            <select name="act">
                <?php
                for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <option <?php echo $product_rates[1096] / 20 == $i ? 'selected="selected"' : ''; ?> value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php
                endfor;?>
            </select>
        </div>
    </div>

    <div class="cld-field-wrap">
        <label>برخورد پرسنل</label>
        <div class="cld-field">
            <select name="personel">
                <?php
                for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <option <?php echo $product_rates[1097] / 20 == $i ? 'selected="selected"' : ''; ?> value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php
                endfor;?>
            </select>
        </div>
    </div>

    <?php
}
