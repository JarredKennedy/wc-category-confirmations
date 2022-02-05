<?php
/**
 * Plugin Name: WooCommerce Category Order Confirmations
 * Description: Show customers a custom order confirmation message depending on the category of product purchased.
 * Version: 1.1.0
 * Author: Jarred Kennedy
 * Author URI: mailto:me@jarredkennedy.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_CATEGORY_CONFIRMATIONS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_CATEGORY_CONFIRMATIONS_URL', plugins_url( '', __FILE__ ) );

function wccc_get_rule_message_excerpt( $rule_message ) {
	$excerpt = strip_tags( $rule_message );

	if ( strlen( $excerpt ) > 100 ) {
		$excerpt = substr( $excerpt, 0, 100 ) . '&hellip;';
	}

	return $excerpt;
}

function wccc_get_rule_messages( $rule_ids ) {
	global $wpdb;

	$options_table = $wpdb->prefix . 'options';

	$rule_options = array_map( function( $rule_id ) {
		return 'wccc_category_rule_message_' . $rule_id;
	}, $rule_ids );

	$in = array_fill( 0, count( $rule_ids ), '%s' );
	$query = $wpdb->prepare( "SELECT `option_name`, `option_value` FROM `" . $options_table . "` WHERE `option_name` IN(" . implode( ',', $in ) . ")", ...$rule_options );

	$results = $wpdb->get_results( $query, OBJECT_K );

	$processed = [];
	foreach ( $results as $index => $result ) {
		$key = str_replace( 'wccc_category_rule_message_', '', $index );
		$processed[$key] = stripcslashes( $result->option_value );
	}

	return $processed;
}

function wccc_get_product_categories() {
	$categories = get_categories( array(
		'taxonomy'		=> 'product_cat',
		'hide_empty'	=> false
	) );

	return $categories;
}

function wccc_walk_categories( $categories, $top = 0, $callback, $depth = 0 ) {
	foreach ( $categories as $category ) {
		if ( $category->parent === $top ) {
			$callback( $category, $depth );

			wccc_walk_categories( $categories, $category->term_id, $callback, $depth + 1 );
		}
	}
}

if ( is_admin() ) {
	require WC_CATEGORY_CONFIRMATIONS_PATH . 'admin.php';
}

function wccc_match_product_category_rule( $product_ids ) {
	$categories = get_categories( array(
		'taxonomy'		=> 'product_cat',
		'object_ids'	=> $product_ids
	) );

	if ( empty( $categories ) ) {
		return null;
	}

	$categories = array_column( $categories, 'slug' );

	$rules = get_option( 'wccc_category_rules', array() );
	$rules_order = get_option( 'wccc_category_rules_order', [] );

	foreach ( $rules_order as $rule_id ) {
		$match = array_intersect( $rules[$rule_id][0], $categories );

		if ( ! empty( $match ) ) {
			$content = get_option( 'wccc_category_rule_message_' . $rule_id );

			if ( ! empty( $content ) ) {
				return $content;
			}
		}
	}
}

function wccc_show_message( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order || $order->has_status( 'failed' ) ) {
		return;
	}

	$product_ids = array_map( function( $item ) {
		return $item['product_id'];
	}, $order->get_items() );

	$message = wccc_match_product_category_rule( $product_ids );

	if ( $message ) {
		$message = stripcslashes( $message );

		echo apply_filters( 'the_content', nl2br( $message ) );
	}
}

add_action( 'woocommerce_before_thankyou', 'wccc_show_message' );

function wccc_activate_plugin() {
	$rules_order = get_option( 'wccc_category_rules_order', [] );

	if ( ! empty( $rules ) ) {
		return;
	}

	$rules = get_option( 'wccc_category_rules', [] );
	$rules_order = array_keys( $rules );

	update_option( 'wccc_category_rules_order', $rules_order );
}

register_activation_hook( __FILE__, 'wccc_activate_plugin' );