<?php
/**
 * Handle the mailchimp form submit
 *
 * @author: Ashiqur Rahman
 * @link: https://www.choobs.com
 */

/**
 * Load the default settings
 */
require_once 'wp-mailchimp-settings.php';
require_once 'third_party/Mailchimp.php';

$return_url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : esc_attr( $_POST['request_url'] );
$form_id = esc_attr( $_POST['form_id'] );
global $wpdb, $wp_mailchimp_forms_table;

$mc = new Mailchimp();

echo '<pre>';
print_r( $_SERVER );
print_r( $_POST );
echo '</pre>';
die();