<?php
/**
 * Deprecated functionality, mainly for backwards compatibility.
 *
 * @package Pixelgrade Customify
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */


namespace { // global code

	use function Pixelgrade\Customify\get_customizer_config;
	use function Pixelgrade\Customify\get_option_details;
	use function Pixelgrade\Customify\get_option_details_all;
	use function Pixelgrade\Customify\get_options_key;
	use function Pixelgrade\Customify\has_option;
	use function Pixelgrade\Customify\plugin;
	use const Pixelgrade\Customify\VERSION;

	/**
	 * Alias the ArrayHelpers so the old Customify_Array could still work.
	 *
	 * @deprecated Use Pixelgrade\Customify\Utils\ArrayHelpers instead.
	 */
	class_alias('Pixelgrade\Customify\Utils\ArrayHelpers', 'Customify_Array');

	/**
	 * Returns the main instance of PixCustomifyPlugin to prevent the need to use globals.
	 *
	 * @deprecated Use Pixelgrade\Customify\plugin() instead.
	 * @since  1.5.0
	 * @return PixCustomifyPlugin
	 */
	function PixCustomifyPlugin() {
		_deprecated_function( __FUNCTION__, '3.0.0', 'Pixelgrade\Customify\plugin()' );

		return PixCustomifyPlugin::instance() ;
	}

	/**
	 * Main plugin class.
	 *
	 * @deprecated Use the Pixelgrade\Customify\Plugin class instead.
	 *
	 * @package   PixCustomify
	 * @author    Pixelgrade <contact@pixelgrade.com>
	 */
	class PixCustomifyPlugin {

		/**
		 * Instance of this class.
		 * @since    1.5.0
		 * @var      object
		 */
		protected static $_instance = null;

		protected function __construct( $file = '', $version = '1.0.0' ) {

		}

		/**
		 * Invalidate all caches.
		 *
		 * @deprecated
		 *
		 * @since 2.6.0
		 */
		public function invalidate_all_caches() {
			do_action( 'customify_invalidate_all_caches' );
		}

		/**
		 * Invalidate all caches, when hooked via a filter (just pass through the value).
		 *
		 * @deprecated
		 *
		 * @since 2.6.0
		 *
		 * @param mixed $value
		 * @return mixed
		 */
		public function filter_invalidate_all_caches( $value ) { return $value; }

		/**
		 * This will clear any instance properties that are used as local cache during a request to avoid
		 * fetching the data from DB on each method call.
		 *
		 * This may be called during a request when something happens that (potentially) invalidates our data mid-request.
		 *
		 * @deprecated
		 */
		public function clear_locally_cached_data() {}

		/**
		 * @deprecated Use Pixelgrade\Customify\get_options_key() instead.
		 *
		 * @param false $skip_cache
		 *
		 * @return string
		 */
		public function get_options_key( $skip_cache = false ) {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\get_options_key()' );

			return get_options_key( $skip_cache );
		}

		/**
		 * @deprecated
		 */
		public function invalidate_customizer_opt_name_cache() {}

		/**
		 * @deprecated
		 *
		 * @param $value
		 *
		 * @return mixed
		 */
		public function filter_invalidate_customizer_opt_name_cache( $value ) { return $value; }

		/**
		 * Get all options' details.
		 *
		 * @deprecated Use Pixelgrade\Customify\get_option_details_all() instead.
		 *
		 * @param bool $only_minimal_details Optional. Whether to return only the minimal details.
		 *                                   Defaults to returning all details.
		 * @param bool $skip_cache           Optional. Whether to skip the options cache and regenerate.
		 *                                   Defaults to using the cache.
		 *
		 * @return array
		 */
		public function get_options_details( $only_minimal_details = false, $skip_cache = false ) {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\get_option_details_all()' );

			return get_option_details_all( $only_minimal_details, $skip_cache );
		}

		/**
		 * @deprecated
		 */
		public function invalidate_options_details_cache() {}

		/**
		 * @deprecated
		 *
		 * @param $value
		 *
		 * @return mixed
		 */
		public function filter_invalidate_options_details_cache( $value ) { return $value; }

		/**
		 * @deprecated Use Pixelgrade\Customify\has_option() instead.
		 *
		 * @param $option
		 *
		 * @return bool
		 */
		public function has_option( $option ) {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\has_option()' );

			return has_option( $option );
		}

		/**
		 * @deprecated Use Pixelgrade\Customify\get_customizer_config() instead.
		 *
		 * @param false $key
		 *
		 * @return array|mixed|null
		 */
		public function get_customizer_config( $key = false ) {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\get_customizer_config()' );

			return get_customizer_config( $key );
		}

		/**
		 * @deprecated
		 */
		public function invalidate_customizer_config_cache() {}

		/**
		 * Invalidate the customizer config cache, when hooked via a filter (just pass through the value).
		 *
		 * @deprecated
		 *
		 * @since 2.4.0
		 *
		 * @param mixed $value
		 * @return mixed
		 */
		public function filter_invalidate_customizer_config_cache( $value ) { return $value;}

		/**
		 * Get the Customify configuration (and value, hence "details") of a certain option.
		 *
		 * @deprecated Use Pixelgrade\Customify\get_option_details() instead.
		 *
		 * @param string $option_id
		 * @param bool $minimal_details Optional. Whether to return only the minimum amount of details (mainly what is needed on the frontend).
		 *                              The advantage is that these details are cached, thus skipping the customizer_config!
		 * @param bool $skip_cache Optional.
		 *
		 * @return array|false The option config or false on failure.
		 */
		public function get_option_details( $option_id, $minimal_details = false, $skip_cache = false ) {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\get_option_details()' );

			return get_option_details( $option_id, $minimal_details, $skip_cache );
		}

		/**
		 * This is just a wrapper for get_option_details_all() for backwards compatibility.
		 *
		 * @deprecated Use Pixelgrade\Customify\get_option_details_all() instead.
		 *
		 * @param bool $only_minimal_details
		 * @param bool $skip_cache
		 *
		 * @return array|mixed|void
		 */
		public function get_options_configs( $only_minimal_details = false, $skip_cache = false ) {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\get_option_details_all()' );

			return get_option_details_all( $only_minimal_details, $skip_cache );
		}

		/**
		 * A public function to get an option's value.
		 * If there is a value and return it.
		 * Otherwise try to get the default parameter or the default from config.
		 *
		 * @deprecated Use Pixelgrade\Customify\get_option() instead.
		 *
		 * @param $option_id
		 * @param mixed $default        Optional.
		 * @param array $option_details Optional.
		 *
		 * @return bool|null|string
		 */
		public function get_option( $option_id, $default = null, $option_details = null ) {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\get_option()' );

			return Pixelgrade\Customify\get_option( $option_id, $default, $option_details );
		}

		/**
		 * @deprecated Use the Pixelgrade\Customify\VERSION constant instead.
		 */
		public function get_version() {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\VERSION' );

			return VERSION;
		}

		/**
		 * @deprecated Use Pixelgrade\Customify\plugin()->get_slug() instead.
		 */
		public function get_slug() {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\plugin()->get_slug()' );

			return plugin()->get_slug();
		}

		/**
		 * @deprecated Use Pixelgrade\Customify\plugin()->get_file() instead.
		 */
		public function get_file() {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\plugin()->get_file()' );

			return plugin()->get_file();
		}

		/**
		 * @deprecated Use Pixelgrade\Customify\plugin()->get_path() instead.
		 */
		public function get_base_path() {
			_deprecated_function( __METHOD__, '3.0.0', 'Pixelgrade\Customify\plugin()->get_path()' );

			return plugin()->get_path();
		}

		/**
		 * Main PixCustomifyPlugin Instance
		 *
		 * Ensures only one instance of PixCustomifyPlugin is loaded or can be loaded.
		 *
		 * @since  1.0.0
		 * @static
		 *
		 * @param string $file File.
		 * @param string $version Version.
		 *
		 * @see    PixCustomifyPlugin()
		 * @return PixCustomifyPlugin Main PixCustomifyPlugin instance
		 */
		public static function instance( $file = '', $version = '1.0.0' ) {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $file, $version );
			}

			return self::$_instance;
		}
	}
}

namespace Pixelgrade\Customify {

}
