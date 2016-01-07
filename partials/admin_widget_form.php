<?php
/**
 * Display form to set widget options
 *
 * @author Ashiqur Rahman
 * @url https://www.choobs.com
 */
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', $this->plugin_slug ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e( 'Form:', $this->plugin_slug ); ?></label>
	<select class="widefat" id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>">
	<?php foreach($forms as $form): ?>
		<option value="<?php echo $form->idform; ?>" <?php if( $form_id == $form->idform ): ?> selected <?php endif; ?>><?php echo $form->form_label; ?></option>
	<?php endforeach; ?>
	</select>
</p>