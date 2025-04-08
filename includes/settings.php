<?php
if (!defined('ABSPATH')) {
    exit;
}

// تابع صفحه تنظیمات
function rotify_settings_page() {
    if (isset($_POST['rotify_settings_save']) && isset($_POST['rotify_settings_nonce']) && wp_verify_nonce($_POST['rotify_settings_nonce'], 'rotify_settings_save')) {
        update_option('rotify_require_purchase', isset($_POST['rotify_require_purchase']) ? 1 : 0);
        update_option('rotify_require_login_qa', isset($_POST['rotify_require_login_qa']) ? 1 : 0);
        update_option('rotify_user_image', sanitize_text_field($_POST['rotify_user_image']));
        update_option('rotify_expert_image', sanitize_text_field($_POST['rotify_expert_image']));
        echo '<div class="rotify-notice rotify-updated"><p class="rotify-notice-text">تنظیمات با موفقیت ذخیره شد.</p></div>';
    }
    $require_purchase = get_option('rotify_require_purchase', 0);
    $require_login_qa = get_option('rotify_require_login_qa', 0);
    $user_image = get_option('rotify_user_image', '');
    $expert_image = get_option('rotify_expert_image', '');
    ?>
    <div class="rotify-wrap">
        <h1 class="rotify-title">تنظیمات Rotify-Comments</h1>
        <form method="post" class="rotify-settings-form">
            <?php wp_nonce_field('rotify_settings_save', 'rotify_settings_nonce'); ?>
            <table class="rotify-form-table">
                <tr class="rotify-form-row">
                    <th class="rotify-form-th"><label for="rotify_require_purchase" class="rotify-label">نیاز به خرید محصول برای نظرات</label></th>
                    <td class="rotify-form-td"><input type="checkbox" name="rotify_require_purchase" id="rotify_require_purchase" value="1" <?php checked($require_purchase, 1); ?> class="rotify-checkbox"></td>
                </tr>
                <tr class="rotify-form-row">
                    <th class="rotify-form-th"><label for="rotify_require_login_qa" class="rotify-label">نیاز به لاگین برای پرسش‌ها</label></th>
                    <td class="rotify-form-td"><input type="checkbox" name="rotify_require_login_qa" id="rotify_require_login_qa" value="1" <?php checked($require_login_qa, 1); ?> class="rotify-checkbox"></td>
                </tr>
                <tr class="rotify-form-row">
                    <th class="rotify-form-th"><label for="rotify_user_image" class="rotify-label">تصویر کاربر</label></th>
                    <td class="rotify-form-td">
                        <input type="text" name="rotify_user_image" id="rotify_user_image" value="<?php echo esc_attr($user_image); ?>" class="rotify-input rotify-text">
                        <input type="button" class="rotify-button rotify-upload-button" value="آپلود تصویر" data-target="#rotify_user_image">
                    </td>
                </tr>
                <tr class="rotify-form-row">
                    <th class="rotify-form-th"><label for="rotify_expert_image" class="rotify-label">تصویر کارشناس</label></th>
                    <td class="rotify-form-td">
                        <input type="text" name="rotify_expert_image" id="rotify_expert_image" value="<?php echo esc_attr($expert_image); ?>" class="rotify-input rotify-text">
                        <input type="button" class="rotify-button rotify-upload-button" value="آپلود تصویر" data-target="#rotify_expert_image">
                    </td>
                </tr>
            </table>
            <p class="rotify-submit"><input type="submit" name="rotify_settings_save" class="rotify-button rotify-primary" value="ذخیره تغییرات"></p>
        </form>
				<h4>داکیومنت</h4>
				<p>شورت کد برای نمایش فرم  ارسال پرسش : [rotify_qa_form]</p>
				<p>شورت کد برای نمایش پرسش و پاسخ ها : [rotify_qa_display]</p>
				<p>شورت کد برای نمایش فرم ارسال نظر : [rotify_comment_form]</p>
				<p>شورت کد برای نمایش نظرات :[rotify_comment_display]</p>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('.rotify-upload-button').click(function(e) {
                e.preventDefault();
                var target = $(this).data('target');
                var mediaUploader = wp.media({
                    title: 'انتخاب تصویر',
                    button: { text: 'استفاده از این تصویر' },
                    multiple: false
                }).on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $(target).val(attachment.url);
                }).open();
            });
        });
    </script>
    <?php
}

// ثبت تنظیمات
function rotify_register_settings() {
    register_setting('rotify_settings', 'rotify_require_purchase');
    register_setting('rotify_settings', 'rotify_require_login_qa');
    register_setting('rotify_settings', 'rotify_user_image');
    register_setting('rotify_settings', 'rotify_expert_image');
}
add_action('admin_init', 'rotify_register_settings');
