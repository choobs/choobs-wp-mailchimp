<?php
/**
 * Class to handle Mailchimp gateway integration
 *
 * @author Ashiqur Rahman
 * @author_url https://www.choobs.com
 **/

class choobs_wp_mailchimp {

	protected $loader;
	protected $plugin_slug;
	protected $version;
	protected static $static_plugin_slug;
	protected static $static_version;
	private $options;

	public function __construct() {

		$this->plugin_slug = 'choobs-wp-mailchimp';
		$this->version = '1.0';

		self::setStaticPluginSlug($this->plugin_slug);
		self::setStaticVersion($this->version);

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_shortcodes();
		$this->define_widgets();

	}

	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . 'choobs-wp-mailchimp-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'choobs-wp-mailchimp-loader.php';
		require_once plugin_dir_path( __FILE__ ) . 'choobs-wp-mailchimp-widget.php';
		require_once plugin_dir_path( __FILE__ ) . '../includes/third_party/Mailchimp.php';
		$this->loader = new choobs_wp_mailchimp_loader();
	}

	private function define_admin_hooks() {
		$admin = new choobs_wp_mailchimp_admin( $this->get_version(), $this->get_plugin_slug() );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $admin, 'add_admin_init' );

		//Handle the AJAX form submit
		$this->loader->add_action( 'wp_ajax_nopriv_mailchimp-submit', $this, 'mailchimp_submit');
		$this->loader->add_action( 'wp_ajax_mailchimp-submit', $this, 'mailchimp_submit');
	}

	private function define_shortcodes() {
		add_shortcode('choobs-wp-mailchimp', array($this, 'choobs_wp_mailchimp_shortcode'));
	}

	private function define_widgets() {
		$widget = new choobs_wp_mailchimp_widget( $this->get_version(), $this->get_plugin_slug() );
		$this->loader->add_action( 'widgets_init', $widget, 'add_widget_init' );
	}

	public function run() {
		$this->loader->run();
	}

	public static function setStaticVersion( $version ) {
		self::$static_version = $version;
	}

	public static function getStaticVersion() {
		return self::$static_version;
	}

	public static function setStaticPluginSlug( $plugin_slug ) {
		self::$static_plugin_slug = $plugin_slug;
	}

	public static function getStaticPluginSlug() {
		return self::$static_plugin_slug;
	}

	public function get_version() {
		return $this->version;
	}

	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	public function choobs_wp_mailchimp_shortcode($atts = array(), $content = null) {

		wp_register_style( 'choobs-wp-mailchimp-form', plugins_url( '../css/form.css', __FILE__ ) );
		wp_enqueue_style( 'choobs-wp-mailchimp-form' );

		wp_register_script( 'choobs-wp-mailchimp-form', plugins_url( '../js/form.js', __FILE__ ), array( 'jquery' ) );

		wp_localize_script( 'choobs-wp-mailchimp-form', 'mailchimp', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'mailchimpNonce' => wp_create_nonce( 'mailchimp-nonce' ),
			)
		);

		wp_enqueue_script( 'choobs-wp-mailchimp-form' );

		$wp_mailchimp_atts = shortcode_atts( array( 'id' => 0 ), $atts );
		if( intval( $wp_mailchimp_atts['id'] ) == 0 ) {
			return false;
		}

		global $wpdb, $choobs_wp_mailchimp_forms_table;

		$form = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $choobs_wp_mailchimp_forms_table . '` WHERE idform = %d AND form_status = %s', $wp_mailchimp_atts['id'], 'active' ) );

		if( !$form ) {
			return false;
		}

		$html = '';

		$html .= __( html_entity_decode( stripslashes( $form->form_html ) ), $this->plugin_slug );
		$html .= '<input type="hidden" name="form_id", id="form_id_' . $wp_mailchimp_atts['id'] . '" value="' . $form->idform . '" />';
		$html .= '<input type="hidden" name="request_url", id="request_url_' . $wp_mailchimp_atts['id'] . '" value="' . site_url( $_SERVER['REQUEST_URI'] ) . '" />';
		$html .= wp_nonce_field( 'mailchimp-nonce', 'mailchimpNonce', true, false );
		$html .= '<div id="loading_circle">
						<div id="loading_circle_1" class="loading-circle"></div>
						<div id="loading_circle_2" class="loading-circle"></div>
						<div id="loading_circle_3" class="loading-circle"></div>
					</div>';

		$html = '<form class="wp-mailchimp-form" name="wp-mailchimp-form-' . $wp_mailchimp_atts['id'] . '"
					id="wp_mailchimp_form_' . $wp_mailchimp_atts['id'] . '"
					method="post"
					action="' . admin_url( 'admin-ajax.php' ) . '?action=mailchimp-submit">' . $html . '</form>';

		return $html;
	}

	public function mailchimp_submit() {

		$nonce = $_POST['mailchimpNonce'];
		if ( ! wp_verify_nonce( $nonce, 'mailchimp-nonce' ) ) {
			die ( __( 'Security verification failed!', $this->plugin_slug ) );
		}

		$return_url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : esc_attr( $_POST['request_url'] );
		$form_id = esc_attr( $_POST['form_id'] );
		$ajax = isset( $_POST['ajax'] ) ? $_POST['ajax'] : 0;
		$email = $_POST['EMAIL'];

		global $wpdb, $choobs_wp_mailchimp_forms_table;

		$form = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $choobs_wp_mailchimp_forms_table . '` WHERE idform = %d AND form_status = %s', $form_id, 'active' ) );
		if( !$form ) {
			if( $ajax == 0 ) {
				wp_redirect( $return_url );
				exit;
			}
			_e( 'Could not complete the signup process. Please try again later.', $this->plugin_slug );
			exit;
		}

		unset($_POST['ajax']);
		unset($_POST['form_id']);
		unset($_POST['mailchimpNonce']);
		unset($_POST['request_url']);
		unset($_POST['_wp_http_referer']);
		unset($_POST['EMAIL']);

		$list_id = $form->form_list;
		$this->options = get_option( 'choobs_wp_mailchimp_info' );
		$mc = new Mailchimp(
			isset( $this->options['choobs_wp_mailchimp_api_key'] ) ? $this->options['choobs_wp_mailchimp_api_key'] : ''
		);
		if( isset( $this->options['choobs_wp_mailchimp_disable_ssl_verify'] ) && $this->options['choobs_wp_mailchimp_disable_ssl_verify'] == 'yes' ) {
			curl_setopt( $mc->ch, CURLOPT_SSL_VERIFYPEER, false );
		}

		$params['id'] = $list_id;
		$params['email'] = array( 'email' => $email);
		$params['merge_vars'] = $_POST;
		$params['merge_vars']['new-email'] = $email;
		$params['double_optin'] = false;
		$params['update_existing'] = true;
		$params['send_welcome'] = true;

		try {
			$mc->call( 'lists/subscribe', $params );
		} catch( Exception $ex ) {
			echo $ex->getMessage();
			exit;
		}

		if( $ajax == 0 ) {
			wp_redirect( $return_url );
			exit;
		}
		_e( 'Thank you for your interest. We will keep you updated with our latest offers and news.', $this->plugin_slug );
		exit;
	}
} 