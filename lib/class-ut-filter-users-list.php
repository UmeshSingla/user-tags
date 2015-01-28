<?php
/*
 * Filter Users list
 * @author Umesh Kumar (.1) <umeshsingla05@gmail.com>
 *
 */
require_once( dirname( __FILE__ ) . "/functions.php" );
// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_Users_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php' );
}

class Filter_Users_List extends WP_Users_List_Table {

	var $taxonomy;
	var $term;

	function __construct( $taxonomy, $term ) {
		if ( empty( $taxonomy ) || empty( $term ) ) {
			return;
		}
		$this->taxonomy = $taxonomy;
		$this->term     = $term;
		$this->prepare_items();
	}

	/**
	 * Overridden function to filter list of users if taxonomy and term is set in URL
	 *
	 * @param $taxonomy
	 * @param $term
	 */
	public function prepare_items() {

		global $role, $usersearch;

		$term = get_term_by( 'slug', $this->term, $this->taxonomy );
		if ( empty( $term ) ) {
			return;
		}
		$users = get_objects_in_term( $term->term_id, $term->taxonomy );

		if ( empty( $users ) ) {
			return;
		}

		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

		$per_page       = ( $this->is_site_users ) ? 'site_users_network_per_page' : 'users_per_page';
		$users_per_page = $this->get_items_per_page( $per_page );

		$paged = $this->get_pagenum();

		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged - 1 ) * $users_per_page,
			'role'   => $role,
			'search' => $usersearch,
			'include' => $users,
			'fields' => 'all_with_meta'
		);

		if ( '' !== $args['search'] ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( $this->is_site_users ) {
			$args['blog_id'] = $this->site_id;
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$args['order'] = $_REQUEST['order'];
		}

		echo "<pre>";
		print_r( $args );
		echo "</pre>";
		// Query the user IDs for this page
		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args( array(
			'total_items' => $wp_user_search->get_total(),
			'per_page'    => $users_per_page,
		) );
	}

}