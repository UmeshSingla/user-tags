<?php
/**
 * Gets the value of single field.
 *
 * @param [numeric] $user_id Post ID.
 * @param [array] details of the field.
 *
 * @return string
 */
function user_directory_get_field_value( $user_id, $field_details ) {

	$value = array();

	if ( isset( $field_details['type'] ) ) {
		switch ( $field_details['type'] ) :
			case 'user':
				$user = get_userdata( $user_id );
				if ( 'user_title' === $field_details['name'] ) :
					$value_raw_content = $user->display_name;
					$value_raw_attr    = $value_raw_content;
					if ( ! empty( $field_details['args']['link'] ) ) :
						$link              = get_author_posts_url( $user_id );
						$value_raw_content = '<a href="' . esc_url( $link ) . '">' . $value_raw_content . '</a>';
					endif;
				elseif ( 'user_email' === $field_details['name'] ):
					$value_raw_content = $user->user_email;
				elseif ( 'user_url' === $field_details['name'] ):
					$value_raw_content = $user->user_url;
				endif;
				break;
			case 'custom_field':
				if( 'image' === $field_details['name'] ) :
					$args = apply_filters( 'user_directory_get_avatar_args', array() );
					$value_raw_content = get_avatar( $user_id, '144', '', '', $args );
				else:
					$value_raw_content = get_user_meta( $user_id, $field_details['name'], true );
				endif;
				break;
			case 'taxonomy':
				$taxonomies = wp_get_object_terms( $user_id, $field_details['name'] );

				$taxonomy_names = $user_taxonomies_ids = array();

				foreach ( $taxonomies as $taxonomy ) {
					$taxonomy_names[]      = $taxonomy->name;
					$user_taxonomies_ids[] = $taxonomy->term_id;
				}

				$value_raw_content = implode( ', ', $taxonomy_names );
				$value_raw_attr    = implode( ',', $user_taxonomies_ids );
				break;
		endswitch;
	}

	if ( ! isset( $value_raw_attr ) ) {
		$value_raw_attr = false;
	}

	if ( ! isset( $value_raw_content ) ) {
		$value_raw_content = false;
	}

	if ( $value_raw_content ) {
		switch ( $field_details['value_type'] ) :
			case 'email':
				$email_data        = explode( '@', $value_raw_content );
				$value_raw_attr    = $email_data[0];
				$value_raw_content = '<a href="mailto:' . esc_attr( $value_raw_content ) . '"><i aria-hidden="true" class="dashicons dashicons-email-alt"></i>' . esc_html( $value_raw_content ) . '</a>';
				break;
		endswitch;

		switch ( $field_details['name'] ) :
			case 'user_url':
				$value_raw_content = '<a href="' . esc_attr( $value_raw_content ) . '"><i aria-hidden="true" class="dashicons dashicons-admin-site-alt"></i>' . esc_html__( 'Website', 'user_taxonomy' ) . '</a>';
				break;
		endswitch;
	}

	$value['content'] = $value_raw_content;
	$value['attr']    = $value_raw_attr;

	return apply_filters( 'user_directory_field_value', $value, $user_id, $field_details );
}