======
User Taxonomies 
======
Tags: user, users, taxonomy, custom taxonomy, register_taxonomy
Tested up to: 3.3.1
Stable tag: trunk

Allows creating and managing User Taxonomies from Backend

Description
======

This plugin extends the default taxonomy functionality and extends it to users, while automating all the boilerplate code.

Read more about [registering taxonomies in the codex](http://codex.wordpress.org/Function_Reference/register_taxonomy)
This is heavily inspired by previous work by [Justin Tadlock](http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress)

Installation
======

1. Upload the `user-taxonomies` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Taxonomies under Users option to create taxonomies for User


Filters Available
======
1. 'ut_template_heading' => Can be used to modify Template Page Heading 
2. 'ut_tepmplate_content' => Can be used to modify users list style, 
        Args => 1 , $users => List of Users
3. 'ut_template_content_empty'  => If there are no users for term
