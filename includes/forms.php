<?php
if (!defined('ABSPATH')) {
    exit;
}

// فرم پرسش
function rotify_qa_form_shortcode() {
    ob_start();
    global $post;
    $context_name = ($post && !is_home() && !is_front_page()) ? get_the_title($post->ID) : 'عمومی';
    $context_link = ($post && !is_home() && !is_front_page()) ? get_permalink($post->ID) : home_url();
    $require_login = get_option('rotify_require_login_qa', 0);
    
    if ($require_login && !is_user_logged_in()) {
        echo '<p class="rotify-error rotify-login-error">برای ارسال پرسش باید وارد شوید.</p>';
    } else {
        $success_message = '';
        
        if (isset($_POST['qa_submit']) && isset($_POST['rotify_qa_nonce']) && wp_verify_nonce($_POST['rotify_qa_nonce'], 'rotify_qa_submit')) {
            $post_id = wp_insert_post(array(
                'post_type' => 'rotify_qa',
                'post_title' => "پرسش در $context_name",
                'post_content' => sanitize_textarea_field($_POST['qa_content']),
                'post_status' => 'pending'
            ));
            
            if ($post_id) {
                update_post_meta($post_id, '_rotify_username', intval($_POST['qa_username']));
                update_post_meta($post_id, '_rotify_contact', sanitize_text_field($_POST['qa_contact']));
                update_post_meta($post_id, '_rotify_context', $context_name);
                update_post_meta($post_id, '_rotify_context_link', $context_link);
                $success_message = '<p class="rotify-success rotify-submit-success">سوال شما با موفقیت ارسال شد و در انتظار پاسخ است.</p>';
            }
        }
        
        ?>
        <form method="post" class="rotify-qa-form">
            <?php wp_nonce_field('rotify_qa_submit', 'rotify_qa_nonce'); ?>
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
        if (!empty($success_message)) {
            echo $success_message;
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
        return '<p class="rotify-error rotify-product-error">این فرم فقط در صفحات محصولات ووکامرس کار می‌کند.</p>';
    }
    $product_id = $post->ID;
    $product_name = get_the_title($product_id);
    $product_link = get_permalink($product_id);
    $require_purchase = get_option('rotify_require_purchase', 0);
    $user_has_purchased = false;

    if (is_user_logged_in() && $require_purchase) {
        global $wpdb;
        $user_id = get_current_user_id();

        // روش 1: چک کردن وجود دسترسی به فایل‌های دانلودی
        $download_permissions = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions 
                WHERE user_id = %d AND product_id = %d",
                $user_id,
                $product_id
            )
        );

        // روش 2: چک کردن سفارش‌های تکمیل‌شده
        $order_check = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}posts AS orders
                INNER JOIN {$wpdb->prefix}woocommerce_order_items AS items ON orders.ID = items.order_id
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS itemmeta ON items.order_item_id = itemmeta.order_item_id
                WHERE orders.post_type = 'shop_order'
                AND orders.post_status = 'wc-completed'
                AND itemmeta.meta_key = '_product_id'
                AND itemmeta.meta_value = %d
                AND orders.post_author = %d",
                $product_id,
                $user_id
            )
        );

        // اگه یکی از دو روش بالا درست باشه، کاربر اجازه نظر دادن داره
        $user_has_purchased = ($download_permissions > 0 || $order_check > 0);
    }

    $can_submit = !$require_purchase || ($require_purchase && $user_has_purchased);
    ?>
    <form method="post" class="rotify-comment-form">
        <?php wp_nonce_field('rotify_comment_submit', 'rotify_comment_nonce'); ?>
        <div class="rotify-form-group">
            <label class="rotify-label">نام و نام خانوادگی:</label>
            <input type="text" id="comment_fullname" name="comment_fullname" class="rotify-input" required>
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
            <textarea id="comment_content" name="comment_content" class="rotify-textarea" required></textarea>
        </div>
        <?php if ($can_submit) : ?>
            <input type="submit" name="comment_submit" value="ارسال نظر" class="rotify-button rotify-primary">
        <?php else : ?>
            <input type="submit" name="comment_submit" value="ارسال نظر" class="rotify-button rotify-disabled" disabled>
            <p class="rotify-error rotify-purchase-error">برای ارسال نظر محصول را خریداری کنید.</p>
        <?php endif; ?>
    </form>
    <?php
    if (isset($_POST['comment_submit']) && isset($_POST['rotify_comment_nonce']) && wp_verify_nonce($_POST['rotify_comment_nonce'], 'rotify_comment_submit')) {
        if ($can_submit) {
            $post_id = wp_insert_post(array(
                'post_type' => 'rotify_comment',
                'post_title' => "نظر در $product_name",
                'post_content' => sanitize_textarea_field($_POST['comment_content']),
                'post_status' => 'pending'
            ));
            
            if ($post_id) {
                update_post_meta($post_id, '_rotify_fullname', sanitize_text_field($_POST['comment_fullname']));
                update_post_meta($post_id, '_rotify_rating', intval($_POST['comment_rating']));
                update_post_meta($post_id, '_rotify_context', $product_name);
                update_post_meta($post_id, '_rotify_context_link', $product_link);
                echo '<p class="rotify-success rotify-submit-success">نظر شما با موفقیت ارسال شد و در انتظار پاسخ است.</p>';
            }
        } else {
            echo '<p class="rotify-error rotify-purchase-error">برای ارسال نظر محصول را خریداری کنید.</p>';
        }
    }
    return ob_get_clean();
}
add_shortcode('rotify_comment_form', 'rotify_comment_form_shortcode');
