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
  * https://www.vijayan.in/how-to-create-custom-table-list-in-wordpress-admin-page/
  */

// If this file is called directly, cancel.
defined( 'ABSPATH' ) || exit;

function woo_linked_products_activate() {

}
register_activation_hook( __FILE__, 'woo_linked_products_activate' );


function woo_linked_products_deactivate() {
    
}
register_deactivation_hook( __FILE__, 'woo_linked_products_deactivate' );

function woo_linked_products_settings_menu() {
    if ( class_exists( 'WooCommerce' ) ) {
        add_submenu_page('edit.php?post_type=product', __('Linked Products', 'woo-linked-products'), __('Linked Products', 'woo-linked-products'), 'manage_woocommerce', 'woo_linked_products', 'woo_linked_products_main_page');
    }
}
add_action('admin_menu', 'woo_linked_products_settings_menu', 99);

/**
 * Do actions on init
 */
function woo_linked_products_init() {
    $page = $_REQUEST['page'];

    if($page === 'woo_linked_products') {
        $nonce = $_REQUEST['sec'];
        $action = $_REQUEST['action'];
        $metaId = $_REQUEST['id'];
        $metaData = $_REQUEST['metadata'];
        
        if($action === 'delete' && wp_verify_nonce($nonce, 'delete_post_metadata_by_mid_'.$metaId)) {
            //delete_meta_by_mid('post', $metaId);
            echo 'DELETE';
        }
        else if($action === 'update' && wp_verify_nonce($nonce, 'update_post_metadata_by_mid_'.$metaId) && isset($metaData)) {	
            //update_meta_by_mid('post', $metaId, $metaData);
            echo 'UPDATE';
        }
        else if ($action === 'create' && wp_verify_nonce($nonce, 'create_post_metadata') && isset($metaData)) {	
            //create_meta('post', $metaId, $metaData);
            echo 'CREATE';
        }
    }
}
add_action ('admin_init', 'woo_linked_products_init');

/*
 * Display the table of linked products
 */
function woo_linked_products_main_page() { 
    require_once plugin_dir_path( __FILE__ ) . 'classes/class-linked-products-list-table.php';
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Linked Products', 'woo-linked-products'); ?></h1>
        <a href="#" class="page-title-action"><?php _e('Add link', 'woo-linked-products'); ?></a>
        <p><?php _e('This page allows you to manage the linked products.', 'woo-linked-products'); ?></p>
    <?php  

    if ( class_exists( 'Linked_Products_List_Table' ) ) {
        $table = new Linked_Products_List_Table();
        $table->prepare_items();
        $table->has_items() ? $table->display() : $table->no_items();
    }
    else {
        _e('Class Linked_Products_List_Table not found !', 'woo-linked-products');
    }

    echo "</div>";
}

/**
 * Display the form to add a new link
 */
function woo_linked_products_edit_form() {}

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
