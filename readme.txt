=== User Tags ===
Contributors: UmeshSingla
Donate link: https://paypal.me/SinglaUmesh
Tags: Tags, taxonomies, user taxonomy, user tags
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.0
Tested up to: 6.3
Stable tag: trunk

User Tags provides an interface to register Taxonomy for Users along with a user-directory block to generate a filterable list of users.

== Description ==

This plugin provides an interface to register Taxonomy for Users along with a user-directory block to generate a filterable list of users.
User themselves/Admin can assign/un-assign Category/Term from User profile page.

user-directory block can be used to list users for a particular role, along with
an option to choose from fields and filters to display in front-end.
User List has following features:
 Search - Allows to search through users list based upon user name.
 Filter - Taxonomies can be used to filter the user list. Multiple Taxonomy filters can be enabled from block setting.
 Fields - Information to display about Users. Fields are set to include User Name by default. Addtiioanl fields like Bio, Image can be enabled from block setting.

Supports Multisite

== Installation ==

1. Upload the `user-tags` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. To create new Taxonomy Go to User -> Taxonomy screen.

== Changelog ==

= 2.0 =

* Refactored code for better readability
* Display registered taxonomy under Users menu
* Added Gutenberg user-directory to create filterable user lists.
** Breaking Changes **
* Removed shortcode `user_tags`, `user-tags-cloud`
* Removed template functionality

= 1.2.8 =

* Fixed: Updated filter name 'ut_tepmplate_content' => ut_template_content : https://github.com/UmeshSingla/user-tags/issues/7
* Fixed: Return $template variable in functions.php https://github.com/UmeshSingla/user-tags/issues/8
* Fixed: string to array conversion.
* Fixed: Compat with WordPress >= 5.5 ( Fixed fatal error )
* Updated: Switch to div instead of table for new User Taxonomy screen

= 1.2.7 =
* Fixed - Fatal error, Initialise as array instead of string

= 1.2.6 =
* New   - Filter: `ut_template_users` in Taxonomy template to filter the list of users before displaying
* Fixed - handle count callback for register taxonomy (Fixes tag cloud size issue )


= 1.2.5 =
* Fixed - Remove PHP closures

= 1.2.4 =

* Update - Taxonomy name is independent of prefix
* Fixed -  Bubbling up of multiple list on repeated click over most used tags link
* Fixed -  Tag being saved for admin too on editing other user profile

( Credits: @Tempera )

= 1.2.3 =
* Fixed - 'ut_template_content' filter args
* Fixed - Translation function (props @stefan)
* Updated - Replaced PHP closure with normal functions

= 1.2.2 =
* Fixed - Tag cloud not appearing for all taxonomies in shortcode
* Update - Shortcode - Do not echo form if user is not loggedin

= 1.2.1 =
* Fixed - Tag cloud for [user_tags] shortcode


= 1.2 =
* Fixed - [user_tags] shortcode

= 1.1 =

* Fix: All tag not being deleted

= 1.0 =

* New: Tag Cloud to choose from most popular
* New: Filter 'ut_tag_cloud_heading' to change tag cloud heading

= 0.1.3 =

* Fixes Page not found error for tag templates
* Fixes tags update for other users by administrator, causing overwrite to current logged in users taxonomy
* Improved Template Styling

= 0.1.2 =
 Fixes Taxonomy length error

= 0.1.1 =
 Code formatting

= 0.1 =
 First Release

== Upgrade Notice ==

Requires Wordpress 4.8 atleast

== Frequently Asked Questions ==

= What if tags template are not working for me? =

You just need to save permalinks once, and it will work absolutely fine for you afterwards.

= Visit https://github.com/UmeshSingla/user-tags for support =

== Screenshots ==

1. Taxonomy Option under Users
2. Manage Tags for Custom User Taxonomy Food Like
3. Tags option in User profile Page
4. Template page for tag, listing all the associated users
== Other Notes ==

= Filters Available =
* 'user_taxonomy_args' => Filter the arguments for registering taxonomy.
* 'user_tags_directory_user_roles' => Filter list of roles displayed in user-directory block
* 'user_tags_directory_fields'  => Filter list of fields available for user-directory block
* 'user_directory_limit' => Number of users to display in Users List

== Credits ==
[Justin Tadlock](http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress)