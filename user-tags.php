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


define( 'UT_URL', plugins_url( '', __FILE__ ) );
define( 'UT_DIR', trailingslashit( dirname( __FILE__ ) ) );
define( 'UT_TEMPLATE_PATH', trailingslashit( UT_DIR ) . trailingslashit( 'templates' ) );

define( 'UT_VERSION', '1.2.8' );

/* Define all necessary variables first */
define( 'UT_CSS_URL', UT_URL . '/assets/css/' );
define( 'UT_JS_URL', UT_URL . '/assets/js/' );

// Includes PHP files located in 'inc' folder
require_once UT_DIR . 'inc/functions.php';
require_once UT_DIR . 'inc/class-usertags.php';
require_once UT_DIR . 'inc/class-user-tags-list.php';
require_once UT_DIR . 'inc/class-user-tag-cloud.php';

/**
 * Class object
 */
add_action( 'init', 'ut_user_tags' );

// Flush rewrite rules
function wp_ut_flush_rules() {
	// Check if there is new taxonomy, if there flush rules
	$ut_new_taxonomy = get_site_option( 'ut_new_taxonomy', '' );
	if ( 'FALSE' !== $ut_new_taxonomy ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );
		delete_site_option( 'ut_new_taxonomy' );
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

// Register plugin activation hook, Set/update plugin version.
register_activation_hook( __FILE__, 'ut_activated' );

function ut_activated() {

	$version = get_site_option( 'ut_version' );
	if ( ! $version || UT_VERSION !== $version ) {
		update_site_option( 'ut_version', UT_VERSION );
	}
}
