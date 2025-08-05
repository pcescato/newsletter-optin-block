<?php

// If this file is called directly, abort.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

global $wpdb;

// Delete the plugin's options.
delete_option( 'fai_settings' );

// Delete the custom table.
$table_name = $wpdb->prefix . 'fai_submissions';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
