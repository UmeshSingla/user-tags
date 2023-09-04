<?php
/**
 * Handles Display/Assignment of taxonomies on users profile
 */

if ( ! class_exists( 'User_Tags_Profile ' ) ) :
	/**
	 * Class definition
	 */
	class User_Tags_Profile {

		/**
		 * Show/Update taxoomies on user profile page.
		 */
		public function __construct() {
			// User Profiles.
			add_action( 'show_user_profile', array( $this, 'user_profile' ) );
			add_action( 'edit_user_profile', array( $this, 'user_profile' ) );

			add_action( 'personal_options_update', array( $this, 'save_taxonomy_terms' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_taxonomy_terms' ) );

			//phpcs:ignore
			//add_filter( 'sanitize_user', array( $this, 'restrict_username' ) );

			// Clear up related tags and taxonomies, when a user is deleted.
			add_action( 'deleted_user', array( $this, 'update_user_list' ) );
		}

		/**
		 * Add the taxonomies to the user view/edit screen
		 *
		 * @param Object $user - The user of the view/edit screen.
		 */
		public function user_profile( $user ) {
			if ( ! current_user_can( 'edit_users' ) && ! current_user_can( 'edit_user', $user->ID ) ) :
				return;
			endif;
			?>
            <h3><?php esc_html_e( 'User Taxonomies', 'user_taxonomy' ); ?></h3>
            <div class="user-taxonomy-wrapper">
				<?php
				wp_nonce_field( 'user-tags', 'user-tags' );

				$user_taxonomies = get_user_taxonomies();
				foreach ( $user_taxonomies as $taxonomy ) {

					if ( taxonomy_exists( $taxonomy['slug'] ) ) :
						$taxonomy = get_taxonomy( $taxonomy['slug'] );
						?>
                        <table class="form-table user-profile-taxonomy">
                            <tr>
                                <th>
                                    <label for="new-tag-user_tag_<?php echo esc_attr( $taxonomy->name ); ?>">
										<?php echo esc_html( $taxonomy->labels->singular_name ); ?>
                                    </label>
                                </th>
                                <td>
									<?php
									wp_terms_checklist(
										$user->ID,
										array(
											'taxonomy' => $taxonomy->name,
											'walker'   => 'Walker_Category_Checklist',
										)
									);
									?>
                                </td>
                            </tr>
                        </table>
					<?php
					endif;
				} // Taxonomies
				?>
            </div>
			<?php
		}

		/**
		 * Save the custom user taxonomies when saving a users profile
		 *
		 * @param Integer $user_id - The ID of the user to update.
		 *
		 * @return bool|void
		 */
		public function save_taxonomy_terms( $user_id ) {

			check_admin_referer( 'update-user_' . $user_id );

			// Check if the current user can edit this user.
			if ( empty( $_POST['tax_input'] ) || ! current_user_can( 'edit_user', $user_id ) ) {
				return;
			}

			//phpcs:ignore
			$input_tags = wp_unslash( $_POST['tax_input'] );
			foreach ( $input_tags as $taxonomy => $taxonomy_terms ) {

				$taxonomy       = sanitize_key( $taxonomy );
				$taxonomy_terms = array_map( 'absint', $taxonomy_terms );

				// Save the data.
				if ( ! empty( $taxonomy_terms ) ) :
					wp_set_object_terms( $user_id, $taxonomy_terms, $taxonomy, false );
				else :
					// No terms left, delete all terms.
					wp_set_object_terms( $user_id, array(), $taxonomy, false );
				endif;
			}
		}

		/**
		 * Usernames can't match any of our user taxonomies
		 *
		 * @param string $username
		 *
		 * @return string
		 */
		public function restrict_username( $username ) {

			$user_taxonomies = get_user_taxonomies();
			if ( isset( $user_taxonomies[ $username ] ) ) {
				return '';
			}

			return $username;
		}

		/**
		 * Updates the users list for a tag or a taxonomy, when a user is deleted
		 *
		 * @param $user_id
		 */
		public function update_user_list( $user_id ) {

			$taxonomies    = get_object_taxonomies( 'user', 'object' );
			$taxonomy_list = array();
			foreach ( $taxonomies as $key => $taxonomy ) {
				$taxonomy_list[] = $key;
			}
			// Delete the relation for a user.
			if ( ! empty( $taxonomy_list ) && is_array( $taxonomy_list ) ) {
				wp_delete_object_term_relationships( $user_id, $taxonomy_list );
			}
		}
	}

	new User_Tags_Profile();
endif;