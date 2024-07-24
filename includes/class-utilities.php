<?php
class Puck_Utilities {
    public static function activate() {
        self::create_custom_tables();
        puck_register_webhook_post_type();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    public static function create_custom_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'custom_emails';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            webhook_name varchar(100) NOT NULL,
            disabled tinyint(1) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
?>
