<?php defined('ABSPATH') or die;

	function pixcustomizer_validate_not_empty($fieldvalue, $processor) {
		return ! empty($fieldvalue);
	}
