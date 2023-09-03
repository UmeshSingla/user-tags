<?php
/**
 * Adds the functionality of custom taxonomy for Users
 */
if ( ! class_exists( 'User_Tags_Taxonomies ' ) ) :
	class User_Tags_Taxonomies {

        public static $instance;

		private function __construct() {
			//Add Menu Page
			add_action( 'admin_menu', array( $this, 'add_taxonomies_page' ) );
			// Set Parent page for User Taxonomy edit page.
			add_filter( 'parent_file', array( $this, 'set_parent_page' ) );

            //Handle Delete Taxonomy action
			add_action( 'wp_ajax_ut_delete_taxonomy', array( $this, 'delete_taxonomy' ) );

			//Process User taxonomies
			add_action( 'wp_loaded', array( $this, 'update_taxonomy_list' ) );
            //Register Taxonomies
			add_action( 'init', array( $this, 'register_taxonomies' ) );

            //Replace the column 'Posts' -> 'Users' in Admin
			add_action( 'registered_taxonomy', array( $this, 'registered_taxonomy' ), 10, 3 );

		}

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new User_Tags_Taxonomies();
			}

			// Returns the instance
			return self::$instance;
		}

		/**
		 * Adds a Taxonomy Sub page to Users menu
		 */
		function add_taxonomies_page() {
			$this->settings_page = add_users_page(
				esc_html__( 'Taxonomies', 'user_taxonomy' ),
				esc_html__( 'Taxonomies', 'user_taxonomy' ),
				'delete_users',
				'user-taxonomies',
				array(
					$this,
					'render_taxonomies_page',
				)
			);

            // Show any exiting taxonomies under users page
			$user_taxonomies = get_user_taxonomies();

			if ( ! empty( $user_taxonomies ) && is_array( $user_taxonomies ) ) :
				foreach ( $user_taxonomies as $taxonomy ) {
					if ( taxonomy_exists( $taxonomy['slug'] ) ) :
						add_submenu_page( 'users.php', $taxonomy['name'], $taxonomy['name'], 'edit_users', 'edit-tags.php?taxonomy=' . $taxonomy['slug'] );
					endif;
				}
			endif;

		}

		/**
		 * Set Users menu as parent if editing a User Taxonomy.
		 *
		 * @param string $parent
		 *
		 * @return string
		 */
		public function set_parent_page( $parent ) {
			global $pagenow;

			// If we're editing one of the user taxonomies
			// We must be within the users menu, so highlight that
			if ( ! empty( $_GET['taxonomy'] )
			     && in_array( $pagenow, array(
					'edit-tags.php',
					'term.php'
				) )
			     && get_user_taxonomy_key( sanitize_key( $_GET['taxonomy'] ) ) ) {
				$parent = 'users.php';
			}

			return $parent;
		}

		/**
		 * Displays the Add New Taxonomy Form, and Edit Option - if taxonomy is set in url.
		 */
		function render_taxonomies_page() {
			$page_title = esc_html__( 'Add new Taxonomy', 'user_taxonomy' );

			$tax_desc = '';
			$tax_slug = '';
			$tax_name = '';

			//If a Taxonomy is being edited.
			if ( ! empty( $_GET['taxonomy'] ) ) :
				$tax_slug = sanitize_title( $_GET['taxonomy'] );

				$page_title = sprintf( esc_html__( 'Edit Taxonomy: %s', 'user_taxonomy' ), $tax_slug );
				$tax   = get_taxonomy( $tax_slug );

				if ( $tax ) :
					$tax_name = $tax->labels->name;
					$user_taxonomies = get_user_taxonomies();

					if ( ! empty( $user_taxonomies ) ) :
						foreach ( $user_taxonomies as $user_taxonomy ) {
							if ( $tax_slug === $user_taxonomy['slug'] ) :
								$tax_desc = ! empty( $user_taxonomy['description'] ) ? trim( $user_taxonomy['description'] ) : '';
							endif;
						}
					endif;
				endif;
			endif;
			?>
			<div class="wrap nosubsub user-taxonomies-page">
				<h2><?php esc_html_e( 'User Taxonomy', 'user_taxonomy' ); ?></h2>

				<div id="col-container" class="wp-clearfix">
					<div id="col-left">
						<div class="col-wrap">
							<div class="form-wrap">
								<h3><?php echo esc_html( $page_title ); ?></h3>

								<form name="editusertaxonomy" id="editusertaxonomy" method="post" action="" class="validate">
									<div class="form-field form-required term-name-wrap">
										<label for="taxonomy_name"><?php esc_html__( 'Name', 'user_taxonomy' ); ?></label>
										<input name="taxonomy_name" id="taxonomy_name" type="text" value="<?php echo esc_attr( $tax_name ); ?>" size="40" aria-required="true">
										<p><?php esc_html_e( 'The name is how it appears on your site.', 'user_taxonomy' ); ?></p>
									</div>
									<div class="form-field term-slug-wrap">
										<label for="taxonomy-slug"><?php esc_html_e( 'Taxonomy Slug', 'user_taxonomy' ); ?></label>
										<input name="taxonomy_slug" id="taxonomy-slug" type="text"
										       value="<?php echo esc_attr( $tax_slug ); ?>" size="40"/>
										<p><?php esc_html_e( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'user_taxonomy' ); ?></p>
									</div>
									<div class="form-field term-description-wrap">
										<label for="description"><?php esc_html_e( 'Description', 'user_taxonomy' ); ?></label>
										<textarea name="description" id="description" rows="5" cols="40"><?php echo esc_html( $tax_desc ); ?></textarea>
										<p><?php esc_html_e( 'The description is not prominent by default; however, some themes may show it.', 'user_taxonomy' ); ?></p>
									</div>
									<?php
									wp_nonce_field( 'ut_register_taxonomy', 'ut_register_taxonomy' );
									if ( ! empty( $tax_slug ) ) :
										?>
                                        <input type="hidden" name="taxonomy_slug" value="<?php echo esc_html( $tax_slug ) ?>"/>
                                        <input type="hidden" name="taxonomy_action" value="edit"/>
									<?php
									else:
										?>
                                        <input type="hidden" name="taxonomy_action" value="add"/>
									<?php
									endif;
									?>
									<p class="submit">
										<?php submit_button( 'Save', 'primary', 'ut_submit', false ); ?>
										<span class="spinner"></span>
									</p>
									<?php
									//Create new Taxonomy Back link
									if ( ! empty( $tax_slug ) ) :
										?>
										<a href="<?php echo esc_url ( admin_url( "users.php?page=user-taxonomies" ) ); ?>" class="ut-back-link"><?php esc_html_e( '&larr; create new taxonomy', 'user_taxonomy' ); ?></a>
									<?php
									endif;
									?>
								</form>
							</div>
						</div>
					</div>
					<div id="col-right">
						<?php
						$user_tags_taxonomy_list = new User_Tags_Taxonomy_List();
						$user_tags_taxonomy_list->prepare_items();
						?>
						<form method="post">
							<?php
							wp_nonce_field( 'user_tax_bulk_action', 'user_tax_bulk_action' );
							$user_tags_taxonomy_list->display();
							?>
						</form>
					</div>
				</div>
				<!-- Col Container -->
			</div>
			<?php
		}

		/**
		 * Ajax Callback function to delete user taxonomy
		 *
		 * @return boolean
		 */

		public function delete_taxonomy() {

			if ( empty( $_POST ) || empty( $_POST['nonce'] ) || empty( $_POST['delete_taxonomy'] ) ) {
				return null;
			}

			// Check for adequate permissions
			if ( ! current_user_can( 'edit_users' ) ) :
				return null;
			endif;

			//Validate nonce
			if ( ! wp_verify_nonce( $_POST['nonce'], 'delete-taxonomy-' . $_POST['delete_taxonomy'] ) ) {
				return null;
			}

			$remove_tax = sanitize_key( $_POST['delete_taxonomy'] );

			$user_taxonomies = get_user_taxonomies();
			foreach ( $user_taxonomies as $key => $tax_array ) {
				if ( $remove_tax === get_taxonomy_slug( $tax_array['slug'] ) ) {
					//Delete any associated terms for the taxonomy
					// remove all custom taxonomies
					$terms = get_terms( array( 'taxonomy' => $remove_tax, 'hide_empty' => false ) );
					foreach ( $terms as $term ) {
						wp_delete_term( $term->term_id, $remove_tax );
					}

					unregister_taxonomy( $remove_tax );
					unset( $user_taxonomies[ $key ] );
				}
			}

			$updated = update_option( 'ut_taxonomies', $user_taxonomies );

			if ( $updated ) {
				wp_send_json_success( 'updated' );
			} else {
				wp_send_json_error( 'failed' );
			}
		}

		/**
		 * After registering taxonomies, Update the column name and User count for taxonomies
		 *
		 * @param String $taxonomy - The name of the taxonomy being registered
		 * @param String $object - The object type the taxonomy is for; We only care if this is "user"
		 * @param Array $args - The user supplied + default arguments for registering the taxonomy
		 */
		function registered_taxonomy( $taxonomy, $object, $args ) {
			// Only modify user taxonomies, everything else can stay as is
			if ( 'user' !== $object ) {
				return $taxonomy;
			}

			// Register any hooks/filters that rely on knowing the taxonomy now
			add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'set_user_column' ) );
			add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'set_user_column_values' ), 10, 3 );

		}

		/**
		 * Saves and Updates the Taxonomy List for User
		 */
		public function update_taxonomy_list() {

			if ( ! isset( $_POST['ut_submit'] ) || empty( $_POST['taxonomy_name'] ) || empty( $_POST['ut_register_taxonomy'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['ut_register_taxonomy'], 'ut_register_taxonomy' ) ) {
				wp_die( 'Invalid request' );
			}

			if ( ! current_user_can( 'edit_users' ) ) :
				return;
			endif;

			$name   = sanitize_text_field( $_POST['taxonomy_name'] );
			$action = sanitize_text_field( $_POST['taxonomy_action'] );

			$description = ! empty( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
			$slug        = sanitize_key( wp_unslash( $_POST['taxonomy_slug'] ) );

			// Get all the existing taxonomies.
			$ut_taxonomies = get_user_taxonomies();

			if ( ! is_array( $ut_taxonomies ) && empty( $ut_taxonomies ) ) {
				$ut_taxonomies = array();
			} elseif ( ! is_array( $ut_taxonomies ) ) {
				$ut_taxonomies = array( $ut_taxonomies );
			}

			// Check if taxonomy already created by user.
			$taxonomy_exists = user_taxonomy_exists( $name );

			if ( 'add' === $action && ! $taxonomy_exists ) {
				$ut_taxonomies[] = array(
					'name'        => $name,
					'slug'        => ! empty( $slug ) ? get_taxonomy_slug( $slug ) : get_taxonomy_slug( $name ),
					'description' => $description,
				);
				update_option( 'ut_taxonomies', $ut_taxonomies );

				// a new taxonomy added, so flush rules required.
				update_option( 'ut_new_taxonomy', true );

				add_action( 'admin_notices', array( $this, 'taxonomy_created' ) );
			} elseif ( 'edit' === $action && $taxonomy_exists ) {

				$key = get_user_taxonomy_key( $name );
				// Update Taxonomy
				$ut_taxonomies[ $key ]['name']        = $name;
				$ut_taxonomies[ $key ]['description'] = $description;
				$ut_taxonomies[ $key ]['slug']        = get_taxonomy_slug( $slug );
				update_option( 'ut_taxonomies', $ut_taxonomies );
				add_action( 'admin_notices', array( $this, 'taxonomy_updated' ) );
			} else {
				// Warning
				add_action( 'admin_notices', array( $this, 'taxonomy_exists_notice' ) );
			}
		}

		/**
		 * Get all the Taxonomies from site option 'ut_taxonomies' and register the taxonomies
		 */
		function register_taxonomies() {

			$ut_taxonomies = get_user_taxonomies();
			if ( empty( $ut_taxonomies ) || ! is_array( $ut_taxonomies ) ) {
				return;
			}

			foreach ( $ut_taxonomies as $ut_taxonomy ) {

				if ( empty( $ut_taxonomy['name'] ) ) :
					continue;
				endif;

				$name          = $ut_taxonomy['name'];
				$taxonomy_slug = ! empty( $ut_taxonomy['slug'] ) ? $ut_taxonomy['slug'] : get_taxonomy_slug( $name );

				// make sure taxonomy name is less than 32
				$taxonomy_slug = 32 < strlen( $taxonomy_slug ) ? substr( $taxonomy_slug, 0, 32 ) : $taxonomy_slug;

                $labels = array(
	                'name'                       => $name,
	                'singular_name'              => $name,
	                'menu_name'                  => $name,
	                'search_items'               => 'Search ' . $name,
	                'popular_items'              => 'Popular ' . $name,
	                'all_items'                  => 'All ' . $name,
	                'edit_item'                  => 'Edit ' . $name,
	                'update_item'                => 'Update ' . $name,
	                'add_new_item'               => 'Add New ' . $name,
	                'new_item_name'              => 'New ' . $name,
	                'separate_items_with_commas' => 'Separate ' . $name . ' with commas',
	                'add_or_remove_items'        => 'Add or remove ' . $name,
	                'choose_from_most_used'      => 'Choose from the most popular ' . $name,
	                'topic_count_text'           => 'Choose from the most popular ' . $name,
                );

				$args = array(
					'public'                => true,
					'hierarchical'          => true,
//					'query_var'             => 'user-tax',
					'labels'                => $labels,
					'show_in_rest'          => true,
					'show_in_nav_menus'     => true,
					'capabilities'          => array(
						'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
						'edit_terms'   => 'edit_users',
						'delete_terms' => 'edit_users',
						'assign_terms' => 'edit_users',
					),
					'update_count_callback' => array( $this, 'update_users_count' ),
				);

				/**
				 * Allows to filter User Taxonomy args before registration.
				 */
				$args = apply_filters( 'user_taxonomy_args', $args, $taxonomy_slug );

				register_taxonomy(
					$taxonomy_slug,
					'user',
					$args
				);
			}
			// End of foreach
		}

		function taxonomy_exists_notice() {
			echo '<div class="notice notice-error">' . esc_html__( 'Taxonomy already exists', 'user_taxonomy' ) . '</div>';
		}

		/**
		 * Keep a track of users count for each taxonomy.
		 *
		 * See the _update_post_term_count() function in WordPress for more info.
		 *
		 * @param array $terms List of Term taxonomy IDs
		 * @param object $taxonomy Current taxonomy object of terms
		 */
		public static function update_users_count( $terms, $taxonomy ) {
			global $wpdb;

			foreach ( (array) $terms as $term ) {

				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );

				do_action( 'edit_term_taxonomy', $term, $taxonomy );
				$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
				do_action( 'edited_term_taxonomy', $term, $taxonomy );
			}
		}

		/**
		 * Show admin message for taxonomy creation
		 */
		function taxonomy_created() {
            ?>
			<div id="message" class="notice notice-success below-h2 is-dismissible">
                <p><?php esc_html_e( 'Taxonomy created', 'user_taxonomy' ); ?></p>
            </div>
            <?php
		}

		/**
		 * Updating a taxonomy
		 */
		function taxonomy_updated() {
            ?>
			<div id="message" class="notice notice-success below-h2 is-dismissible">
                <p><?php esc_html_e( 'Taxonomy updated', 'user_taxonomy' ); ?></p>
            </div>
        <?php
		}

		/**
		 *
		 * Correct the column names for user taxonomies
		 * Need to replace "Posts" with "Users"
		 *
		 * @param $columns
		 *
		 * @return array $columns
		 */
		public function set_user_column( $columns ) {
			if ( empty( $columns ) ) {
				return $columns;
			}
			unset( $columns['posts'] );
			$columns['users'] = esc_html__( 'Users', 'user_taxonomy' );

			return $columns;
		}

		/**
		 * Set values for custom columns in user taxonomies
		 *
		 * @param $display
		 * @param $column
		 * @param $term_id
		 *
		 * @return $display
		 */
		public function set_user_column_values( $display, $column, $term_id ) {
			if ( empty( $column ) ) {
				return $display;
			}

			$input_taxonomy = '';

			if ( ! empty( $_GET['taxonomy'] ) ) :
				$input_taxonomy = sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) );
			endif;

			if ( 'users' === $column && ! empty( $_GET['taxonomy'] ) ) {
				$term = get_term( $term_id, $input_taxonomy );

				$count = $term->count;
			} else {
				return $display;
			}
			$count = number_format_i18n( $count );

			$tax = get_taxonomy( $input_taxonomy );

			if ( $tax->query_var ) {
				$args = array( $tax->query_var => $tax->name, $tax->name => $term->slug );
			} else {
				$args = array(
					'user_tax' => $tax->name,
					'term'     => $term->slug,
				);
			}

			return sprintf( '<a href="%1$s">%2$d</a>', esc_url( add_query_arg( $args, 'users.php' ) ), $count );
		}
	}

	User_Tags_Taxonomies::get_instance();
endif;