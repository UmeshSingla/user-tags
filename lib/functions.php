<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function ut_taxonomy_name($name = ''){
    if(empty($name)) return;
    $taxonomy_name = str_replace ( '-', '_', str_replace(' ', '_', strtolower($name) ) );
    $taxonomy_slug = 'wp_user_' . $taxonomy_name;
    $taxonomy_slug = strlen($taxonomy_slug) > 32 ? substr($taxonomy_slug, 0, 32 ) : $taxonomy_slug;
    return esc_html( ut_stripallslashes( $taxonomy_slug ) );
}
add_filter( 'taxonomy_template', 'get_custom_taxonomy_template' );
function get_custom_taxonomy_template($template = '') {
    
    $taxonomy = get_query_var('taxonomy');

    if (strpos($taxonomy,'wp_user_') !== false) {
        $taxonomy_template = WP_UT_TEMPLATES ."user-taxonomy-template.php";
        $file_headers = @get_headers($taxonomy_template);
        if( $file_headers[0] != 'HTTP/1.0 404 Not Found'){
           return $taxonomy_template;
        }
        
    }
   return $template; 
}
//Shortcode for Tags UI in frontend
function wp_ut_tag_box(){
    $user_id = get_current_user_id();
    $taxonomies = get_object_taxonomies('user', 'object');
    wp_nonce_field('user-tags', 'user-tags'); ?>
    <form name="user-tags" action="" method="post">
        <ul class="form-table user-profile-taxonomy user-taxonomy-wrapper"><?php
            foreach( $taxonomies as $key=>$taxonomy):
                // Check the current user can assign terms for this taxonomy
                if(!current_user_can($taxonomy->cap->assign_terms)){ continue; }
                // Get all the terms in this taxonomy
                $terms	= wp_get_object_terms($user_id, $taxonomy->name);
                $num = 0; $html = ''; $user_tags = '';
                if(!empty($terms)){ 
                    foreach($terms  as $term ){
                        $user_tags[] = $term->name;
                        $term_url = site_url().'/'.$taxonomy->rewrite['slug'].'/'.$term->slug;
                        $html .="<div class='tag-hldr'>";
                        $html .= '<span><a id="user_tag-'.$taxonomy->name.'-'.$num. '" class="ntdelbutton">x</a></span>&nbsp;<a href="'.$term_url.'" class="term-link">'.$term->name.'</a>';
                        $html .="</div>";
                        $num++;
                    }
                    $user_tags = implode(',', $user_tags);
                } ?>
                <li>
                    <label for="new-tag-user_tag_<?php echo $taxonomy->name; ?>"><?php _e("{$taxonomy->labels->singular_name}")?></label>
                    <div class="taxonomy-wrapper">
                        <input type="text" id="new-tag-user_tag_<?php echo $taxonomy->name; ?>" name="newtag[user_tag]" class="newtag form-input-tip float-left hide-on-blur" size="16" autocomplete="off" value="">
                        <input type="button" class="button tagadd float-left" value="Add">
                        <p class="howto"><?php _e('Separate tags with commas', WP_UT_TRANSLATION_DOMAIN ); ?></p>
                        <div class="tagchecklist"><?php echo $html; ?></div>
                        <input type="hidden" name="user-tags[<?php echo $taxonomy->name; ?>]" id="user-tags-<?php echo $taxonomy->name; ?>" value="<?php echo $user_tags; ?>" />
                    </div>
                </li><?php
                endforeach; ?>
            </ul> 
            <input type="submit" name="update-user-tags" class="button tagadd float-left" value="Update">
    </form><?php
}
//shortcode
add_shortcode( 'user_tags', 'wp_ut_tag_box' );
add_action('in_admin_footer', 'wp_ut_ajax_url');
add_action('wp_footer', 'wp_ut_ajax_url');
function wp_ut_ajax_url(){?>
    <script type="text/javascript">
        $wp_ut_ajax_url = <?php echo json_encode(admin_url('admin-ajax.php')); ?>
    </script><?php
}
function ut_stripallslashes($string) { 
    while(strchr($string,'\\')) { 
        $string = stripslashes($string); 
    }
    return $string;
}