<?php
/**
 * Class to handle Mailchimp plugin activation
 *
 * @author Ashiqur Rahman
 * @author_url https://www.choobs.com
 **/

class choobs_wp_mailchimp_activator {
	public static function  activate() {

		global $wpdb, $choobs_wp_mailchimp_db_version, $choobs_wp_mailchimp_forms_table;

		if(!isset($choobs_wp_mailchimp_forms_table)) {
			return false;
		}

		$installed_version = get_option( "choobs_wp_mailchimp_db_version" );
		if ( $installed_version == $choobs_wp_mailchimp_db_version ) {
			return true;
		}

		$table_name = $wpdb->prefix . $choobs_wp_mailchimp_forms_table;
		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * idform, int, primary key
		 * form_list, text, mailchimp list id
		 * form_label, text, title of the form
		 * form_html, text, html body of the form
		 * form_shortcode, text, shortcode generated for the form
		 * edit_date, datetime, time of editing the form
		 * edited_by, int, user id of editing user
		 * form_status, enum, [active, inacrive, draft]
		 */

		$sql = "CREATE TABLE $table_name (
					idform bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					form_list text NOT NULL,
					form_label text NOT NULL,
					form_html text NOT NULL,
					form_shortcode text NOT NULL,
					edit_date datetime,
					edited_by bigint(20) UNSIGNED,
					form_status enum('active', 'inactive', 'draft') DEFAULT 'draft' NOT NULL,
					PRIMARY KEY (idform)
					) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'choobs_wp_mailchimp_db_version', $choobs_wp_mailchimp_db_version );
	}
}