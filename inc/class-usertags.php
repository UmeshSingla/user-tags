<?php
/**
 * Enqueue scripts and styles
 * Loads taxonomy template for custom taxonomies
 */

if ( ! class_exists( 'UserTags' ) ) :
	/**
	 * Class definition
	 */
	class UserTags {
		/**
		 * Enqueue/Register scripts and styles
		 */
		public function __construct() {
			// Taxonomies
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_styles' ) );

			add_filter( 'taxonomy_template', array( $this, 'load_template' ) );
			add_action( 'wp_head', array( $this, 'admin_ajax' ) );
		}

		/**
		 * Script/Styles
		 *
		 * @param string $hook Page hook
		 *
		 * @return void
		 */
		public function register_scripts_styles( $hook ) {

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
		private function admin_ajax() {
			?>
            <script type="text/javascript">
                var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
            </script>
			<?php
		}

		/**
		 * Load custom taxonomy template for user taxonomies.
		 *
		 * @param string $template
		 *
		 * @return mixed|string
		 */
		public function load_template( $template ) {

			$taxonomy = get_query_var( 'taxonomy' );

			// check if taxonomy is for user or not
			$user_taxonomies = get_object_taxonomies( 'user', 'object' );

			if ( ! is_array( $user_taxonomies ) || empty( $user_taxonomies[ $taxonomy ] ) ) {
				return $template;
			}

			wp_enqueue_style( 'user-tags-style' );

			// Check if theme is overriding the template
			$overridden_template = locate_template( 'user-taxonomy-template.php', false, false );

			if ( ! empty( $overridden_template ) ) {
				$template = $overridden_template;
			} else {
				$template = UT_DIR . 'template/user-taxonomy-template.php';
			}

			return $template;
		}
	}

	new UserTags();
endif;
