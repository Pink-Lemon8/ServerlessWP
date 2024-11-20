<?php

if (get_option('pw_enable_foundation', 1)) {
	/************* ENQUEUE CSS *****************/
	function plugin_foundation_styles()
	{
		wp_register_style('foundation-css', THEME_URL . 'vendor/foundation/css/foundation.min.css', array(), '6.5.3', 'all');
		wp_enqueue_style('foundation-css');
	}

	add_action('wp_enqueue_scripts', 'plugin_foundation_styles');

	/************* ENQUEUE JS *************************/
	function plugin_foundation_js()
	{
		wp_register_script('foundation-js', THEME_URL . 'vendor/foundation/js/foundation.min.js', array('jquery'), '6.5.3', true);
		wp_enqueue_script('foundation-js');

		wp_register_script('init-foundation-js', THEME_URL . 'js/init-foundation.js', array('jquery', 'foundation-js'), '6.5.3', true);
		wp_enqueue_script('init-foundation-js');
	}

	add_action('wp_enqueue_scripts', 'plugin_foundation_js');
}
