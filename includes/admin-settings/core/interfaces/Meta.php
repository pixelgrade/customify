<?php defined('ABSPATH') or die;

/**
 * @package    pixcustomify
 * @category   core
 * @author     Pixelgrade Team
 * @copyright  (c) 2013, Pixelgrade
 */
interface PixCustomifyMeta {

	/**
	 * @param string meta key
	 * @return boolean true if key exists, false otherwise
	 */
	function has($key);

	/**
	 * @return mixed value or default
	 */
	function get($key, $default = null);

	/**
	 * @return static $this
	 */
	function set($key, $value);

	/**
	 * Set the key if it's not already set.
	 *
	 * @param string key
	 * @param string value
	 */
	function ensure($key, $value);

	/**
	 * If the key is currently a non-array value it will be converted to an
	 * array maintaning the previous value (along with the new one).
	 *
	 * @param  string name
	 * @param  mixed  value
	 * @return static $this
	 */
	function add($name, $value);

	/**
	 * @return array all metadata as array
	 */
	function metadata_array();

	/**
	 * Shorthand for a calling set on multiple keys.
	 *
	 * @return static $this
	 */
	function overwritemeta($overwrites);

} # interface
