<?php

if ( ! class_exists( 'UserTags' ) ) :
	class UserTags {
		public function __construct() {
			// Taxonomies
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_styles' ) );

			add_action( 'wp_head', array( $this, 'admin_ajax' ) );
		}

		function register_scripts_styles( $hook ) {

			$js_mtime = filemtime( UT_DIR . '/assets/js/user_taxonomy.min.js' );
			$version  = UT_VERSION . $js_mtime;
			wp_register_script( 'user-tags-js', UT_URL . '/assets/js/user_taxonomy.js', array( 'jquery' ), $version, true );

			$css_mtime = filemtime( UT_DIR . '/assets/css/main.min.css' );
			$version   = UT_VERSION . $css_mtime;
			wp_register_style( 'user-tags-style', UT_URL . '/assets/css/main.min.css', '', $version );

			$css_mtime = filemtime( UT_DIR . '/assets/css/block.min.css' );
			$version   = UT_VERSION . $css_mtime;
			wp_register_style( 'user-directory-block-style', UT_URL . '/assets/css/block.min.css', '', $version );

			if ( 'user-edit.php' === $hook || 'profile.php' === $hook || 'users_page_user-taxonomies' === $hook ) {
				user_tags_enqueue_assets();
			}
		}

		/**
		 * Admin ajax URL
		 */
		function admin_ajax() {
			?>
			<script type="text/javascript">
                var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			</script>
			<?php
		}

	}

	/**
	 * Instantiate Class
	 */
	add_action( 'init', 'ut_user_tags' );
	function ut_user_tags() {
		global $user_tags;
		$user_tags = new UserTags();
	}
endif;
