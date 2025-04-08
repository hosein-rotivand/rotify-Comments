<?php
/*
Plugin Name: Rotify-Comments
Description: افزونه‌ای برای مدیریت پرسش‌ها و نظرات با فرم و پنل ادمین
Version: 1.0.0
Author: Hosein Rotivand
Author URI: https://rotify.ir/hosein-rotivand-ghiasvand/
*/

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// لود فایل‌ها
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/forms.php';
require_once plugin_dir_path(__FILE__) . 'includes/displays.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';


// لود استایل‌ها
function rotify_enqueue_styles() {
    wp_enqueue_style('rotify-styles', plugin_dir_url(__FILE__) . 'assets/style.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'rotify_enqueue_styles');
add_action('admin_enqueue_scripts', 'rotify_enqueue_styles');
