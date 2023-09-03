======
User Tags
======

User Tags provides an interface to register Taxonomy for Users along with a user-directory block to generate a filterable list of users.

Description
======

This plugin provides an interface to register Taxonomy for Users along with a user-directory block to generate a filterable list of users.
User themselves/Admin can assign/un-assign Category/Term from User profile page.

user-directory block can be used to list users for a particular role, along with
an option to choose from fields and filters to display in front-end.
User List has following features:
Search - Allows to search through users list based upon user name.
Filter - Taxonomies can be used to filter the user list. Multiple Taxonomy filters can be enabled from block setting.
Fields - Information to display about Users. Fields are set to include User Name by default. Addtiioanl fields like Bio, Image can be enabled from block setting.

Supports Multisite

Ref: [Justin Tadlock](http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress)

Installation
======

1. Upload the `user-tags` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Taxonomy under Users option to create taxonomy for User


Filters Available
======
1. 'user_taxonomy_args' => Filter the arguments for registering taxonomy.
2. 'user_tags_directory_user_roles' => Filter list of roles displayed in user-directory block
3. 'user_tags_directory_fields'  => Filter list of fields available for user-directory block
4. 'user_directory_limit' => Number of users to display in Users List

FAQs
======
1. How to delete Taxonomies?
You can use the built-in interface under Users -> Taxonomies to delete any user taxonomy you would like to.