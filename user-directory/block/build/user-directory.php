<?php
/**
 * Block callback template
 */

// Include helper.
require_once UT_DIR . 'user-directory/inc/helpers.php';
require_once UT_DIR . 'user-directory/inc/class-user-directory-data.php';

/**
 * $attributes passed as argument
 */
$data = new User_Directory_Data( $attributes );

if ( ! $data ) {
	return;
}

$class_name = 'wp-block-user-tags-user-directory user-directory';
if ( ! empty( $atts['className'] ) ) {
	$class_name .= ' ' . $atts['className'];
}

$dir_id = $data->get_directory_id();

$entries       = $data->get_users();
$entries_count = count( $entries );

$filters            = $data->get_filters();
$taxonomies_filters = $data->get_taxonomy_filters();

$users_per_page = $data->get_users_per_page( $entries_count );
$filters_logic  = ! empty( $atts['filters_logic'] ) ? $atts['filters_logic'] : '';
$pagination     = $users_per_page && ! empty( $atts['pagination'] );

$label = esc_html__( 'Users', 'user_taxonomy' );

$content_class = 'user-directory-content';
if ( ! $entries_count ) {
	$content_class .= ' user-directory-content--no-results';
}
?>
<div class="<?php echo esc_attr( $class_name ); ?>" id="<?php echo esc_attr( $dir_id ); ?>"
	 aria-label="<?php echo esc_attr( $label ); ?>" data-source=""
	 data-filters-logic="<?php echo esc_attr( $filters_logic ); ?>">
	<?php
	if ( ! empty( $filters ) || ! empty( $taxonomies_filters ) ) {
		?>
		<form class="user-directory-controls" aria-controls="<?php echo esc_attr( $dir_id ); ?>-content">
			<fieldset>
				<legend class="user-directory-sr-info screen-reader-text">
					<?php echo esc_html__( 'Items will instantly refresh upon filtering.', 'user_taxonomy' ); ?>
				</legend>

				<?php include( UT_DIR . 'user-directory/template-parts/directory-filters.php' ); ?>
			</fieldset>
		</form>
		<?php
	}
	?>
	<div class="<?php echo esc_attr( $content_class ); ?>" id="<?php echo esc_attr( $dir_id ); ?>-content"
		 aria-label="<?php echo esc_attr( sprintf( esc_html__( '%s Entries', 'user_taxonomy' ), $label ) ); ?>">
		<div class="user-directory-sr-info user-directory-sr-info-count screen-reader-text" aria-live="polite"
			 aria-atomic="true" aria-relevant="all">
			<?php printf( esc_html__( '%s results found.', 'user_taxonomy' ), '<span class="user-directory-sr-info-count-number">' . $entries_count . '</span>' ); ?>
			<?php
			if ( $users_per_page ) {
				printf( esc_html__( 'First %s results are being shown', 'user_taxonomy' ), '<span class="user-directory-sr-info-per-page">' . $users_per_page . '</span>' );
			}
			?>
		</div>
		<?php
		if ( $entries_count ) {
			$fields = $data->get_fields();

			include UT_DIR . 'user-directory/template-parts/directory-content.php';
			include UT_DIR . 'user-directory/template-parts/directory-after.php';

			if ( $filters || $taxonomies_filters ) {
				$field_js = wp_json_encode( $data->get_fields_js() );
				$args     = array(
					'valueNames'  => $data->get_fields_js(),
					'listClass'   => 'user-directory-content-list',
					'searchClass' => 'user-directory-field-search',
					'searchDelay' => 250,
					'page'        => esc_js( $users_per_page ? $users_per_page : $data->get_users_limit() ),
				);
				if ( $pagination ) {
					$args['pagination'] = array(
						'paginationClass' => 'user-directory-pagination',
						'item'            => "<li><button class='page'></button></li>",
					);
				}
				ob_start();
				?>
				<script>
					userDirectories['<?php echo esc_attr( $dir_id ); ?>'] = new List('<?php echo esc_attr( $dir_id ); ?>', <?php echo wp_json_encode( $args ); ?> );
				</script>
				<?php
				$inline_script = str_replace( array( '<script>', '</script>' ), '', ob_get_clean() );
				wp_add_inline_script( 'user-directory-block', $inline_script );

				add_action(
					'wp_footer',
					function () {
						wp_enqueue_script( 'user-directory-block' );
					}
				);
			}
		}
		?>
		<div class="user-directory-no-results-info" aria-hidden="true">
			<?php echo apply_filters( 'user_directory_no_results_info', __( 'No results found.', 'user_taxonomy' ) ); ?>
		</div>
		<?php if ( $pagination ) { ?>
			<nav class="user-directory-pagination-holder"
				 aria-label="<?php echo esc_attr( sprintf( esc_html__( '%s pagination' ), $aria_label ) ); ?>">
				<ul class="user-directory-pagination"></ul>
			</nav>
		<?php } ?>
	</div>
</div>
