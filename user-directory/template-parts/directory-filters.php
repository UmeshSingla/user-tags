<?php if( in_array( 'search', $filters ) ) { ?>
	<div class="user-directory-control user-directory-control-text user-directory-control-search">
		<?php $field_id = $dir_id . '-search'; ?>
		<label class="screen-reader-text" for="<?php echo esc_attr($field_id); ?>">Search</label>
		<input class="user-directory-field user-directory-field-search" id="<?php echo esc_attr($field_id); ?>" name="search" type="text" placeholder="<?php esc_attr_e( 'Search &hellip;', 'user_taxonomy' ); ?>" value="">
	</div>
<?php } ?>
<?php
if( $taxonomies_filters ) {
	foreach( $taxonomies_filters as $filter ) {
		?>
		<div class="user-directory-control user-directory-control-select" data-field-name="<?php echo esc_attr( $filter['field_name'] ); ?>">
			<label class="screen-reader-text" for="<?php echo esc_attr( $filter['select_id'] ); ?>"><?php printf( __( 'Filter By %s', 'user_taxonomy' ), ucfirst( $filter['label'] ) ); ?></label>
			<?php
			wp_dropdown_categories( array(
				'show_option_all' => '<span aria-hidden="true">' . sprintf( __( '%s', 'user_taxonomy' ), ucfirst( $filter['label'] ) ) . '</span>',
				'taxonomy' => $filter['taxonomy'],
				'hierarchical' => true,
				'orderby' => 'name',
				'name' => 'taxonomies[' . $filter['taxonomy'] . '][]',
				'id' => $filter['select_id'],
				'class' => 'user-directory-field user-directory-field-tax',
				'hide_if_empty' => true
			) );
			?>
		</div>
		<?php
	}
}
?>
<?php if( in_array( 'clear', $filters ) ) { ?>
	<div class="user-directory-control user-directory-control-button user-directory-control-clear">
		<button type="button" disabled><?php _e( 'Clear Results', 'user_taxonomy' ); ?></button>
	</div>
<?php } ?>
