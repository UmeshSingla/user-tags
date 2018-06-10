<?php
/*
 * Generates table for Taxonomy Listing
 * @author Umesh Kumar (.1) <umeshsingla05@gmail.com>
 *
 */
require_once( dirname( __FILE__ ) . "/functions.php" );

//If WP List table isn't included
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class User_Tags_List extends WP_List_Table {
	public function __construct() {

		// Define singular and plural labels, as well as whether we support AJAX.
		parent::__construct( array(
			'ajax'     => false,
			'plural'   => 'taxonomies',
			'singular' => 'taxonomy',
		) );
		$this->count_context = null;
	}

	/**
	 * Default Function to handle Column Names
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	function column_default($item, $column_name){
		switch($column_name){
			case 'cb':
			case 'name':
			case 'taxonomy':
				return $item[$column_name];
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	function prepare_items() {
		/* -- Register the Columns -- */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$this->items = $this->ut_list_taxonomies();

	}

	function ut_list_taxonomies() {
		/* -- Fetch the items -- */
		$ut_taxonomies = get_site_option( 'ut_taxonomies', array() );

		return $ut_taxonomies;
	}

	function no_items() {
		_e( 'No Taxonomies found.', WP_UT_TRANSLATION_DOMAIN );
	}

	function get_bulk_actions() {
		$actions           = array();
		$actions['delete'] = __( 'Delete', WP_UT_TRANSLATION_DOMAIN ) . '</a>';

		return $actions;
	}

	function get_columns() {
		return array(
			'cb'       => '<input type="checkbox" />',
			'name'     => __( 'Display Name', WP_UT_TRANSLATION_DOMAIN ),
			'taxonomy' => __( 'Taxonomy', WP_UT_TRANSLATION_DOMAIN ),
		);
	}

	function column_cb( $item ) {
		printf( '<label class="screen-reader-text" for="cb-select-%2$s">' . __( 'Select %1$s %2$s', WP_UT_TRANSLATION_DOMAIN ) . '</label><input type="checkbox" name="%1$s[]" value="%2$s" id="cb-select-%2$s" />', $this->_args['plural'], $item['name'] );
	}

	function column_taxonomy( $item ) {
		$taxonomy_slug = ! empty( $item['slug'] ) ? $item['slug'] : ut_taxonomy_name( $item['name'] );
		//var_dump($user_info);
		echo $taxonomy_slug;
	}

	function column_name( $item ) {
		$taxonomy_slug = ! empty( $item['slug'] ) ? $item['slug'] : ut_taxonomy_name( $item['name'] );
		echo '<strong> <a href="edit-tags.php?taxonomy=' . $taxonomy_slug . '">' . $item['name'] . '</a> </strong><div class="taxonomy-row-actions"><a href="users.php?page=user-taxonomies&taxonomy=' . $taxonomy_slug . '">' . __( 'Edit', WP_UT_TRANSLATION_DOMAIN ) . '</a> |';
		wp_nonce_field( 'delete-taxonomy-' . $taxonomy_slug, 'delete-taxonomy-' . $taxonomy_slug );
		echo ' <span class="delete-taxonomy"> <a href="#" id="del-' . $taxonomy_slug . '" data-name="' . $taxonomy_slug . '" title="' . __( 'Delete Taxonomy', WP_UT_TRANSLATION_DOMAIN ) . '">' . __( 'Trash', WP_UT_TRANSLATION_DOMAIN ) . '</a> </span>  </div>';
	}

	function process_bulk_action() {
		if ( empty( $_REQUEST['taxonomies'] ) ) {
			return;
		}

		$taxonomies = $_REQUEST['taxonomies'];

		$ut_taxonomies = get_site_option( 'ut_taxonomies' );
		foreach ( $taxonomies as $taxonomy ) {
			foreach ( $ut_taxonomies as $ut_taxonomy_key => $ut_taxonomy_array ) {
				if ( $ut_taxonomy_array['name'] == $taxonomy ) {
					unset( $ut_taxonomies[ $ut_taxonomy_key ] );
				}
			}
		}
		update_site_option( 'ut_taxonomies', $ut_taxonomies );
	}

}