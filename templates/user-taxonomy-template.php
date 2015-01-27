<?php
/**
 * The template for displaying Custom User taxonomies
 * @author Umesh Kumar
 * @subpackage Custom User Taxonomy Plugin
 */
get_header(); ?>
	<section id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<header class="page-header">
				<?php $taxonomy = get_taxonomy( get_query_var( 'taxonomy' ) );
				$term           = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); ?>
				<h1 class="page-title"> <?php
					echo apply_filters( 'ut_template_heading', sprintf( '%s: %s', __( $taxonomy->labels->name, WP_UT_TRANSLATION_DOMAIN ), __( $term->name, WP_UT_TRANSLATION_DOMAIN ) ) );
					?> </h1>
			</header> <?php
			$term_id = get_queried_object_id();
			$term    = get_queried_object();

			$users            = get_objects_in_term( $term_id, $term->taxonomy );

			/**
			 * Allows to filter user list before displaying it in template
			 * can be used for sorting the users as per username
			 */
			$users = apply_filters( 'ut_template_users', $users );
			$template_content = '';
			if ( ! empty( $users ) ) {
				?>
				<div id="ut-content">
					<ul class="ut-term-users-list"> <?php
						foreach ( $users as $user_id ) {
							$c = '
                            <li class="ut-user-entry">' .
							     get_avatar( $user_id, '96' ) . '
                                <h2 class="ut-user-title"><a href="' . esc_url( get_author_posts_url( $user_id ) ) . '">' . get_the_author_meta( 'display_name', $user_id ) . '</a></h2>
                                <div class="ut-description">' .
							     wpautop( get_the_author_meta( 'description', $user_id ) ) . '
                                </div>
                            </li>';
							$template_content .= apply_filters( 'ut_tepmplate_content', $c, $user_id );
						}
						echo $template_content; ?>
					</ul>
				</div>
			<?php
			} else {
				$content = "<p>No Users found.</p>";
				echo apply_filters( 'ut_template_content_empty', __( $content ) );
			} ?>
		</div>
		<!-- #content -->
	</section><!-- #primary --> <?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
