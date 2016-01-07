<?php
/**
 * Template page for Mailchimp forms
 *
 * @author Ashiqur Rahman
 * @url https://www.choobs.com
 **/
?>
<div class="wrap choobs-wp-mailchimp-options">
	<h2><?php _e('Edit Subscription Forms', $this->plugin_slug); ?></h2>
	<?php
		settings_errors();
		if( !empty( $error ) ) {
			echo '<div class="error">';
			foreach( $error as $e ) {
				echo '<p>' . $e. '</p>';
			}
			echo '</div>';
		}
	?>
	<?php
		if($this->lists['total'] > 0):
	?>
			<form method="post" class="form" action="admin.php?page=choobs-wp-mailchimp-subscription-forms&do=view&id=<?php echo $form_id; ?>">
				<div class="form-group">
					<label for="form_list"><?php _e( 'Mailing List', $this->plugin_slug ); ?></label>
					<select name="form_list" id="form_list" class="form-control">
						<?php
						foreach($this->lists['data'] as $list) {
						?>
							<option value="<?php echo $list['id']; ?>" <?php if( $form->form_list == $list['id'] ): ?> selected <?php endif; ?>><?php echo $list['name']; ?></option>
						<?php
						}
						?>
					</select>
				</div>
				<div class="form-group available_fields_group hidden">
					<label for="available_fields"><?php _e( 'Available Fields', $this->plugin_slug ); ?></label>
					<div class="form-control" id="available_fields">
						<?php
						if( function_exists( 'array_column' ) ) {
							$ids = array_column( $this->lists['data'], 'id' );
						} else {
							foreach($this->lists['data'] as $list) {
								$ids[] = $list['id'];
							}
						}
						if( !empty( $ids ) ) {
							$vars = $this->mc->call( 'lists/merge-vars', array( 'id' => $ids ) );

							foreach ( $vars['data'] as $list_vars ) {
						?>
								<table id="list_<?php echo $list_vars['id']; ?>" <?php if ( $form->form_list == $list_vars['id'] ): ?> class="hidden" <?php endif; ?> >
									<thead>
										<tr>
											<th><?php _e( 'Label', $this->plugin_slug ); ?></th>
											<th><?php _e( 'Field Name', $this->plugin_slug ); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php
									foreach ( $list_vars['merge_vars'] as $merge_vars ) {
									?>
										<tr>
											<td><?php echo $merge_vars['name']; ?></td>
											<th><?php echo $merge_vars['tag']; ?></th>
										</tr>
									<?php
									}
									?>
									</tbody>
								</table>
						<?php
							}
						}
						?>
					</div><br />
					<?php
					if(  !empty( $ids ) && isset( $vars['success_count'] ) && $vars['success_count'] > 0 ) {
					?>
						<small><?php _e( 'You must use the field names as displayed above', $this->plugin_slug ); ?></small>
					<?php
					}
					?>
				</div>
				<div class="form-group">
					<label for="form_label"><?php _e( 'Form Label', $this->plugin_slug ); ?></label>
					<input type="text" name="form_label" id="form_label" class="form-control" value="<?php echo ( isset( $_POST['form_label'] ) ? $_POST['form_label'] : stripslashes( $form->form_label ) ); ?>" placeholder="<?php _e( 'Something to identify the form later', $this->plugin_slug ); ?>" required />
				</div>
				<div class="form-group">
					<label for="form_html"><?php _e( 'Form HTML', $this->plugin_slug ); ?></label>
					<textarea name="form_html" id="form_html" class="form-control" placeholder="<?php _e( 'HTML to display the form', $this->plugin_slug ); ?>" required><?php echo ( isset( $_POST['form_label'] ) ? $_POST['form_html'] : stripslashes( $form->form_html ) ); ?></textarea><br />
					<small><?php _e( 'The HTML must have a field named <strong>EMAIL</strong> for the email address', $this->plugin_slug ); ?></small>
				</div>
				<div class="form-group">
					<label for="form_status"><?php _e( 'Form Status', $this->plugin_slug ); ?></label>
					<select name="form_status" id="form_status" class="form-control">
						<option value="draft" <?php if( $form->form_status == "draft" ): ?> selected <?php endif; ?>><?php _e( 'Draft', $this->plugin_slug ); ?></option>
						<option value="inactive" <?php if( $form->form_status == "inactive" ): ?> selected <?php endif; ?>><?php _e( 'Inactive', $this->plugin_slug ); ?></option>
						<option value="active" <?php if( $form->form_status == "active" ): ?> selected <?php endif; ?>><?php _e( 'Active', $this->plugin_slug ); ?></option>
					</select>
				</div>
				<div class="form-submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Submit', $this->plugin_slug ); ?>" />
				</div>
			</form>
	<?php
		else:
	?>
			<div class="notice error">
				<p><?php printf( __( 'You have to create at least one mailing list before starting the subscription. <a href="%s" target="_blank">Create list from here</a>.' ), 'https://admin.mailchimp.com/lists/' ); ?></p>
			</div>
	<?php
		endif;
	?>
</div>