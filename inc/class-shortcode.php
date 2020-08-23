<?php
/*
 * Shortcode for tags cloud
 * @author Umesh Kumar (.1) <umeshsingla05@gmail.com>
 *
 */
class Ut_User_Tag_Cloud {
	function __construct() {
		add_shortcode('user-tags-cloud', array( $this, 'tag_cloud') );
	}
	function tag_cloud( $atts ) {

		extract(shortcode_atts(array(
			'term' => '',
			'limit' =>  25
		), $atts));
		echo "<pre>Variables";
		print_r( $term);
		print_r( $limit );
		echo "</pre>";
	}
}
new Ut_User_Tag_Cloud();