<?php
/**
 * Class to handle Mailchimp integration admin side
 *
 * @author Ashiqur Rahman
 * @author_url https://www.choobs.com
 **/

class choobs_wp_mailchimp_admin {

	private $version;
	private $plugin_slug;
	private $options;
	private $mc;
	private $mc_error;
	private $lists;

	public function __construct( $version, $plugin_slug ) {
		$this->version = $version;
		$this->plugin_slug = $plugin_slug;
		$this->options = get_option( 'choobs_wp_mailchimp_info' );
		try{
			$this->mc = new Mailchimp(
				isset($this->options['choobs_wp_mailchimp_api_key']) ? $this->options['choobs_wp_mailchimp_api_key'] : ''
			);
			if(isset($this->options['choobs_wp_mailchimp_disable_ssl_verify']) && $this->options['choobs_wp_mailchimp_disable_ssl_verify'] == 'yes') {
				curl_setopt($this->mc->ch, CURLOPT_SSL_VERIFYPEER, false);
			}
			$this->lists = $this->mc->call('lists/list', array());
		} catch(Exception $e) {
			$this->mc_error[] = $e->getMessage();
		}
	}

	public function enqueue_styles() {
		wp_register_style('choobs-wp-mailchimp-admin', plugins_url( '../css/admin.css', __FILE__ ));
		wp_enqueue_style('choobs-wp-mailchimp-admin');

		wp_register_script('choobs-wp-mailchimp-admin-js', plugins_url( '../js/admin.js', __FILE__ ), array('jquery'), '', true);
		wp_enqueue_script('choobs-wp-mailchimp-admin-js');
	}

	/**
	 * Add administrative option menu
	 */
	public function add_admin_menu() {
		add_menu_page( __('Mailchimp', $this->plugin_slug), __('Mailchimp', $this->plugin_slug), 'activate_plugins', 'choobs-wp-mailchimp-options', array($this, 'mailchimp_options'), 'dashicons-email', 81 );
		add_submenu_page( 'choobs-wp-mailchimp-options', __('Subscription Forms', $this->plugin_slug), __('Subscription Forms', $this->plugin_slug), 'activate_plugins', 'choobs-wp-mailchimp-subscription-forms', array($this, 'mailchimp_subscription_forms') );

		//Rename the submenu
		global $submenu;
		if( isset( $submenu['choobs-wp-mailchimp-options'] ) && isset( $submenu['choobs-wp-mailchimp-options'][0] ) ) {
			$submenu['choobs-wp-mailchimp-options'][0][0] = __( 'Mailchimp Settings', $this->plugin_slug );
			$submenu['choobs-wp-mailchimp-options'][0][3] = __( 'Mailchimp Settings', $this->plugin_slug );
		}
	}

	/**
	 * Add footer text on the admin side
	 */
	public function add_admin_footer() {
		echo sprintf( __( 'This <a href="%s" target="_blank">WordPress</a> plugin was developed by <a href="%s" target="_blank">Choobs Ltd.</a>', $this->plugin_slug ), '//wordpress.org', 'https://www.choobs.com' );
	}

	/**
	 * Register the option fields for the plugin
	 */
	public function add_admin_init() {
		register_setting(
			'choobs_wp_mailchimp',
			'choobs_wp_mailchimp_info',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'choobs_wp_mailchimp_api',
			__('Mailchimp API', $this->plugin_slug),
			array( $this, 'api_info' ),
			'choobs-wp-mailchimp-options'
		);

		add_settings_field(
			'choobs_wp_mailchimp_api_key',
			__('API Key', $this->plugin_slug),
			array( $this, 'api_key_callback' ),
			'choobs-wp-mailchimp-options',
			'choobs_wp_mailchimp_api'
		);

		add_settings_field(
			'choobs_wp_mailchimp_disable_ssl_verify',
			__('Disable SSL Verification', $this->plugin_slug),
			array( $this, 'disable_ssl_verify_callback' ),
			'choobs-wp-mailchimp-options',
			'choobs_wp_mailchimp_api'
		);
	}

	/**
	 * Display the options page for the plugin
	 */
	public function mailchimp_options() {
		require_once plugin_dir_path( __FILE__ ) . '../partials/admin_option_page.php';
	}

	/**
	 * Manage the subscription forms
	 */
	public function mailchimp_subscription_forms() {
		global $wpdb, $choobs_wp_mailchimp_forms_table;

		$this->options = get_option( 'choobs_wp_mailchimp_info' );

		$action = isset( $_GET['do'] ) ? esc_attr( $_GET['do'] ) : 'list';
		$error = array();

		$archive = isset( $_GET['archive'] ) ? intval( $_GET['archive'] ) : 0;
		$page = isset( $_GET['p'] ) ? intval( $_GET['p'] ) : 1;
		$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 25;
		$order_by = isset( $_GET['order_by'] ) ? intval( $_GET['order_by'] ) : 'date';
		$order = isset( $_GET['order'] ) ? intval( $_GET['order'] ) : 'DESC';
		$form_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		if( $order_by == 'date' ) {
			$order_by = 'edit_date';
		} else {
			$order_by = 'form_label';
		}

		if( $form_id == 0 && ( $action == 'view' || $action == 'edit' ) ) {
			$action = 'list';
			$error[] = __( 'Invalid form reference.', $this->plugin_slug );
		}

		if(isset($_POST['form_list'])) {
			$form_list = esc_attr( $_POST['form_list'] );
			$form_label = esc_attr( trim( $_POST['form_label'] ) );
			$form_html = esc_html( trim ( $_POST['form_html'] ) );
			$form_status = esc_attr( trim ( $_POST['form_status'] ) );
			if( !empty( $form_list ) && !empty( $form_label ) && !empty( $form_html ) ) {
				if( $action == 'add' ) {
					$query = $wpdb->prepare( 'INSERT INTO `' . $wpdb->prefix . $choobs_wp_mailchimp_forms_table . '`
									(
										`form_list`, `form_label`, `form_html`, `form_status`, `edit_date`
									) VALUES (
										%s, %s, %s, %s, %s
									)',
						$form_list, $form_label, $form_html, $form_status, date('Y-m-d H:i:s')
					);
					$wpdb->query( $query );
					$form_id = $wpdb->insert_id;
					$wpdb->update( $wpdb->prefix . $choobs_wp_mailchimp_forms_table, array( 'form_shortcode' => '[wp-mailchimp id="' . $form_id . '"]' ), array( 'idform' => $form_id ), array( '%s' ), array( '%d' ) );
					$action = 'list';
				} elseif( $action == 'view' || $action == 'edit' ) {
					$wpdb->update( $wpdb->prefix . $choobs_wp_mailchimp_forms_table,
						array(
							'form_list' => $form_list,
							'form_label' => $form_label,
							'form_html' => $form_html,
							'form_status' => $form_status,
							'edit_date' => date('Y-m-d H:i:s')
						),
						array(
							'idform' => $form_id
						),
						array(
							'%s',
							'%s',
							'%s',
							'%s',
							'%s'
						),
						array(
							'%d'
						)
					);
					$action = 'list';
				}
			} else {
				$error[] = __( 'All the fields are required.', $this->plugin_slug );
			}
		}

		switch( $action ) {
			case 'add':
				require_once plugin_dir_path( __FILE__ ) . '../partials/admin_mailchimp_form_add.php';
				break;
			case 'view':
			case 'edit':
				$form = $wpdb->get_row( $wpdb->prepare (
					'SELECT * FROM `' . $wpdb->prefix . $choobs_wp_mailchimp_forms_table . '` WHERE idform = %d',
					$form_id
				) );
				require_once plugin_dir_path( __FILE__ ) . '../partials/admin_mailchimp_form_edit.php';
				break;
			case 'delete':
				break;
			case 'list':
			default:
				require_once plugin_dir_path( __FILE__ ) . '../partials/admin_mailchimp_form_list.php';
				break;
		}
	}

	/**
	 * Sanitize the settings fields if necessary
	 * @param $input array
	 * @return array
	 */
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['choobs_wp_mailchimp_api_key'] ) ) {
			$new_input['choobs_wp_mailchimp_api_key'] = sanitize_text_field( $input['choobs_wp_mailchimp_api_key'] );
		}
		if( isset( $input['choobs_wp_mailchimp_disable_ssl_verify'] ) ) {
			$new_input['choobs_wp_mailchimp_disable_ssl_verify'] = sanitize_text_field( $input['choobs_wp_mailchimp_disable_ssl_verify'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function api_info()
	{
		print sprintf( __( 'Set the API key for your Mailchimp account. You can <a href="%s" target="_blank">get the API key here</a>.', $this->plugin_slug ), 'https://admin.mailchimp.com/account/api' );
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function api_key_callback()
	{
		printf(
			'<input type="text" id="choobs_wp_mailchimp_api_key" name="choobs_wp_mailchimp_info[choobs_wp_mailchimp_api_key]" required="required" value="%s" size="80" />',
			isset( $this->options['choobs_wp_mailchimp_api_key'] ) ? esc_attr( $this->options['choobs_wp_mailchimp_api_key']) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function disable_ssl_verify_callback()
	{
		printf(
			'<input type="checkbox" id="choobs_wp_mailchimp_disable_ssl_verify" name="choobs_wp_mailchimp_info[choobs_wp_mailchimp_disable_ssl_verify]" value="yes" %s />',
			( isset( $this->options['choobs_wp_mailchimp_disable_ssl_verify'] ) && ( $this->options['choobs_wp_mailchimp_disable_ssl_verify'] == 'yes' ) )? 'checked' : ''
		);
	}
} 