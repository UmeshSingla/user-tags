"use strict";

/**
 * Check for empty fields
 */
function validate_form(parameters) {
  var empty_fields = new Array();
  var $i = 0;
  jQuery('#editusertaxonomy input, #editusertaxonomy textarea').each(function () {
    if (!jQuery(this).is('textarea')) {
      var _input_value = jQuery(this).val();
    }

    if (!input_value && jQuery(this).attr('data-required')) {
      jQuery(this).parents().eq(1).addClass('form-invalid');
      empty_fields[$i] = jQuery(this).attr('name');
      $i++;
    }
  });
  return empty_fields;
}
/**
 * Creates Tag from input value in the form
 * @param tag_input Input field
 * @param taxonomy_name
 * @param term
 * @param tag_html Tag markup to be displayed.
 */


function insert_tags(tag_input, taxonomy_name, term, tag_html) {
  //Fetch current values and split from comma to array
  var input = jQuery('#user-tags-' + taxonomy_name);
  var input_val = input.val();
  var tag_checklist = input.siblings('.tagchecklist'); // Append to the existing values.

  if (input_val) {
    var input_val_array = input_val.split(',');
    var insert = true;

    for (var $i = 0; $i < input_val_array.length; $i++) {
      var val = input_val_array[$i];

      if (jQuery.trim(val) == jQuery.trim(term)) {
        insert = false;
        break;
      }
    }

    if (insert) {
      input.val(input_val + ', ' + term);
      tag_checklist.append(tag_html);
    }
  } else {
    // Add a value.
    input.val(term);
    tag_checklist.append(tag_html);
  }

  tag_input.val(''); // Remove the particular suggestion.

  jQuery('body .tag-suggestion').remove();
}

jQuery(document).ready(function ($) {
  /**
   * Checks for Empty fields on Edit Taxonomy form submission
   */
  $('body').on('submit', '#editusertaxonomy', function (e) {
    var fields = validate_form();
    return !fields.length;
  });
  $('#editusertaxonomy input').on('keyup', function () {
    if (jQuery(this).parents().eq(1).hasClass('form-invalid')) {
      $input_value = $(this).val();

      if ($input_value) {
        $(this).parents().eq(1).removeClass('form-invalid');
      }
    }
  }); //Delete Taxonomy

  $('body').on('click', '.delete-taxonomy a', function (e) {
    e.preventDefault();

    if (!confirm("Delete taxonomy?")) {
      return false;
    }

    var current_elem = $(this);
    var $taxonomy_id = current_elem.attr('id');

    if ($taxonomy_id) {
      $taxonomy_id = $taxonomy_id.split('-');
      $taxonomy_id = $taxonomy_id[1];
    }

    var $taxonomy_name = current_elem.attr('data-name');
    var $nonce = $('#delete-taxonomy-' + $taxonomy_id).val();
    $.ajax({
      'type': 'POST',
      'url': ajaxurl,
      'data': {
        action: 'ut_delete_taxonomy',
        delete_taxonomy: $taxonomy_name,
        nonce: $nonce
      },
      success: function success(resp_data) {
        if (typeof resp_data.success !== 'undefined' && resp_data.success) {
          var $message = '<div id="message" class="updated below-h2"><p>Taxonomy deleted.</p></div>';
          $('.user-taxonomies-page h2:first').after($message);
          current_elem.parents().eq(3).remove();
          setInterval(function () {
            $('.user-taxonomies-page #message.below-h2').hide('slow', function () {
              $('.user-taxonomies-page #message.below-h2').remove();
            });
          }, 10000);

          if (!$('#the-taxonomy-list tr').length) {
            var $no_taxonomies = '<tr class="no-items"><td class="colspanchange" colspan="5">No Taxonomy found.</td></tr>';
            $('#the-taxonomy-list').append($no_taxonomies);
          }
        } else {
          var $error_div = '<div id="message" class="error below-h2"><p>Taxonomy not deleted.</p></div>';
          $('.user-taxonomies-page h2:first').after($error_div);
          setInterval(function () {
            $('.user-taxonomies-page #message.below-h2').hide('slow', function () {
              $('.user-taxonomies-page #message.below-h2').remove();
            });
          }, 10000);
        }
      },
      error: function error(resp_error) {
        console.log(resp_error);
      }
    });
  });

  var delay = function () {
    var timer = 0;
    return function (callback, ms) {
      clearTimeout(timer);
      timer = setTimeout(callback, ms);
    };
  }();
  /**
   * Fetches the tag suggestion based on user input
   */


  $('.user-profile-taxonomy').on('keyup', '.newtag', function () {
    var current_elem = $(this);
    var tag_input_value = current_elem.val().split(',');
    tag_input_value = $.trim(tag_input_value[tag_input_value.length - 1]);

    if (tag_input_value.length >= 2) {
      delay(function () {
        var tag_id = current_elem.attr('id');
        var tag_name = tag_id.split('new-tag-user_tag_');
        $.ajax({
          'type': 'post',
          'url': wp_ut.ajax_url,
          'data': {
            'action': 'ut_load_tag_suggestions',
            'tag': 'user_tag',
            'q': tag_input_value,
            'taxonomy': tag_name[1],
            'nonce': jQuery('#user-tags').val()
          },
          'success': function success(res) {
            $('.tag-suggestion').remove();

            if (res.success && 'undefined' !== typeof res.data) {
              current_elem.siblings('p.howto').before(res.data);
            }
          },
          'error': function error(res_error) {
            console.log(res_error);
          }
        });
      }, 200);
    } else {
      jQuery('.tag-suggestion').remove();
    }
  }); //Tags UI

  $('body').on('click', '.tag-suggestion li', function () {
    var current_elem = $(this);
    var taxonomy_name = '';
    var term = current_elem.html();
    var tag_checklist = current_elem.parent().siblings('.tagchecklist');
    var num = tag_checklist.length;
    var taxonomy_id = current_elem.parent().siblings('.newtag').attr('id');

    if (taxonomy_id) {
      taxonomy_id = taxonomy_id.split('new-tag-user_tag_');
      taxonomy_name = taxonomy_id[1];
    }

    var tag_html = '<div class="tag-hldr"><a href="#" class="term-link">' + term + '</a><span><a id="user_tag-' + taxonomy_name + '-check-num-' + num + '" class="ntdelbutton">&#10005;</a></span></div'; //Taxonomy Name

    insert_tags(current_elem.parent().siblings('.newtag'), taxonomy_name, term, tag_html);
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
    var current_elem = $(this);
    var sibling = current_elem.siblings('.newtag');
    var newtag_val = sibling.val();
    if (!newtag_val) return;
    newtag_val = newtag_val.split(',');
    var taxonomy_name = sibling.attr('id').split('new-tag-user_tag_');
    taxonomy_name = taxonomy_name[1];
    var tag_checklist = current_elem.siblings('.tagchecklist');
    var num = '';

    for (var $i = 0; $i < newtag_val.length; $i++) {
      num = tag_checklist.length;
      var tag_html = '<div class="tag-hldr"><a href="#" class="term-link">' + newtag_val[$i] + '</a><span><a id="post_tag-' + taxonomy_name + '-check-num-' + num + '" class="ntdelbutton">&#10005;</a></span></div>';
      insert_tags(sibling, taxonomy_name, newtag_val[$i], tag_html);
    }

    $('.tag-suggestion').remove();
  }); // Handle tag delete click.

  $('body').on('click', '.ntdelbutton', function () {
    var current_elem = $(this);
    var parent = current_elem.parents().eq(1);
    var term = parent.find('.term-link').html();
    var tags_list = current_elem.parents().eq(2).siblings('input[type="hidden"]');
    var current_tags = tags_list.val();
    current_tags = current_tags.split(','); // Delete the tag from the list.

    var updated_tags = $.grep(current_tags, function (value) {
      return value != term;
    }); // Store the updated list.

    tags_list.val(updated_tags.join(',')); // Remove tag holder.

    parent.remove();
  });
  $('body').on('click', '.term-link', function (e) {
    if ($(this).attr('href') != '#') return true;else {
      e.preventDefault();
      return false;
    }
  });
  var doing_ajax = false; // Load most Popular tag list.

  $('body').on('click', '.tagcloud-link.user-taxonomy', function (e) {
    e.preventDefault();

    if (doing_ajax) {
      return false;
    }

    doing_ajax = true;

    if ($(this).parent().find('.the-tagcloud').length) {
      $(this).parent().find('.the-tagcloud').remove();
      return true;
    }

    var id = $(this).attr('id');
    var tax = id.substr(id.indexOf("-") + 1);
    $.post(ajaxurl, {
      'action': 'get-tagcloud',
      'tax': tax
    }, function (r, stat) {
      if (0 === r || 'success' != stat) r = wpAjax.broken;
      r = jQuery('<p id="tagcloud-' + tax + '" class="the-tagcloud">' + r + '</p>');
      $('a', r).click(function () {
        var current_elem = $(this);
        var taxonomy_name = '';
        var term_name = current_elem.html();
        var tag_checklist = current_elem.parents().eq(2).siblings('.tagchecklist');
        var tax_input = current_elem.parents().eq(2).siblings('.newtag');

        if (tag_checklist.length === 0) {
          tag_checklist = current_elem.parents().eq(3).siblings('.taxonomy-wrapper').find('.tagchecklist');
        }

        if (tax_input.length === 0) {
          tax_input = current_elem.parents().eq(3).siblings('.taxonomy-wrapper').find('.newtag');
        }

        var num = tag_checklist.length;
        var taxonomy_id = tax_input.attr('id');

        if (taxonomy_id) {
          taxonomy_id = taxonomy_id.split('new-tag-user_tag_');
          taxonomy_name = taxonomy_id[1];
        }

        var tag_html = '<div class="tag-hldr"><a href="#" class="term-link">' + term_name + '</a><span><a id="user_tag-' + taxonomy_name + '-check-num-' + num + '" class="ntdelbutton">&#10005;</a></span></div'; //Taxonomy Name

        insert_tags(tax_input, taxonomy_name, term_name, tag_html);
        return false;
      });
      $('#' + id).after(r);
    }).always(function () {
      doing_ajax = false;
    });
  }); //Remove notices

  setInterval(function () {
    $('#message.below-h2').hide('slow', function () {
      $('.user-taxonomies-page #message.below-h2').remove();
    });
  }, 3000); // User Taxonomy Filters

  $('.users-php select.ut-taxonomy-filter').each(function () {
    if ($(this).val() !== '') {
      $('select.ut-taxonomy-filter').not(this).prop('disabled', true);
    }
  });
  $('.users-php').on('change', 'select.ut-taxonomy-filter', function () {
    if ($(this).val() === '') {
      $('select.ut-taxonomy-filter').prop('disabled', false);
    } else {
      $('select.ut-taxonomy-filter').not(this).prop('disabled', true);
    }
  }); //Load Terms in dropdown for the selected taxonomy

  $('#ut-taxonomy-filter').on('change', function () {
    var sel_tax = $(this).val();

    if (sel_tax === '') {
      return false;
    } //We got the taxonomy, lets load the options

  });
});