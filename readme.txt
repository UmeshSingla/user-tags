=== User Taxonomy & Directory ===
Contributors: UmeshSingla
Donate link: https://paypal.me/SinglaUmesh
Tags: Tags, taxonomies, user taxonomy, user tags, user directory, staff directory, employee directory, directory
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.0
Tested up to: 6.3
Stable tag: tags/2.0
Requires PHP: 7.2

User Taxonomy & Directory helps you effortlessly manage user taxonomies on your WordPress website. With a user-friendly interface, it simplifies the process of creating and managing user taxonomies, all while offering the flexibility to display taxonomy archive pages.

== Description ==
The plugin offers a user-friendly solution to help you manage user taxonomies and create user directories on your WordPress site.

Key Features:

    Easy Taxonomy Management: Register and manage user taxonomies effortlessly, providing structure to your user base without unnecessary complexity.
    Admin users with the edit_users capability can assign or un-assign categories from the User profile page, made even more flexible with the ut_render_taxonomy_dropdown filter to modify the check.

    Taxonomy Template Customization: Each taxonomy term generates its own archive featuring the list of assigned users. The plugin allows you to customize this template by simply creating a folder named user-taxonomy-template.php in your theme's root directory."
    Make sure to refresh your permalinks after creating a new taxonomy to ensure that the template functions correctly.

    Dynamic User Lists: user-directory block allows you to display user lists based on roles, with a variety of customizable fields and filters for your front-end design.

User List Features:

    Simple Search: Find users easily by name.

    Filtering: Use taxonomies to refine user lists, with the option to activate multiple taxonomy filters via block settings.

    Flexible Fields: User Name is included by default, but you can add more fields like Bio and Images, all configured with the block settings along with an option to filter it.

The plugin is compatible with multisite environment.

== Installation ==

1. Upload the `user-tags` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. To create new Taxonomy Go to User -> Taxonomy screen.

== Changelog ==

= 2.0 =

* Refactored code for better readability and added functionality.
* Registered Taxonomies are now displayed under Users menu.
* Added Gutenberg block user-directory to create filterable user lists.

** Breaking Changes **
* Removed shortcode `user_tags`, `user-tags-cloud` along with front-end functionality of tags assignment.

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

Requires WordPress 6.0 atleast

= Visit https://github.com/UmeshSingla/user-tags for support =

== Screenshots ==

1. Taxonomy CRUD Screen.
2. Taxonomy Tax archive page.
3. Taxonomy Terms List.
4. User Directory Block output.

== Frequently Asked Questions ==
1. What if Template is not working?
Ans: You need to save permalinks after you create a new taxonomy for template to work properly.

= Filters Available =
* 'user_taxonomy_args' => Filter the arguments for registering taxonomy.
* 'user_tags_directory_user_roles' => Filter list of roles displayed in user-directory block
* 'user_tags_directory_fields'  => Filter list of fields available for user-directory block
* 'user_directory_limit' => Number of users to display in Users List
* 'ut_render_taxonomy_dropdown' => Whether to show/update Taxonomy dropdown on user profile.

== Credits ==
[Justin Tadlock](http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress)