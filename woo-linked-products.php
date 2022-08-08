<?php

/**
 * Plugin Name: Woo Linked Products
 * Plugin URI: https://github.com/Sylvebois/woo-linked-products
 * Description: Automatically add linked products to cart when the a specified product is added to cart
 * Version: 1.0.0
 * Author: Sylvebois
 * Author URI: http://sanidel.com/
 * Text Domain: woo-linked-products
 * Domain Path: /languages
 * 
 * WC requires at least: 6.0
 * WC tested up to: 6.7
 * 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

 /*
  * Usefull links:
  * https://wp-kama.com/plugin/woocommerce/hook
  */

// If this file is called directly, cancel.
defined( 'ABSPATH' ) || exit;

function woo_linked_products_activate() {
    // Your activation logic goes here.
}
register_activation_hook( __FILE__, 'woo_linked_products_activate' );


function woo_linked_products_deactivate() {
    // Your deactivation logic goes here.
}
register_deactivation_hook( __FILE__, 'woo_linked_products_deactivate' );

/**
 * Add linked products to cart when main product is added to cart
 */
function woo_linked_products_add_to_cart( $cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {

}
add_filter( 'woocommerce_add_to_cart', 'woo_linked_products_add_to_cart', 10, 6 );

/*
 * Remove linked products from cart when main product is removed from cart
 */
function woo_linked_products_remove_from_cart($cart_item_key, $that){

}
add_filter( 'woocommerce_cart_item_removed', 'woo_linked_products_remove_from_cart', 10, 1 );
