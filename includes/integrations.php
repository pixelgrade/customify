<?php
/**
 * Load all integrations.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'integrations/pixelgrade-care.php';
require_once 'integrations/pixelgrade-assistant.php';
require_once 'integrations/autoptimize.php';
require_once 'integrations/w3-total-cache.php';
require_once 'integrations/wp-fastest-cache.php';
require_once 'integrations/wp-optimize.php';
require_once 'integrations/wp-rocket.php';
require_once 'integrations/the-events-calendar.php';
