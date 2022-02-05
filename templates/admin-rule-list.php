<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<h1><?php _e( 'Product Category Order Confirmation Messages', 'wc-category-confirmations' ); ?></h1>

	<p>
		<?php _e( 'Map product categories to order confirmation messages.<br/>The customer is shown the order confirmation message after they complete the checkout process.<br/>The messagee they see is determined by the product category of the purchased products.', 'wc-category-confirmations' ); ?>
	</p>

	<div>
		<p>
			<a class="button button-secondary" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=category-confirmations&wccc-action=edit' ); ?>"><?php _e( 'Add a Rule', 'wc-category-confirmations' ); ?></a>
		</p>

		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e( 'Product Categories', 'wc-category-confirmations' ); ?></th>
					<th><?php _e( 'Message', 'wc-category-confirmations' ); ?></th>
					<th><?php _e( 'Priority', 'wc-category-confirmations' ); ?></th>
					<th><?php _e( 'Actions', 'wc-category-confirmations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( count( $rules ) > 0 ): ?>
					<?php foreach( $rules as $rule_id => $rule ): ?>
						<tr>
							<td><?php echo implode( ', ', array_intersect_key( $categories, array_flip( $rule[0] ) ) ); ?></td>
							<td><?php echo wccc_get_rule_message_excerpt( $rule_messages[$rule_id] ); ?></td>
							<td><?php echo array_search( $rule_id, $rules_order ) + 1; ?></td>
							<td>
								<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=category-confirmations&wccc-action=edit&rule_id=' . $rule_id ); ?>"><?php _e( 'Edit', 'wc-category-confirmations' ); ?></a>
								&nbsp;&nbsp;
								<a style="color:red" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=wc-settings&tab=category-confirmations&action=wccc_delete_rule&rule_id=' . $rule_id ), 'wccc_delete_rule' ) ; ?>"><?php _e( 'Delete', 'wc-category-confirmations' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="3"><?php _e( 'There are no rules currently', 'wc-category-confirmations' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>