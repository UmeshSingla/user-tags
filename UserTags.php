<?php
/**
 * Plugin Name: User Tags for Wordpress
 * Author: Umesh Kumar<umeshsingla05@gmail.com>
 * Author URI:    http://codechutney.com
 * Description:    Adds User Taxonomy functionality
 * Version: 1.2.6
 * Reference :  http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress
 * Text Domain : user_taxonomy
 */
define( 'WP_UT_TRANSLATION_DOMAIN', 'user_taxonomy' );
define( 'WP_UT_URL', plugins_url( '', __FILE__ ) );
define( 'WP_UT_PLUGIN_FOLDER', dirname( __FILE__ ) );
define( 'WP_UT_TEMPLATES', trailingslashit( WP_UT_PLUGIN_FOLDER ) . trailingslashit( 'templates' ) );

/* Define all necessary variables first */
define( 'WP_UT_CSS', WP_UT_URL . "/assets/css/" );
define( 'WP_UT_JS', WP_UT_URL . "/assets/js/" );
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Includes PHP files located in 'lib' folder
foreach ( glob( dirname( __FILE__ ) . "/lib/*.php" ) as $lib_filename ) {
	require_once( $lib_filename );
}

class UserTags {
	private static $taxonomies = array();

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
		if ( ! empty( $_POST['taxonomy_name'] ) ) {
			$this->ut_update_taxonomy_list();
		}
		add_action( 'registered_taxonomy', array( $this, 'ut_registered_taxonomy' ), 10, 3 );

		/**
		 * Register all the available taxonomies
		 */
		$this->ut_register_taxonomies();

		// Menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'parent_file', array( $this, 'parent_menu' ) );

		// User Profiles
		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'ut_save_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'ut_save_profile' ) );
		add_filter( 'sanitize_user', array( $this, 'restrict_username' ) );
		add_action( 'wp_head', array( $this, 'admin_ajax' ) );
	}

	function ut_enqueue_scripts( $hook ) {
		wp_enqueue_style( 'ut-style', WP_UT_CSS . 'style.css' );
		wp_register_script( 'user_taxonomy_js', WP_UT_JS . 'user_taxonomy.js', array( 'jquery' ), false, true );
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
		if ( $object != 'user' ) {
			return;
		}

		// Array => Object
		$args = (object) $args;

		// Register any hooks/filters that rely on knowing the taxonomy now
		add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'set_user_column' ) );
		add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'set_user_column_values' ), 10, 3 );

		// Save changes
		$wp_taxonomies[ $taxonomy ]    = $args;
		self::$taxonomies[ $taxonomy ] = $args;
	}

	/**
	 *Adds a Taxonomy Sub page to Users menu
	 */
	public function admin_menu() {
		global $users_taxonomy;
		if ( is_super_admin() ) {
			$users_taxonomy = add_users_page( __( 'User Taxonomies', WP_UT_TRANSLATION_DOMAIN ), __( 'Taxonomies', WP_UT_TRANSLATION_DOMAIN ), 'read', 'user-taxonomies', array(
				$this,
				"ut_user_taxonomies"
			) );
		}
	}

	/**
	 * Displays the New Taxonomy Form and if taxonomy is set in url allows to update
	 * the name and other values for taxonomy
	 */
	public function ut_user_taxonomies() {
		$page_title    = 'Add New Taxonomy';
		$taxonomy_name = $taxonomy_description = '';
		if ( ! empty( $_GET['taxonomy'] ) ) {
			$slug          = $_GET['taxonomy'];
			$page_title    = 'Edit Taxonomy: ' . $slug;
			$taxonomy      = get_taxonomy( $slug );
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
			<h2><?php _e( 'User Taxonomies', WP_UT_TRANSLATION_DOMAIN ); ?></h2>

			<div id="col-container">
				<div id="col-right"><?php
					$uttaxonomylisttable = new User_Tags_List();
					$uttaxonomylisttable->prepare_items(); ?>
					<form method="post"> <?php
						wp_nonce_field( 'taxonomy_bulk_action', 'taxonomy_bulk_action' );
						$uttaxonomylisttable->display(); ?>
					</form>
				</div>
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<h3><?php _e( $page_title, WP_UT_TRANSLATION_DOMAIN ); ?></h3>

							<form name="editusertaxonomy" id="editusertaxonomy" method="post" action="" class="validate">
								<table class="form-table">
									<tr class="form-field form-required">
										<th scope="row" valign="top">
											<label for="taxonomy_name"><?php _ex( 'Name', 'Taxonomy Name' ); ?></label>
										</th>
										<td>
											<input name="taxonomy_name" id="taxonomy_name" type="text" value="<?php echo $taxonomy_name; ?>" size="40" data-required="true" maxlength="24"/>
										</td>
									</tr>
									<tr class="form-field">
										<th scope="row" valign="top">
											<label for="description"><?php _ex( 'Description', 'Taxonomy Description' ); ?></label>
										</th>
										<td>
											<textarea name="description" id="description" rows="5" cols="50" class="large-text"><?php echo $taxonomy_description; ?></textarea>
										</td>
									</tr> <?php
									wp_nonce_field( 'ut_register_taxonomy', 'ut_register_taxonomy' );
									echo ! empty( $slug ) ? '<input type="hidden" name="taxonomy_slug" value="' . $slug . '"/>' : ''; ?>
								</table>
								<?php submit_button( __( 'Save' ) );
								if ( ! empty( $slug ) ) {
									?>
									<a href="users.php?page=user-taxonomies" class="ut-back-link"><?php _e( '&larr; create new taxonomy', WP_UT_TRANSLATION_DOMAIN ); ?></a>
								<?php } ?>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- Col Container -->
		</div> <?php
	}

	/**
	 * Saves and Updates the Taxonomy List for User
	 */
	function ut_update_taxonomy_list() {
		if ( empty( $_POST ) ) {
			return;
		}
		$taxonomy_description = $taxonomy_key = '';
		extract( $_POST );
		$nonce_verified = ! empty( $ut_register_taxonomy ) ? wp_verify_nonce( $ut_register_taxonomy, 'ut_register_taxonomy' ) : false;
		if ( ! $nonce_verified ) {
			wp_die( 'Invalid request' );
		}
		$ut_taxonomies = get_site_option( 'ut_taxonomies' );
		if ( ! is_array( $ut_taxonomies ) && empty( $ut_taxonomies ) ) {
			$ut_taxonomies = array();
		} elseif ( ! is_array( $ut_taxonomies ) ) {
			$ut_taxonomies = array( $ut_taxonomies );
		}
		$taxonomy_exists = false;
		foreach ( $ut_taxonomies as $ut_taxonomy_key => $ut_taxonomy ) {
			if ( empty( $taxonomy_slug ) && ( $ut_taxonomy['name'] == $taxonomy_name || $ut_taxonomy['slug'] == ut_taxonomy_name( $taxonomy_name ) ) ) {
				$taxonomy_exists = true;
				break;
			} elseif ( ! empty( $taxonomy_slug ) && $taxonomy_slug == $ut_taxonomy['slug'] ) {
				$taxonomy_exists = true;
				$taxonomy_key    = $ut_taxonomy_key;
				break;
			}
		}
		if ( ! $taxonomy_exists ) {
			$ut_taxonomies[]      = array(
				'name'        => $taxonomy_name,
				'slug'        => ut_taxonomy_name( $taxonomy_name ),
				'description' => $taxonomy_description
			);
			$taxonomy_site_option = update_site_option( 'ut_taxonomies', $ut_taxonomies );
			//a new taxonomy added, so flush rules required
			update_site_option( 'ut_new_taxonomy', true );

			add_action( 'admin_notices', 'ut_taxonomy_created' );
		} elseif ( $taxonomy_exists && ! empty( $taxonomy_slug ) ) {
			//Update Taxonomy
			$ut_taxonomies[ $taxonomy_key ]['name']        = $taxonomy_name;
			$ut_taxonomies[ $taxonomy_key ]['description'] = $taxonomy_description;
			update_site_option( 'ut_taxonomies', $ut_taxonomies );
			add_action( 'admin_notices', 'ut_taxonomy_updated' );
		} else {
			//Warning
			add_action( 'admin_notices', array( $this, 'taxonomy_exists_notice' ) );
		}
	}

	function taxonomy_exists_notice() {
		echo '<div class="error">' . __( 'Taxonomy already exists', WP_UT_TRANSLATION_DOMAIN ) . '</div>';
	}

	/**
	 * Function for updating the 'profession' taxonomy count.  What this does is update the count of a specific term
	 * by the number of users that have been given the term.  We're not doing any checks for users specifically here.
	 * We're just updating the count with no specifics for simplicity.
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
	 *
	 */
	function ut_register_taxonomies() {
		$ut_taxonomies = get_site_option( 'ut_taxonomies' );
		$errors        = array();
		if ( empty( $ut_taxonomies ) || ! is_array( $ut_taxonomies ) ) {
			return;
		}
		foreach ( $ut_taxonomies as $ut_taxonomy ) {
			global $user_tags;
			extract( $ut_taxonomy );
			$taxonomy_slug = ! empty( $slug ) ? $slug : ut_taxonomy_name( $name );
			//make sure taxonomy name is less than 32
			$taxonomy_slug = strlen( $taxonomy_slug ) > 32 ? substr( $taxonomy_slug, 0, 32 ) : $taxonomy_slug;
			$registered    = register_taxonomy(
				$taxonomy_slug,
				'user',
				array(
					'public'                => true,
					'hierarchical'          => false,
					'labels'                => array(
						'name'                       => __( $name ),
						'singular_name'              => __( $name ),
						'menu_name'                  => __( $name ),
						'search_items'               => __( 'Search ' . $name ),
						'popular_items'              => __( 'Popular ' . $name ),
						'all_items'                  => __( 'All ' . $name ),
						'edit_item'                  => __( 'Edit ' . $name ),
						'update_item'                => __( 'Update ' . $name ),
						'add_new_item'               => __( 'Add New ' . $name ),
						'new_item_name'              => __( 'New ' . $name ),
						'separate_items_with_commas' => __( 'Separate ' . $name . ' with commas' ),
						'add_or_remove_items'        => __( 'Add or remove ' . $name ),
						'choose_from_most_used'      => __( 'Choose from the most popular ' . $name ),
						'topic_count_text'           => __( 'Choose from the most popular ' . $name ),
					),
					'rewrite'               => array(
						'with_front' => true,
						'slug'       => 'tag/' . $taxonomy_slug // Use 'author' (default WP user slug).
					),
					'capabilities'          => array(
						'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
						'edit_terms'   => 'edit_users',
						'delete_terms' => 'edit_users',
						'assign_terms' => 'read',
					),
					'update_count_callback' => array( 'UserTags', 'update_users_count' )
				)
			);
			if ( is_wp_error( $registered ) ) {
				$errors[] = $registered;
			}
		}
		//End of foreach
	}

	/**
	 * Highlight User Menu item
	 */
	function parent_menu( $parent = '' ) {
		global $pagenow;

		// If we're editing one of the user taxonomies
		// We must be within the users menu, so highlight that
		if ( ! empty( $_GET['taxonomy'] ) && $pagenow == 'edit-tags.php' && isset( self::$taxonomies[ $_GET['taxonomy'] ] ) ) {
			$parent = 'users.php';
		}

		return $parent;
	}

	/**
	 * Correct the column names for user taxonomies
	 * Need to replace "Posts" with "Users"
	 */
	public function set_user_column( $columns ) {
		if ( empty( $columns ) ) {
			return;
		}
		unset( $columns['posts'] );
		$columns['users'] = __( 'Users' );

		return $columns;
	}

	/**
	 * Set values for custom columns in user taxonomies
	 */
	public function set_user_column_values( $display, $column, $term_id ) {
		if ( empty( $column ) ) {
			return;
		}
		if ( 'users' === $column && ! empty( $_GET['taxonomy'] ) ) {
			$term = get_term( $term_id, $_GET['taxonomy'] );

			$count = $term->count;
		} else {
			return;
		}
		$count = number_format_i18n( $count );

		$tax = get_taxonomy( $_GET['taxonomy'] );

		if ( $tax->query_var ) {
			$args = array( $tax->query_var => $term->slug );
		} else {
			$args = array( 'taxonomy' => $tax->name, 'term' => $term->slug );
		}

		return $count;
//		return "<a href='" . esc_url( add_query_arg( $args, 'users.php' ) ) . "'>$count</a>";
	}

	/**
	 * Add the taxonomies to the user view/edit screen
	 *
	 * @param Object $user - The user of the view/edit screen
	 */
	public function user_profile( $user ) {
		wp_nonce_field( 'user-tags', 'user-tags' ); ?>
		<div class="user-taxonomy-wrapper"><?php
		foreach ( self::$taxonomies as $key => $taxonomy ): // Check the current user can assign terms for this taxonomy
		{
			if ( ! current_user_can( $taxonomy->cap->assign_terms ) ) {
				continue;
			}

			// Get all the terms in this taxonomy

			$terms            = wp_get_object_terms( $user->ID, $taxonomy->name );
			$num              = 0;
			$html             = '';
			$user_tags        = '';
			$choose_from_text = apply_filters( 'ut_tag_cloud_heading', $taxonomy->labels->choose_from_most_used, $taxonomy );
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$user_tags[] = $term->name;
					$term_url    = site_url() . '/' . $taxonomy->rewrite['slug'] . '/' . $term->slug;
					$html .= "<div class='tag-hldr'>";
					$html .= '<span><a id="user_tag-' . $taxonomy->name . '-' . $num . '" class="ntdelbutton">x</a></span>&nbsp;<a href="' . $term_url . '" class="term-link">' . $term->name . '</a>';
					$html .= "</div>";
					$num ++;
				}
				$user_tags = implode( ',', $user_tags );
			} ?>
			<table class="form-table user-profile-taxonomy">
			<tr>
				<th>
					<label for="new-tag-user_tag_<?php echo $taxonomy->name; ?>"><?php _e( "{$taxonomy->labels->singular_name}" ) ?></label>
				</th>
				<td class="ajaxtag">
					<input type="text" id="new-tag-user_tag_<?php echo $taxonomy->name; ?>" name="newtag[user_tag]" class="newtag form-input-tip float-left hide-on-blur" size="16" autocomplete="off" value="">
					<input type="button" class="button tagadd float-left" value="Add">

					<p class="howto"><?php _e( 'Separate tags with commas', WP_UT_TRANSLATION_DOMAIN ); ?></p>

					<div class="tagchecklist"><?php echo $html; ?></div>
					<input type="hidden" name="user-tags[<?php echo $taxonomy->name; ?>]" id="user-tags-<?php echo $taxonomy->name; ?>" value="<?php echo $user_tags; ?>"/>
					<!--Display Tag cloud for most used terms-->
					<p class="hide-if-no-js tagcloud-container">
						<a href="#titlediv" class="tagcloud-link user-taxonomy" id="link-<?php echo $taxonomy->name; ?>"><?php echo $choose_from_text; ?></a>
					</p>
				</td>
			</tr>
			</table><?php
		}
		endforeach; // Taxonomies
		?>
		</div><?php
	}

	/**
	 * Save the custom user taxonomies when saving a users profile
	 *
	 * @param Integer $user_id - The ID of the user to update
	 */
	public function ut_save_profile( $user_id ) {
		if ( empty( $_POST['user-tags'] ) ) {
			return;
		}
		foreach ( $_POST['user-tags'] as $taxonomy => $taxonomy_terms ) {
			// Check the current user can edit this user and assign terms for this taxonomy
			if ( ! current_user_can( 'edit_user', $user_id ) && current_user_can( $taxonomy->cap->assign_terms ) ) {
				return false;
			}

			// Save the data
			if ( ! empty( $taxonomy_terms ) ) {
				$taxonomy_terms = array_map( 'trim', explode( ',', $taxonomy_terms ) );
				$updated        = wp_set_object_terms( $user_id, $taxonomy_terms, $taxonomy, false );
			} else {
				//No terms left, delete all terms
				$updated = wp_set_object_terms( $user_id, array(), $taxonomy, false );
			}
		}
	}

	/**
	 * Usernames can't match any of our user taxonomies
	 * As otherwise it will cause a URL conflict
	 * This method prevents that happening
	 */
	public function restrict_username( $username ) {
		if ( isset( self::$taxonomies[ $username ] ) ) {
			return '';
		}

		return $username;
	}

	/**
	 * Ajax Callback function to delete a taxonomy
	 * @return boolean
	 */

	function ut_delete_taxonomy_callback() {
		if ( empty( $_POST ) || empty( $_POST['nonce'] ) || empty( $_POST['delete_taxonomy'] ) ) {
			return false;
		}
		extract( $_POST );
		$taxonomy_slug = ut_taxonomy_name( $delete_taxonomy );
		if ( ! wp_verify_nonce( $nonce, 'delete-taxonomy-' . $taxonomy_slug ) ) {
			return false;
		}
		$ut_taxonomies = get_site_option( 'ut_taxonomies' );
		foreach ( $ut_taxonomies as $ut_taxonomy_key => $ut_taxonomy_array ) {
			if ( ut_stripallslashes( $ut_taxonomy_array['name'] ) == ut_stripallslashes( $delete_taxonomy ) ) {
				unset( $ut_taxonomies[ $ut_taxonomy_key ] );
			}
		}
		$updated = update_site_option( 'ut_taxonomies', $ut_taxonomies );

		if ( $updated ) {
			echo "deleted";
		} else {
			echo "failed";
		}
		die( 1 );
	}

	/**
	 * Loads Tag Suggestions
	 * @return boolean
	 */
	function ut_load_tag_suggestions_callback() {
		if ( empty( $_POST ) || empty( $_POST['nonce'] ) || empty( $_POST['q'] ) || empty( $_POST['taxonomy'] ) ) {
			return false;
		}
		extract( $_POST );
		if ( ! wp_verify_nonce( $nonce, 'user-tags' ) ) {
			return false;
		}
		$tags = get_terms( $taxonomy, array(
			'orderby'    => 'count',
			'hide_empty' => 0
		) );
		if ( empty( $tags ) || ! is_array( $tags ) ) {
			return false;
			die;
		}
		$tag_list = array();
		foreach ( $tags as $tag ) {
			$tag_list[] = $tag->name;
		}

		//Matching Tags
		$input  = preg_quote( trim( $q ), '~' );
		$result = preg_grep( '~' . $input . '~i', $tag_list );
		if ( empty( $result ) ) {
			return;
		}
		$output = '<ul class="tag-suggestion float-left hide-on-blur">';
		foreach ( $result as $r ) {
			$output .= "<li>" . $r . "</li>";
		}
		$output .= '</ul>';
		if ( ! empty( $output ) ) {
			echo $output;
		}
		die( 1 );
	}

	/**
	 * Admin ajax URL
	 */
	function admin_ajax() {
		?>
		<script type="text/javascript">
			var ajaxurl = <?php echo json_encode( admin_url( "admin-ajax.php" ) ); ?>;
		</script><?php
	}

}

/**
 * Class object
 */
add_action( 'init', 'ut_user_tags' );

//Flush rewrite rules
function wp_ut_flush_rules() {
	//Check if there is new taxonomy, if there flush rules
	$ut_new_taxonomy = get_site_option( 'ut_new_taxonomy', '', false );
	if ( $ut_new_taxonomy !== 'FALSE' ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );
		$updated = update_site_option( 'ut_new_taxonomy', 'FALSE' );
	}
}

/**
 * Show admin message for taxonomy creation
 */
function ut_taxonomy_created() {
	echo '<div id="message" class="updated below-h2">' . __( 'Taxonomy created', WP_UT_TRANSLATION_DOMAIN ) . '</div>';
}

/**
 * Updating a taxonomy
 */
function ut_taxonomy_updated() {
	echo '<div id="message" class="updated below-h2">' . __( 'Taxonomy updated', WP_UT_TRANSLATION_DOMAIN ) . '</div>';
}

/**
 * Class object
 */
function ut_user_tags() {
	$user_tags = new UserTags();
}

/**
 * If a new taxonomy was created, Flush rules for template
 */
add_action( 'init', 'wp_ut_flush_rules', 10 );