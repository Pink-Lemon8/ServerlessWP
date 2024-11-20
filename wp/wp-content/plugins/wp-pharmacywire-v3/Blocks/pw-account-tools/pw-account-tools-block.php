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
 * Text Domain:       pw-account-tools
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
function create_block_pw_account_tools_block_init() {
	register_block_type( __DIR__ , array(
		'render_callback' => 'pw_account_tools_dynamic_render_callback',
		'attributes' => [
			"verticalMenu" => [ 
				"type" => "boolean",
				"default" => false
			],
			"hideHeading" => [ 
				"type" => "boolean",
				"default" => false
			],
			"hideAccount" => [ 
				"type" => "boolean",
				"default" => false
			],
			"hideLoginLogoutLink" => [ 
				"type" => "boolean",
				"default" => false
			],
			"hideCartLink" => [
				"type" => "boolean",
				"default" => false
			],
		]
	));
}
add_action( 'init', 'create_block_pw_account_tools_block_init' );

function pw_account_tools_block_scripts_enqueue() {
    wp_enqueue_script(
        'pw-account-tools-block-script',
        plugins_url( '/build/pw-account-tools-block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-dom-ready', 'jquery', 'pwire-plugin-scripts'),
        filemtime( plugin_dir_path( __FILE__ ) . 'src/pw-account-tools-block.js' )
    );
	PW_JSON::pwire_localize_script('pw-account-tools-block-script');
}

// php render initial block
function pw_account_tools_dynamic_render_callback($block_attributes, $content ) {
	pw_account_tools_block_scripts_enqueue();
	add_action( 'enqueue_block_assets', 'pw_account_tools_block_scripts_enqueue' );

	if (WebUser::isLoggedIn()) {
		$loggedInState = 'logged-in';
	} else {
		$loggedInState = 'logged-out';
	} 

	$hide_heading = $block_attributes[ 'hideHeading' ] ?? false;
	$vertical_menu = $block_attributes['verticalMenu'] ?? false;
	$hide_account = $block_attributes[ 'hideAccount' ] ?? false;
	$hide_login = $block_attributes[ 'hideLoginLogoutLink' ] ?? false;
	$hide_cart = $block_attributes[ 'hideCartLink' ] ?? false;
	$cartBlockTitle = translate( 'Account Tools', 'pw-cart' );

	$generatedOutput = '';
	ob_start();	
	?>
		<div class="pwire-ajax-account-tools-widget block widget pwire-account-tools-widget pwire-account-<?php echo $loggedInState; ?>">
			<?php if (!$hide_heading) : ?>
			<div class="grid-x block-title account-tools-widget-header">
				<h3><?php echo $cartBlockTitle; ?></h3>
			</div>
			<?php endif; ?>
			<ul class="account-tools-widget-content menu <?php if ($vertical_menu) echo 'vertical'; ?>">
				<?php if (!$hide_account) : ?>
				<li class="account-link menu-item"></li>
				<?php endif; ?>
				<?php if (!$hide_login) : ?>
				<li class="login-link menu-item"></li>
				<?php endif; ?>
				<?php if (!$hide_cart) : ?>
				<li class="cart-link menu-item shopping-cart"><a href="<?php echo PC_getShoppingURL(); ?>"><span class="cart-label">Cart</span> <span class="cart-quantity"></span></a></li>
				<?php endif; ?>
			</ul>
		</div>
	<?php
	$generatedOutput = ob_get_clean();
	return $generatedOutput;
}