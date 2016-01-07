<?php
/*
 * Plugin Name:       Choobs WordPress Mailchimp
 * Plugin URI:        https://www.choobs.com
 * Description:       Integrate the Mailchimp to WordPress website.
 * Version:           1.0
 * Author:            Ashiqur Rahman
 * Author URI:        https://www.choobs.com
 * Text Domain:       choobs
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
if ( ! defined( 'WPINC' ) ) {
	die('Are you supposed to be here?');
}

/**
 * Load the default settings
 */
require_once 'includes/choobs-wp-mailchimp-settings.php';

/**
 * Hook plugin activation
 */
register_activation_hook( __FILE__, 'activate_choobs_wp_mailchimp' );
function activate_choobs_wp_mailchimp() {
	require_once plugin_dir_path( __FILE__ ) . 'class/choobs-wp-mailchimp-activator.php';
	choobs_wp_mailchimp_activator::activate();
}

/**
 * Hook plugin deactivation
 */
register_deactivation_hook( __FILE__, 'deactivate_choobs_wp_mailchimp' );
function deactivate_choobs_wp_mailchimp() {
	require_once plugin_dir_path( __FILE__ ) . 'class/choobs-wp-mailchimp-deactivator.php';
	choobs_wp_mailchimp_deactivator::deactivate();
}

/**
 * Hook to check plugin updates
 */
add_action( 'plugins_loaded', 'choobs_wp_mailchimp_update_db_check' );
function choobs_wp_mailchimp_update_db_check() {
	global $choobs_wp_mailchimp_db_version;
	if ( get_site_option( 'choobs_wp_mailchimp_db_version' ) != $choobs_wp_mailchimp_db_version ) {
		activate_choobs_wp_mailchimp();
	}
}

/**
 * Load the plugin and start the magic!
 */
function choobs_wp_mailchimp_start() {
	require_once plugin_dir_path( __FILE__ ) . 'class/choobs-wp-mailchimp.php';
	$wpmailchimp = new choobs_wp_mailchimp();
	$wpmailchimp->run();
}
choobs_wp_mailchimp_start();