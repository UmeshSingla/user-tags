<?php

/*
 * Shortcode for tags cloud
 * @author Umesh Kumar (.1) <umeshsingla05@gmail.com>
 *
 */

class Ut_User_Tag_Cloud {
	function __construct() {
		add_shortcode( 'user-tags-cloud', array( $this, 'tag_cloud' ) );
	}

	function tag_cloud( $attr ) {

		$atts  = shortcode_atts(
			array(
				'term'  => '',
				'limit' => 25,
			),
			$attr,
			'user-tags-cloud'
		);

		$term  = $atts['term'];
		$limit = $atts['limit'];

	}
}

new Ut_User_Tag_Cloud();
