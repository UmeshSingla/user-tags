<?php

/**
 * Get taxonomy slug from taxonomy name
 *
 *
 * @param string $name
 *
 * @return mixed
 */
function get_taxonomy_slug( $name = '' ) {
	if ( empty( $name ) ) {
		return;
	}

	return substr( sanitize_title_with_dashes( $name ), 0, 32 );
}

/**
 * Enqueue necessary style and scripts
 *
 * @return void
 */
function user_tags_enqueue_assets() {
	wp_localize_script( 'user-tags-js', 'wp_ut', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'user-tags-js' );

	wp_enqueue_style( 'user-tags-style' );
}

/**
 * Checks if the provided string is a registered taxonomy name.
 *
 * @param string $tax - A taxonomy slug or name to verify
 *
 * @return bool Whether a taxonomy exists.
 */
function user_taxonomy_exists( $tax ) {
	$user_taxonomies = get_user_taxonomies();

	if ( empty( $user_taxonomies ) ) {
		return false;
	}

	$taxonomy_exists = false;
	if ( is_array( $user_taxonomies ) ) :
		// Check if taxonomy already created by user.
		foreach ( $user_taxonomies as $key => $taxonomy ) {
			if ( $tax === $taxonomy['name'] || $tax === $taxonomy['slug'] ) :
				$taxonomy_exists = true;
				break;
			endif;
		}
	endif;

	if ( ! $taxonomy_exists ) :
		$tax_slug        = get_taxonomy_slug( $tax );
		$taxonomy_exists = taxonomy_exists( $tax_slug );
	endif;

	return $taxonomy_exists;
}

/**
 * Get array key for Taxonomy name in registered taxonomies
 *
 * @param string $tax Taxonomy name to check for.
 *
 * @return false|string - False if key not found, else key
 */
function get_user_taxonomy_key( $tax ) {
	$user_taxonomies = get_user_taxonomies();

	if ( empty( $user_taxonomies ) ) {
		return false;
	}

	if ( is_array( $user_taxonomies ) ) :
		// Check if taxonomy already created by user.
		foreach ( $user_taxonomies as $key => $taxonomy ) {
			if ( $tax === $taxonomy['name'] || $tax === $taxonomy['slug'] ) :
				return $key;
			endif;
		}
	endif;

	return false;
}

/**
 * Get only class attribute in block editor
 *
 * @param $extra_attributes
 *
 * @return string
 */
function ut_get_block_wrapper_attributes( $extra_attributes = array() ) {
	global $current_screen;

	if ( is_admin() || ( $current_screen instanceof WP_Screen && $current_screen->is_block_editor() ) ) {
		return 'class="' . esc_attr( $extra_attributes['class'] ) . '"';
	}

	return get_block_wrapper_attributes( $extra_attributes );
}

/**
 *
 * Returns a list of valid/registered user taxonomy.
 * @return array
 *
 */
function get_registered_user_taxonomies() {
	$user_taxonomies = get_user_taxonomies();

	$taxonomies = [];
	if ( is_array( $user_taxonomies ) ) :
		foreach ( $user_taxonomies as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy['slug'] ) ) :
				$tax = get_taxonomy( $taxonomy['slug'] );
				$taxonomies[] = array(
					'name'  => $tax->name,
					'label' => $tax->label
				);
			endif;
		}
	endif;

	return $taxonomies;
}

/**
 * Get User taxonomies based on plugin version
 *
 * @return array $taxonomies
 */
function get_user_taxonomies() {
	$version = get_option( 'ut_version' );
	if ( empty( $version ) ) {
		$version = get_site_option( 'ut_version' );
	}

	if ( empty( $version ) || version_compare( $version, '2.0', '<' ) ) :
		$taxonomies = get_site_option('ut_taxonomies');
	else:
		$taxonomies = get_option('ut_taxonomies');
	endif;

	return $taxonomies;
}