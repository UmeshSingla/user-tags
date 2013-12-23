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
});