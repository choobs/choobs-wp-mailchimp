<?php
/**
 * Template page for Mailchimp plugin's options
 *
 * @author Ashiqur Rahman
 * @url https://www.choobs.com
 **/
?>
<div class="wrap choobs-wp-mailchimp-options">
	<h2><?php _e('Mailchimp Options', $this->plugin_slug); ?></h2>
	<?php
	if( !empty( $this->mc_error ) ) {
		echo '<div class="notice warning"><p>' . implode( '<br />', $this->mc_error ) . '</p></div>';
	}
	?>
	<?php settings_errors(); ?>
	<form method="post" action="options.php" class="choobs-wp-mailchimp-admin-form">
		<?php
		settings_fields( 'choobs_wp_mailchimp' );
		do_settings_sections( 'choobs-wp-mailchimp-options' );
		submit_button();
		?>
	</form>
</div>