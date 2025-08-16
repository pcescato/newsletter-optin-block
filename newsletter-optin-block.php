<?php
/**
 * Plugin Name:       Newsletter Optin Block
 * Description:       Injecte automatiquement un formulaire Contact Form 7 dans les articles.
 * Version:           1.0.0
 * Author:            Pascal CESCATO
 * Plugin URI:        https://github.com/pcescato/newsletter-optin-block
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       newsletter-optin-block
 * Domain Path:       /languages
 */

// phpcs:disable WordPress.WP.AlternativeFunctions -- Third-party vendor directory contains manually included Mailjet library, not Composer dependencies

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Load the Mailjet library
require_once __DIR__ . '/vendor/guzzlehttp/guzzle/src/functions_include.php';

/**
 * Check if Contact Form 7 is active.
 */
function newsletter_optin_block_check_cf7() {
    if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
        add_action( 'admin_notices', 'newsletter_optin_block_cf7_not_active_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
}
add_action( 'admin_init', 'newsletter_optin_block_check_cf7' );

/**
 * Display a notice if Contact Form 7 is not active.
 */
function newsletter_optin_block_cf7_not_active_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php esc_html_e( 'Newsletter Opt-in Block requires Contact Form 7 to be installed and active. Please install and activate Contact Form 7.', 'newsletter-optin-block' ); ?></p>
    </div>
    <?php
}

define( 'NEWSOPBL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include the dependencies.
require_once NEWSOPBL_PLUGIN_DIR . 'includes/functions.php';
require_once NEWSOPBL_PLUGIN_DIR . 'admin/settings-page.php';

/**
 * The code that runs during plugin activation.
 */
function activate_formulaire_auto_injecte() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsopbl_submissions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        submission_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        email varchar(100) NOT NULL,
        ip_address varchar(100) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_formulaire_auto_injecte() {
    // Deactivation code here.
}

register_activation_hook( __FILE__, 'activate_formulaire_auto_injecte' );
register_deactivation_hook( __FILE__, 'deactivate_formulaire_auto_injecte' );

/**
 * Check if Contact Form 7 is active.
 */
function newsopbl_check_dependencies() {
    if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
        add_action( 'admin_notices', 'newsopbl_dependency_notice' );
    }
}
add_action( 'admin_init', 'newsopbl_check_dependencies' );

/**
 * Display a notice if Contact Form 7 is not active.
 */
function newsopbl_dependency_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php esc_html_e( 'Le plugin "Formulaire Auto-injecté" nécessite que Contact Form 7 soit installé et activé.', 'newsletter-optin-block' ); ?></p>
    </div>
    <?php
}

// Initialize the plugin.
add_action( 'plugins_loaded', 'newsopbl_init' );

function newsopbl_init() {
    $options = get_option( 'newsopbl_settings' );
    if ( isset( $options['newsopbl_activate'] ) && $options['newsopbl_activate'] ) {
        add_filter( 'the_content', 'newsopbl_inject_form' );
        add_action( 'wpcf7_before_send_mail', 'newsopbl_handle_submission' );
        add_action( 'wp_enqueue_scripts', 'newsopbl_enqueue_scripts' );
    }
}
