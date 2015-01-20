<?php defined('ABSPATH') or die;
/**
 * On save action we process all settings for each theme settings we have in db
 *
 * Think about inserting this function in after_theme_switch hook so the settings should be updated on theme switch
 *
 * @param $values
 */

function save_customizer_plugin_settings( $values ){

//	$options = get_option('pixtypes_settings');
	// maybe proccess some setting on save

	// save this settings back
//	update_option('pixtypes_settings', $options);

	/** Usually these settings will change slug settings se we need to flush the permalinks */
//	global $wp_rewrite;
//	//Call flush_rules() as a method of the $wp_rewrite object
//	$wp_rewrite->flush_rules();

	/**
	 * http://wordpress.stackexchange.com/questions/36152/flush-rewrite-rules-not-working-on-plugin-deactivation-invalid-urls-not-showing
	 * nothing from above works in plugin so ...
	 */
	delete_option('rewrite_rules');

}