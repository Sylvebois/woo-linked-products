<?php

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'class-linked-products-list-table.php';

class Linked_Products_Forms {
    public function __construct() {

    }
    public function begin_page(){
        $createUrl = add_query_arg([
            'post_type' => 'product',
            'page' => 'woo_linked_products',
            'action' => 'creating',
        ], admin_url('edit.php'));
        ?>
    
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Linked Products', 'woo-linked-products'); ?></h1>
            <a href="<?php echo $createUrl ?>" class="page-title-action"><?php _e('Add link', 'woo-linked-products'); ?></a>
            <p><?php _e('This page allows you to manage the linked products.', 'woo-linked-products'); ?></p>

        <?php
    }

    public function end_page(){
        echo '</div>';
    }

    public function creation_form() {
        $nonce = wp_create_nonce('create_post_metadata');
    ?>
    <form method="get" class="linkedproducts creation-form">
        <input type="hidden" name="post_type" value="product" />
        <input type="hidden" name="page" value="woo_linked_products" />
        <input type="hidden" name="action" value="create" />
        <input type="hidden" name="sec" value=<?php echo $nonce?> />

        <label for="mainProductId"><?php _e('Main Product', 'woo-linked-products')?></label>
        <input type="text" name="mainProductId" value="" /> 
        <label for="linkedProductId"><?php _e(' --> Linked Product', 'woo-linked-products')?></label>
        <input type="text" name="linkedProductId" value="" />
        <label for="qty"><?php _e(' Qty', 'woo-linked-products')?></label>
        <input type="number" name="qty" value="1" />

        <?php submit_button(__('Save', 'woo-linked-products'), 'primary', 'save', false)?>
    </form>
    <?php
    }

    public function update_form($metaId = "", $mainProductId = "", $linkedProductId = "", $qty = 1) {
        $nonce = wp_create_nonce('update_post_metadata_by_mid_'.$metaId);
    ?>
    <form method="get" class="linkedproducts update-form">
        <input type="hidden" name="post_type" value="product" />
        <input type="hidden" name="page" value="woo_linked_products" />
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="metaId" value=<?php echo $metaId ?> />
        <input type="hidden" name="sec" value=<?php echo $nonce?> />

        <label for="mainProductId"><?php _e('Main Product', 'woo-linked-products')?></label>
        <input type="text" name="mainProductId" value="<?php echo $mainProductId ?>" /> 
        <label for="linkedProductId"><?php _e(' --> Linked Product', 'woo-linked-products')?></label>
        <input type="text" name="linkedProductId" value="<?php echo $linkedProductId ?>" />
        <label for="qty"><?php _e(' Qty', 'woo-linked-products')?></label>
        <input type="number" name="qty" value="<?php echo $qty ?>" />

        <?php submit_button(__('Update', 'woo-linked-products'), 'primary', 'update', false)?>
    </form>
    <?php
    }

    public function list_table() {
        if ( class_exists( 'Linked_Products_List_Table' ) ) {
            ?>
            <form method="get">
            <input type="hidden" name="post_type" value="product" />
            <input type="hidden" name="page" value="woo_linked_products" />
            <?php
            $table = new Linked_Products_List_Table();
            $table->prepare_items();
            $table->has_items() ? $table->display() : $table->no_items();
            ?>
            </form>
            <?php
        }
        else {
            _e('Class Linked_Products_List_Table not found !', 'woo-linked-products');
        }
    }
}