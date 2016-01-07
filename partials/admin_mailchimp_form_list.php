<?php
/**
 * Template page for Mailchimp forms
 *
 * @author Ashiqur Rahman
 * @url https://www.choobs.com
 **/
?>
<div class="wrap choobs-wp-mailchimp-options">
	<h2><?php _e('Mailchimp Forms', $this->plugin_slug); ?></h2>
	<?php
		settings_errors();

		if( !empty( $error ) ) {
			echo '<div class="error">';
			foreach( $error as $e ) {
				echo '<p>' . $e. '</p>';
			}
			echo '</div>';
		}
		$query = $wpdb->prepare(
			'SELECT * FROM `' . $wpdb->prefix . $choobs_wp_mailchimp_forms_table . '`
				ORDER BY ' . $order_by . ' ' . $order . '
				LIMIT %d, %d ',
			( ( $page - 1 ) * $limit ), $limit
		);
		$forms = $wpdb->get_results( $query );
		if( $forms ):
	?>
			<div class="choobs-wp-mailchimp-msg"><p><span class="dashicons dashicons-plus-alt"></span> <?php printf( __( '<a href="%s">Create new form</a>.' ), 'admin.php?page=wp-mailchimp-subscription-forms&do=add' ); ?></p></div>

			<table class="forms-table widefat fixed">
				<thead>
					<tr>
						<th>
							<?php _e( 'Form ID', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Label', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Short Code', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Status', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Last Edit', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Action', $this->plugin_slug ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$row_count = 1;
					foreach( $forms as $form ):

						$class = ' ' . $form->form_status;
				?>
						<tr class="<?php echo $class; ?>">
							<td>
								<?php echo $form->idform; ?>
							</td>
							<td>
								<?php echo $form->form_label; ?>
							</td>
							<td>
								<?php echo $form->form_shortcode; ?>
							</td>
							<td>
								<?php echo ucfirst( $form->form_status ); ?>
							</td>
							<td>
								<?php echo date( 'd F, Y (h:i a)', strtotime( $form->edit_date ) ); ?>
							</td>
							<td>
								<?php
									$view_url = admin_url( 'admin.php?page=choobs-wp-mailchimp-subscription-forms&do=view&id=' . $form->idform );
									$delete_url = admin_url( 'admin.php?page=choobs-wp-mailchimp-subscription-forms&do=delete&id=' . $form->idform );
								?>
								<a href="<?php echo $view_url; ?>" title="<?php _e( 'View / Edit', $this->plugin_slug ); ?>">
									<span class="dashicons dashicons-editor-code"></span>
								</a>
								<a href="<?php echo $delete_url; ?>" title="<?php _e( 'Delete', $this->plugin_slug ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</a>
							</td>
						</tr>
				<?php
						$row_count++;
					endforeach;
				?>
				</tbody>
			</table>


			<div class="tablenav bottom">
				<?php
				$form_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}{$choobs_wp_mailchimp_forms_table} WHERE %d", 1 ) );
				?>
				<div class="tablenav-pages">
					<span class="displaying-num">
						<?php printf( _n( '1 item', '%s items', $form_count, $this->plugin_slug ), $form_count ); ?>
					</span>
				<?php
				if( $form_count > $limit ) {
					?>
					<span class="pagination-links">
					<?php
					$total_pages = ceil( $form_count / $limit );
					$link_params = $_GET;

					$link_params['p'] = 1;
					$first_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == 1 ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $first_params ) . '" class="' . $disabled . '" title="' . __( 'First page', $this->plugin_slug ) . '">&laquo;</a>';
					$link_params['p'] = max( 1, ( $page - 1 ) );
					$prev_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == 1 ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $prev_params ) . '" class="' . $disabled . '" title="' . __( 'Previous page', $this->plugin_slug ) . '">&lsaquo;</a>';

					echo ' <span class="paging-input">';
					echo $page . ' ' . __( 'of', $this->plugin_slug );
					echo ' <span class="total-pages">' . $total_pages . '</span>';
					echo '</span> ';

					$link_params['p'] = min( $total_pages, ( $page + 1 ) );
					$prev_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == $total_pages ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $prev_params ) . '" class="' . $disabled . '" title="' . __( 'Next page', $this->plugin_slug ) . '">&rsaquo;</a>';
					$link_params['p'] = $total_pages;
					$last_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == $total_pages ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $last_params ) . '" class="' . $disabled . '" title="' . __( 'Last page', $this->plugin_slug ) . '">&raquo;</a>';

					?>
					</span>
					<?php
				}
				?>
				</div>
			</div>
	<?php
		else:
	?>
			<div class="choobs-wp-mailchimp-msg"><p><span class="dashicons dashicons-warning"></span> <?php _e( 'No forms to display!', $this->plugin_slug ); ?><br /><span class="dashicons dashicons-plus-alt"></span> <?php printf( __( '<a href="%s">Create your first one</a>.' ), 'admin.php?page=choobs-wp-mailchimp-subscription-forms&do=add' ); ?></p></div>
	<?php
		endif;
	?>
</div>