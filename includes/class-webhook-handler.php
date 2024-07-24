<?php
class Puck_Webhook_Handler {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    // Register public REST routes
    public function register_rest_routes() {
        register_rest_route('webhook/v1', '/receive/(?P<webhook>[\w-]+)/', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_webhook($request) {
        $webhook_name = $request->get_param('webhook');
        $body = $request->get_json_params();

        error_log('Webhook: ' . esc_html($webhook_name));

        $webhooks = get_option('webhook_settings', array());
        $api_key_defined = null;
        foreach ($webhooks as $webhook) {
            if ($webhook['name'] === $webhook_name) {
                $api_key_defined = $webhook['api_key'];
                error_log('Expected API Key: ' . esc_html($api_key_defined));
                break;
            }
        }

        $api_key_sent = isset($body['api_key']) ? sanitize_text_field($body['api_key']) : '';

        if (!$api_key_defined || $api_key_sent !== $api_key_defined) {
            error_log('API key validation failed.');
            return new WP_Error('authentication_failed', 'API key not valid', array('status' => 403));
        }

        $title = sanitize_text_field($body['title']);
        $content = sanitize_textarea_field($body['content']);

        $post_id = wp_insert_post(array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'webhook_post'
        ));

        if (is_wp_error($post_id)) {
            return new WP_Error('post_creation_failed', 'Post creation failed', array('status' => 500));
        }

        // Fetch email addresses for the specific webhook
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_emails';
        $emails = $wpdb->get_col($wpdb->prepare("SELECT email FROM $table_name WHERE webhook_name = %s AND disabled <> 1", $webhook_name));

        // Send email
        $subject = "New Post Created: " . esc_html($title);
        $message = "A new post has been created with the following content:\n\n";
        $message .= esc_html($content) . "\n\n";
        $unsubscribe_url = add_query_arg(array(
            'action' => 'unsubscribe',
            'email' => '__EMAIL__',
            'webhook' => esc_html($webhook_name)
        ), home_url());
        $message .= "View the post: " . esc_url(get_permalink($post_id)) . "\n";
        $message .= "Unsubscribe: " . esc_url($unsubscribe_url);

        foreach ($emails as $to) {
            wp_mail($to, $subject, str_replace('__EMAIL__', esc_html($to), $message));
        }

        return new WP_REST_Response('Webhook processed', 200);
    }
}
?>
