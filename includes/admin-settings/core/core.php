<?php defined('ABSPATH') or die;

/**
 * @package    pixcustomify
 * @category   core
 * @author     Pixel Grade Team
 * @copyright  (c) 2013, Pixel Grade Media
 */
class pixcustomify {

	/** @var array core defaults */
	protected static $defaults = null;

	/**
	 * @return array
	 */
	static function defaults() {
		if (self::$defaults === null) {
			self::$defaults = include self::corepath().'defaults'.EXT;
		}

		return self::$defaults;
	}

	// Simple Dependency Injection Container
	// ------------------------------------------------------------------------

	/** @var array interface -> implementation mapping */
	protected static $mapping = array();

	/**
	 * @return mixed instance of class registered for the given interface
	 */
	static function instance() {
		$args = func_get_args();
		$interface = array_shift($args);

		if (isset(self::$mapping[$interface])) {
			$class = self::$mapping[$interface];
		}
		else { // the interface isn't mapped to a class
			// we fallback to interface name + "Impl" suffix
			$class = $interface.'Impl';
		}

		return call_user_func_array(array($class, 'instance'), $args);
	}

	/**
	 * Registers a class for the given interface. If no class is registered for
	 * an interface the interface name with a Impl suffix is used.
	 */
	static function use_impl($interface, $class) {
		self::$mapping[$interface] = $class;
	}


	// Syntactic Sugar
	// ------------------------------------------------------------------------

	/**
	 * @param array configuration
	 * @return PixCustomifyForm
	 */
	static function form($config, $processor) {
		$form = self::instance('PixCustomifyForm', $config);
		$form->autocomplete($processor->data());
		$form->errors($processor->errors());
		return $form;
	}

	/**
	 * @param array configuration
	 * @return PixCustomifyProcessor
	 */
	static function processor($config) {
		return self::instance('PixCustomifyProcessor', $config);
	}


	// Paths
	// ------------------------------------------------------------------------

	/**
	 * @return string root path for core
	 */
	static function corepath() {
		return dirname(__FILE__).DIRECTORY_SEPARATOR;
	}

	/** @var string plugin path */
	protected static $pluginpath = null;

	/**
	 * @return string path
	 */
	static function pluginpath() {
		if (self::$pluginpath === null) {
			self::$pluginpath = realpath(self::corepath().'..').DIRECTORY_SEPARATOR;
		}

		return self::$pluginpath;
	}

	/**
	 * Sets a custom plugin path; required in non-standard plugin structures.
	 */
	static function setpluginpath($path) {
		self::$pluginpath = $path;
	}


	// Helpers
	// ------------------------------------------------------------------------

	/**
	 * Hirarchical array merge. Will always return an array.
	 *
	 * @param  ... arrays
	 * @return array
	 */
	static function merge() {
		$base = array();
		$args = func_get_args();

		foreach ($args as $arg) {
			self::array_merge($base, $arg);
		}

		return $base;
	}

	/**
	 * Overwrites base array with overwrite array.
	 *
	 * @param array base
	 * @param array overwrite
	 */
	protected static function array_merge(array &$base, array $overwrite) {
		foreach ($overwrite as $key => &$value)
		{
			if (is_int($key))
			{
				// add only if it doesn't exist
				if ( ! in_array($overwrite[$key], $base))
				{
					$base[] = $overwrite[$key];
				}
			}
			// non-int key
			else if (is_array($value))
			{
				if (isset($base[$key]) && is_array($base[$key]))
				{
					self::array_merge($base[$key], $value);
				}
				else # does not exist or it's a non-array
				{
					$base[$key] = $value;
				}
			}
			else # not an array and not numeric key
			{
				$base[$key] = $value;
			}
		}
	}

	/**
	 * @param string callback key
	 * @return string callback function name
	 * @throws Exception
	 */
	static function callback($key, PixCustomifyMeta $meta) {
		$defaults = pixcustomify::defaults();
		$default_callbacks = $defaults['callbacks'];
		$plugin_callbacks = $meta->get('callbacks', array());

		$callbacks = array_merge($default_callbacks, $plugin_callbacks);

		if (isset($callbacks[$key])) {
			return $callbacks[$key];
		}
		else { // missing callback
			throw new Exception('Missing callback for ['.$key.'].');
		}
	}

	/** @var string the translation text domain */
	protected static $textdomain = 'customify';

	/**
	 * @return string text domain
	 */
	static function textdomain() {
		return self::$textdomain;
	}

	/**
	 * Sets a custom text domain; if null is passed the text domain will revert
	 * to the default text domain.
	 */
	static function settextdomain($textdomain) {
		if ( ! empty($textdomain)) {
			self::$textdomain = $textdomain;
		}
		else { // null or otherwise empty value
			// revert to default
			self::$textdomain = 'customify';
		}
	}

	/**
	 * Recursively finds all files in a directory.
	 *
	 * @param string directory to search
	 * @return array found files
	 */
	static function find_files($dir)
	{
		$found_files = array();
		$files = scandir($dir);

		foreach ($files as $value) {
			// skip special dot files and directories
			if (strpos($value,'.') === 0) {
				continue;
			}

			// is it a file?
			if (is_file("$dir/$value")) {
				$found_files []= "$dir/$value";
				continue;
			}
			else { // it's a directory
				foreach (self::find_files("$dir/$value") as $value) {
					$found_files []= $value;
				}
			}
		}

		return $found_files;
	}

	/**
	 * Requires all PHP files in a directory.
	 * Use case: callback directory, removes the need to manage callbacks.
	 *
	 * Should be used on a small directory chunks with no sub directories to
	 * keep code clear.
	 *
	 * @param string path
	 */
	static function require_all($path)
	{
		$files = self::find_files(rtrim($path, '\\/'));

		$priority_list = array();
		foreach ($files as $file) {
			$priority_list[$file] = self::file_priority($file);
		}

		asort($priority_list, SORT_ASC);

		foreach ($priority_list as $file => $priority) {
			if (strpos($file, EXT)) {
				require_once $file;
			}
		}
	}

	/**
	 * Priority based on path length and number of directories. Files in the
	 * same directory have higher priority if their path is shorter; files in
	 * directories have +100 priority bonus for every directory.
	 *
	 * @param  string file path
	 * @return int
	 */
	protected static function file_priority($path) {
		$path = str_replace('\\', '/', $path);
		return strlen($path) + substr_count($path, '/') * 100;
	}

	static function option( $option, $default = null ) {
		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		return $local_plugin->get_option($option, $default = null);

	}

} # class
