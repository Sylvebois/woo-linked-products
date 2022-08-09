<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    // Use the original WP_List_Table class but may be safer to use a copy in the current folder.
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Linked_Products_List_Table extends WP_List_Table {
    const RESULTS_PER_PAGE = 100;

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'linked product',
				'plural'   => 'linked products',
				'ajax'     => false,
			)
		);
	}

    /**
    * Retrieve list of linked products from the database
    *
    * @param int $per_page      Number of rows to display per page.
    * @param int $page_number   Page number.
    *
    * @return array
    */
    public static function get_linked_product_data( $pageNumber = 1 ) {
        //could query be improved by using WP_meta_query ?
        global $wpdb;
        
        $sql = "SELECT ID, meta_id, SUBSTRING(meta_key,16) AS 'linked_id', meta_value, post_title, sku 
                FROM {$wpdb->prefix}postmeta 
                JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id 
                JOIN {$wpdb->prefix}wc_product_meta_lookup ON {$wpdb->prefix}wc_product_meta_lookup.product_id = {$wpdb->prefix}posts.ID
                WHERE meta_key LIKE 'linked_product_%'";
        
        if ( !empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY '.esc_sql( $_REQUEST['orderby'] );
            $sql .= !empty( $_REQUEST['order'] ) ? ' '.esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= ' LIMIT '.self::RESULTS_PER_PAGE;
        $sql .= ' OFFSET '.($pageNumber - 1) * self::RESULTS_PER_PAGE;

        return $wpdb->get_results( $sql, 'ARRAY_A' );
    }

    /**
     * Get additional data for the linked products
     * @param string $id
     * 
     * @return array
     */
    private static function get_additional_data( $id ) {
        global $wpdb;
        $sql = 'SELECT ID, post_title, sku '
                .'FROM '.$wpdb->prefix.'posts '
                .'JOIN '.$wpdb->prefix.'wc_product_meta_lookup ON '.$wpdb->prefix.'wc_product_meta_lookup.product_id = '.$wpdb->prefix.'posts.ID '
                .'WHERE ID = '.$id;
        return $wpdb->get_results( $sql, 'ARRAY_A' );
    }
    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'linked_product_%'";
        return $wpdb->get_var( $sql );
    }

    /**
	 * Prepare the data for the WP List Table
	 *
	 * @return void
	 */
    public function prepare_items() {
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden = $this->get_hidden_columns();
        $primary = 'ID';
        $this->_column_headers = array( $columns, $hidden, $sortable, $primary );
        $data = array();

        $this->process_bulk_action();

        $totalLinks = $this->record_count();
        $currentPage = $this->get_pagenum();
        $totalPage = ceil( $totalLinks / self::RESULTS_PER_PAGE );

		$this->set_pagination_args(
			array(
				'total_items' => $totalLinks,
				'per_page'    => self::RESULTS_PER_PAGE,
				'total_pages' => $totalPage,
			)
		);

        $linked_products = $this->get_linked_product_data($currentPage);

        foreach( $linked_products as $linked_product ) {
            $additionalData = $this->get_additional_data( $linked_product['linked_id'] );
            $data[$linked_product['meta_id']] = array(
                'mainProductId' => $linked_product['ID'],
                'mainProductSku' => $linked_product['sku'],
                'mainProductName' => $linked_product['post_title'],
                'linkedProductId' => $linked_product['linked_id'],
                'linkedProductSku' => $additionalData[0]['sku'],
                'linkedProductName' => $additionalData[0]['post_title'],
                'qty' => $linked_product['meta_value'],
                'metaId' => $linked_product['meta_id'],
            );
        }

		wp_reset_postdata();    // usefull ?
		$this->items = $data;
    }

    /**
	 * Default column formatting, it will escape everythig for security.
	 *
	 * @param array  $item          The item array.
	 * @param string $column_name   Column name to display.
	 *
	 * @return string               The content to display
	 */
    public function column_default( $item, $column_name ) {
        $column_html = '';
        switch($column_name){
            case 'mainProductName':
                $column_html = '('.esc_html( $item['mainProductSku'] ).') '.esc_html( $item['mainProductName'] )
                                .'<div class="row-actions"><span class="id">ID : '.esc_html( $item['mainProductId'] ).'</div>';
                break;
            case 'linkedProductName':
                $column_html = '('.esc_html( $item['linkedProductSku'] ).') '.esc_html( $item['linkedProductName'] )
                                .'<div class="row-actions"><span class="id">ID : '.esc_html( $item['linkedProductId'] ).'</div>';
                break;
            case 'metaId':
                $column_html = esc_html( $item[ 'metaId' ] ).$this->maybe_render_actions( $item );
                break;
            default:
                $column_html = esc_html( $item[ $column_name ] );
        }
		return $column_html;
    }

    /**
     * Set the columns to be displayed in the table
     * 
     * @return Array
     */
    public function get_columns() {
		return array(
			'cb' => '<input type="checkbox"/>',
            'metaId' => __('Link Id', 'woo-linked-products'),
            'mainProductName' => __( 'Main Product', 'woo-linked-products' ),
			'linkedProductName' => __( 'Linked Product', 'woo-linked-products' ),
			'qty' => __( 'Quantity', 'woo-linked-products' ),
		);
	}

    /**
	 * Include the columns which can be sortable.
	 *
	 * @return Array    array of sortable columns.
	 */
    public function get_sortable_columns() {
        return array(
            'mainProductName' => array( 'mainProductName', false ),
            'linkedProductName' => array( 'linkedProduct', false ),
        );
    }

    /**
	 * Include the columns which are hidden.
	 *
	 * @return Array
	 */
    public function get_hidden_columns() {
        return array( );
    }

    /**
     * <TODO> ?
     * public function column_title( $item )
     */

    /**
	 * Column cb.
	 *
	 * @param  array $item Item data.
     * 
	 * @return string
	 */
    public function column_cb( $item ) {
        return sprintf(
			'<input type="checkbox" name="%1$s_id[]" value="%2$s" />',
			esc_attr( $this->_args['singular'] ),
			esc_attr( $item['meta_id'] )
		);
	}

    /**
	 * Renders the row-actions.
	 *
	 * This method renders the action menu.
	 *
	 * @param array  $row   Row to be rendered.
     * 
	 * @return string
	 */
	protected function maybe_render_actions( $row ) {
		return '<div class="row-actions">'
                    .'<span class="edit"><a href="#" aria-label="'.__( 'Edit link', 'woo-linked-products' ).'">'.__( 'Edit', 'woo-linked-products' ).'</a> | </span>'
                    .'<span class="submitdelete"><a href="#" aria-label="'.__( 'Delete link', 'woo-linked-products' ).'">'.__( 'Delete', 'woo-linked-products' ).'</a></span>'
                .'</div>';
	}

    /**
    * Returns an associative array containing the bulk action
    *
    * @return array
    */
    public function get_bulk_actions() {
        return array(
            'bulk_delete' => __( 'Delete', 'woo-linked-products' ),
        );
    }

    /**
	 * <TODO> Get bulk actions.
	 *
	 * @return void
	 */
	public function process_bulk_action() {
        if ( 'bulk_delete' === $this->current_action() ) {
            //1. get list of selected meta ids
            //2. then delete them
		}
	}

    /**
	 * Generates the table navigation above or below the table
	 *
	 * @param string $which Position of the navigation, either top or bottom.
	 *
	 * @return void
	 */
	protected function display_tablenav( $which ) {
		?>
	    <div class="tablenav <?php echo esc_attr( $which ); ?>">

		<?php if ( $this->has_items() ) : ?>
		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
			<?php
		endif;
		$this->pagination( $which );
		?>

		<br class="clear" />
	</div>
		<?php
	}
}