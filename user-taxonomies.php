<?php
/**
 * Plugin Name:	User Taxonomies
 * Author: Umesh Kumar
 * Author URI:	http://codechutney.com
 * Description:	Allow Registering of User Taxonomy
 * Version: 1.0
 * Reference :  http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress, http://wordpress.org/plugins/user-taxonomies/
 */
define('UT_TRANSLATION_DOMAIN', 'user_taxonomy');
define( 'UT_URL', plugins_url('', __FILE__) );
define( 'UT_TEMPLATES_URL', plugins_url('', __FILE__).'/templates/' );

/* Define all necessary variables first */
define( 'UT_CSS', UT_URL. "/assets/css/" );
define( 'UT_JS',  UT_URL. "/assets/js/" );
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Includes PHP files located in 'lib' folder
foreach( glob ( dirname(__FILE__). "/lib/*.php" ) as $lib_filename ) {
     require_once( $lib_filename );
}
class UT_UserTaxonomies {
	private static $taxonomies	= array();
	
	/**
	 * Register all the hooks and filters we can in advance
	 * Some will need to be registered later on, as they require knowledge of the taxonomy name
	 */
	public function __construct() {
            add_action( 'wp_ajax_ut_delete_taxonomy',array($this, 'ut_delete_taxonomy_callback'));
            add_action( 'wp_ajax_ut_load_tag_suggestions',array($this, 'ut_load_tag_suggestions_callback'));
            // Taxonomies
            add_action( 'admin_enqueue_scripts', array( $this, 'ut_enqueue_scripts' ) );
            add_action('init', array($this, 'ut_init') );
           
            add_action('registered_taxonomy', array($this, 'registered_taxonomy'), 10, 3);
            // Menus
            add_action('admin_menu', array($this, 'admin_menu'));
            add_filter('parent_file', array($this, 'parent_menu'));
            
            // User Profiles
            add_action('show_user_profile', array($this, 'user_profile'));
            add_action('edit_user_profile', array($this, 'user_profile'));
            add_action('personal_options_update',	array($this, 'save_profile'));
            add_action('edit_user_profile_update',	array($this, 'save_profile'));
            add_filter('sanitize_user', array($this, 'restrict_username'));
	}
	function ut_init(){
            $this->ut_update_taxonomy_list();
            $this->ut_register_taxonomies();
        }
        function ut_enqueue_scripts($hook) {
            if($hook == 'users_page_user-taxonomies' || $hook == 'profile.php'){
                wp_enqueue_style( 'ut-style', UT_CSS.'style.css' );
                wp_enqueue_script( 'user_taxonomy_js', UT_JS.'user_taxonomy.js', array('jquery'), false, true );
            }
        }
	/**
	 * This is our way into manipulating registered taxonomies
	 * It's fired at the end of the register_taxonomy function
	 * 
	 * @param String $taxonomy	- The name of the taxonomy being registered
	 * @param String $object	- The object type the taxonomy is for; We only care if this is "user"
	 * @param Array $args		- The user supplied + default arguments for registering the taxonomy
	 */
	public function registered_taxonomy($taxonomy, $object, $args) {
		global $wp_taxonomies;
		
		// Only modify user taxonomies, everything else can stay as is
		if($object != 'user') return;
		
		// We're given an array, but expected to work with an object later on
		$args	= (object) $args;
		
		// Register any hooks/filters that rely on knowing the taxonomy now
		add_filter("manage_edit-{$taxonomy}_columns",	array($this, 'set_user_column'));
		add_action("manage_{$taxonomy}_custom_column",	array($this, 'set_user_column_values'), 10, 3);
		
		// Set the callback to update the count if not already set
		if(empty($args->update_count_callback)) {
			$args->update_count_callback	= array($this, 'update_count');
		}
		
		// We're finished, make sure we save out changes
		$wp_taxonomies[$taxonomy]		= $args;
		self::$taxonomies[$taxonomy]	= $args;
	}
	
	/**
	 * We need to manually update the number of users for a taxonomy term
	 * 
	 * @see	_update_post_term_count()
	 * @param Array $terms		- List of Term taxonomy IDs
	 * @param Object $taxonomy	- Current taxonomy object of terms
	 */
	public function update_count($terms, $taxonomy) {
		global $wpdb;
		
		foreach((array) $terms as $term) {
			$count	= $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term));
			
			do_action('edit_term_taxonomy', $term, $taxonomy);
			$wpdb->update($wpdb->term_taxonomy, compact('count'), array('term_taxonomy_id'=>$term));
			do_action('edited_term_taxonomy', $term, $taxonomy);
		}
	}
	
	/**
	 *Adds a Taxonomy Sub page to Users menu
	 */
	public function admin_menu() {
            global $users_taxonomy;
            if(is_super_admin()){
                $users_taxonomy = add_users_page( __( 'User Taxonomies', UT_TRANSLATION_DOMAIN ), __( 'Taxonomies', UT_TRANSLATION_DOMAIN ), 'read', 'user-taxonomies', array( $this, "ut_user_taxonomies") );
            }
	}
        
        public function ut_user_taxonomies(){ ?>
            <div class="wrap nosubsub user-taxonomies-page">
                <h2><?php _e ( 'User Taxonomies', 'rtmedia' ); ?></h2>
                <div id="col-container">
                    <div id="col-right"><?php
                        $uttaxonomylisttable = new UTTaxonomyListTable();
                        $uttaxonomylisttable->prepare_items(); 
                        //                         $rtmediaproalbummediaList->views(); ?>
                        <form method="post"> <?php
                            wp_nonce_field('taxonomy_bulk_action', 'taxonomy_bulk_action');
                            $uttaxonomylisttable->display(); ?>
                        </form>
                    </div>
                    <div id="col-left">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <h3><?php _e('Add New Taxonomy', UT_TRANSLATION_DOMAIN ); ?></h3>
                                <form name="editusertaxonomy" id="editusertaxonomy" method="post" action="" class="validate">
                                    <table class="form-table">
                                        <tr class="form-field form-required">
                                            <th scope="row" valign="top"><label for="taxonomy_name"><?php _ex('Name', 'Taxonomy Name'); ?></label></th>
                                            <td><input name="taxonomy_name" id="taxonomy_name" type="text" value="" size="40" data-required="true" /></td>
                                        </tr>
                                        <tr class="form-field form-required">
                                            <th scope="row" valign="top"><label for="taxonomy_group"><?php _ex('Group', 'Buddypress Group'); ?></label></th>
                                            <td><input name="taxonomy_group" id="taxonomy_group" type="text" value="" size="40" data-required="true" /></td>
                                        </tr>
                                        <tr class="form-field form-required">
                                            <th scope="row" valign="top"><label for="taxonomy_order"><?php _ex('Order', 'Taxonomy Order'); ?></label></th>
                                            <td><input name="taxonomy_order" id="taxonomy_order" type="text" value="" size="40" data-required="true" /></td>
                                        </tr>
                                        <tr class="form-field">
                                            <th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>
                                            <td><textarea name="description" id="description" rows="5" cols="50" class="large-text"></textarea></td>
                                        </tr>
                                        <?php wp_nonce_field('ut_register_taxonomy', 'ut_register_taxonomy'); ?>
                                    </table>
                                    <?php submit_button( __('Save') ); ?>  
                                </form>
                            </div>
                        </div>
                    </div>
                </div><!-- Col Container -->
            </div> <?php
        }
        
        public function ut_update_taxonomy_list (){
            if( empty($_POST['taxonomy_name']) || !isset( $_POST['taxonomy_group'] ) || !isset( $_POST['taxonomy_group'] ) ){
                return;
            }
            $taxonomy_description = '';
            extract($_POST);
            $nonce_verified = wp_verify_nonce($ut_register_taxonomy, 'ut_register_taxonomy');
            if(!$nonce_verified ){
                wp_die('Invalid request');
            };
            
            $ut_taxonomies = get_site_option('ut_taxonomies');
            if(!is_array($ut_taxonomies)){
                $ut_taxonomies = array($ut_taxonomies);
            }
            $taxonomy_exists = FALSE;
            foreach( $ut_taxonomies as $ut_taxonomy ){
                if( $ut_taxonomy['name'] == $taxonomy_name ){
                    $taxonomy_exists = TRUE;
                    break;
                }
            }
            if( !$taxonomy_exists ){
                $ut_taxonomies[] = array(
                    'name'  =>  $taxonomy_name,
                    'group' => (int)$taxonomy_group,
                    'order' =>  (int)$taxonomy_order,
                    'description'   => $taxonomy_description
                );
                $taxonomy_site_option = update_site_option('ut_taxonomies', $ut_taxonomies);
            }else{
                //Warning Taxonomy Already exists
            }
        }
        function ut_register_taxonomies(){
            $ut_taxonomies = get_site_option('ut_taxonomies');
            $errors = array();
            if( empty($ut_taxonomies) || !is_array($ut_taxonomies) ) return;
            foreach ( $ut_taxonomies as $ut_taxonomy ){
                extract($ut_taxonomy);
                $taxonomy_slug = ut_taxonomy_name($name);
                $registered = register_taxonomy(
                       $taxonomy_slug,
                       'user',
                       array(
                               'public' => true,
                               'hierarchical'	=> FALSE,
                               'labels' => array(
                                       'name' => __( $name ),
                                       'singular_name' => __( $name ),
                                       'menu_name' => __( $name ),
                                       'search_items' => __( 'Search '.$name ),
                                       'popular_items' => __( 'Popular '.$name ),
                                       'all_items' => __( 'All '.$name ),
                                       'edit_item' => __( 'Edit '.$name ),
                                       'update_item' => __( 'Update '.$name ),
                                       'add_new_item' => __( 'Add New '.$name ),
                                       'new_item_name' => __( 'New '.$name ),
                                       'separate_items_with_commas' => __( 'Separate '.  strtolower($name) . ' with commas' ),
                                       'add_or_remove_items' => __( 'Add or remove '.  strtolower($name) ),
                                       'choose_from_most_used' => __( 'Choose from the most popular '.  strtolower($name) ),
                               ),
                               'rewrite' => array(
                                       'with_front' => false,
                                       'slug' => 'author/'.$taxonomy_slug // Use 'author' (default WP user slug).
                               ),
                               'capabilities' => array(
                                       'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
                                       'edit_terms'   => 'edit_users',
                                       'delete_terms' => 'edit_users',
                                       'assign_terms' => 'read',
                               ),
                               'update_count_callback' => 'my_update_'.$taxonomy_slug.'_count' // Use a custom function to update the count.
                       )
                );
                if(is_wp_error($registered)){
                    $errors[] = $registered;
                }
            }//End of foreach
            if(!empty($errors)){
                echo "<pre>";
                print_r($errors);
                echo "</pre>";
                die;
            }
        }
        /**
	 * Fix a bug with highlighting the parent menu item
	 * By default, when on the edit taxonomy page for a user taxonomy, the Posts tab is highlighted
	 * This will correct that bug
	 */
	function parent_menu($parent = '') {
		global $pagenow;
		
		// If we're editing one of the user taxonomies
		// We must be within the users menu, so highlight that
		if(!empty($_GET['taxonomy']) && $pagenow == 'edit-tags.php' && isset(self::$taxonomies[$_GET['taxonomy']])) {
			$parent	= 'users.php';
		}
		
		return $parent;
	}
	
	/**
	 * Correct the column names for user taxonomies
	 * Need to replace "Posts" with "Users"
	 */
	public function set_user_column($columns) {
		unset($columns['posts']);
		$columns['users']	= __('Users');
		return $columns;
	}
	
	/**
	 * Set values for custom columns in user taxonomies
	 */
	public function set_user_column_values($display, $column, $term_id) {
		if('users' === $column && !empty($_GET['taxonomy']) ) {
			$term	= get_term($term_id, $_GET['taxonomy']);
			echo $term->count;
		}
	}
	
	/**
	 * Add the taxonomies to the user view/edit screen
	 * 
	 * @param Object $user	- The user of the view/edit screen
	 */
	public function user_profile($user) {
		// Using output buffering as we need to make sure we have something before outputting the header
		// But we can't rely on the number of taxonomies, as capabilities may vary
		wp_nonce_field('user-tags', 'user-tags');
                ob_start(); ?>
                <div class="user-taxonomy-wrapper"><?php
                    foreach(self::$taxonomies as $key=>$taxonomy):
			// Check the current user can assign terms for this taxonomy
			if(!current_user_can($taxonomy->cap->assign_terms)) continue;
			
			// Get all the terms in this taxonomy
			$terms	= wp_get_object_terms($user->ID, $taxonomy->name);
                        $num = 0; $html = ''; $user_tags = '';
                        if(!empty($terms)){
                            foreach($terms  as $term ){
                                $user_tags[] = $term->name;
                                $html .= '<span><a id="user_tag-'.$taxonomy->name.'-'.$num. '" class="ntdelbutton">X</a></span>&nbsp;<a href="#" class="term-link">'.$term->name.'</a>';
                                $num++;
                            }
                            $user_tags = implode(',', $user_tags);
                        } ?>
			<table class="form-table user-profile-taxonomy">
                            <tr>
                                <th><label for="new-tag-user_tag_<?php echo $taxonomy->name; ?>"><?php _e("{$taxonomy->labels->singular_name}")?></label></th>
                                <td class="ajaxtag">
                                    <input type="text" id="new-tag-user_tag_<?php echo $taxonomy->name; ?>" name="newtag[user_tag]" class="newtag form-input-tip float-left hide-on-blur" size="16" autocomplete="off" value="">
                                    <input type="button" class="button tagadd float-left" value="Add">
                                    <p class="howto"><?php _e('Separate tags with commas', UT_TRANSLATION_DOMAIN ); ?></p>
                                    <div class="tagchecklist"><?php echo $html; ?></div>
                                    <input type="hidden" name="user-tags[<?php echo $taxonomy->name; ?>]" id="user-tags-<?php echo $taxonomy->name; ?>" value="<?php echo $user_tags; ?>" />
                                </td>
                            </tr>
			</table> <?php
		endforeach; // Taxonomies ?>
                </div><?php
		
		// Output the above if we have anything, with a heading
		$output	= ob_get_clean();
		if(!empty($output)) {
			echo $output;
		}
	}
	
	/**
	 * Save the custom user taxonomies when saving a users profile
	 * 
	 * @param Integer $user_id	- The ID of the user to update
	 */
	public function save_profile($user_id) {
           if(empty($_POST['user-tags'])) return;
            foreach($_POST['user-tags'] as $taxonomy=>$taxonomy_terms) {
                // Check the current user can edit this user and assign terms for this taxonomy
                if(!current_user_can('edit_user', $user_id) && current_user_can($taxonomy->cap->assign_terms)) return false;

                // Save the data
                if(!empty($taxonomy_terms))
                $taxonomy_terms = array_map('trim', explode(',', $taxonomy_terms));
                wp_set_object_terms($user_id, $taxonomy_terms, $taxonomy, false);
            }
	}
	
	/**
	 * Usernames can't match any of our user taxonomies
	 * As otherwise it will cause a URL conflict
	 * This method prevents that happening
	 */
	public function restrict_username($username) {
		if(isset(self::$taxonomies[$username])) return '';
		
		return $username;
	}
        //Delete Taxonomy
        function ut_delete_taxonomy_callback(){
            if( empty($_POST) || empty($_POST['nonce'] ) || empty($_POST['taxonomy_name'] ) ) return false;
            extract($_POST);
            $taxonomy_slug = ut_taxonomy_name($taxonomy_name);
            if( !wp_verify_nonce ( $nonce , 'delete-taxonomy-'.$taxonomy_slug ) ){
                return FALSE;
            }
            $ut_taxonomies = get_site_option('ut_taxonomies');
            foreach ($ut_taxonomies as $ut_taxonomy_key => $ut_taxonomy_array ){
                if( $ut_taxonomy_array['name'] == $taxonomy_name ){
                    unset($ut_taxonomies[$ut_taxonomy_key]);
                }
            }
            $updated = update_site_option( 'ut_taxonomies', $ut_taxonomies);
            if($updated){
               echo "deleted";
            }else{
                echo "<pre>";
                print_r($ut_taxonomies);
                echo "</pre>";
            }
            die(1);
        }
        function ut_load_tag_suggestions_callback(){
            if( empty($_POST) || empty($_POST['nonce'] ) || empty($_POST['q'] ) || empty($_POST['taxonomy'] ) ) return;
            extract($_POST);
            if( !wp_verify_nonce ( $nonce , 'user-tags' ) ){
                return;
            }
            $tags = get_terms($taxonomy, array(
                    'orderby'    => 'count',
                    'hide_empty' => 0
             ));
            if(empty($tags) || !is_array($tags)) { return;}
            $tag_list = array();
            foreach($tags as $tag){
                $tag_list[] = $tag->name;
            }

            //Matching Tags
            $input = preg_quote( trim( $q ), '~');
            $result = preg_grep('~' . $input . '~i', $tag_list);
            if(empty($result)) return;
            $output = '<ul class="tag-suggestion float-left hide-on-blur">';
            foreach ($result as $r ){
                $output .= "<li>".$r."</li>";
            }
            $output .= '</ul>';
            if(!empty($output)){
                echo $output;
            }
            die(1);
        }
}

new UT_UserTaxonomies();