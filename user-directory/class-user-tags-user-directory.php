<?php
/**
 * Handles registration of custom block user-directory
 */
if ( ! class_exists( 'User_Tags_User_Directory' ) ):

	class User_Tags_User_Directory {

		/** @var object Class instance */
		private static $instance;

		private function __construct() {
			add_action( 'init', array( $this, 'register_block' ) );
		}

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new User_Tags_User_Directory();
			}

			// Returns the instance
			return self::$instance;
		}

		public function register_block() {
			register_block_type( __DIR__ . '/block/build' );

			// Enqueue script after register_block_type() so script handle is valid
			add_action( 'admin_enqueue_scripts', array( $this, 'add_inline_script' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_inline_script' ) );
		}

		/**
		 * Localize Block data for the block editor script
		 *
		 * @return void
		 */
		public function add_inline_script() {

			$handle     = 'user-tags-user-directory-editor-script';
			$block_data = array(
				'user_role' => $this->user_get_role_names(),
//		        'taxonomies' => get_registered_user_taxonomies(),
				'filters'   => $this->get_filters(),
				'fields'    => $this->get_user_fields(),
//		        'order'      => get_order(),
			);
			wp_localize_script( $handle, 'userDir', $block_data );

			$block_js = 'frontend.js';
			wp_register_script(
				'user-directory-block',
				UT_URL . '/user-directory/' .$block_js,
				array('jquery'),
				filemtime( UT_DIR . 'user-directory/' . $block_js )
			);
		}

		/**
		 * Get registered user roles
		 * @return string[]
		 */
		public function user_get_role_names() {

			global $wp_roles;
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}

			return apply_filters( 'user_tags_directory_user_roles', $wp_roles->get_names() );
		}

		public function get_filters() {
			$filters = array();

			$filters['search'] = array(
				'name'  => 'search',
				'label' => __( 'Search', 'user_taxonomy' ),
				'type'  => 'search',
			);

			$user_taxonomies = get_registered_user_taxonomies();
			foreach ( $user_taxonomies as $taxonomy ) {

				$filters[ 'tax_' . $taxonomy['name'] ] = array(
					'name'       => $taxonomy['name'],
					'label'      => $taxonomy['label'],
					'type'       => 'taxonomy',
					'value_type' => 'text',
					'default'    => false,
				);
			}

			return $filters;

		}

		/**
		 * Fields to be displayed in front-end
		 *
		 * args:
		 *  default - If a field should be displayed by default. Others can be enabled/disabled from block setting
		 *
		 * @return array
		 */
		public function get_user_fields() {
			$fields = array();

			$fields['user_title'] = array(
				'field_name' => 'user-directory-field-post_title',
				'name'       => 'user_title',
				'label'      => __( 'User name', 'user_taxonomy' ),
				'type'       => 'user',
				'value_type' => 'text',
				'default'    => true,
				'args'       => array(
					'link' => true,
				),
			);

			$user_taxonomies = get_registered_user_taxonomies();
			foreach ( $user_taxonomies as $taxonomy ) {

				$fields[ 'tax_' . $taxonomy['name'] ] = array(
					'name'       => $taxonomy['name'],
					'label'      => $taxonomy['label'],
					'type'       => 'taxonomy',
					'value_type' => 'text',
					'default'    => false,
				);
			}

			return apply_filters( 'user_tags_directory_fields', $fields );

		}
	}
	User_Tags_User_Directory::get_instance();
endif;
