<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<?php if ( empty( $rule_id ) ): ?>
		<h1><?php _e( 'Add Product Category Order Message Rule', 'wc-category-confirmations' ); ?></h1>
	<?php else: ?>
		<h1><?php _e( 'Edit Product Category Order Message Rule', 'wc-category-confirmations' ); ?></h1>
	<?php endif; ?>

	<form method="POST" action="<?php echo admin_url( 'admin.php?page=wc-settings&tab=category-confirmations&wccc-action=edit' ); ?>">
		<p>
			<h2><?php _e( 'Select Product Categories', 'wc-category-confirmations' ); ?></h2>
			<ul style="list-style:none;">
				<?php
					wccc_walk_categories( $categories, 0, function( $category, $depth ) use ( $selected_categories ) {
						if ( $depth ) {
							echo '<div style="padding-left:' . ( $depth * 15 ) . 'px">';
						}
						?>
						<li>
							<label>
								<input type="checkbox" value="<?php echo $category->slug; ?>" name="product_categories[]" <?php checked( in_array( $category->slug, $selected_categories ) ); ?>>
								<?php echo $category->name; ?>
							</label>
						</li>
						<?php
						if ( $depth ) {
							echo '</div>';
						}
					} );
				?>
			</ul>
		</p>

		<br>

		<h2>Priority (lowest-first)</h2>
		<div>
			<input type="number" name="priority" value="<?php echo $priority; ?>">
		</div>

		<h2><?php _e( 'Order Confirmation Message', 'wc-category-confirmations' ); ?></h2>
		<div>
			<?php wp_editor( $confirmation_message, 'rule-message-editor', [ 'textarea_name' => 'confirmation_message' ] ); ?>
		</div>

		<p class="submit">
			<?php wp_nonce_field( 'wccc_save_rule' ); ?>

			<?php if ( $rule_id ): ?>
				<input type="hidden" name="rule_id" value="<?php echo $rule_id; ?>">
			<?php endif; ?>

			<input type="hidden" name="action" value="wccc_save_rule">
			<input class="button button-primary" type="submit" value="<?php _e( 'Save Rule', 'wc-category-confirmations' ); ?>">
			&nbsp;&nbsp;
			<a class="button button-secondary" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=category-confirmations' ); ?>"><?php _e( 'Cancel', 'wc-category-confirmations' ); ?></a>
		</p>
	</form>

</div>