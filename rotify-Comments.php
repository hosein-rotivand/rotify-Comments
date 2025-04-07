<?php
/*
Plugin Name: Rotify-Comments
Description: افزونه‌ای برای مدیریت پرسش‌ها و نظرات با فرم و پنل ادمین
Version: 1.0
Author: نام شما
*/

// ثبت پست‌تایپ سفارشی برای پرسش‌ها و نظرات
function rotify_register_post_types() {
    // پست‌تایپ پرسش‌ها
    register_post_type('rotify_qa',
        array(
            'labels' => array(
                'name' => __('پرسش‌ها'),
                'singular_name' => __('پرسش'),
                'menu_name' => __('نظرات و پرسش‌ها'), // نام منوی اصلی
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

    // پست‌تایپ نظرات
    register_post_type('rotify_comment',
        array(
            'labels' => array(
                'name' => __('نظرات'),
                'singular_name' => __('نظر'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=rotify_qa', // زیرمنوی "نظرات و پرسش‌ها"
            'supports' => array('title', 'editor'),
            'capability_type' => 'post',
        )
    );
}
add_action('init', 'rotify_register_post_types');

// اضافه کردن تعداد موارد در انتظار به منو
function rotify_pending_count() {
    global $menu;
    $qa_pending = wp_count_posts('rotify_qa')->pending; // پرسش‌های در انتظار
    $comment_pending = wp_count_posts('rotify_comment')->pending; // نظرات در انتظار
    $total_pending = $qa_pending + $comment_pending;

    if ($total_pending > 0) {
        foreach ($menu as $key => $value) {
            if ($menu[$key][2] === 'edit.php?post_type=rotify_qa') {
                $menu[$key][0] .= " <span class='awaiting-mod count-$total_pending' style='background: #d54e21; color: white; border-radius: 50%; padding: 2px 6px; margin-right: 5px; font-size: 12px;'>$total_pending</span>";
            }
        }
    }
}
add_action('admin_menu', 'rotify_pending_count');

// استایل سفارشی برای منو
function rotify_admin_styles() {
    ?>
    <style>
        #toplevel_page_edit-php-post_type-rotify_qa .wp-menu-name {
            font-weight: 600;
            color: #fff;
            background: linear-gradient(90deg, #0073aa, #00a0d2);
            padding: 10px 8px !important;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        #toplevel_page_edit-php-post_type-rotify_qa:hover .wp-menu-name {
            background: linear-gradient(90deg, #005177, #0073aa);
        }
        #toplevel_page_edit-php-post_type-rotify_qa .wp-menu-image {
            padding: 7px 0 0 0 !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'rotify_admin_styles');

// متاباکس برای پرسش‌ها و نظرات
function rotify_add_meta_boxes() {
    add_meta_box('rotify_meta', 'جزئیات', 'rotify_meta_box_callback', array('rotify_qa', 'rotify_comment'), 'side');
}
add_action('add_meta_boxes', 'rotify_add_meta_boxes');

function rotify_meta_box_callback($post) {
    $contact = get_post_meta($post->ID, '_rotify_contact', true);
    $username = get_post_meta($post->ID, '_rotify_username', true);
    $context = get_post_meta($post->ID, '_rotify_context', true);
    $context_link = get_post_meta($post->ID, '_rotify_context_link', true);
    $email = get_post_meta($post->ID, '_rotify_email', true);
    $fullname = get_post_meta($post->ID, '_rotify_fullname', true);
    $rating = get_post_meta($post->ID, '_rotify_rating', true);

    if ($post->post_type === 'rotify_qa') { // برای پرسش‌ها
        ?>
        <p><label>شماره تماس:</label><br><input type="text" name="rotify_contact" value="<?php echo esc_attr($contact); ?>" style="width: 100%;"></p>
        <p><label>نوع ارسال:</label><br><?php echo $username ? 'با نام کاربری' : 'ناشناس'; ?></p>
        <p><label>مربوط به:</label><br><?php echo esc_html($context); ?></p>
        <p><label>لینک صفحه:</label><br><a href="<?php echo esc_url($context_link); ?>" target="_blank"><?php echo esc_url($context_link); ?></a></p>
        <?php
    } elseif ($post->post_type === 'rotify_comment') { // برای نظرات
        ?>
        <p><label>ایمیل:</label><br><input type="email" name="rotify_email" value="<?php echo esc_attr($email); ?>" style="width: 100%;"></p>
        <p><label>نام و نام خانوادگی:</label><br><input type="text" name="rotify_fullname" value="<?php echo esc_attr($fullname); ?>" style="width: 100%;"></p>
        <p><label>امتیاز:</label><br><input type="number" name="rotify_rating" min="1" max="5" value="<?php echo esc_attr($rating); ?>" style="width: 100%;"></p>
        <p><label>محصول:</label><br><?php echo esc_html($context); ?></p>
        <p><label>لینک محصول:</label><br><a href="<?php echo esc_url($context_link); ?>" target="_blank"><?php echo esc_url($context_link); ?></a></p>
        <?php
    }
}

// ذخیره متادیتا
function rotify_save_meta($post_id) {
    if ($post_id && isset($_POST['rotify_contact'])) {
        update_post_meta($post_id, '_rotify_contact', sanitize_text_field($_POST['rotify_contact']));
    }
    if ($post_id && isset($_POST['rotify_email'])) {
        update_post_meta($post_id, '_rotify_email', sanitize_email($_POST['rotify_email']));
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
    <form method="post" class="rotify-qa-form" style="max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ddd;">
        <div style="margin-bottom: 15px;">
            <label>سوال شما:</label><br>
            <textarea name="qa_content" required style="width: 100%; height: 100px;"></textarea>
        </div>
        <div style="margin-bottom: 15px;">
            <label>شماره تماس (اختیاری):</label><br>
            <input type="text" name="qa_contact" style="width: 100%;">
        </div>
        <div style="margin-bottom: 15px;">
            <label>نحوه نمایش:</label><br>
            <select name="qa_username" style="width: 100%;">
                <option value="1">ارسال با نام کاربری</option>
                <option value="0">ارسال ناشناس</option>
            </select>
        </div>
        <input type="submit" name="qa_submit" value="ارسال سوال" style="background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">
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
            echo '<p style="color: green; text-align: center;">سوال شما با موفقیت ارسال شد و در انتظار تأیید است.</p>';
        }
    }
    return ob_get_clean();
}
add_shortcode('rotify_qa_form', 'rotify_qa_form_shortcode');

// فرم نظرات برای ووکامرس
function rotify_comment_form_shortcode() {
    ob_start();
    global $post;
    if (!function_exists('wc_get_product') || $post->post_type !== 'product') {
        return '<p style="color: red;">این فرم فقط در صفحات محصولات ووکامرس کار می‌کند.</p>';
    }
    $product_name = get_the_title($post->ID);
    $product_link = get_permalink($post->ID);
    ?>
    <form method="post" class="rotify-comment-form" style="max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ddd;">
        <div style="margin-bottom: 15px;">
            <label>ایمیل:</label><br>
            <input type="email" name="comment_email" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 15px;">
            <label>نام و نام خانوادگی:</label><br>
            <input type="text" name="comment_fullname" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 15px;">
            <label>امتیاز (۱ تا ۵):</label><br>
            <select name="comment_rating" required style="width: 100%;">
                <option value="1">۱ ستاره</option>
                <option value="2">۲ ستاره</option>
                <option value="3">۳ ستاره</option>
                <option value="4">۴ ستاره</option>
                <option value="5">۵ ستاره</option>
            </select>
        </div>
        <div style="margin-bottom: 15px;">
            <label>نظر شما:</label><br>
            <textarea name="comment_content" required style="width: 100%; height: 100px;"></textarea>
        </div>
        <input type="submit" name="comment_submit" value="ارسال نظر" style="background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">
    </form>
    <?php
    if (isset($_POST['comment_submit'])) {
        $post_title = 'نظر در ' . $product_name;
        $post_id = wp_insert_post(array(
            'post_type' => 'rotify_comment',
            'post_title' => $post_title,
            'post_content' => sanitize_textarea_field($_POST['comment_content']),
            'post_status' => 'pending'
        ));
        
        if ($post_id) {
            update_post_meta($post_id, '_rotify_email', sanitize_email($_POST['comment_email']));
            update_post_meta($post_id, '_rotify_fullname', sanitize_text_field($_POST['comment_fullname']));
            update_post_meta($post_id, '_rotify_rating', intval($_POST['comment_rating']));
            update_post_meta($post_id, '_rotify_context', $product_name);
            update_post_meta($post_id, '_rotify_context_link', $product_link);
            echo '<p style="color: green; text-align: center;">نظر شما با موفقیت ارسال شد و در انتظار تأیید است.</p>';
        }
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
    <div class="rotify-qa-display" style="max-width: 700px; margin: 20px auto;">
        <?php while ($qa_query->have_posts()) : $qa_query->the_post(); 
            $username = get_post_meta(get_the_ID(), '_rotify_username', true);
            $name = $username && is_user_logged_in() ? wp_get_current_user()->display_name : 'ناشناس';
            $answer = get_post_meta(get_the_ID(), '_wp_editor_answer', true);
        ?>
            <div class="qa-item" style="padding: 15px; border-bottom: 1px solid #ddd;">
                <p style="margin: 0 0 10px;"><strong><?php echo esc_html($name); ?>:</strong> <?php the_content(); ?></p>
                <?php if ($answer) : ?>
                    <p style="margin: 0; color: #666;"><strong>پاسخ کارشناس:</strong> <?php echo wpautop($answer); ?></p>
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
    ?>
    <div class="rotify-comment-display" style="max-width: 700px; margin: 20px auto;">
        <?php while ($comment_query->have_posts()) : $comment_query->the_post(); 
            $fullname = get_post_meta(get_the_ID(), '_rotify_fullname', true);
            $rating = get_post_meta(get_the_ID(), '_rotify_rating', true);
            $answer = get_post_meta(get_the_ID(), '_wp_editor_answer', true);
        ?>
            <div class="comment-item" style="padding: 15px; border-bottom: 1px solid #ddd;">
                <p style="margin: 0 0 10px;">
                    <strong><?php echo esc_html($fullname); ?>:</strong> <?php the_content(); ?>
                    <span style="color: #f1c40f;">(<?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?>)</span>
                </p>
                <?php if ($answer) : ?>
                    <p style="margin: 0; color: #666;"><strong>پاسخ کارشناس:</strong> <?php echo wpautop($answer); ?></p>
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
            the_content();
            break;
        case 'answer':
            $answer = get_post_meta($post_id, '_wp_editor_answer', true);
            echo $answer ? wpautop($answer) : 'بدون پاسخ';
            break;
        case 'status':
            echo get_post_status($post_id) === 'publish' ? 'تأیید شده' : 'در انتظار';
            break;
        case 'actions':
            echo '<a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '" class="button">پاسخ به کاربر</a>';
            echo ' <a href="' . wp_nonce_url(admin_url('post.php?post=' . $post_id . '&action=trash'), 'trash-post_' . $post_id) . '" class="button" style="color: red;">حذف</a>';
            break;
    }
}
add_action('manage_rotify_qa_posts_custom_column', 'rotify_admin_column_content', 10, 2);
add_action('manage_rotify_comment_posts_custom_column', 'rotify_admin_column_content', 10, 2);

// ویرایشگر پاسخ
function rotify_answer_editor($post) {
    if (!in_array($post->post_type, array('rotify_qa', 'rotify_comment'))) return;
    $answer = get_post_meta($post->ID, '_wp_editor_answer', true);
    echo '<h2>پاسخ به کاربر</h2>';
    wp_editor($answer, 'rotify_answer_editor', array('textarea_name' => 'rotify_answer'));
}
add_action('edit_form_after_editor', 'rotify_answer_editor');

function rotify_save_answer($post_id) {
    if (isset($_POST['rotify_answer'])) {
        update_post_meta($post_id, '_wp_editor_answer', wp_kses_post($_POST['rotify_answer']));
    }
}
add_action('save_post', 'rotify_save_answer');
