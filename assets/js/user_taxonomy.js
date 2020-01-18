/**
 * Check for empty fields
 */
function validate_form(parameters) {
	$empty_fields = new Array();
	$i = 0;
	jQuery('#editusertaxonomy input, #editusertaxonomy textarea').each(function () {
		if (!jQuery(this).is('textarea')) {
			$input_value = jQuery(this).val();
		}
		if (!$input_value && jQuery(this).attr('data-required')) {
			jQuery(this).parents().eq(1).addClass('form-invalid');
			$empty_fields[$i] = jQuery(this).attr('name');
			$i++;
		}
	});
	return $empty_fields;
}
/**
 * Creates Tag from input value in the form
 * @param $tag_input
 * @param $taxonomy_name
 * @param $term
 * @param $tag_html
 */
function insert_tags($tag_input, $taxonomy_name, $term, $tag_html) {
	//Fetch current values and split from comma to array
	$user_tag_input = jQuery('#user-tags-' + $taxonomy_name);
	$user_tag_input_val = $user_tag_input.val();
	if ($user_tag_input_val) {
		$user_tag_input_val_array = $user_tag_input_val.split(',');
		$insert = true;
		for ($i = 0; $i < $user_tag_input_val_array.length; $i++) {
			if (jQuery.trim($user_tag_input_val_array[$i]) == jQuery.trim($term)) {
				$insert = false;
				break;
			}
		}
		if ($insert) {
			$user_tag_input.val($user_tag_input_val + ', ' + $term);
			$tag_checklist.append($tag_html);
		}
	} else {
		$user_tag_input.val($term);
		$tag_checklist.append($tag_html);
	}
	$tag_input.val('');
	jQuery('body .tag-suggestion').remove();
}
jQuery(document).ready(function ($) {
	/**
	 * Checks for Empty fields on Edit Taxonomy form submission
	 */
	$('body').on('submit', '#editusertaxonomy', function (e) {
		$empty_fields = validate_form();
		if (!$empty_fields.length) {
			return true;
		} else {
			return false;
		}
	});
	$('#editusertaxonomy input').on('keyup', function () {
		if (jQuery(this).parents().eq(1).hasClass('form-invalid')) {
			$input_value = $(this).val();
			if ($input_value) {
				$(this).parents().eq(1).removeClass('form-invalid');
			}
		}
	});
	//Delete Taxonomy
	$('body').on('click', '.delete-taxonomy a', function (e) {
		e.preventDefault();
		if (!confirm("Are you sure, you want to delete the taxonomy?")) {
			return false;
		}
		$this = $(this);
		$taxonomy_id = $this.attr('id');
		if ($taxonomy_id) {
			$taxonomy_id = $taxonomy_id.split('-');
			$taxonomy_id = $taxonomy_id[1];
		}
		$taxonomy_name = $this.attr('data-name');
		$nonce = $('#delete-taxonomy-' + $taxonomy_id).val();
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
					$message = '<div id="message" class="updated below-h2"><p>Taxonomy deleted.</p></div>';
					$('.user-taxonomies-page h2:first').after($message);
					$this.parents().eq(3).remove();
					setInterval(function () {
						$('.user-taxonomies-page #message.below-h2').hide('slow', function () {
							$('.user-taxonomies-page #message.below-h2').remove();
						});
					}, 3000);
					if (!$('#the-taxonomy-list tr').length) {
						$no_taxonomies = '<tr class="no-items"><td class="colspanchange" colspan="5">No Taxonomies found.</td></tr>';
						$('#the-taxonomy-list').append($no_taxonomies);
					}
				} else {
					$error_div = '<div id="message" class="error below-h2"><p>Taxonomy not deleted.</p></div>';
					$('.user-taxonomies-page h2:first').after($error_div);
					setInterval(function () {
						$('.user-taxonomies-page #message.below-h2').hide('slow', function () {
							$('.user-taxonomies-page #message.below-h2').remove();
						});
					}, 3000);
				}
			},
			error: function (resp_error) {
				console.log(resp_error);
			}

		});
	});
	var delay = (function () {
		var timer = 0;
		return function (callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();
	/**
	 * Fetches the tag suggestion based on user input
	 */
	$('.user-profile-taxonomy').on('keyup', '.newtag', function () {

		$this = $(this);
		$tag_input_value = $this.val().split(',');
		$tag_input_value = $.trim($tag_input_value[$tag_input_value.length - 1]);

		if ($tag_input_value.length >= 2) {
			delay(function () {
				$tag_id = $this.attr('id');
				$tag_name = $tag_id.split('new-tag-user_tag_');
				$.ajax({
					'type': 'post',
					'url': wp_ut_ajax_url,
					'data': {
						'action': 'ut_load_tag_suggestions',
						'tag': 'user_tag',
						'q': $tag_input_value,
						'taxonomy': $tag_name[1],
						'nonce': jQuery('#user-tags').val()
					},
					'success': function (res_data) {
						$('.tag-suggestion').remove();
						if (res_data != '' && res_data != 0) {
							$this.siblings('p.howto').before(res_data);
						}
					},
					'error': function (res_error) {
						console.log(res_error);
					}
				});
			}, 200);
		}
		else {
			jQuery('.tag-suggestion').remove();
		}
	});
	//Tags UI
	$('body').on('click', '.tag-suggestion li', function () {
		$this = $(this);
		$taxonomy_name = '';
		$term = $this.html();
		$tag_checklist = $this.parent().siblings('.tagchecklist');
		$num = ( $tag_checklist.length );

		$taxonomy_id = $this.parent().siblings('.newtag').attr('id');
		if ($taxonomy_id) {
			$taxonomy_id = $taxonomy_id.split('new-tag-user_tag_');
			$taxonomy_name = $taxonomy_id[1];
		}
		$tag_html = '<div class="tag-hldr"><span><a id="user_tag-' + $taxonomy_name + '-check-num-' + $num + '" class="ntdelbutton">x</a></span>&nbsp;<a href="#" class="term-link">' + $term + '</a></div';
		//Taxonomy Name
		insert_tags($this.parent().siblings('.newtag'), $taxonomy_name, $term, $tag_html);
	});
	$(document).mouseup(function (e) {
		var container = $(".hide-on-blur");

		if (!container.is(e.target) && container.has(e.target).length === 0) {
			$('.tag-suggestion').remove();
		}
	});

	/**
	 * Handles the Add Tag button click, Takes the value from input and add it to the tags section
	 */
	$('body').on('click', '.button.tagadd', function () {
		$this = $(this);
		$sibling = $this.siblings('.newtag');
		$newtag_val = $sibling.val();
		if (!$newtag_val) return;
		$newtag_val = $newtag_val.split(',');

		$taxonomy_name = $sibling.attr('id').split('new-tag-user_tag_');
		$taxonomy_name = $taxonomy_name[1];
		$tag_checklist = $this.siblings('.tagchecklist');
		for ($i = 0; $i < $newtag_val.length; $i++) {
			$num = ( $tag_checklist.length );
			$tag_html = '<div class="tag-hldr"><span><a id="post_tag-' + $taxonomy_name + '-check-num-' + $num + '" class="ntdelbutton">x</a></span>&nbsp;<a href="#" class="term-link">' + $newtag_val[$i] + '</a></div>';
			insert_tags($sibling, $taxonomy_name, $newtag_val[$i], $tag_html);
		}
		$('.tag-suggestion').remove();
	});
	//Delete Tag
	$('body').on('click', '.ntdelbutton', function () {
		$this = $(this);
		$term = $this.parent().next('.term-link').html();
		$tags_input = $this.parents().eq(2).siblings('input[type="hidden"]').val();
		$tags_input = $tags_input.split(',');

		$tags_input = $.grep($tags_input, function (value) {
			return value != $term;
		});

		$this.parents().eq(2).siblings('input[type="hidden"]').val($tags_input.join(','));
		$this.parent().next('.term-link').remove();
		$this.parent().parent().remove();
	});
	$('body').on('click', '.term-link', function (e) {
		if ($(this).attr('href') != '#') return true;
		else {
			e.preventDefault();
			return false;
		}
	});
	var doing_ajax = false;
	//Most Popular tag list
	$('body').on('click', '.tagcloud-link.user-taxonomy', function (e) {
		e.preventDefault();
		if (doing_ajax) {
			return false;
		}
		if ($(this).parent().find('.the-tagcloud').length) {
			$(this).parent().find('.the-tagcloud').remove();
			return true;
		}
		doing_ajax = true;
		var id = $(this).attr('id');
		var tax = id.substr(id.indexOf("-") + 1);
		$.post(ajaxurl, {'action': 'get-tagcloud', 'tax': tax}, function (r, stat) {
			doing_ajax = false;
			if (0 === r || 'success' != stat)
				r = wpAjax.broken;

			r = jQuery('<p id="tagcloud-' + tax + '" class="the-tagcloud">' + r + '</p>');
			$('a', r).click(function () {
				$this = $(this);
				$taxonomy_name = '';
				$term = $this.html();
				$tag_checklist = $this.parents().eq(1).siblings('.tagchecklist');
				$sibling = $this.parents().eq(1).siblings('.newtag');
				if ($tag_checklist.length === 0) {
					$tag_checklist = $this.parents().eq(1).siblings('.taxonomy-wrapper').find('.tagchecklist');
				}
				if ($sibling.length === 0) {
					$sibling = $this.parents().eq(1).siblings('.taxonomy-wrapper').find('.newtag');
				}
				$num = ( $tag_checklist.length );

				$taxonomy_id = $sibling.attr('id');
				if ($taxonomy_id) {
					$taxonomy_id = $taxonomy_id.split('new-tag-user_tag_');
					$taxonomy_name = $taxonomy_id[1];
				}
				$tag_html = '<div class="tag-hldr"><span><a id="user_tag-' + $taxonomy_name + '-check-num-' + $num + '" class="ntdelbutton">x</a></span>&nbsp;<a href="#" class="term-link">' + $term + '</a></div';
				//Taxonomy Name
				insert_tags($sibling, $taxonomy_name, $term, $tag_html);
				return false;
			});

			$('#' + id).after(r);
		});
	});
	//Remove notices
	setInterval(function () {
		$('#message.below-h2').hide('slow', function () {
			$('.user-taxonomies-page #message.below-h2').remove();
		});
	}, 3000);
	// User Taxonomy Filters
	$('.users-php select.ut-taxonomy-filter').each(function () {
		if ($(this).val() != '') {
			$('select.ut-taxonomy-filter').not(this).prop('disabled', true);
		}
	});

	$('.users-php').on('change', 'select.ut-taxonomy-filter', function () {
		if ($(this).val() == '') {
			$('select.ut-taxonomy-filter').prop('disabled', false);
		} else {
			$('select.ut-taxonomy-filter').not(this).prop('disabled', true);
		}
	});
	//Load Terms in dropdown for the selected taxonomy
	$('#ut-taxonomy-filter').on('change', function(){
		var sel_tax = $(this).val();
		if( sel_tax == '' ) {
			return false;
		}
		//We got the taxonomy, lets load the options

	});
});
