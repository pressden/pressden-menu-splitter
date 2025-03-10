<?php
/**
 * PDMS Splitter Page
 *
 * @package PDMSMenus
 */

?>

<div class="wrap">

	<h2>Menu Splitter</h2>

	<form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post" id="pdms-form">

		<input type="hidden" name="action" value="pdms_save">

		<?php
		// Add nonce field.
		wp_nonce_field( 'pdms_secure_save', 'pdms_nonce' );

		do_settings_sections( 'pdms' );
		?>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">
					<label for="slug">Menu Location</label>
				</th>
				<td>

					<?php if ( 'edit' === $this->action ) : ?>

						<input type="hidden" name="slug" value="<?php echo esc_attr( $this->active_segment['slug'] ); ?>">
						<p>
							<?php echo esc_html( $this->active_segment['name'] ); ?>
							<span style="color:silver"> (<?php echo esc_html( $this->active_segment['slug'] ); ?>)</span>
						</p>

					<?php else : ?>

						<select name="slug" id="pdms-slug">
							<option value="">Select one:</option>

							<?php
							foreach ( $this->locations as $slug => $name ) {
								$selected = ( isset( $this->active_segment['slug'] ) && $slug === $this->active_segment['slug'] ) ? 'selected' : '';
								echo '<option value="' . esc_attr( $slug ) . '"' . esc_attr( $selected ) . '>' . esc_html( $name ) . '</option>';
							}
							?>

						</select>

					<?php endif; ?>

				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="segment-count">Segments</label>
				</th>
				<td>
					<select name="segment-count" id="pdms-segment-count">
						<option disabled>Select one:</option>

						<?php
						for ( $i = 2; $i <= 10; $i++ ) {
							$selected = ( isset( $this->active_segment['segment-count'] ) && $i === $this->active_segment['segment-count'] ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $i ) . '"' . esc_attr( $selected ) . '>' . esc_html( $i ) . '</option>';
						}
						?>

					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="segment-names">Segment Names</label>
				</th>
				<td>

					<?php
					// Get the segment names.
					$segment_names = ( isset( $this->active_segment['segment-names'] ) ) ? $this->active_segment['segment-names'] : null;
					?>

					<input
						type="text"
						name="segment-names"
						id="pdms-segment-names"
						value="<?php echo esc_attr( $segment_names ); ?>"
						class="large-text"
					>
					<p class="description">Optional list of custom menu segment names in CSV format.</p>
				</td>
			</tr>

		</table>

		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php echo esc_attr( 'Save Changes' ); ?>" />
		</p>

	</form>

	<hr>

	<h2>Menu Location Segments</h2>

	<?php $list_table->display(); ?>

</div> <!-- .wrap -->
