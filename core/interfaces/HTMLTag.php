<?php defined('ABSPATH') or die;

/* This file is property of Pixel Grade Media. You may NOT copy, or redistribute
 * it. Please see the license that came with your copy for more information.
 */

/**
 * @package    pixcustomify
 * @category   core
 * @author     Pixel Grade Team
 * @copyright  (c) 2013, Pixel Grade Media
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
