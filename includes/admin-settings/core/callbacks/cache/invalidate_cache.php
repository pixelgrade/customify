<?php defined('ABSPATH') or die;

	function pixcustomify_cache_invalidate_cache() {
		PixCustomifyPlugin()->invalidate_all_caches();
	}
