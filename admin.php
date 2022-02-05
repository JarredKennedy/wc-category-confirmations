<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wccc_show_admin_page() {
	$rules = get_option( 'wccc_category_rules', array() );
	$rule_messages = array();

	$categories = wccc_get_product_categories();
	$categories = array_column( $categories, 'name', 'slug' );

	if ( ! empty( $rules ) ) {
		$rule_messages = wccc_get_rule_messages( array_keys( $rules ) );
	}

	include WC_CATEGORY_CONFIRMATIONS_PATH . 'templates/admin-rule-list.php';
}

function wccc_show_edit_rule_page() {
	$rule_id = $_GET['rule_id'] ?? null;

	$selected_categories = [];
	$confirmation_message = '';

	if ( $rule_id ) {
		$rules = get_option( 'wccc_category_rules', array() );

		if ( isset( $rules[$rule_id] ) ) {
			$selected_categories = $rules[$rule_id][0];
			$confirmation_message = get_option( 'wccc_category_rule_message_' . $rule_id, '' );
		}
	}

	$selected_categories = $_POST['product_categories'] ?? $selected_categories;
	$confirmation_message = $_POST['confirmation_message'] ?? $confirmation_message;

	$confirmation_message = stripcslashes( $confirmation_message );

	$categories = wccc_get_product_categories();

	include WC_CATEGORY_CONFIRMATIONS_PATH . 'templates/admin-rule-edit.php';
}

function wccc_add_woocommerce_tab() {
	$current_tab = $_GET['tab'] ?? '';

	echo '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=category-confirmations' ) . '" class="nav-tab ' . ( $current_tab === 'category-confirmations' ? 'nav-tab-active' : '' ) . '">' . __( 'Confirmation Messages', 'wc-category-confirmations' ) . '</a>';
}

add_action( 'woocommerce_settings_tabs', 'wccc_add_woocommerce_tab' );

function wccc_show_settings() {
	$wccc_action = $_GET['wccc-action'] ?? '';
	$GLOBALS['hide_save_button'] = true;

	if ( $wccc_action === 'edit' ) {
		wccc_show_edit_rule_page();
	} else {
		wccc_show_admin_page();
	}
}

add_action( 'woocommerce_settings_category-confirmations', 'wccc_show_settings' );

function wccc_handle_action() {
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'wccc_save_rule' ) {
		$nonce = $_POST['_wpnonce'] ?? '';

		check_admin_referer( 'wccc_save_rule' );

		if ( ! wp_verify_nonce( $nonce, 'wccc_save_rule' ) ) {
			$GLOBALS['wccc_save_status'] = 'failed_nonce';
			return;
		}

		$rule_id = $_POST['rule_id'] ?? uniqid( 'rule' );
		$confirmation_message = $_POST['confirmation_message'] ?? '';
		$selected_categories = $_POST['product_categories'] ?? '';

		if ( empty( $confirmation_message ) ) {
			$GLOBALS['wccc_save_status'] = 'empty_message';
			return;
		}

		if ( empty( $selected_categories ) ) {
			$GLOBALS['wccc_save_status'] = 'empty_categories';
			return;
		}

		$rules = get_option( 'wccc_category_rules', array() );
		$rule = [ $selected_categories ];

		$rules[$rule_id] = $rule;

		update_option( 'wccc_category_rules', $rules );
		update_option( 'wccc_category_rule_message_' . $rule_id, $confirmation_message );

		wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=category-confirmations&wccc_saved=1' ) );
		exit;
	} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'wccc_delete_rule' ) {
		$nonce = $_GET['_wpnonce'] ?? null;
		$rule_id = $_GET['rule_id'] ?? null;

		if ( $rule_id ) {
			if ( ! wp_verify_nonce( $nonce, 'wccc_delete_rule' ) ) {
				wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=category-confirmations&wccc_deleted=failed_nonce' ) );
				exit;
			}

			$rules = get_option( 'wccc_category_rules', array() );
			
			if ( isset( $rules[$rule_id] ) ) {
				unset( $rules[$rule_id] );

				delete_option( 'wccc_category_rule_message_' . $rule_id );

				update_option( 'wccc_category_rules', $rules );
			}

			wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=category-confirmations&wccc_deleted=1' ) );
			exit;
		}
	}
}

add_action( 'admin_init', 'wccc_handle_action' );

function wccc_show_admin_notices() {
	$status = '';
	$message = '';

	if ( isset( $GLOBALS['wccc_save_status'] ) ) {

		if ( $GLOBALS['wccc_save_status'] === 'failed_nonce' ) {
			$status = 'error';
			$message = __( 'Rule not saved. Nonce has expired.', 'wc-category-confirmations' );
		} else if ( $GLOBALS['wccc_save_status'] === 'empty_message' ) {
			$status = 'error';
			$message = __( 'Rule not saved. Message is required.', 'wc-category-confirmations' );
		} else if ( $GLOBALS['wccc_save_status'] === 'empty_categories' ) {
			$status = 'error';
			$message = __( 'Rule not saved. At least one category must be selected.', 'wc-category-confirmations' );
		}
	}

	if ( isset( $_GET['wccc_saved'] ) && $_GET['wccc_saved'] == '1' ) {
		$status = 'success';
		$message = __( 'Rule saved.', 'wc-category-confirmations' );
	}

	if ( isset( $_GET['wccc_deleted'] ) ) {
		if ( $_GET['wccc_deleted'] === 'failed_nonce' ) {
			$status = 'error';
			$message = __( 'Rule not deleted. Nonce has expired', 'wc-category-confirmations' );
		} else if ( $_GET['wccc_deleted'] === '1' ) {
			$status = 'success';
			$message = __( 'Rule deleted.', 'wc-category-confirmations' );
		}
	}

	if ( $status ) {
		?>
			<div class="notice notice-<?php echo $status; ?>">
				<p><?php echo $message; ?></p>
			</div>
		<?php
	}
}

add_action( 'admin_notices', 'wccc_show_admin_notices' );