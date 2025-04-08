<?php
if (!defined('ABSPATH')) {
    exit;
}

// نمایش پرسش‌ها
function rotify_qa_display_shortcode() {
    ob_start();
    global $post;
    $current_page_link = get_permalink($post->ID); // لینک صفحه فعلی رو می‌گیره
    // تنظیمات کوئری برای گرفتن پرسش‌های مربوط به صفحه فعلی
    $args = array(
        'post_type' => 'rotify_qa',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_rotify_context_link', // فقط پرسش‌هایی که لینک‌شون با صفحه فعلی برابر باشه
                'value' => $current_page_link,
                'compare' => '='
            )
        )
    );
    $qa_query = new WP_Query($args);
    $user_image = get_option('rotify_user_image', '');
    $expert_image = get_option('rotify_expert_image', '');
    if ($qa_query->have_posts()) :
        ?>
        <div class="rotify-qa-display">
            <?php while ($qa_query->have_posts()) : $qa_query->the_post(); 
                $username = get_post_meta(get_the_ID(), '_rotify_username', true);
                $name = $username && is_user_logged_in() ? wp_get_current_user()->display_name : 'ناشناس';
                $answer = get_post_meta(get_the_ID(), '_rotify_answer', true); // تغییر به کلید درست
            ?>
                <div class="rotify-qa-item">
                    <p class="rotify-qa-content">
                        <?php if ($user_image) : ?>
                            <img src="<?php echo esc_url($user_image); ?>" alt="کاربر" class="rotify-user-image">
                        <?php endif; ?>
                        <strong class="rotify-qa-name"><?php echo esc_html($name); ?>:</strong>
                        <span class="rotify-qa-text"><?php the_content(); ?></span>
                    </p>
                    <?php if ($answer) : ?>
                        <p class="rotify-qa-answer">
                            <?php if ($expert_image) : ?>
                                <img src="<?php echo esc_url($expert_image); ?>" alt="کارشناس" class="rotify-expert-image">
                            <?php endif; ?>
                            <strong class="rotify-answer-label">پاسخ کارشناس:</strong>
                            <span class="rotify-answer-text"><?php echo wpautop($answer); ?></span>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
    endif;
    return ob_get_clean();
}
add_shortcode('rotify_qa_display', 'rotify_qa_display_shortcode');

// نمایش نظرات
function rotify_comment_display_shortcode() {
    ob_start();
    global $post;
    $current_page_link = get_permalink($post->ID); // لینک صفحه فعلی رو می‌گیره
    // تنظیمات کوئری برای گرفتن نظرات مربوط به صفحه فعلی
    $args = array(
        'post_type' => 'rotify_comment',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_rotify_context_link', // فقط نظراتی که لینک‌شون با صفحه فعلی برابر باشه
                'value' => $current_page_link,
                'compare' => '='
            )
        )
    );
    $comment_query = new WP_Query($args);
    $user_image = get_option('rotify_user_image', '');
    $expert_image = get_option('rotify_expert_image', '');
    if ($comment_query->have_posts()) :
        ?>
        <div class="rotify-comment-display">
            <?php while ($comment_query->have_posts()) : $comment_query->the_post(); 
                $fullname = get_post_meta(get_the_ID(), '_rotify_fullname', true);
                $rating = get_post_meta(get_the_ID(), '_rotify_rating', true);
                $answer = get_post_meta(get_the_ID(), '_rotify_answer', true); // تغییر به کلید درست
            ?>
                <div class="rotify-comment-item">
                    <p class="rotify-comment-content">
                        <?php if ($user_image) : ?>
                            <img src="<?php echo esc_url($user_image); ?>" alt="کاربر" class="rotify-user-image">
                        <?php endif; ?>
                        <strong class="rotify-comment-name"><?php echo esc_html($fullname); ?>:</strong>
                        <span class="rotify-comment-text"><?php the_content(); ?></span>
                        <span class="rotify-comment-rating">(<?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?>)</span>
                    </p>
                    <?php if ($answer) : ?>
                        <p class="rotify-comment-answer">
                            <?php if ($expert_image) : ?>
                                <img src="<?php echo esc_url($expert_image); ?>" alt="کارشناس" class="rotify-expert-image">
                            <?php endif; ?>
                            <strong class="rotify-answer-label">پاسخ کارشناس:</strong>
                            <span class="rotify-answer-text"><?php echo wpautop($answer); ?></span>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
    endif;
    return ob_get_clean();
}
add_shortcode('rotify_comment_display', 'rotify_comment_display_shortcode');
