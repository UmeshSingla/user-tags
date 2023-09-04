<?php
/**
 * Renders pagination for the directory block
 */

if ( $users_per_page ) {
	?>
    <div class="user-directory-pagination" data-per-page="<?php echo esc_attr( $users_per_page ); ?>">
        <div class="user-directory-control-load-more">
            <button class="<?php echo esc_attr( apply_filters( 'user_directory_load_more_button_class', 'user-directory-btn user-directory-control-load-more-btn' ) ); ?>"
                    aria-controls="<?php echo esc_attr( $dir_id ); ?>-content">
				<?php echo apply_filters( 'user_directory_load_more_label', esc_html__( 'Load More', 'user_taxonomy' ) ); ?>
            </button>
        </div>
        <button class="user-directory-sr-load-jump-btn screen-reader-text" style="display: none;">
			<?php echo esc_html__( 'Go to first recently loaded item', 'user_taxonomy' ); ?>
        </button>
    </div>
	<?php
}
