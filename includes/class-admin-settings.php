<?php
class Puck_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
    }

    // Add options page
    public function add_admin_menu() {
        add_options_page('Webhook Consumer', 'Webhook Consumer', 'manage_options', 'webhook_consumer', array($this, 'options_page'));
    }

    // Register settings
    public function settings_init() {
        register_setting('webhook_consumer_plugin', 'webhook_settings');

        add_settings_section(
            'webhook_consumer_section',
            __('Webhook Consumer Settings', 'webhook_consumer'),
            array($this, 'settings_section_callback'),
            'webhook_consumer_plugin'
        );

        add_settings_field(
            'webhook_list',
            __('Webhooks', 'webhook_consumer'),
            array($this, 'webhook_list_render'),
            'webhook_consumer_plugin',
            'webhook_consumer_section'
        );
    }

    // Render the webhooks list
    public function webhook_list_render() {
        $webhooks = get_option('webhook_settings', array());
        ?>
        <div id="webhook_list">
            <?php foreach ($webhooks as $index => $webhook): ?>
                <div class="webhook_item" id="webhook_item_<?php echo esc_attr($index); ?>">
                    <input type="text" name="webhook_settings[<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($webhook['name']); ?>" placeholder="Webhook Name">
                    <input type="text" name="webhook_settings[<?php echo esc_attr($index); ?>][api_key]" value="<?php echo esc_attr($webhook['api_key']); ?>" placeholder="API Key">
                    <button type="button" onclick="generateApiKey(<?php echo esc_attr($index); ?>)">Generate API Key</button>
                    <button type="button" onclick="removeWebhook(<?php echo esc_attr($index); ?>)">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addWebhook()">Add Webhook</button>
        <script type="text/javascript">
            function generateApiKey(index) {
                document.querySelector(`[name="webhook_settings[${index}][api_key]"]`).value = '<?php echo wp_generate_password(32, false); ?>';
            }
            function addWebhook() {
                let index = document.querySelectorAll('.webhook_item').length;
                let div = document.createElement('div');
                div.className = 'webhook_item';
                div.id = 'webhook_item_' + index;
                div.innerHTML = `
                    <input type="text" name="webhook_settings[${index}][name]" placeholder="Webhook Name">
                    <input type="text" name="webhook_settings[${index}][api_key]" placeholder="API Key">
                    <button type="button" onclick="generateApiKey(${index})">Generate API Key</button>
                    <button type="button" onclick="removeWebhook(${index})">Remove</button>
                `;
                document.getElementById('webhook_list').appendChild(div);
            }
            function removeWebhook(index) {
                let div = document.getElementById('webhook_item_' + index);
                div.remove();
            }
        </script>
        <?php
    }

    // Section callback
    public function settings_section_callback() {
        echo __('Manage your webhooks and API keys here.', 'webhook_consumer');
    }

    // Options page output
    public function options_page() {
        ?>
        <form action='options.php' method='post'>
            <h2>Webhook Consumer</h2>
            <?php
            settings_fields('webhook_consumer_plugin');
            do_settings_sections('webhook_consumer_plugin');
            submit_button();
            ?>

            <h3>How to Use</h3>
            <p>The following shortcodes and curl examples will help you utilize the webhooks:</p>

            <h4>Shortcodes</h4>
            <p>Use the following shortcode to create a subscription form for a specific webhook. Replace <code>[webhook_name]</code> with the name of the webhook.</p>
            <pre><code>[puck_subscribe webhook="[webhook_name]"]</code></pre>

            <h4>cURL Example</h4>
            <p>You can use curl to send a POST request to the webhook URL. Replace <code>[webhook_name]</code> with the name of the webhook and <code>[api_key]</code> with the corresponding API key:</p>
            <pre><code>
curl -X POST <?php echo esc_url(home_url('/wp-json/webhook/v1/receive/[webhook_name]/')); ?> \
-H "Content-Type: application/json" \
-d '{
  "title": "Test Post",
  "content": "This is the content of the post.",
  "api_key": "[api_key]"
}'
            </code></pre>
        </form>
        <?php
    }
}
?>
