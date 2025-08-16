<?php
// If this file is called directly, abort.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

global $wpdb;

// Delete the plugin's options.
delete_option( 'newsopbl_settings' );

// Delete the custom table.
$table_name = $wpdb->prefix . 'newsopbl_submissions';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Direct query required for plugin uninstall, table name is safely prefixed
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );