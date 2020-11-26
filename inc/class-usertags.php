<?php

class UserTags {
	// Store a copy of data for each taxonomy locally.
	private static $taxonomies = array();

	public $settings_page = '';

	/**
	 * Register all the hooks and filters we can in advance
	 * Some will need to be registered later on, as they require knowledge of the taxonomy name
	 */
	public function __construct() {
		add_action( 'wp_ajax_ut_delete_taxonomy', array( $this, 'ut_delete_taxonomy_callback' ) );

		/**
		 * Tag suggestion ajax handler
		 */
		add_action( 'wp_ajax_ut_load_tag_suggestions', array( $this, 'ut_load_tag_suggestions_callback' ) );
		add_action( 'wp_ajax_nopriv_ut_load_tag_suggestions', array( $this, 'ut_load_tag_suggestions_callback' ) );

		// Taxonomies
		add_action( 'admin_enqueue_scripts', array( $this, 'ut_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'ut_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'ut_update_taxonomy_list' ) );
		add_action( 'registered_taxonomy', array( $this, 'ut_registered_taxonomy' ), 10, 3 );

		/**
		 * Register all the available taxonomies
		 */
		$this->ut_register_taxonomies();

		// Menus
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_filter( 'parent_file', array( $this, 'parent_menu' ) );

		// User Profiles
		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );

		add_action( 'personal_options_update', array( $this, 'ut_save_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'ut_save_profile' ) );

		add_filter( 'sanitize_user', array( $this, 'restrict_username' ) );
		add_action( 'wp_head', array( $this, 'admin_ajax' ) );

		// User Query Filter
//		add_filter( 'pre_user_query', array( $this, 'ut_users_filter_query' ) );
//		add_action( 'restrict_manage_users', array( $this, 'ut_users_filter' ) );

		// Clear up related tags and taxonomies, when a user is deleted
		add_action( 'deleted_user', array( $this, 'update_user_list' ) );
	}

	function ut_enqueue_scripts() {

		$js_mtime = filemtime( UT_DIR . '/assets/js/user_taxonomy.js');
		$version = UT_VERSION . $js_mtime;
		wp_register_script( 'user_taxonomy_js', UT_JS_URL . 'user_taxonomy.js', array( 'jquery' ), $version, true );

		$css_mtime = filemtime( UT_DIR . '/assets/css/style.css');
		$version = UT_VERSION . $css_mtime;
		wp_enqueue_style( 'ut-style', UT_CSS_URL . 'style.css', '', $version );

		wp_localize_script( 'user_taxonomy_js', 'wp_ut_ajax_url', admin_url( 'admin-ajax.php' ) );


		wp_enqueue_script( 'user_taxonomy_js' );
	}

	/**
	 * After registered taxonomies, store them in private var
	 * It's fired at the end of the register_taxonomy function
	 *
	 * @param String $taxonomy - The name of the taxonomy being registered
	 * @param String $object - The object type the taxonomy is for; We only care if this is "user"
	 * @param Array $args - The user supplied + default arguments for registering the taxonomy
	 */
	public function ut_registered_taxonomy( $taxonomy, $object, $args ) {
		global $wp_taxonomies;

		// Only modify user taxonomies, everything else can stay as is
		if ( 'user' !== $object ) {
			return;
		}

		// Array => Object
		$args = (object) $args;

		// Register any hooks/filters that rely on knowing the taxonomy now
		add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'set_user_column' ) );
		add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'set_user_column_values' ), 10, 3 );

		// Save changes
		self::$taxonomies[ $taxonomy ] = $args;
	}

	/**
	 * Adds a Taxonomy Sub page to Users menu
	 */
	public function register_page() {
		if ( apply_filters( 'ut_is_admin', is_super_admin() ) ) {
			$this->settings_page = add_users_page(
				esc_html__( 'User Taxonomy', 'user_taxonomy' ), esc_html__( 'Taxonomy', 'user_taxonomy' ), 'read', 'user-taxonomies', array(
					$this,
					'ut_user_taxonomies',
				)
			);
		}
	}

	/**
	 * Displays the New Taxonomy Form and if taxonomy is set in url allows to update
	 * the name and other values for taxonomy
	 */
	public function ut_user_taxonomies() {
		$page_title           = esc_html__( 'Add new Taxonomy', 'user_taxonomy' );
		$taxonomy_description = '';
		$slug                 = '';
		$taxonomy_name        = '';

		if ( ! empty( $_GET['taxonomy'] ) ) {
			$slug = sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) );

			$page_title = 'Edit Taxonomy: ' . $slug;
			$taxonomy   = get_taxonomy( $slug );

			$taxonomy_name = ! empty( $taxonomy ) ? $taxonomy->labels->name : '';
			$ut_taxonomies = get_site_option( 'ut_taxonomies' );

			if ( ! empty( $ut_taxonomies ) ) {
				foreach ( $ut_taxonomies as $ut_taxonomy ) {
					if ( $ut_taxonomy['slug'] == $slug ) {
						$taxonomy_description = ! empty( $ut_taxonomy['description'] ) ? trim( $ut_taxonomy['description'] ) : '';
					}
				}
			}
		} ?>
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
									<input name="taxonomy_name" id="taxonomy_name" type="text" value="<?php echo esc_attr( $taxonomy_name ); ?>" size="40" aria-required="true">
									<p>The name is how it appears on your site.</p>
								</div>
								<?php if ( ! global_terms_enabled() ) : ?>
									<div class="form-field term-slug-wrap">
										<label for="taxonomy-slug"><?php esc_html_e( 'Taxonomy Slug', 'user_taxonomy' ); ?></label>
										<input name="taxonomy_slug" id="taxonomy-slug" type="text" value="<?php echo esc_attr( $slug ); ?>" size="40"/>
										<p><?php esc_html_e( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' ); ?></p>
									</div>
								<?php endif; // global_terms_enabled() ?>
								<div class="form-field term-description-wrap">
									<label for="description"><?php esc_html_e( 'Description' ); ?></label>
									<textarea name="description" id="description" rows="5" cols="40"><?php echo esc_html( $taxonomy_description ); ?></textarea>
									<p><?php esc_html_e( 'The description is not prominent by default; however, some themes may show it.' ); ?></p>
								</div>
								<?php
								wp_nonce_field( 'ut_register_taxonomy', 'ut_register_taxonomy' );
								echo ! empty( $slug ) ? '<input type="hidden" name="taxonomy_slug" value="' . esc_html( $slug ) . '"/>' : '';
								?>
								<p class="submit">
									<?php submit_button( 'Save', 'primary', 'submit', false ); ?>
									<span class="spinner"></span>
								</p>
								<?php
								if ( ! empty( $slug ) ) {
									?>
									<a href="users.php?page=user-taxonomies"
									   class="ut-back-link"><?php esc_html_e( '&larr; create new taxonomy', 'user_taxonomy' ); ?></a>
									<?php
								}
								?>
							</form>
						</div>
					</div>
				</div>
				<div id="col-right">
					<?php
					$uttaxonomylisttable = new User_Tags_List();
					$uttaxonomylisttable->prepare_items();
					?>
					<form method="post">
						<?php
						wp_nonce_field( 'taxonomy_bulk_action', 'taxonomy_bulk_action' );
						$uttaxonomylisttable->display();
						?>
					</form>
				</div>
			</div>
			<!-- Col Container -->
		</div>
		<?php
	}

	/**
	 * Saves and Updates the Taxonomy List for User
	 */
	function ut_update_taxonomy_list() {
		if ( empty( $_POST['taxonomy_name'] ) || empty( $_POST['ut_register_taxonomy'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['ut_register_taxonomy'], 'ut_register_taxonomy' ) ) {
			wp_die( 'Invalid request' );
		}

		$name = sanitize_text_field( wp_unslash( $_POST['taxonomy_name'] ) );

		$description = !empty( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$slug        = sanitize_key( wp_unslash( $_POST['taxonomy_slug'] ) );

		// Get all the existing taxonomies.
		$ut_taxonomies = get_site_option( 'ut_taxonomies' );

		if ( ! is_array( $ut_taxonomies ) && empty( $ut_taxonomies ) ) {
			$ut_taxonomies = array();
		} elseif ( ! is_array( $ut_taxonomies ) ) {
			$ut_taxonomies = array( $ut_taxonomies );
		}

		// Check if taxonomy already created by user.
		$taxonomy_exists = false;
		foreach ( $ut_taxonomies as $ut_taxonomy_key => $ut_taxonomy ) {
			if ( empty( $slug ) && ( $name === $ut_taxonomy['name'] || ut_taxonomy_name( $name ) === $ut_taxonomy['slug'] ) ) {
				$taxonomy_exists = true;
				break;
			} elseif ( ! empty( $slug ) && $slug == $ut_taxonomy['slug'] ) {
				$taxonomy_exists = true;
				$taxonomy_key    = $ut_taxonomy_key;
				break;
			}
		}
		if ( ! $taxonomy_exists ) {
			$ut_taxonomies[] = array(
				'name'        => $name,
				'slug'        => ! empty( $slug ) ? ut_taxonomy_name( $slug ) : ut_taxonomy_name( $name ),
				'description' => $description,
			);
			update_site_option( 'ut_taxonomies', $ut_taxonomies );
			// a new taxonomy added, so flush rules required.
			update_site_option( 'ut_new_taxonomy', true );

			add_action( 'admin_notices', 'ut_taxonomy_created' );
		} elseif ( $taxonomy_exists && ! empty( $slug ) ) {
			// Update Taxonomy
			$ut_taxonomies[ $taxonomy_key ]['name']        = $name;
			$ut_taxonomies[ $taxonomy_key ]['description'] = $description;
			update_site_option( 'ut_taxonomies', $ut_taxonomies );
			add_action( 'admin_notices', 'ut_taxonomy_updated' );
		} else {
			// Warning
			add_action( 'admin_notices', array( $this, 'taxonomy_exists_notice' ) );
		}
	}

	function taxonomy_exists_notice() {
		echo '<div class="error">' . esc_html__( 'Taxonomy already exists', 'user_taxonomy' ) . '</div>';
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
	 * Get all the Taxonomies from site option 'ut_taxonomies' and register the taxonomies
	 */
	function ut_register_taxonomies() {
		$ut_taxonomies = get_site_option( 'ut_taxonomies' );
		$errors        = array();
		if ( empty( $ut_taxonomies ) || ! is_array( $ut_taxonomies ) ) {
			return;
		}
		foreach ( $ut_taxonomies as $ut_taxonomy ) {

			//@todo: Test and remove extract, based on data stored in DB.
			extract( $ut_taxonomy );

			$name = $ut_taxonomy['name'];
			$slug = $ut_taxonomy['slug'];

			$taxonomy_slug = ! empty( $slug ) ? $slug : ut_taxonomy_name( $name );

			// make sure taxonomy name is less than 32
			$taxonomy_slug = 32 < strlen( $taxonomy_slug ) ? substr( $taxonomy_slug, 0, 32 ) : $taxonomy_slug;

			$args = array(
				'public'                => true,
				'hierarchical'          => false,
				'labels'                => array(
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
				),
				'show_in_rest'          => false,
				'rewrite'               => array(
					'slug' => get_url_prefix() . $taxonomy_slug,
				),
				'capabilities'          => array(
					'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
					'edit_terms'   => 'edit_users',
					'delete_terms' => 'edit_users',
					'assign_terms' => 'read',
				),
				'update_count_callback' => array( $this, 'update_users_count' ),
			);

			$registered = register_taxonomy(
				$taxonomy_slug,
				'user',
				$args
			);

			if ( is_wp_error( $registered ) ) {
				$errors[] = $registered;
			}
		}
		// End of foreach
	}

	/**
	 * Set Users menu as parent for User Taxonomy edit page.
	 *
	 * @param string $parent
	 *
	 * @return string
	 */
	function parent_menu( $parent = '' ) {
		global $pagenow;

		// If we're editing one of the user taxonomies
		// We must be within the users menu, so highlight that
		if ( ! empty( $_GET['taxonomy'] ) && 'edit-tags.php' === $pagenow && isset( self::$taxonomies[ sanitize_key( $_GET['taxonomy'] ) ] ) ) {
			$parent = 'users.php';
		}

		return $parent;
	}

	/**
	 * Correct the column names for user taxonomies
	 * Need to replace "Posts" with "Users"
	 *
	 * @param $columns
	 */
	public function set_user_column( $columns ) {
		if ( empty( $columns ) ) {
			return;
		}
		unset( $columns['posts'] );
		$columns['users'] = esc_html__( 'Users' );

		return $columns;
	}

	/**
	 * Set values for custom columns in user taxonomies
	 *
	 * @param $display
	 * @param $column
	 * @param $term_id
	 *
	 * @return mixed|string|void
	 */
	public function set_user_column_values( $display, $column, $term_id ) {
		if ( empty( $column ) ) {
			return;
		}

		$input_taxonomy = '';

		if ( ! empty( $_GET['taxonomy'] ) ) :
			$input_taxonomy = sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) );
		endif;

		if ( 'users' === $column && ! empty( $_GET['taxonomy'] ) ) {
			$term = get_term( $term_id, $input_taxonomy );

			$count = $term->count;
		} else {
			return;
		}
		$count = number_format_i18n( $count );

		$tax = get_taxonomy( $input_taxonomy );

		if ( $tax->query_var ) {
			$args = array( $tax->query_var => $term->slug );
		} else {
			$args = array(
				'taxonomy' => $tax->name,
				'term'     => $term->slug,
			);
		}

		return sprintf( '<a href="%1$s">%2$d</a>', esc_url( add_query_arg( $args, 'users.php' ) ), $count );
	}

	/**
	 * Add the taxonomies to the user view/edit screen
	 *
	 * @param Object $user - The user of the view/edit screen
	 */
	public function user_profile( $user ) {
		?>
		<h3>User Tags</h3>
		<div class="user-taxonomy-wrapper">
			<?php
			wp_nonce_field( 'user-tags', 'user-tags' );
			foreach ( self::$taxonomies as $key => $taxonomy ) { // Check the current user can assign terms for this taxonomy
				if ( ! current_user_can( $taxonomy->cap->assign_terms ) ) {
					continue;
				}

				// Get all the terms in this taxonomy
				$terms     = wp_get_object_terms( $user->ID, $taxonomy->name );
				$num       = 0;
				$html      = '';
				$user_tags = array();

				$choose_from_text = apply_filters( 'ut_tag_cloud_heading', $taxonomy->labels->choose_from_most_used, $taxonomy );
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$user_tags[] = $term->name;
						$term_url    = site_url() . '/' . $taxonomy->rewrite['slug'] . '/' . $term->slug;
						$html        .= '<div class="tag-hldr">';
						$html        .= sprintf( '<a href="%s" class="term-link">%s</a><span><a id="user_tag-' . $taxonomy->name . '-' . $num . '" class="ntdelbutton">&#10005;</a></span>', esc_url( $term_url ), $term->name );
						$html        .= '</div>';
						$num ++;
					}
					$user_tags = implode( ',', $user_tags );
				}

				?>
				<table class="form-table user-profile-taxonomy">
					<tr>
						<th>
							<label for="new-tag-user_tag_<?php echo esc_attr( $taxonomy->name ); ?>">
								<?php echo esc_html( $taxonomy->labels->singular_name ); ?>
							</label>
						</th>
						<td class="ajaxtag">
							<input type="text" id="new-tag-user_tag_<?php echo esc_attr( $taxonomy->name ); ?>" name="newtag[user_tag]"
							       class="newtag form-input-tip float-left hide-on-blur" size="16" autocomplete="off" value="">
							<input type="button" class="button tagadd float-left" value="Add">

							<p class="howto"><?php esc_html_e( 'Separate tags with commas', 'user_taxonomy' ); ?></p>

							<div class="tagchecklist"><?php echo $html; ?></div>
							<input type="hidden" name="user-tags[<?php echo esc_attr( $taxonomy->name ); ?>]"
							       id="user-tags-<?php echo esc_attr( $taxonomy->name ); ?>" value="<?php echo ! empty( $user_tags ) ? esc_html( $user_tags ) : ''; ?>"/>
							<!--Display Tag cloud for most used terms-->
							<p class="hide-if-no-js tagcloud-container">
								<a href="#titlediv" class="tagcloud-link user-taxonomy"
								   id="link-<?php echo esc_attr( $taxonomy->name ); ?>"><?php echo esc_html( $choose_from_text ); ?></a>
							</p>
						</td>
					</tr>
				</table>
				<?php
			} // Taxonomies
			?>
		</div>
		<?php
	}

	/**
	 * Save the custom user taxonomies when saving a users profile
	 *
	 * @param Integer $user_id - The ID of the user to update
	 *
	 * @return bool|void
	 */
	public function ut_save_profile( $user_id ) {
		if ( empty( $_POST['user-tags'] ) ) {
			return;
		}
		$input_tags = wp_unslash( $_POST['user-tags'] );
		foreach ( $input_tags as $taxonomy => $taxonomy_terms ) {
			// Check the current user can edit this user and assign terms for this taxonomy
			if ( ! current_user_can( 'edit_user', $user_id ) && current_user_can( $taxonomy->cap->assign_terms ) ) {
				return false;
			}

			// Save the data
			if ( ! empty( $taxonomy_terms ) ) {
				$taxonomy_terms = array_map( 'trim', explode( ',', $taxonomy_terms ) );
				wp_set_object_terms( $user_id, $taxonomy_terms, $taxonomy, false );
			} else {
				// No terms left, delete all terms
				wp_set_object_terms( $user_id, array(), $taxonomy, false );
			}
		}
	}

	/**
	 * Usernames can't match any of our user taxonomies
	 * As otherwise it will cause a URL conflict
	 * This method prevents that happening
	 *
	 * @param $username
	 *
	 * @return string
	 */
	public function restrict_username( $username ) {
		if ( isset( self::$taxonomies[ $username ] ) ) {
			return '';
		}

		return $username;
	}

	/**
	 * Ajax Callback function to delete a taxonomy
	 *
	 * @return boolean
	 */

	function ut_delete_taxonomy_callback() {

		if ( empty( $_POST ) || empty( $_POST['nonce'] ) || empty( $_POST['delete_taxonomy'] ) ) {
			return false;
		}

		$nonce   = sanitize_key( $_POST['nonce'] );
		$taxnomy = sanitize_key( $_POST['delete_taxonomy'] );
		if ( ! wp_verify_nonce( $nonce, 'delete-taxonomy-' . $taxnomy ) ) {
			return false;
		}
		$ut_taxonomies = get_site_option( 'ut_taxonomies' );
		foreach ( $ut_taxonomies as $ut_taxonomy_key => $ut_taxonomy_array ) {
			if ( ut_stripallslashes( $ut_taxonomy_array['slug'] ) == ut_stripallslashes( $taxnomy ) ) {
				unset( $ut_taxonomies[ $ut_taxonomy_key ] );
			}
		}
		$updated = update_site_option( 'ut_taxonomies', $ut_taxonomies );

		if ( $updated ) {
			wp_send_json_success( 'updated' );
		} else {
			wp_send_json_error( 'failed' );
		}
	}

	/**
	 * Loads Tag Suggestions
	 *
	 * @return boolean
	 */
	function ut_load_tag_suggestions_callback() {
		if ( empty( $_POST ) || empty( $_POST['nonce'] ) || empty( $_POST['q'] ) || empty( $_POST['taxonomy'] ) ) {
			wp_send_json_error( array( 'error' => 'Invalid request.' ) );
		}

		$nonce    = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
		$taxonomy = sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) );
		$q        = sanitize_text_field( wp_unslash( $_POST['q'] ) );

		if ( ! wp_verify_nonce( $nonce, 'user-tags' ) ) {
			wp_send_json_error( array( 'error' => 'Couldn\'t validate the request.' ) );
		}

		$tags = get_terms(
			$taxonomy,
			array(
				'orderby'    => 'count',
				'hide_empty' => 0,
			)
		);
		if ( empty( $tags ) || ! is_array( $tags ) ) {
			wp_send_json_error();
		}
		$tag_list = array();
		foreach ( $tags as $tag ) {
			$tag_list[] = $tag->name;
		}

		// Matching Tags
		$input  = preg_quote( trim( $q ), '~' );
		$result = preg_grep( '~' . $input . '~i', $tag_list );
		if ( empty( $result ) ) {
			wp_send_json_error();
		}
		ob_start();
		?>
		<ul class="tag-suggestion float-left hide-on-blur">
			<?php
			foreach ( $result as $r ) {
				?>
				<li><?php echo esc_html( $r ); ?></li>
				<?php
			}
			?>
		</ul>
		<?php
		$sugestion = ob_get_clean();

		wp_send_json_success( array( $sugestion ) );
		exit();
	}

	/**
	 * Admin ajax URL
	 */
	function admin_ajax() {
		?>
		<script type="text/javascript">
			let ajaxurl = "<?php echo json_encode( admin_url( 'admin-ajax.php' ) ); ?>";
		</script>
		<?php
	}

	/**
	 * Filters the user query to show list of users for a particular tag or taxonomy
	 *
	 * @author Garrett Eclipse
	 */
	function ut_users_filter_query( $query ) {
		global $wpdb, $pagenow;

		if ( ! is_admin() || 'users.php' !== $pagenow ) {
			return $query;
		}

		if ( isset( $_GET['taxonomy'] ) && ! empty( $_GET['taxonomy'] ) && isset( $_GET['term'] ) && ! empty( $_GET['term'] ) ) {
			$term_slug = sanitize_text_field( $_GET['term'] );
		} else {
			$ut_taxonomies = get_site_option( 'ut_taxonomies' );
			if ( ! empty( $ut_taxonomies ) && is_array( $ut_taxonomies ) ) {
				foreach ( $ut_taxonomies as $ut_taxonomy ) {
					extract( $ut_taxonomy );
					$taxonomy_slug = ! empty( $slug ) ? $slug : ut_taxonomy_name( $name );
					$taxonomy_slug = strlen( $taxonomy_slug ) > 32 ? substr( $taxonomy_slug, 0, 32 ) : $taxonomy_slug;
					$taxonomy      = get_taxonomy( $taxonomy_slug );
					if ( $taxonomy && isset( $_GET[ $taxonomy_slug ] ) && ! empty( $_GET[ $taxonomy_slug ] ) ) {
						$term_slug = sanitize_text_field( $_GET[ $taxonomy_slug ] );
						continue;
					}
				}
			}
		}

		if ( ! empty( $term_slug ) ) {
			$query->query_from  .= " INNER JOIN {$wpdb->term_relationships} ON {$wpdb->users}.`ID` = {$wpdb->term_relationships}.`object_id` INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.`term_taxonomy_id` = {$wpdb->term_taxonomy}.`term_taxonomy_id` INNER JOIN {$wpdb->terms} ON {$wpdb->terms}.`term_id` = {$wpdb->term_taxonomy}.`term_id`";
			$query->query_where .= " AND {$wpdb->terms}.`slug` = '{$term_slug}'";
		}

		return $query;

	}

	/**
	 * Adds a dropdown for each taxonomy and used tags to allow filtering of users list
	 *
	 * @author Garrett Eclipse
	 */
	function ut_users_filter() {
		// Show All the taxonomies in single drop down
		$ut_taxonomies = get_site_option( 'ut_taxonomies' );
		if ( empty( $ut_taxonomies ) || ! is_array( $ut_taxonomies ) ) {
			return;
		}
		?>
		<select name="ut-taxonomy-filter" id="ut-taxonomy-filter">
			<option value=""><?php esc_html_e( 'Filter by Taxonomy:', 'user_taxonomy' ); ?></option>
			<?php
			foreach ( $ut_taxonomies as $ut_taxonomy ) {
				$taxonomy_slug = ! empty( $ut_taxonomy['slug'] ) ? $ut_taxonomy['slug'] : ut_taxonomy_name( $ut_taxonomy['name'] );
				$taxonomy_slug = strlen( $taxonomy_slug ) > 32 ? substr( $taxonomy_slug, 0, 32 ) : $taxonomy_slug;
				$taxonomy      = get_taxonomy( $taxonomy_slug );
				if ( $taxonomy ) {
					?>
					<option value='<?php echo esc_attr( $taxonomy_slug ); ?>'><?php echo esc_html( $ut_taxonomy['name'] ); ?></option>
					<?php
				}
			}
			?>
		</select>
		<!-- Secondary dropdown to load terms in the taxonomy-->
		<select id="ut-taxonomy-term-filter" name="ut-taxonomy-term-filter">
			<option value=""><?php esc_html_e( 'Select a taxonomy first', 'user_taxonomy' ); ?></option>
		</select>
		<?php
		submit_button( esc_html__( 'Filter', 'user_taxonomy' ), 'secondary', 'ut-filter-users', false );

		wp_nonce_field( 'ut-filter-users', 'ut-filter-users-nonce' );

		?>
		<a class="ut-reset-filters button-primary" href="users.php" title="Reset User Filters">Reset Filters</a>
		<?php
	}

	/**
	 * Updates the users list for a tag or a taxonomy, when a user is deleted
	 *
	 * @param $user_id
	 */
	function update_user_list( $user_id ) {

		$taxonomies    = get_object_taxonomies( 'user', 'object' );
		$taxonomy_list = array();
		foreach ( $taxonomies as $key => $taxonomy ) {
			$taxonomy_list[] = $key;
		}
		// Delete the relation for a user
		if ( ! empty( $taxonomy_list ) && is_array( $taxonomy_list ) ) {
			wp_delete_object_term_relationships( $user_id, $taxonomy_list );
		}
	}

}
