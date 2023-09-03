<?php
if ( ! class_exists( 'UserDirectoryData' ) ) {
	class UserDirectoryData {
		private static $id = 0;

		var $atts;

		var $taxonomy_filters = null;
		var $fields = null;


		function __construct( $atts ) {
			// Bumps the id on every init so each directory has unique ID.
			self::$id ++;

			// Sets block attribute for easy access inside class.
			$this->atts = $atts;
		}

		function get_directory_id() {
			return 'user-dir-' . self::$id;
		}

		function get_taxonomy_filters() {
			if ( $this->taxonomy_filters !== null ) {
				return $this->taxonomy_filters;
			}

			$this->taxonomy_filters = array();

			if ( ! isset( $this->atts['filters'] ) ) {
				return $this->taxonomy_filters;
			}

			//Checks if any "tax_" filters exist.
			$filters    = $this->get_filters();
			$tax_exists = false;
			foreach ( $filters as $filter ) {
				if ( substr( $filter, 0, 4 ) === 'tax_' ) {
					$tax_exists = true;
					break;
				}
			}

			if ( ! $tax_exists ) {
				return $this->taxonomy_filters;
			}

			$taxonomies = get_registered_user_taxonomies();
			foreach ( $taxonomies as $taxonomy ) {

				$tax_name = $taxonomy['name'];
				if ( in_array( 'tax_' . $tax_name, $filters ) ) {
					$parent_id = 0;

					if ( in_array( 'tax_childs_' . $tax_name, $filters ) ) {
						$terms = get_terms(
							$tax_name,
							array(
								'hide_empty' => true,
								'parent'     => $parent_id,
							)
						);
						foreach ( $terms as $term ) {
							$field_key = 'tax_' . $tax_name . '-' . $term->term_id;

							if ( get_term_children( $term->term_id, $tax_name ) ) {
								$this->taxonomy_filters[ $field_key ] = array(
									'label'      => $term->name,
									'taxonomy'   => $tax_name,
									'parent_id'  => $term->term_id,
									'select_id'  => $this->get_directory_id() . '-tax-' . $tax_name . '-' . $term->term_id,
									'field_name' => 'user-directory-field-' . $tax_name . '-' . $term->term_id,
								);
							}
						}
					} else {
						// Use parent name if we are filtering.
						if ( $parent_id ) {
							$term = get_term( $parent_id, $tax_name );

							$label      = $term->name;
							$field_key  = 'tax_' . $tax_name . '-' . $term->term_id;
							$field_name = $tax_name . '-' . $term->term_id;
						} else {
							$label      = $taxonomy['label'];
							$field_key  = 'tax_' . $tax_name;
							$field_name = $tax_name;
						}
						if ( ! $parent_id || get_term_children( $parent_id, $tax_name ) ) {
							$this->taxonomy_filters[ $field_key ] = array(
								'label'      => $label,
								'taxonomy'   => $tax_name,
								'parent_id'  => $parent_id,
								'select_id'  => $this->get_directory_id() . '-tax-' . $tax_name,
								'field_name' => 'user-directory-field-' . $field_name,
							);
						}
					}
				}
			}

			return $this->taxonomy_filters;
		}

		/**
		 * Displayed on front-end. Fields list is used to display on front-end
		 * as well as for adding values for the filters
		 *
		 * If a field has been added as filter but not availbale in fields list,
		 * it's included as hidden field to eb able to filter the list using
		 * the value.
		 *
		 * @return array Fields list
		 */
		function get_fields() {
			if ( ! empty( $this->fields ) ) :
				return $this->fields;
			endif;

			$enabled_fields = array();
			if ( isset( $this->atts['fields'] ) ) :
				$enabled_fields = $this->atts['fields'];
			endif;

			$this->fields = array();

			$available_fields = User_Tags_User_Directory::get_instance()->get_user_fields();
			foreach ( $available_fields as $field_key => $field_details ) {
				//Include the field if specifically enabled, or it is set to be included by default.
				$field_enabled = in_array( $field_key, $enabled_fields ) || $field_details['default'];

				if ( $field_enabled && 'taxonomy' !== $field_details['type'] ) :
					$field_name = 'user-directory-field-' . $field_details['name'];

					$this->fields[ $field_key ] = array_merge(
						$field_details,
						array(
							'field_name' => $field_name,
							'hidden'     => false,
						)
					);
				endif;
			}

			$taxonomy_filters = $this->get_taxonomy_filters();
			// Process taxonomies.
			foreach ( $taxonomy_filters as $filter_key => $filter ) {
				$field_enabled = in_array( 'tax_' . $filter['taxonomy'], $enabled_fields );

				$this->fields[ $filter_key ] = array_merge(
					$available_fields[ 'tax_' . $filter['taxonomy'] ],
					array(
						'label'      => $filter['label'],
						'field_name' => $filter['field_name'],
						'args'       => array( 'parent_id' => $filter['parent_id'] ),
						'hidden'     => $field_enabled ? false : true,
					)
				);
			}

			return $this->fields;
		}

		/**
		 * Output data for list.js
		 * @return array Field JS data
		 */
		function get_fields_js() {

			$fields_js = array( array( 'data' => array( 'entry-id', 'entry-parent-ids' ) ) );
			$fields    = $this->get_fields();
			foreach ( $fields as $field ) {
				if ( isset( $field['type'] ) && 'taxonomy' === $field['type'] ) {
					$fields_js[] = array(
						'name' => $field['field_name'],
						'attr' => 'data-value',
					);
				} elseif ( isset( $field['name'] ) && 'user_title' === $field['name'] ) {
					$fields_js[] = array(
						'name' => $field['field_name'],
						'attr' => 'data-value',
					);
				} elseif ( isset( $field['value_type'] ) && 'email' === $field['value_type'] ) {
					$fields_js[] = array(
						'name' => $field['field_name'],
						'attr' => 'data-value',
					);
				} else {
					if ( isset( $field['hidden'] ) && $field['hidden'] ) {
						$fields_js[] = array(
							'name' => $field['field_name'],
							'attr' => 'data-value',
						);
					} else {
						$fields_js[] = $field['field_name'];
					}
				}
			}

			return apply_filters( 'user_directory_get_fields_js', $fields_js, $this->atts );
		}

		function get_users() {

			$args = array(
				'role__in' => $this->atts['role'],
				'number'   => $this->get_users_limit(),
				'orderby'  => array( 'display_name' => 'ASC' ),
				'fields'   => 'ID',
			);

			/**
			 * Allows to filter the entries args
			 */
			$args = apply_filters( 'user_directory_get_users_args', $args, $this->atts );

			$users = get_users( $args );

			return apply_filters( 'user_directory_get_users', $users, $args, $this->atts );
		}

		function get_filters() {
			$filters = array();
			if ( isset( $this->atts['filters'] ) ) {
				$filters = $this->atts['filters'];
			}

			return $filters;
		}

		function get_users_per_page( $total = false ) {
			$users_per_page = false;
			if ( isset( $this->atts['users_per_page'] ) && $this->atts['users_per_page'] ) {
				if ( ! $total || $total > $this->atts['users_per_page'] || ( defined( 'REST_REQUEST' ) && $total >= $this->atts['users_per_page'] ) ) {
					$users_per_page = $this->atts['users_per_page'];
				}
			}

			return $users_per_page;
		}

		function get_users_limit() {
			$users_limit = apply_filters( 'cp_dir_get_entries_limit', 500, $this->atts );

			if ( defined( 'REST_REQUEST' ) ) {
				$users_limit = $this->get_users_per_page();
			}

			return $users_limit;
		}
	}
}
