=== User Taxonomies ===
Contributors: gostomski
Tags: user, users, taxonomy, custom taxonomy, register_taxonomy, developer
Tested up to: 3.3.1
Stable tag: trunk

Simplify the process of adding support for custom taxonomies for Users. Just use `register_taxonomy` and everything else is taken care of.

== Description ==

This plugin extends the default taxonomy functionality and extends it to users, while automating all the boilerplate code.

Once activated, you can register user taxonomies using the following code:
`
register_taxonomy('profession', 'user', array(
	'public'		=>true,
	'labels'		=>array(
		'name'						=>'Professions',
		'singular_name'				=>'Profession',
		'menu_name'					=>'Professions',
		'search_items'				=>'Search Professions',
		'popular_items'				=>'Popular Professions',
		'all_items'					=>'All Professions',
		'edit_item'					=>'Edit Profession',
		'update_item'				=>'Update Profession',
		'add_new_item'				=>'Add New Profession',
		'new_item_name'				=>'New Profession Name',
		'separate_items_with_commas'=>'Separate professions with commas',
		'add_or_remove_items'		=>'Add or remove professions',
		'choose_from_most_used'		=>'Choose from the most popular professions',
	),
	'rewrite'		=>array(
		'with_front'				=>true,
		'slug'						=>'author/profession',
	),
	'capabilities'	=> array(
		'manage_terms'				=>'edit_users',
		'edit_terms'				=>'edit_users',
		'delete_terms'				=>'edit_users',
		'assign_terms'				=>'read',
	),
));
`

Read more about [registering taxonomies in the codex](http://codex.wordpress.org/Function_Reference/register_taxonomy)
This is heavily inspired by previous work by [Justin Tadlock](http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress)

== Installation ==

1. Upload the `user-taxonomies` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use `register_taxonomy` as shown in the description
