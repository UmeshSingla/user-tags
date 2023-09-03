<?php
/**
 * The template for displaying Custom User taxonomy
 *
 * @author Umesh Kumar
 * @subpackage Custom User Taxonomy Plugin
 */
get_header();
?>
	<div id="main-wrapper" class="wrapper">
		<section id="primary" class="content-area container">
			<div id="content" class="site-content row" role="main">
				<?php
				$term_id = get_queried_object_id();
				$term    = get_queried_object();

				$users = get_objects_in_term( $term_id, $term->taxonomy );

				/**
				 * Allows to filter user list before displaying it in template
				 * can be used for sorting the users as per username
				 */
				$users            = apply_filters( 'ut_template_users', $users );
				$template_content = '';
				if ( ! empty( $users ) ) {
					?>
					<div id="ut-content" class="col-12">
						<div class="ut-content__users-list">
							<?php
							foreach ( $users as $user_id ) {
								// Skip loop if user doesn't exists anymore.
								if ( ! get_user_by( 'id', $user_id ) ) {
									// Clean Up
									wp_delete_object_term_relationships( $user_id, $term->taxonomy );
									continue;
								}
								$c = '<div class="ut-content__user">' . get_avatar( $user_id, '300' );
								$c .= '<div class="ut-content__user-title"><a href="' . esc_url( get_author_posts_url( $user_id ) ) . '">' . get_the_author_meta( 'display_name', $user_id ) . '</a></div>';
                                $c .= '<div class="ut-content__user-email">' . make_clickable( get_the_author_meta( 'user_email', $user_id ) ) . '</div>';
								$c .= '<div class="ut-content__user-bio">' . wpautop( get_the_author_meta( 'description', $user_id ) ) . '</div>';
								$c .= '</div>';

								$user = apply_filters( 'ut_template_content', $c, $user_id );

								$template_content .= $user;
							}
							echo $template_content;
							?>
						</div>
					</div>
					<?php
				} else {
					$content = apply_filters( 'ut_template_content_empty', '<p>No Users found.</p>' );
					echo $content;
				}
				?>
			</div>
			<!-- #content -->
		</section><!-- #primary -->
	</div>
<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
