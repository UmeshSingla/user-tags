/**
 * Comment
 */
function validate_form(parameters) {
    $empty_fields = new Array();
    $i = 0;
    jQuery('#editusertaxonomy input, #editusertaxonomy textarea').each(function(){
        if( !jQuery(this).is('textarea') ){
            $input_value = jQuery(this).val();
        }
        if( !$input_value && jQuery(this).attr('data-required') ){
            jQuery(this).parents().eq(1).addClass('form-invalid');
            $empty_fields[$i] = jQuery(this).attr('name');
            $i++;
        }
    });
    return $empty_fields;
}
/**
 * Insert Tags
 * @param {type} $this
 * @param {type} $taxonomy_name
 * @param {type} $term
 * @param {type} $tag_html
 * @returns {undefined}
 */
function insert_tags($tag_input, $taxonomy_name, $term, $tag_html){
    //Fetch current values and split from comma to array
    $user_tag_input = jQuery('input[name="user-tags-'+$taxonomy_name+'"]');
    $user_tag_input_val = $user_tag_input.val();
    if($user_tag_input_val){
        $user_tag_input_val_array = $user_tag_input_val.split(',');
        console.log($user_tag_input_val_array);
        $insert = true;
        for($i=0;$i<$user_tag_input_val_array.length; $i++){
            if( jQuery.trim( $user_tag_input_val_array[$i] ) == jQuery.trim( $term ) ){
                $insert = false;
                break;
            }
        }
        if($insert){
            $user_tag_input.val( $user_tag_input_val + ', ' + $term );
            $tag_checklist.append($tag_html);
        }
    }else{
        $user_tag_input.val( $term );
        $tag_checklist.append($tag_html);
    }
    $tag_input.val('');
    jQuery('body .tag-suggestion').remove();
}
jQuery(document).ready( function($){
    jQuery('body').on('submit', '#editusertaxonomy', function(e){
        $empty_fields = validate_form();
        if($empty_fields.length == 0){
            return true;
        }else{
            return false;
        }
    });
    jQuery('#editusertaxonomy input').on( 'keyup', function(){
        if ( jQuery(this).parents().eq(1).hasClass('form-invalid') ){
            $input_value = jQuery(this).val();
            if( $input_value ){
                 jQuery(this).parents().eq(1).removeClass('form-invalid');
            }
        }
    });
    //Delete Taxonomy
    jQuery('body').on('click', '.delete-taxonomy a', function(e){
        e.preventDefault();
        $this = jQuery(this);
        $taxonomy_id = $this.attr('id');
        if($taxonomy_id){
            $taxonomy_id = $taxonomy_id.split('-');
            $taxonomy_id = $taxonomy_id[1];
        }
        $taxonomy_name = $this.attr('data-name');
        $nonce = jQuery('#delete-taxonomy-'+$taxonomy_id).val();
        jQuery.ajax({
            'type'  :   'POST',
            'url'   : ajaxurl,
            'data'  : {
                action  :   'ut_delete_taxonomy',
                taxonomy_name   :   $taxonomy_name,
                nonce   :   $nonce
            },
            success :   function(resp_data){
                if( resp_data == "deleted" ){
                    $message = '<div id="message" class="updated below-h2"><p>Taxonomy deleted.</p></div>';
                    jQuery('.user-taxonomies-page h2:first').after($message);
                    $this.parents().eq(3).remove();
                    setInterval(function(){
                        jQuery('.user-taxonomies-page #message.below-h2').hide('slow', function(){ 
                            jQuery('.user-taxonomies-page #message.below-h2').remove(); 
                        });
                    },3000);
                    if(!jQuery('#the-taxonomy-list tr').length){
                        $no_taxonomies = '<tr class="no-items"><td class="colspanchange" colspan="5">No Taxonomies found.</td></tr>';
                        jQuery('#the-taxonomy-list').append($no_taxonomies);
                    }
                }else{
                    $error_div = '<div id="message" class="error below-h2"><p>Taxonomy not deleted.</p></div>';
                    jQuery('.user-taxonomies-page h2:first').after($error_div);
                     setInterval(function(){
                        jQuery('.user-taxonomies-page #message.below-h2').hide('slow', function(){ 
                            jQuery('.user-taxonomies-page #message.below-h2').remove(); 
                        });
                    },3000);
                }
            },
            error   :   function(resp_error){
                console.log(resp_error);
            }
            
        });
    });
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
          clearTimeout (timer);
          timer = setTimeout(callback, ms);
        };
      })();
    jQuery('.user-profile-taxonomy').on('keyup', '.newtag', function(){
       
        $this = jQuery(this);
        if($this.val().length >= 2){
            delay(function(){
                    $tag_id = $this.attr('id');
                    $tag_name = $tag_id.split('new-tag-user_tag_');
                    jQuery.ajax({
                         'type' :   'post',
                         'url'  :   ajaxurl,
                         'data' :   {
                             'action'   :   'ut_load_tag_suggestions',
                             'tag'  :   'user_tag',
                             'q'    :   $this.val(),
                             'taxonomy' :   $tag_name[1],
                             'nonce'    :   jQuery('#user-tags').val()
                         },
                         'success'  :   function(res_data){
                              jQuery('.tag-suggestion').remove();
                             if(res_data != ''){
                                 $this.siblings('p.howto').before(res_data);
                             }
                         },
                         'error'    :   function(res_error){
                             console.log(res_error);
                         }
                    });
                }, 300);
        }
        else{
            jQuery('.tag-suggestion').remove();
            return;
        }
    });
    jQuery('body').on('click', '.tag-suggestion li', function(){
        $this = jQuery(this);
        $taxonomy_name = '';
        $term = $this.html();
        $tag_checklist = $this.parent().siblings('.tagchecklist');
        $num = ( $tag_checklist.length ) + 1;
        $tag_html = '<span><a id="post_tag-check-num-'+$num+ '" class="ntdelbutton">X</a>&nbsp;'+$term+'</span>';
        $taxonomy_id = $this.parent().siblings('.newtag').attr('id');
        if($taxonomy_id){
            $taxonomy_id = $taxonomy_id.split('new-tag-user_tag_');
            $taxonomy_name = $taxonomy_id[1];
        }
        
        //Taxonomy Name
        insert_tags($this.parent().siblings('.newtag'), $taxonomy_name, $term, $tag_html);
    });
    jQuery(document).mouseup(function (e) {
        var container = jQuery(".hide-on-blur");

        if (!container.is(e.target) && container.has(e.target).length === 0) {
                    jQuery('.tag-suggestion').remove();
        }
    });

    jQuery('body').on('click', '.button.tagadd', function(){
        $this = jQuery(this);
        $sibling = $this.siblings('.newtag');
        $newtag_val = $sibling.val();
        $newtag_val = $newtag_val.split(',');
 
        $taxonomy_name = $sibling.attr('id').split('new-tag-user_tag_');
        $taxonomy_name = $taxonomy_name[1];

        $tag_checklist = $this.siblings('.tagchecklist');
        for( $i=0; $i < $newtag_val.length; $i++ ){
            $num = ( $tag_checklist.length ) + 1;
            $tag_html = '<span><a id="post_tag-check-num-'+$num+ '" class="ntdelbutton">X</a>&nbsp;'+$newtag_val[$i]+'</span>';
            insert_tags( $sibling, $taxonomy_name, $newtag_val[$i], $tag_html);
        }
    });
});