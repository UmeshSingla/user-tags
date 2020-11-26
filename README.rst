======
User Tags
======

Allows creating and managing User Taxonomy from WordPress admin.

Description
======

This plugin extends the default taxonomy functionality to users.

Ref: [Justin Tadlock](http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress)

Installation
======

1. Upload the `user-tags` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Taxonomy under Users option to create taxonomy for User


Filters Available
======
1. 'ut_template_heading' => Can be used to modify Template Page Heading 
2. 'ut_template_content' => Can be used to modify users list style,
        args => 1 , $users => List of Users
3. 'ut_template_content_empty'  => Display custom message, if there are no users for term
4. 'ut_tag_cloud_heading', Allow to modify Tag cloud heading

Shortcode
======
[user_tags], will generate the User Tags UI in frontend and save the tags