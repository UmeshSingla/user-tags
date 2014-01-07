<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function ut_taxonomy_name($name){
    if(empty($name)) return;
    $taxonomy_name = str_replace ( '-', '_', str_replace(' ', '_', strtolower($name) ) );
    $taxonomy_slug = 'rce_user_' . $taxonomy_name;
    return $taxonomy_slug;
}
function top_tags( $taxonomy = FALSE ) {
        if( !$taxonomy){
            $taxonomy = array();
            $ut_taxonomies = get_site_option( 'ut_taxonomies' );
            if ( empty($ut_taxonomies ) || !is_array( $ut_taxonomies) ) return;
            foreach ( $ut_taxonomies as $ut_taxonomy ){
                $taxonomy[] = ut_taxonomy_name($ut_taxonomy['name']);
            }
        }
        $tags = get_terms($taxonomy, array(
                'orderby'    => 'count',
                'hide_empty' => 0
         ));
        if (empty($tags))
                return;
        $counts = $tag_links = array();
        foreach ( (array) $tags as $tag ) {
                $counts[$tag->name] = $tag->count;
                $tag_links[$tag->name] = get_tag_link( $tag->term_id );
        }
        asort($counts);
        $counts = array_reverse( $counts, true );
        $i = 0;
        $output = '';
        foreach ( $counts as $tag => $count ) {
                $i++;
                $tag_link = esc_url($tag_links[$tag]);
                $tag = str_replace(' ', '&nbsp;', esc_html( $tag ));
                if($i < 11){
                        $output .= "<a href=\"$tag_link\">$tag ($count)</a>";
                }else{
                    break;
                }
        }
        return $output;
}
add_filter( 'taxonomy_template', 'get_custom_taxonomy_template' );
function get_custom_taxonomy_template($template) {
    
    $taxonomy = get_query_var('taxonomy');

    if (strpos($taxonomy,'rce_user_') !== false) {
        $taxonomy_template = RCE_UT_TEMPLATES ."user-taxonomy-template.php";
        $file_headers = @get_headers($taxonomy_template);
        if( $file_headers[0] != 'HTTP/1.0 404 Not Found'){
           return $taxonomy_template;
        }
        
    }
   return $template; 
}
//Shortcode for Tags UI in frontend
function rce_ut_tag_box(){
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
                        <p class="howto"><?php _e('Separate tags with commas', RCE_UT_TRANSLATION_DOMAIN ); ?></p>
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
add_shortcode( 'user_tags', 'rce_ut_tag_box' );
add_action('wp_footer', 'rce_ut_ajax_url');
function rce_ut_ajax_url(){?>
    <script type="text/javascript">
        $rce_ut_ajax_url = <?php echo json_encode(admin_url('admin-ajax.php')); ?>
    </script><?php
}
add_action('wp_loaded', 'rce_ut_process_form');
function rce_ut_process_form(){
    $user_id = get_current_user_id();
    if(isset($_POST)){
        if(empty($_POST['user-tags'])) return;
            foreach($_POST['user-tags'] as $taxonomy=>$taxonomy_terms) {
                // Check the current user can edit this user and assign terms for this taxonomy
                if(!current_user_can('edit_user', $user_id) && current_user_can($taxonomy->cap->assign_terms)) return false;

                // Save the data
                if(!empty($taxonomy_terms))
                $taxonomy_terms = array_map('trim', explode(',', $taxonomy_terms));
                $terms_updated = wp_set_object_terms($user_id, $taxonomy_terms, $taxonomy, false);
            }
    }
}