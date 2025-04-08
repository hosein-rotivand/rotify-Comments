<?php
if (!defined('ABSPATH')) {
    exit;
}

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
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['rotify_contact'])) {
        update_post_meta($post_id, '_rotify_contact', sanitize_text_field($_POST['rotify_contact']));
    }
    if (isset($_POST['rotify_fullname'])) {
        update_post_meta($post_id, '_rotify_fullname', sanitize_text_field($_POST['rotify_fullname']));
    }
    if (isset($_POST['rotify_rating'])) {
        update_post_meta($post_id, '_rotify_rating', intval($_POST['rotify_rating']));
    }
}
add_action('save_post', 'rotify_save_meta');

// ستون‌های مدیریتی
function rotify_admin_columns($columns) {
    return array(
        'cb' => '<input type="checkbox" class="rotify-checkbox" />',
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
            $answer = get_post_meta($post_id, '_rotify_answer', true);
            echo '<div class="rotify-column-answer">' . ($answer ? wpautop($answer) : 'بدون پاسخ') . '</div>';
            break;
        case 'status':
            echo '<span class="rotify-column-status">' . (get_post_status($post_id) === 'publish' ? 'تأیید شده' : 'در انتظار') . '</span>';
            break;
        case 'actions':
            echo '<a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '" class="rotify-button rotify-action-edit">پاسخ</a>';
            echo ' <a href="' . wp_nonce_url(admin_url('post.php?post=' . $post_id . '&action=trash'), 'trash-post_' . $post_id) . '" class="rotify-button rotify-action-delete">حذف</a>';
            break;
    }
}
add_action('manage_rotify_qa_posts_custom_column', 'rotify_admin_column_content', 10, 2);
add_action('manage_rotify_comment_posts_custom_column', 'rotify_admin_column_content', 10, 2);

// ویرایشگر پاسخ
function rotify_answer_editor($post) {
    if (!in_array($post->post_type, array('rotify_qa', 'rotify_comment'))) return;
    $answer = get_post_meta($post->ID, '_rotify_answer', true);
    echo '<h2 class="rotify-editor-title">پاسخ به کاربر</h2>';
    wp_editor($answer, 'rotify_answer', array('textarea_name' => 'rotify_answer', 'textarea_rows' => 10));
}
add_action('edit_form_after_editor', 'rotify_answer_editor');

function rotify_save_answer($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['rotify_answer'])) {
        update_post_meta($post_id, '_rotify_answer', wp_kses_post($_POST['rotify_answer']));
    }
}
add_action('save_post', 'rotify_save_answer');
