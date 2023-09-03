<?php
/**
 * Include custom template.
 */
add_filter( 'template_include', 'user_taxonomy_template' );
/**
 * @param string $template
 *
 * @return string
 */
function user_taxonomy_template( $template ) {

	$taxonomy = get_query_var( 'taxonomy' );

	// check if taxonomy is for user or not
	$user_taxonomies = get_object_taxonomies( 'user', 'object' );

	if ( ! array( $user_taxonomies ) || empty( $user_taxonomies[ $taxonomy ] ) ) {
		return $template;
	}

	// Check if theme is overriding the template
	$overridden_template = locate_template( 'user-taxonomy-template.php', false, false );

	if ( ! empty( $overridden_template ) ) {
		$taxonomy_template = $overridden_template;
	} else {
		$taxonomy_template = UT_TEMPLATE_PATH . 'user-taxonomy-template.php';
	}

	if ( file_exists( $taxonomy_template ) ) {
		return $taxonomy_template;
	}

	return $template;
}

/**
 * Shortcode for Tags UI in frontend
 */
function wp_ut_tag_box() {
	$user_id    = get_current_user_id();
	$taxonomies = get_object_taxonomies( 'user', 'object' );

	wp_nonce_field( 'user-tags', 'user-tags' );

	ut_enqueue_assets();

	if ( empty( $taxonomies ) ) {
		?>
		<p><?php echo esc_html__( 'No taxonomies found', 'user_taxonomy' ); ?></p>
		<?php
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>

	<form name="user-tags" action="" method="post">
		<ul class="form-table user-profile-taxonomy user-taxonomy-wrapper">
			<?php
			foreach ( $taxonomies as $key => $taxonomy ) {
				// Check the current user can assign terms for this taxonomy
				if ( ! current_user_can( $taxonomy->cap->assign_terms ) ) {
					continue;
				}
				$choose_from_text = apply_filters( 'ut_tag_cloud_heading', $taxonomy->labels->choose_from_most_used, $taxonomy );
				// Get all the terms in this taxonomy
				$terms     = wp_get_object_terms( $user_id, $taxonomy->name );
				$num       = 0;
				$html      = '';
				$user_tags = '';
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$user_tags[] = $term->name;
						$term_url    = site_url() . '/' . $taxonomy->rewrite['slug'] . '/' . $term->slug;
						$html       .= '<div class="ag-hldr">';
						$html       .= '<span><a id="user_tag-' . $taxonomy->name . '-' . $num . '" class="ntdelbutton">x</a></span>&nbsp;<a href="' . $term_url . '" class="term-link">' . $term->name . '</a>';
						$html       .= '</div>';
						$num ++;
					}
					$user_tags = implode( ',', $user_tags );
				}
				?>
				<li>
					<label for="new-tag-user_tag_<?php echo esc_attr( $taxonomy->name ); ?>"><?php echo esc_attr( $taxonomy->labels->singular_name ); ?></label>

					<div class="taxonomy-wrapper">
						<input type="text" id="new-tag-user_tag_<?php echo esc_attr( $taxonomy->name ); ?>" name="newtag[user_tag]" class="newtag form-input-tip float-left hide-on-blur" size="16" autocomplete="off" value="">
						<input type="button" class="button tagadd float-left" value="Add">

						<p class="howto"><?php esc_html_e( 'Separate tags with commas', 'user_taxonomy' ); ?></p>

						<div class="tagchecklist"><?php echo esc_attr( $html ); ?></div>
						<input type="hidden" name="user-tags[<?php echo esc_attr( $taxonomy->name ); ?>]" id="user-tags-<?php echo esc_attr( $taxonomy->name ); ?>" value="<?php echo esc_attr( $user_tags ); ?>"/>
					</div>
					<?php
					if ( ! empty( $terms ) ) :
						?>
						<!--Display Tag cloud for most used terms-->
						<p class="hide-if-no-js tagcloud-container">
							<a href="#titlediv" class="tagcloud-link user-taxonomy" id="link-<?php echo esc_attr( $taxonomy->name ); ?>"><?php echo esc_attr( $choose_from_text ); ?></a>
						</p>
					<?php
					endif;
					?>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
		wp_nonce_field( 'save-user-tags', 'user-tags-nonce' );
		?>
		<input type="submit" name="update-user-tags" class="button tagadd float-left" value="Update">
	</form>
	<?php
}

// shortcode
add_shortcode( 'user_tags', 'wp_ut_tag_box' );

/**
 * Process and save user tags from shortcode
 */
add_action( 'wp_loaded', 'rce_ut_process_form' );
function rce_ut_process_form() {

	$_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

	if ( ! isset( $_POST ) || empty( $input_tags ) || empty( $_POST['user-tags-nonce'] ) || ! wp_verify_nonce( $_POST['user-tags-nonce'], 'save-user-tags' ) ) {
		return;
	}

	$user_id    = get_current_user_id();
	$input_tags = wp_unslash( $_POST['user-tags'] ); // input var okay

	foreach ( $input_tags as $taxonomy => $taxonomy_terms ) {
		// Check the current user can edit this user and assign terms for this taxonomy
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		// Save the data
		if ( ! empty( $taxonomy_terms ) ) {
			$taxonomy_terms = array_map( 'trim', explode( ',', $taxonomy_terms ) );
		}
		wp_set_object_terms( $user_id, $taxonomy_terms, $taxonomy, false );
	}
}


/**
 * URL path for user taxonomy template
 *
 * @return string
 */
function get_url_prefix() {
	$url_prefix = apply_filters( 'ut_tag_url_prefix', 'tag' );

	return trailingslashit( $url_prefix );
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