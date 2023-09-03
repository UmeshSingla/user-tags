(function ($) {
    $(document).ready(function ($) {

        //Delete Taxonomy
        $('body').on('click', '.delete-taxonomy a', function (e) {
            e.preventDefault();

            let current_elem = $(this);
            let $taxonomy_id = current_elem.attr('id');

            if ($taxonomy_id) {
                $taxonomy_id = $taxonomy_id.split('del-');
                $taxonomy_id = $taxonomy_id[1];
            }

            let $taxonomy_name = current_elem.attr('data-name');
            let $nonce = $('#delete-taxonomy-' + $taxonomy_id).val();

            if (!confirm('Delete taxonomy "' + $taxonomy_name + '"?')) {
                return false;
            }

            $.ajax({
                'type': 'POST',
                'url': ajaxurl,
                'data': {
                    action: 'ut_delete_taxonomy',
                    delete_taxonomy: $taxonomy_name,
                    nonce: $nonce
                },
                success: function (resp_data) {
                    if (typeof resp_data.success !== 'undefined' && resp_data.success) {

                        const $message = '<div id="message" class="notice notice-success below-h2 is-dismissible"><p>Taxonomy deleted.</p></div>';
                        $('.user-taxonomies-page h2:first').after($message);

                        current_elem.parents().eq(3).remove();

                        if (!$('#the-taxonomy-list tr').length) {
                            const $no_taxonomies = '<tr class="no-items"><td class="colspanchange" colspan="5">No Taxonomy found.</td></tr>';
                            $('#the-taxonomy-list').append($no_taxonomies);
                        }

                    } else {
                        const $error_div = '<div id="message" class="notice notice-error below-h2"><p>Taxonomy not deleted.</p></div>';
                        $('.user-taxonomies-page h2:first').after($error_div);
                    }
                },
                error: function (resp_error) {
                    console.log(resp_error);
                }

            });
        });
    });

})(jQuery);
