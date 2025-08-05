<?php
/**
 * Plugin Name:       Newsletter Optin Block
 * Plugin URI:        https://tsw.ovh/
 * Description:       Injecte automatiquement un formulaire Contact Form 7 dans les articles.
 * Version:           1.0.0
 * Author:            Pascal CESCATO
 * Author URI:        https://tsw.ovh/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       newsletter-optin-block
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Load the Mailjet library via autoload.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

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
        <p><?php _e( 'Newsletter Opt-in Block requires Contact Form 7 to be installed and active. Please install and activate Contact Form 7.', 'newsletter-optin-block' ); ?></p>
    </div>
    <?php
}

define( 'FAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include the dependencies.
require_once FAI_PLUGIN_DIR . 'includes/functions.php';
require_once FAI_PLUGIN_DIR . 'admin/settings-page.php';

/**
 * The code that runs during plugin activation.
 */
function activate_formulaire_auto_injecte() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fai_submissions';
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
function fai_check_dependencies() {
    if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
        add_action( 'admin_notices', 'fai_dependency_notice' );
    }
}
add_action( 'admin_init', 'fai_check_dependencies' );

/**
 * Display a notice if Contact Form 7 is not active.
 */
function fai_dependency_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e( 'Le plugin "Formulaire Auto-injecté" nécessite que Contact Form 7 soit installé et activé.', 'formulaire-auto-injecte' ); ?></p>
    </div>
    <?php
}

// Initialize the plugin.
add_action( 'plugins_loaded', 'fai_init' );

function fai_init() {
    $options = get_option( 'fai_settings' );
    if ( isset( $options['fai_activate'] ) && $options['fai_activate'] ) {
        add_filter( 'the_content', 'fai_inject_form' );
        add_action( 'wpcf7_before_send_mail', 'fai_handle_submission' );
        add_action( 'wp_enqueue_scripts', 'fai_enqueue_scripts' );
    }
}
