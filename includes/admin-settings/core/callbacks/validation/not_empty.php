<?php defined('ABSPATH') or die;

	function pixcustomify_validate_not_empty($fieldvalue, $processor) {
		return ! empty($fieldvalue);
	}
