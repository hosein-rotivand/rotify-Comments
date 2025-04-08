<?php
if (!defined('ABSPATH')) {
    exit;
}

// ثبت پست‌تایپ‌ها
function rotify_register_post_types() {
    register_post_type('rotify_qa',
        array(
            'labels' => array(
                'name' => __('پرسش‌ها'),
                'singular_name' => __('پرسش'),
                'menu_name' => __('پرسش و نظرات'),
                'all_items' => __('پرسش‌ها'),
                'add_new' => __(''),
                'add_new_item' => __(''),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 4,
            'menu_icon' => 'dashicons-admin-comments',
            'supports' => array('title', 'editor'),
            'capability_type' => 'post',
            'show_in_admin_bar' => false,
        )
    );

    register_post_type('rotify_comment',
        array(
            'labels' => array(
                'name' => __('نظرات'),
                'singular_name' => __('نظر'),
                'all_items' => __('نظرات'),
                'add_new' => __(''),
                'add_new_item' => __(''),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=rotify_qa',
            'supports' => array('title', 'editor'),
            'capability_type' => 'post',
            'show_in_admin_bar' => false,
        )
    );
}
add_action('init', 'rotify_register_post_types');

// تنظیم منوها
function rotify_custom_menu() {
    // حذف زیرمنوهای پیش‌فرض
    remove_submenu_page('edit.php?post_type=rotify_qa', 'edit.php?post_type=rotify_qa');
    remove_submenu_page('edit.php?post_type=rotify_qa', 'edit.php?post_type=rotify_comment');
    remove_submenu_page('edit.php?post_type=rotify_qa', 'post-new.php?post_type=rotify_qa');
    remove_submenu_page('edit.php?post_type=rotify_qa', 'post-new.php?post_type=rotify_comment');

    // اضافه کردن زیرمنوهای دلخواه
    add_submenu_page(
        'edit.php?post_type=rotify_qa',
        __('پرسش‌ها'),
        __('پرسش‌ها'),
        'manage_options',
        'edit.php?post_type=rotify_qa'
    );
    add_submenu_page(
        'edit.php?post_type=rotify_qa',
        __('نظرات'),
        __('نظرات'),
        'manage_options',
        'edit.php?post_type=rotify_comment'
    );
    add_submenu_page(
        'edit.php?post_type=rotify_qa',
        __('تنظیمات'),
        __('تنظیمات'),
        'manage_options',
        'rotify-settings',
        'rotify_settings_page' // تابع تنظیمات رو اینجا فراخوانی می‌کنیم
    );
}
add_action('admin_menu', 'rotify_custom_menu', 999);

// تعداد موارد در انتظار
function rotify_pending_count() {
    global $menu;
    $qa_pending = wp_count_posts('rotify_qa', 'readable')->pending;
    $comment_pending = wp_count_posts('rotify_comment', 'readable')->pending;
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
