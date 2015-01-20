<?php defined('ABSPATH') or die;

	function pixcustomizer_cleanup_switch_not_available($fieldvalue, $meta, $processor) {
		return $fieldvalue !== null ? $fieldvalue : false;
	}
