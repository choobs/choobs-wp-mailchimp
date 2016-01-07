<?php
/**
 * Class to handle widgets for Mailchimp form.
 *
 * @author Ashiqur Rahman
 * @url https://www.choobs.com
 */

if(!class_exists('choobs_wp_mailchimp')) {
	include plugin_dir_path( __FILE__ ) . '/choobs-wp-mailchimp.php';
}

class choobs_wp_mailchimp_widget extends WP_Widget {

	private $version;
	private $plugin_slug;

	public function __construct() {
		$this->version = choobs_wp_mailchimp::getStaticVersion();
		$this->plugin_slug = choobs_wp_mailchimp::getStaticPluginSlug();

		parent::__construct( 'choobs_wp_mailchimp_widget', __( 'Mailchimp Subscribe', $this->plugin_slug ), array( 'description' => __( 'Widget to display Mailchimp subscribe form', $this->plugin_slug ) ) );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo do_shortcode( '[choobs-wp-mailchimp id="' . $instance['form_id'] . '"]' );
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Subscribe', $this->plugin_slug );
		}

		if( isset( $instance[ 'form_id' ] ) ) {
			$form_id = $instance[ 'form_id' ];
		} else {
			$form_id = null;
		}

		global $wpdb, $choobs_wp_mailchimp_forms_table;
		$forms = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $choobs_wp_mailchimp_forms_table . '` WHERE form_status = %s', 'active' ) );
		require plugin_dir_path( __FILE__ ) . '../partials/admin_widget_form.php';
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['form_id'] = ( ! empty( $new_instance['form_id'] ) ) ? strip_tags( $new_instance['form_id'] ) : '';
		return $instance;
	}

	public function add_widget_init() {
		register_widget( 'choobs_wp_mailchimp_widget' );
	}

} 