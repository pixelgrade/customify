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
interface PixCustomifyFormField extends PixCustomifyHTMLElement {

	/**
	 * @return boolean true if field has errors
	 */
	function has_errors();

	/**
	 * @return string first error message
	 */
	function one_error();

	/**
	 * Render field emulates wordpress template behaviour. First searches for
	 * name, then searches field type and so on.
	 *
	 * @return string
	 */
	function render();

} # interface
