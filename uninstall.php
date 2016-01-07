<?php
/**
 * Uninstalls the plugin
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit('Are you supposed to be here?');

require_once 'includes/choobs-wp-mailchimp-settings.php';

delete_option( 'choobs_wp_mailchimp_info' );
delete_site_option( 'choobs_wp_mailchimp_info' );
delete_option( 'choobs_wp_mailchimp_db_version' );
delete_site_option( 'choobs_wp_mailchimp_db_version' );

//drop a custom db table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}$choobs_wp_mailchimp_forms_table" );