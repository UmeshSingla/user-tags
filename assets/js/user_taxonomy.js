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
    jQuery('body').on('click', '.delete-taxonomy', function(){
        $taxonomy_name = jQuery(this).attr('data-delname');
        $nonce = jQuery(this).closest('._wp_nonce').val();
        jQuery.ajax({
            'type'  :   'POST',
            'url'   : ajaxurl,
            'data'  : {
                action  :   'ut_delete_taxonomy',
                taxonomy_name   :   $taxonomy_name,
                nonce   :   $nonce
            },
            success :   function(resp_data){
                console.log(resp_data);
            },
            error   :   function(resp_error){
                console.log(resp_error);
            }
            
        });
    });
});