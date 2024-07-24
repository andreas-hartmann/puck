<?php
/*
Plugin Name: Puck
Description: A WordPress plugin for automating news feeds and mailing lists.
Version: 0.5.1
Author: Andreas Hartmann
Author URI: https://ohok.org/
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Load required modules.
define('PUCK_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once PUCK_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once PUCK_PLUGIN_DIR . 'includes/class-webhook-handler.php';
require_once PUCK_PLUGIN_DIR . 'includes/class-utilities.php';

// Register Webhook Post Type Function
function puck_register_webhook_post_type() {
    register_post_type('webhook_post', array(
        'labels' => array(
            'name'          => __('Webhook Posts'),
            'singular_name' => __('Webhook Post'),
        ),
        'public'       => true,
        'publicly_queryable' => true,
        'rewrite'      => array('slug' => 'webhook_post'),
        'show_ui'      => true,
        'has_archive'  => false,
        'supports'     => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
    ));
}

add_action('init', 'puck_register_webhook_post_type');

// Initialize Admin Settings
new Puck_Admin_Settings();

// Initialize Webhook Handler
new Puck_Webhook_Handler();

// Activation and Deactivation Hooks
register_activation_hook(__FILE__, array('Puck_Utilities', 'activate'));
register_deactivation_hook(__FILE__, array('Puck_Utilities', 'deactivate'));

// Register Shortcode for Hooks Subscription
function register_hooks_subscribe_shortcode() {
    add_shortcode('puck_subscribe', 'handle_hooks_subscribe_shortcode');
}
add_action('init', 'register_hooks_subscribe_shortcode');

// Register Unsubscribe Query Vars
function register_unsubscribe_query_vars($vars) {
    $vars[] = 'action';
    $vars[] = 'email';
    $vars[] = 'webhook';
    return $vars;
}
add_filter('query_vars', 'register_unsubscribe_query_vars');

// Handle Unsubscribe Action
function handle_unsubscribe_action() {
    if (get_query_var('action') == 'unsubscribe' && get_query_var('email') && get_query_var('webhook')) {
        $email = sanitize_email(get_query_var('email'));
        $webhook = sanitize_text_field(get_query_var('webhook'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_emails';
        $wpdb->update($table_name, array('disabled' => 1), array('email' => $email, 'webhook_name' => $webhook));

        wp_redirect(home_url('/unsubscribe-success'));
        exit;
    }
}
add_action('template_redirect', 'handle_unsubscribe_action');

// User sign up shortcode handler
function handle_hooks_subscribe_shortcode($atts) {
    $webhook = isset($atts['webhook']) ? sanitize_text_field($atts['webhook']) : '';

    if (isset($_POST['email']) && is_email($_POST['email'])) {
        $email = sanitize_email($_POST['email']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_emails';
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE email = %s AND webhook_name = %s", $email, $webhook));

        if ($existing) {
            $wpdb->update($table_name, array('disabled' => 0), array('email' => $email, 'webhook_name' => $webhook));
        } else {
            $wpdb->insert($table_name, array('email' => $email, 'webhook_name' => $webhook, 'disabled' => 0));
        }

        echo 'Thank you for subscribing!';
    }

    ob_start();
    ?>
    <form method="post">
        <input type="email" name="email" required>
        <button type="submit">Subscribe</button>
    </form>
    <?php
    return ob_get_clean();
}
?>
