<?php
/**
 * @param string $name
 *
 * @return mixed
 */
function ut_taxonomy_name( $name = '' ) {
	if ( empty( $name ) ) {
		return;
	}
	$taxonomy_name = str_replace( '-', '_', str_replace( ' ', '_', strtolower( $name ) ) );
	$taxonomy_slug = $taxonomy_name;
	$taxonomy_slug = strlen( $taxonomy_slug ) > 32 ? substr( $taxonomy_slug, 0, 32 ) : $taxonomy_slug;

	return esc_html( ut_stripallslashes( $taxonomy_slug ) );
}

/**
 *
 */
add_filter( 'taxonomy_template', 'get_custom_taxonomy_template' );
/**
 * @param string $template
 *
 * @return string
 */
function get_custom_taxonomy_template( $template = '' ) {

	$taxonomy = get_query_var( 'taxonomy' );

	//check if taxonomy is for user or not
	$user_taxonomies = get_object_taxonomies( 'user', 'object' );

	if ( ! array( $user_taxonomies ) || empty( $user_taxonomies[ $taxonomy ] ) ) {
		return;
	}

	$taxonomy_template = WP_UT_TEMPLATES . "user-taxonomy-template.php";
	$file_headers      = @get_headers( $taxonomy_template );
	if ( $file_headers[0] != 'HTTP/1.0 404 Not Found' ) {
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
	wp_enqueue_script( 'user_taxonomy_js' );
	if ( empty ( $taxonomies ) ) {
		?>
		<p><?php echo __( 'No taxonomies found', WP_UT_TRANSLATION_DOMAIN ); ?></p><?php
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>

	<form name="user-tags" action="" method="post">
	<ul class="form-table user-profile-taxonomy user-taxonomy-wrapper"><?php
		foreach ( $taxonomies as $key => $taxonomy ):
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
					$html .= "<div class='tag-hldr'>";
					$html .= '<span><a id="user_tag-' . $taxonomy->name . '-' . $num . '" class="ntdelbutton">x</a></span>&nbsp;<a href="' . $term_url . '" class="term-link">' . $term->name . '</a>';
					$html .= "</div>";
					$num ++;
				}
				$user_tags = implode( ',', $user_tags );
			} ?>
			<li>
			<label for="new-tag-user_tag_<?php echo $taxonomy->name; ?>"><?php _e( "{$taxonomy->labels->singular_name}" ) ?></label>

			<div class="taxonomy-wrapper">
				<input type="text" id="new-tag-user_tag_<?php echo $taxonomy->name; ?>" name="newtag[user_tag]" class="newtag form-input-tip float-left hide-on-blur" size="16" autocomplete="off" value="">
				<input type="button" class="button tagadd float-left" value="Add">

				<p class="howto"><?php _e( 'Separate tags with commas', WP_UT_TRANSLATION_DOMAIN ); ?></p>

				<div class="tagchecklist"><?php echo $html; ?></div>
				<input type="hidden" name="user-tags[<?php echo $taxonomy->name; ?>]" id="user-tags-<?php echo $taxonomy->name; ?>" value="<?php echo $user_tags; ?>"/>
			</div>
			<!--Display Tag cloud for most used terms-->
			<p class="hide-if-no-js tagcloud-container">
				<a href="#titlediv" class="tagcloud-link user-taxonomy" id="link-<?php echo $taxonomy->name; ?>"><?php echo $choose_from_text; ?></a>
			</p>
			</li><?php
		endforeach; ?>
	</ul>
	<?php wp_nonce_field( 'save-user-tags', 'user-tags-nonce' ); ?>
	<input type="submit" name="update-user-tags" class="button tagadd float-left" value="Update">
	</form><?php
}

//shortcode
add_shortcode( 'user_tags', 'wp_ut_tag_box' );
add_action( 'in_admin_footer', 'wp_ut_ajax_url' );
add_action( 'wp_footer', 'wp_ut_ajax_url' );
function wp_ut_ajax_url() {
	?>
	<script type="text/javascript">
		$wp_ut_ajax_url =
		<?php echo json_encode(admin_url('admin-ajax.php')); ?>
	</script><?php
}

function ut_stripallslashes( $string ) {
	while ( strchr( $string, '\\' ) ) {
		$string = stripslashes( $string );
	}

	return $string;
}

/**
 * Process and save user tags from shortcode
 */
add_action( 'wp_loaded', 'rce_ut_process_form' );
function rce_ut_process_form() {
	$user_id = get_current_user_id();
	if ( isset( $_POST ) ) {
		if ( empty( $_POST['user-tags'] ) || empty( $_POST['user-tags-nonce'] ) || ! wp_verify_nonce( $_POST['user-tags-nonce'], 'save-user-tags' ) ) {
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
			}
			$terms_updated = wp_set_object_terms( $user_id, $taxonomy_terms, $taxonomy, false );
		}
	}
}