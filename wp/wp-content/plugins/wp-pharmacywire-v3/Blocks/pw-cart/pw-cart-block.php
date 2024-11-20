<?php
/**
 * Plugin Name:       Pharmacywire Cart (Summary)
 * Description:       Example block written with ESNext standard and JSX support – build step required.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pw-cart
 *
 * @package           create-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/writing-your-first-block-type/
 */
function create_block_pw_cart_block_init() {
	register_block_type( __DIR__ , array(
		'render_callback' => 'pw_cart_dynamic_render_callback'
	));
}
add_action( 'init', 'create_block_pw_cart_block_init' );

function pw_cart_block_scripts_enqueue() {
	// if ( has_block( 'my-plugin/my-block' ) ) {
    wp_enqueue_script(
        'pw-cart-block-script',
        plugins_url( '/build/pw-cart-block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-dom-ready', 'jquery', 'pwire-plugin-scripts'),
        filemtime( plugin_dir_path( __FILE__ ) . '/build/pw-cart-block.js' )
    );
	PW_JSON::pwire_localize_script('pw-cart-block-script');
	// }
}
// add_action( 'enqueue_block_editor_assets', 'pw_cart_scripts_enqueue' );
// add_action( 'enqueue_block_assets', 'pw_cart_scripts_enqueue' );

// php render initial block
function pw_cart_dynamic_render_callback($block_attributes, $content ) {
	pw_cart_block_scripts_enqueue();
	add_action( 'enqueue_block_assets', 'pw_cart_block_scripts_enqueue' );

	$cartBlockTitle = translate( 'Shopping Cart', 'pw-cart' );
	$blockEl = <<<EOT
	<div class="pwire-ajax-shopping-cart-widget block widget pw-block-widget pwire-cart-widget pwire-cart pwire-ajax-cart-widget pwire-cart-empty">
		<div class="grid-x block-title cart-widget-header">
			<h3>{$cartBlockTitle}</h3>
		</div>
		<div class="grid-x cart-widget-content">
			<div class="line-items cell"></div>
		</div>
		<div class="cart-footer"></div>
	</div>
	EOT;

	return $blockEl;
}