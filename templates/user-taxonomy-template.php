<?php
/**
 * The template for displaying Custom User taxonomies
 * @author Umesh Kumar
 * @subpackage Custom User Taxonomy Plugin
 */
var_dump(function_exists('get_header'));
get_header(); ?>
    <section id="primary" class="content-area">
            <div id="content" class="site-content" role="main">
                <header class="page-header">
                    <h1 class="page-title"> <?php _e( et_query_var('term').':', UT_TRANSLATION_DOMAIN); ?> </h1>
                </header> <?php
                $term_id = get_queried_object_id();
                $term = get_queried_object();

                $users = get_objects_in_term( $term_id, $term->taxonomy );

                if (!empty($users)) {
                    foreach ($users as $user_id) { ?>
                        <div class="user-entry">
                            <?php echo get_avatar(get_the_author_meta('email', $user_id), '96'); ?>
                            <h2 class="user-title"><a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>"><?php the_author_meta('display_name', $user_id); ?></a></h2>
                            <div class="description">
                                <?php echo wpautop(get_the_author_meta('description', $user_id)); ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div><!-- #content -->
    </section><!-- #primary --> <?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
