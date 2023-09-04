<?php
/**
 *
 * Generates table for Taxonomy Listing
 * @author Umesh Kumar (.1) <umeshsingla05@gmail.com>
 *
 */

// Include core class.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'User_Tags_Taxonomy_List' ) ) :
	class User_Tags_Taxonomy_List extends WP_List_Table {
		public function __construct() {

			// Define singular and plural labels, as well as whether we support AJAX.
			parent::__construct(
				array(
					'ajax'     => false,
					'plural'   => 'taxonomies',
					'singular' => 'taxonomy',
				)
			);
			$this->count_context = null;
		}

		/**
		 * Default Function to handle Column Names
		 *
		 * @param object $item
		 * @param string $column_name
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'cb':
				case 'name':
				case 'taxonomy':
				case 'description':
					return $item[ $column_name ];
				default:
					return false;
			}
		}

		/**
		 * Prepare Items
		 *
		 * @return void
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, array(), $sortable );

			// Process any bulk action before rendering the table.
			$this->process_bulk_action();

			$this->items = $this->get_items();

		}

		/**
		 * Get Items
		 *
		 * @return array
		 */
		public function get_items() {
			return get_user_taxonomies();
		}

		/**
		 * No items message
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No Taxonomy found.', 'user_taxonomy' );
		}

		/**
		 * Columns for the custom table.
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'cb'          => '<input type="checkbox" />',
				'name'        => esc_html__( 'Taxonomy Name', 'user_taxonomy' ),
				'taxonomy'    => esc_html__( 'Slug', 'user_taxonomy' ),
				'description' => esc_html__( 'Description', 'user_taxonomy' ),
			);
		}

		/**
		 * Available bulk action.
		 *
		 * @return string[]
		 */
		public function get_bulk_actions() {
			return array( 'delete' => esc_html__( 'Delete', 'user_taxonomy' ) . '</a>' );
		}

		/**
		 * Checkbox column
		 *
		 * @param array $item Current item.
		 *
		 * @return void
		 */
		public function column_cb( $item ) {
			?>
            <label class="screen-reader-text"
                   for="cb-select-<?php esc_attr( $item['name'] ); ?>"> <?php printf( 'Select %1$s %2$s', esc_html( $this->_args['plural'] ), esc_html( $item['name'] ) ); ?> </label>
            <input type="checkbox" name="<?php echo esc_attr( $this->_args['plural'] ); ?>[]"
                   value="<?php echo esc_html( $item['name'] ); ?>" id="cb-select-<?php esc_attr( $item['name'] ); ?>"/>
			<?php
		}

		/**
		 * Taxonomy column value
		 *
		 * @param array $item Current item.
		 *
		 * @return void
		 */
		public function column_taxonomy( $item ) {
			$taxonomy_slug = ! empty( $item['slug'] ) ? $item['slug'] : get_taxonomy_slug( $item['name'] );
			// var_dump($user_info);
			echo esc_html( $taxonomy_slug );
		}

		/**
		 * Name column
		 *
		 * @param array $item current item
		 *
		 * @return void
		 */
		public function column_name( $item ) {

			$tax_slug = ! empty( $item['slug'] ) ? $item['slug'] : get_taxonomy_slug( $item['name'] );

			$edit_tags_url = admin_url( 'edit-tags.php?taxonomy=' . esc_attr( $tax_slug ) );

			$user_tax_url = admin_url( 'users.php?page=user-taxonomies&taxonomy=' . esc_attr( $tax_slug ) );
			?>
            <strong>
                <a href="<?php echo esc_url( $edit_tags_url ); ?>"><?php echo esc_html( $item['name'] ); ?> </a>
            </strong>
            <div class="taxonomy-row-actions">
                <a href="<?php echo esc_url( $user_tax_url ); ?>"><?php esc_html_e( 'Edit', 'user_taxonomy' ); ?> </a> |
				<?php wp_nonce_field( 'delete-taxonomy-' . $tax_slug, 'delete-taxonomy-' . $tax_slug ); ?>
                <span class="delete-taxonomy">
                    <a href="#" id="del-<?php echo esc_attr( $tax_slug ); ?>"
                       data-name="<?php echo esc_attr( $tax_slug ); ?>"
                       title="<?php esc_html_e( 'Delete Taxonomy', 'user_taxonomy' ); ?>">
                        <?php esc_html_e( 'Trash', 'user_taxonomy' ); ?>
                    </a>
                </span>
            </div>
			<?php
		}

		/**
		 * Taxonomy Description
		 *
		 * @param array $item Current Item.
		 *
		 * @return mixed
		 */
		public function column_description( $item ) {
			return $item['description'];
		}

		/**
		 * Handle User taxonomy List table bulk action
		 *
		 * @return void
		 */
		private function process_bulk_action() {

			// Return: If no taxonomy is set in url; If nonce is not set.
			if ( empty( $_POST['user_tax_bulk_action'] ) || empty( $_REQUEST['taxonomies'] ) ) {
				return;
			}

			// Check for nonce.
			$nonce = filter_input( INPUT_POST, 'user_tax_bulk_action', FILTER_SANITIZE_STRING );
			if ( ! wp_verify_nonce( $nonce, 'user_tax_bulk_action' ) ) {
				$message = esc_html__( 'Sorry! Security check failed.', 'user_taxonomies' );
				wp_die( $message );
			}

			// Capability Check.
			if ( ! current_user_can( 'edit_users' ) ) :
				return;
			endif;

			$user_taxonomies = wp_unslash( $_REQUEST['taxonomies'] );

			if ( is_array( $user_taxonomies ) ) :
				$ut_taxonomies = get_user_taxonomies();
				foreach ( $user_taxonomies as $taxonomy ) {
					$taxonomy = sanitize_key( $taxonomy );
					// Check if taxonomy exists in User Taxonomies array.
					$key = array_search( $taxonomy, array_column( $ut_taxonomies, 'name' ) );
					if ( $key ) :
						unset( $ut_taxonomies[ $key ] );
					endif;
				}
				update_option( 'ut_taxonomies', $ut_taxonomies );
			endif;
		}
	}
endif;
