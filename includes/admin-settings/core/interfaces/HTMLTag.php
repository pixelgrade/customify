<?php defined('ABSPATH') or die;

/**
 * @package    pixcustomify
 * @category   core
 * @author     Pixelgrade Team
 * @copyright  (c) 2013, Pixelgrade
 */
interface PixCustomifyHTMLTag {

	/**
	 * @param string key
	 * @param mixed default
	 * @return mixed
	 */
	function get($key, $default = null);

	/**
	 * @param string key
	 * @param mixed value
	 * @return static $this
	 */
	function set($key, $value);

	/**
	 * @return string
	 */
	function htmlattributes(array $extra = array());

} # interface
