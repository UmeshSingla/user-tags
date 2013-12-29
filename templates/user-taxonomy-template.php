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
                    <?php $taxonomy = get_taxonomy(get_query_var('taxonomy')); 
                    $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy')); ?>
                    <h1 class="page-title"> <?php 
                        echo apply_filters( 'ut_template_heading', sprintf( '%s: %s', __( $taxonomy->labels->name, UT_TRANSLATION_DOMAIN ), __( $term->name , UT_TRANSLATION_DOMAIN) ), $taxonomy, $term ); 
                    ?> </h1>
                </header> <?php
                $term_id = get_queried_object_id();
                $term = get_queried_object();

                $users = get_objects_in_term( $term_id, $term->taxonomy );
                $template_content = '';
                if (!empty($users)) {
                    foreach ($users as $user_id) { 
                        $template_content .= apply_filters( 'ut_tepmplate_content','
                        <div class="user-entry">'.
                            get_avatar(get_the_author_meta('email', $user_id), '96').'
                            <h2 class="user-title"><a href="' .esc_url(get_author_posts_url($user_id)).'">'. get_the_author_meta('display_name', $user_id).'</a></h2>
                            <div class="description">'.
                                wpautop(get_the_author_meta('description', $user_id)).'
                            </div>
                        </div>', $users, $taxonomy, $term );
                     }
                echo $template_content; ?>
                <?php }else{
                    $content = "<p>No Users found.</p>";
                    echo apply_filters('ut_template_content_empty', __($content), $content, $taxonomy, $term ) ;
                } ?>
            </div><!-- #content -->
    </section><!-- #primary --> <?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
