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
jQuery(document).ready( function(){
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
});