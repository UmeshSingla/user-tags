=== User Tags ===
Contributors: UmeshSingla
Donate link: https://www.paypal.com/
Tags: Tags, taxonomies, user taxonomy, user tags
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.0
Tested up to: 4.0
Stable tag: trunk

Adds an admin option to allow creating User Taxonomies and create tags for different taxonomies.

== Description ==

Adds a **Taxonomies** option under **User** to create custom user taxonomy.
All taxonomies are listed in Profile page for all users which allows users to add tags for the taxonomy.
Each Tag is associated with a template, listing all users who added that tag in their profile.

Supports Multisite
Note:
Only admin can manage Taxonomies.
Users can add new tags.

== Installation ==

1. Upload the `wp-user-taxonomies` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Taxonomies under Users option to create taxonomies for User

== Changelog ==

= 1.2.6 =
* New   - Filter: `ut_template_users` in Taxonomy template to filter the list of users before displaying
* Fixed - handle count callback for register taxonomy (Fixes tag cloud size issue )


= 1.2.5 =
* Fixed - Remove PHP closures

= 1.2.4 =

* Update - Taxonomy name is independent of prefix
* Fixed -  Bubbling up of multiple list on repeated click over most used tags link
* Fixed -  Tag being saved for admin too on editing other user profile

( Thank you @Tempera for reporting all the issues )

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

Requires Wordpress 3.0 atleast

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
* 'ut_template_heading' => Can be used to modify Template Page Heading 
* 'ut_tepmplate_content' => Can be used to modify users list style, 
        args => 1 , $users => List of Users
* 'ut_template_content_empty'  => Display custom message, if there are no users for term
* 'ut_tag_cloud_heading', Allow to modify Tag cloud heading

= Shortcode =

* [user_tags], will generate the User Tags UI in frontend and save the tags

== Credits ==
[Justin Tadlock](http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress)