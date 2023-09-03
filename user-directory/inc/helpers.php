<?php
/**
 * Gets the value of single field.
 *
 * @param [numeric] $entry_id Post ID.
 * @param [array] details of the field.
 *
 * @return string
 */
function user_directory_get_field_value( $entry_id, $field_details ) {

	$value = array();

	if ( isset( $field_details['type'] ) ) {
		switch ( $field_details['type'] ) :
			case 'user':
				if ( 'user_title' === $field_details['name'] ) :
					$user              = get_userdata( $entry_id );
					$value_raw_content = $user->display_name;
					$value_raw_attr    = $value_raw_content;
					if ( ! empty( $field_details['args']['link'] ) ) :
						$link = get_author_posts_url( $entry_id );
						$value_raw_content = '<a href="' . esc_url( $link ) . '">' . $value_raw_content . '</a>';
					endif;
				endif;
				break;
			case 'custom_field':
				$value_raw_content = get_user_meta( $entry_id, $field_details['name'], true );
				break;
			case 'taxonomy':
				// For some reason "fields => 'id=>name'" is not working here.
				$args = array( 'hierarchical' => true );
				if ( isset( $field_details['args']['parent_id'] ) ) {
					// Includes all children and parent so it works when parent is not selected in entry.
					$term_include = get_term_children( $field_details['args']['parent_id'], $field_details['name'] );
					//looks like this is no longer needed?
					//$term_include[]  = $field_details['args']['parent_id'];
					$args['include'] = $term_include;
				}
				$entry_taxonomies       = wp_get_object_terms( $entry_id, $field_details['name'], $args );
				$entry_taxonomies_names = $entry_taxonomies_ids = array();
				foreach ( $entry_taxonomies as $entry_taxonomy ) {
					$entry_taxonomies_names[] = $entry_taxonomy->name;
					$entry_taxonomies_ids[]   = $entry_taxonomy->term_id;
					if ( $entry_taxonomy->parent && ! in_array( $entry_taxonomy->parent, $entry_taxonomies_ids ) ) :
						$entry_taxonomies_ids[] = $entry_taxonomy->parent;
					endif;
				}

				// If entry is checking in children and finds some, lets say it is also part of parent.
				if ( isset( $field_details['args']['parent_id'] ) && $entry_taxonomies_ids ) :
					$entry_taxonomies_ids[] = $field_details['args']['parent_id'];
				endif;

				$value_raw_content = implode( ', ', $entry_taxonomies_names );
				$value_raw_attr    = implode( ',', $entry_taxonomies_ids );
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
				$value_raw_content = '<a href="mailto:' . esc_attr( $value_raw_content ) . '">' . esc_html( $value_raw_content ) . '</a>';
				break;
		endswitch;
	}

	$value['content'] = $value_raw_content;
	$value['attr']    = $value_raw_attr;

	return $value;
}