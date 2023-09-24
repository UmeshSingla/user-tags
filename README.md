User Taxonomy & Directory
===

User Taxonomy & Directory helps you effortlessly manage user taxonomies on your WordPress website. With a user-friendly interface, it simplifies the process of creating and managing user taxonomies, all while offering the flexibility to display taxonomy archive pages.

Description
======

The plugin offers a user-friendly solution to help you manage user taxonomies and create user directories on your WordPress site.

Key Features:

    Easy Taxonomy Management: Register and manage user taxonomies effortlessly, providing structure to your user base without unnecessary complexity.
    Admin users with the edit_users capability can assign or un-assign categories from the User profile page, made even more flexible with the ut_render_taxonomy_dropdown filter.

    Dynamic User Lists: user-directory block allows you to display user lists based on roles, with a variety of customizable fields and filters for your front-end design.

User List Features:

    Simple Search: Find users easily by name.

    Filtering: Use taxonomies to refine user lists, with the option to activate multiple taxonomy filters via block settings.

    Flexible Fields: User Name is included by default, but you can add more fields like Bio and Images, all configured with the block settings along with an option to filter it.

The plugin is compatible with multisite enviornment.

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
