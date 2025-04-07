<?php
/*
Plugin Name: Rotify-Comments
Description: افزونه‌ای برای مدیریت پرسش‌ها و نظرات با فرم و پنل ادمین
Version: 1.2
Author: نام شما
*/

// ثبت پست‌تایپ‌ها
function rotify_register_post_types() {
    register_post_type('rotify_qa',
        array(
            'labels' => array(
                'name' => __('پرسش‌ها'),
                'singular_name' => __('پرسش'),
                'menu_name' => __('نظرات و پرسش‌ها'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 4,
            'menu_icon' => 'dashicons-admin-comments',
            'supports' => array('title', 'editor'),
            'capability_type' => 'post',
        )
    );

    register_post_type('rotify_comment',
        array(
            'labels' => array(
                'name' => __('نظرات'),
                'singular_name' => __('نظر'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=rotify_qa',
            'supports' => array('title', 'editor'),
            'capability_type' => 'post',
        )
    );
}
add_action('init', 'rotify_register_post_types');

// منوی تنظیمات
function rotify_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=rotify_qa',
        'تنظیمات Rotify',
        'تنظیمات',
        'manage_options',
        'rotify-settings',
        'rotify_settings_page'
    );
}
add_action('admin_menu', 'rotify_settings_menu');

function rotify_settings_page() {
    if (isset($_POST['rotify_settings_save'])) {
        update_option('rotify_require_purchase', isset($_POST['rotify_require_purchase']) ? 1 : 0);
        update_option('rotify_user_image', sanitize_text_field($_POST['rotify_user_image']));
        update_option('rotify_expert_image', sanitize_text_field($_POST['rotify_expert_image']));
        echo '<div class="rotify-notice rotify-updated"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
    }
    $require_purchase = get_option('rotify_require_purchase', 0);
    $user_image = get_option('rotify_user_image', '');
    $expert_image = get_option('rotify_expert_image', '');
    ?>
    <div class="rotify-wrap">
        <h1 class="rotify-title">تنظیمات Rotify-Comments</h1>
        <form method="post" class="rotify-settings-form">
            <table class="rotify-form-table">
                <tr class="rotify-form-row">
                    <th class="rotify-form-th"><label for="rotify_require_purchase" class="rotify-label">نیاز به خرید محصول برای نظر دادن</label></th>
                    <td class="rotify-form-td"><input type="checkbox" name="rotify_require_purchase" id="rotify_require_purchase" value="1" <?php checked($require_purchase, 1); ?> class="rotify-checkbox"></td>
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
    register_setting('rotify_settings', 'rotify_user_image');
    register_setting('rotify_settings', 'rotify_expert_image');
}
add_action('admin_init', 'rotify_register_settings');

// تعداد موارد در انتظار
function rotify_pending_count() {
    global $menu;
    $qa_pending = wp_count_posts('rotify_qa')->pending;
    $comment_pending = wp_count_posts('rotify_comment')->pending;
    $total_pending = $qa_pending + $comment_pending;

    if ($total_pending > 0) {
        foreach ($menu as $key => $value) {
            if ($menu[$key][2] === 'edit.php?post_type=rotify_qa') {
                $menu[$key][0] .= " <span class='rotify-awaiting-mod rotify-count-$total_pending'>$total_pending</span>";
            }
        }
    }
}
add_action('admin_menu', 'rotify_pending_count');

// استایل‌ها
function rotify_admin_styles() {
    ?>
    <style>
        .rotify-menu { font-weight: 600; color: #fff; background: linear-gradient(90deg, #0073aa, #00a0d2); padding: 10px 8px !important; border-radius: 5px; transition: all 0.3s ease; }
        .rotify-menu:hover { background: linear-gradient(90deg, #005177, #0073aa); }
        .rotify-menu-icon { padding: 7px 0 0 0 !important; }
        .rotify-awaiting-mod { background: #d54e21; color: white; border-radius: 50%; padding: 2px 6px; margin-right: 5px; font-size: 12px; }
        .rotify-star-rating { direction: rtl; font-size: 24px; color: #ddd; }
        .rotify-star-rating input[type="radio"] { display: none; }
        .rotify-star-rating label { cursor: pointer; padding: 0 5px; }
        .rotify-star-rating input[type="radio"]:checked ~ label, .rotify-star-rating label:hover, .rotify-star-rating label:hover ~ label { color: #f1c40f; }
        .rotify-comment-item img { width: 40px; height: 40px; border-radius: 50%; margin-left: 10px; vertical-align: middle; }
        .rotify-input, .rotify-textarea, .rotify-select { width: 100%; margin-top: 5px; }
        .rotify-form-group { margin-bottom: 15px; }
        .rotify-button { padding: 10px 20px; border: none; cursor: pointer; }
        .rotify-primary { background: #007bff; color: white; }
        .rotify-disabled { background: #ccc; cursor: not-allowed; }
        .rotify-error { color: red; margin-top: 10px; }
    </style>
    <?php
}
add_action('wp_head', 'rotify_admin_styles');
add_action('admin_head', 'rotify_admin_styles');

// متاباکس
function rotify_add_meta_boxes() {
    add_meta_box('rotify_meta', 'جزئیات', 'rotify_meta_box_callback', array('rotify_qa', 'rotify_comment'), 'side');
}
add_action('add_meta_boxes', 'rotify_add_meta_boxes');

function rotify_meta_box_callback($post) {
    $contact = get_post_meta($post->ID, '_rotify_contact', true);
    $username = get_post_meta($post->ID, '_rotify_username', true);
    $context = get_post_meta($post->ID, '_rotify_context', true);
    $context_link = get_post_meta($post->ID, '_rotify_context_link', true);
    $fullname = get_post_meta($post->ID, '_rotify_fullname', true);
    $rating = get_post_meta($post->ID, '_rotify_rating', true);

    if ($post->post_type === 'rotify_qa') {
        ?>
        <p class="rotify-meta-field"><label class="rotify-label">شماره تماس:</label><br><input type="text" name="rotify_contact" value="<?php echo esc_attr($contact); ?>" class="rotify-input"></p>
        <p class="rotify-meta-field"><label class="rotify-label">نوع ارسال:</label><br><span class="rotify-text"><?php echo $username ? 'با نام کاربری' : 'ناشناس'; ?></span></p>
        <p class="rotify-meta-field"><label class="rotify-label">مربوط به:</label><br><span class="rotify-text"><?php echo esc_html($context); ?></span></p>
        <p class="rotify-meta-field"><label class="rotify-label">لینک صفحه:</label><br><a href="<?php echo esc_url($context_link); ?>" target="_blank" class="rotify-link"><?php echo esc_url($context_link); ?></a></p>
        <?php
    } elseif ($post->post_type === 'rotify_comment') {
        ?>
        <p class="rotify-meta-field"><label class="rotify-label">نام و نام خانوادگی:</label><br><input type="text" name="rotify_fullname" value="<?php echo esc_attr($fullname); ?>" class="rotify-input"></p>
        <p class="rotify-meta-field"><label class="rotify-label">امتیاز:</label><br><input type="number" name="rotify_rating" min="1" max="5" value="<?php echo esc_attr($rating); ?>" class="rotify-input"></p>
        <p class="rotify-meta-field"><label class="rotify-label">محصول:</label><br><span class="rotify-text"><?php echo esc_html($context); ?></span></p>
        <p class="rotify-meta-field"><label class="rotify-label">لینک محصول:</label><br><a href="<?php echo esc_url($context_link); ?>" target="_blank" class="rotify-link"><?php echo esc_url($context_link); ?></a></p>
        <?php
    }
}

// ذخیره متادیتا
function rotify_save_meta($post_id) {
    if ($post_id && isset($_POST['rotify_contact'])) {
        update_post_meta($post_id, '_rotify_contact', sanitize_text_field($_POST['rotify_contact']));
    }
    if ($post_id && isset($_POST['rotify_fullname'])) {
        update_post_meta($post_id, '_rotify_fullname', sanitize_text_field($_POST['rotify_fullname']));
    }
    if ($post_id && isset($_POST['rotify_rating'])) {
        update_post_meta($post_id, '_rotify_rating', intval($_POST['rotify_rating']));
    }
}
add_action('save_post', 'rotify_save_meta');

// فرم پرسش
function rotify_qa_form_shortcode() {
    ob_start();
    global $post;
    $context_name = ($post && !is_home() && !is_front_page()) ? get_the_title($post->ID) : 'عمومی';
    $context_link = ($post && !is_home() && !is_front_page()) ? get_permalink($post->ID) : home_url();
    ?>
    <form method="post" class="rotify-qa-form">
        <div class="rotify-form-group">
            <label class="rotify-label">سوال شما:</label>
            <textarea name="qa_content" class="rotify-textarea" required></textarea>
        </div>
        <div class="rotify-form-group">
            <label class="rotify-label">شماره تماس (اختیاری):</label>
            <input type="text" name="qa_contact" class="rotify-input">
        </div>
        <div class="rotify-form-group">
            <label class="rotify-label">نحوه نمایش:</label>
            <select name="qa_username" class="rotify-select">
                <option value="1">ارسال با نام کاربری</option>
                <option value="0">ارسال ناشناس</option>
            </select>
        </div>
        <input type="submit" name="qa_submit" value="ارسال سوال" class="rotify-button rotify-primary">
    </form>
    <?php
    if (isset($_POST['qa_submit'])) {
        $post_title = ($post && !is_home() && !is_front_page()) ? 'پرسش در ' . $context_name : 'پرسش جدید';
        $post_id = wp_insert_post(array(
            'post_type' => 'rotify_qa',
            'post_title' => $post_title,
            'post_content' => sanitize_textarea_field($_POST['qa_content']),
            'post_status' => 'pending'
        ));
        
        if ($post_id) {
            update_post_meta($post_id, '_rotify_username', intval($_POST['qa_username']));
            update_post_meta($post_id, '_rotify_contact', sanitize_text_field($_POST['qa_contact']));
            update_post_meta($post_id, '_rotify_context', $context_name);
            update_post_meta($post_id, '_rotify_context_link', $context_link);
            echo '<p class="rotify-success">سوال شما با موفقیت ارسال شد و در انتظار تأیید است.</p>';
        }
    }
    return ob_get_clean();
}
add_shortcode('rotify_qa_form', 'rotify_qa_form_shortcode');

// فرم نظرات
function rotify_comment_form_shortcode() {
    ob_start();
    global $post;
    if (!function_exists('wc_get_product') || $post->post_type !== 'product') {
        return '<p class="rotify-error">این فرم فقط در صفحات محصولات ووکامرس کار می‌کند.</p>';
    }
    $product_id = $post->ID;
    $product_name = get_the_title($product_id);
    $product_link = get_permalink($product_id);
    $require_purchase = get_option('rotify_require_purchase', 0);
    $user_has_purchased = false;

    if (is_user_logged_in() && $require_purchase) {
        $user_id = get_current_user_id();
        $user_has_purchased = wc_customer_bought_product('', $user_id, $product_id);
    }

    $can_submit = !$require_purchase || ($require_purchase && $user_has_purchased);
    ?>
    <form method="post" class="rotify-comment-form">
        <div class="rotify-form-group">
            <label class="rotify-label">نام و نام خانوادگی:</label>
            <input type="text" name="comment_fullname" class="rotify-input" required>
        </div>
        <div class="rotify-form-group">
            <label class="rotify-label">امتیاز:</label>
            <div class="rotify-star-rating">
                <input type="radio" id="rotify-star5" name="comment_rating" value="5" required class="rotify-star-input"><label for="rotify-star5" class="rotify-star-label">★</label>
                <input type="radio" id="rotify-star4" name="comment_rating" value="4" class="rotify-star-input"><label for="rotify-star4" class="rotify-star-label">★</label>
                <input type="radio" id="rotify-star3" name="comment_rating" value="3" class="rotify-star-input"><label for="rotify-star3" class="rotify-star-label">★</label>
                <input type="radio" id="rotify-star2" name="comment_rating" value="2" class="rotify-star-input"><label for="rotify-star2" class="rotify-star-label">★</label>
                <input type="radio" id="rotify-star1" name="comment_rating" value="1" class="rotify-star-input"><label for="rotify-star1" class="rotify-star-label">★</label>
            </div>
        </div>
        <div class="rotify-form-group">
            <label class="rotify-label">نظر شما:</label>
            <textarea name="comment_content" class="rotify-textarea" required></textarea>
        </div>
        <?php if ($can_submit) : ?>
            <input type="submit" name="comment_submit" value="ارسال نظر" class="rotify-button rotify-primary">
        <?php else : ?>
            <input type="submit" name="comment_submit" value="ارسال نظر" class="rotify-button rotify-disabled" disabled>
            <p class="rotify-error">برای ارسال نظر محصول را خریداری کنید.</p>
        <?php endif; ?>
    </form>
    <?php
    if (isset($_POST['comment_submit']) && $can_submit) {
        $post_title = 'نظر در ' . $product_name;
        $post_id = wp_insert_post(array(
            'post_type' => 'rotify_comment',
            'post_title' => $post_title,
            'post_content' => sanitize_textarea_field($_POST['comment_content']),
            'post_status' => 'pending'
        ));
        
        if ($post_id) {
            update_post_meta($post_id, '_rotify_fullname', sanitize_text_field($_POST['comment_fullname']));
            update_post_meta($post_id, '_rotify_rating', intval($_POST['comment_rating']));
            update_post_meta($post_id, '_rotify_context', $product_name);
            update_post_meta($post_id, '_rotify_context_link', $product_link);
            echo '<p class="rotify-success">نظر شما با موفقیت ارسال شد و در انتظار تأیید است.</p>';
        }
    } elseif (isset($_POST['comment_submit']) && !$can_submit) {
        echo '<p class="rotify-error">برای ارسال نظر محصول را خریداری کنید.</p>';
    }
    return ob_get_clean();
}
add_shortcode('rotify_comment_form', 'rotify_comment_form_shortcode');

// نمایش پرسش‌ها
function rotify_qa_display_shortcode() {
    ob_start();
    $args = array('post_type' => 'rotify_qa', 'post_status' => 'publish', 'posts_per_page' => -1);
    $qa_query = new WP_Query($args);
    ?>
    <div class="rotify-qa-display">
        <?php while ($qa_query->have_posts()) : $qa_query->the_post(); 
            $username = get_post_meta(get_the_ID(), '_rotify_username', true);
            $name = $username && is_user_logged_in() ? wp_get_current_user()->display_name : 'ناشناس';
            $answer = get_post_meta(get_the_ID(), '_wp_editor_answer', true);
        ?>
            <div class="rotify-qa-item">
                <p class="rotify-qa-content"><strong class="rotify-qa-name"><?php echo esc_html($name); ?>:</strong> <?php the_content(); ?></p>
                <?php if ($answer) : ?>
                    <p class="rotify-qa-answer"><strong class="rotify-answer-label">پاسخ کارشناس:</strong> <?php echo wpautop($answer); ?></p>
                <?php endif; ?>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('rotify_qa_display', 'rotify_qa_display_shortcode');

// نمایش نظرات
function rotify_comment_display_shortcode() {
    ob_start();
    $args = array('post_type' => 'rotify_comment', 'post_status' => 'publish', 'posts_per_page' => -1);
    $comment_query = new WP_Query($args);
    $user_image = get_option('rotify_user_image', '');
    $expert_image = get_option('rotify_expert_image', '');
    ?>
    <div class="rotify-comment-display">
        <?php while ($comment_query->have_posts()) : $comment_query->the_post(); 
            $fullname = get_post_meta(get_the_ID(), '_rotify_fullname', true);
            $rating = get_post_meta(get_the_ID(), '_rotify_rating', true);
            $answer = get_post_meta(get_the_ID(), '_wp_editor_answer', true);
        ?>
            <div class="rotify-comment-item">
                <p class="rotify-comment-content">
                    <?php if ($user_image) : ?><img src="<?php echo esc_url($user_image); ?>" alt="کاربر" class="rotify-user-image"><?php endif; ?>
                    <strong class="rotify-comment-name"><?php echo esc_html($fullname); ?>:</strong> <?php the_content(); ?>
                    <span class="rotify-comment-rating">(<?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?>)</span>
                </p>
                <?php if ($answer) : ?>
                    <p class="rotify-comment-answer">
                        <?php if ($expert_image) : ?><img src="<?php echo esc_url($expert_image); ?>" alt="کارشناس" class="rotify-expert-image"><?php endif; ?>
                        <strong class="rotify-answer-label">پاسخ کارشناس:</strong> <?php echo wpautop($answer); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('rotify_comment_display', 'rotify_comment_display_shortcode');

// ستون‌های مدیریتی
function rotify_admin_columns($columns) {
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => 'عنوان',
        'content' => 'محتوا',
        'answer' => 'پاسخ',
        'status' => 'وضعیت',
        'actions' => 'عملیات'
    );
}
add_filter('manage_rotify_qa_posts_columns', 'rotify_admin_columns');
add_filter('manage_rotify_comment_posts_columns', 'rotify_admin_columns');

function rotify_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'content':
            echo '<div class="rotify-column-content">';
            the_content();
            echo '</div>';
            break;
        case 'answer':
            $answer = get_post_meta($post_id, '_wp_editor_answer', true);
            echo '<div class="rotify-column-answer">' . ($answer ? wpautop($answer) : 'بدون پاسخ') . '</div>';
            break;
        case 'status':
            echo '<span class="rotify-column-status">' . (get_post_status($post_id) === 'publish' ? 'تأیید شده' : 'در انتظار') . '</span>';
            break;
        case 'actions':
            echo '<a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '" class="rotify-button rotify-action-edit">پاسخ به کاربر</a>';
            echo ' <a href="' . wp_nonce_url(admin_url('post.php?post=' . $post_id . '&action=trash'), 'trash-post_' . $post_id) . '" class="rotify-button rotify-action-delete">حذف</a>';
            break;
    }
}
add_action('manage_rotify_qa_posts_custom_column', 'rotify_admin_column_content', 10, 2);
add_action('manage_rotify_comment_posts_custom_column', 'rotify_admin_column_content', 10, 2);

// ویرایشگر پاسخ
function rotify_answer_editor($post) {
    if (!in_array($post->post_type, array('rotify_qa', 'rotify_comment'))) return;
    $answer = get_post_meta($post->ID, '_wp_editor_answer', true);
    echo '<h2 class="rotify-editor-title">پاسخ به کاربر</h2>';
    wp_editor($answer, 'rotify_answer_editor', array('textarea_name' => 'rotify_answer', 'textarea_rows' => 10));
}
add_action('edit_form_after_editor', 'rotify_answer_editor');

function rotify_save_answer($post_id) {
    if (isset($_POST['rotify_answer'])) {
        update_post_meta($post_id, '_wp_editor_answer', wp_kses_post($_POST['rotify_answer']));
    }
}
add_action('save_post', 'rotify_save_answer');
