<?php
/**
 * Plugin Name: User Tags
 * Author: Umesh Kumar<umeshsingla05@gmail.com>
 * Author URI:http://codechutney.com
 * Description: Adds User Taxonomy functionality, It allows you to categorize users on tags and taxonomy basis.
 * Version: 1.2.8
 * Reference :  http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress
 * Text Domain : user_taxonomy
 */


define( 'WP_UT_URL', plugins_url( '', __FILE__ ) );
define( 'WP_UT_PLUGIN_FOLDER', dirname( __FILE__ ) );
define( 'WP_UT_TEMPLATES', trailingslashit( WP_UT_PLUGIN_FOLDER ) . trailingslashit( 'templates' ) );

/* Define all necessary variables first */
define( 'WP_UT_CSS', WP_UT_URL . '/assets/css/' );
define( 'WP_UT_JS', WP_UT_URL . '/assets/js/' );

// Includes PHP files located in 'inc' folder
require_once 'inc/functions.php';
require_once 'inc/class-user-tags.php';
require_once 'inc/class-tags-list.php';
require_once 'inc/class-shortcode.php';

/**
 * Class object
 */
add_action( 'init', 'ut_user_tags' );

//Flush rewrite rules
function wp_ut_flush_rules() {
	//Check if there is new taxonomy, if there flush rules
	$ut_new_taxonomy = get_site_option( 'ut_new_taxonomy', '', false );
	if ( 'FALSE' !== $ut_new_taxonomy ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );
		update_site_option( 'ut_new_taxonomy', 'FALSE' );
	}
}

/**
 * Show admin message for taxonomy creation
 */
function ut_taxonomy_created() {
	echo '<div id="message" class="updated below-h2">' . esc_html__( 'Taxonomy created', 'user_taxonomy' ) . '</div>';
}

/**
 * Updating a taxonomy
 */
function ut_taxonomy_updated() {
	echo '<div id="message" class="updated below-h2">' . esc_html__( 'Taxonomy updated', 'user_taxonomy' ) . '</div>';
}

/**
 * Class object
 */
function ut_user_tags() {
	global $user_tags;
	$user_tags = new UserTags();
}

/**
 * If a new taxonomy was created, Flush rules for template
 */
add_action( 'init', 'wp_ut_flush_rules', 10 );
