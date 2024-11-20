<?php 

// gives error on submitting create account page
// require("inc/lib/PHPMailer/src/PHPMailer.php");
// require("inc/lib/PHPMailer/src/Exception.php");
// require("inc/lib/PHPMailer/src/SMTP.php");

require("inc/ajax.php");
require("inc/helper.php");
require("inc/colors.php");
require("inc/theme_helper.php");
require("inc/shortcode.php");
require("inc/admin_panel.php");
require("inc/PL_DB.php");
/* Theme Setup */



function website_setup()
{
	register_nav_menus(array('header-menu-bar' => __('Header Menu Navbar')));
	register_nav_menus(array('header-menu-bar-mobile' => __('Header Menu Navbar Mobile')));
	register_nav_menus(array('client-account' => __('Client Account')));

	register_nav_menus(array('footer-1' => __('Footer 1')));
	register_nav_menus(array('footer-2' => __('Footer 2')));
	register_nav_menus(array('footer-3' => __('Footer 3')));


	register_nav_menus(array('header-client-log-in' => __('Header Client Log In')));
	register_nav_menus(array('header-client-info' => __('Header Client Info')));

	custom_logos();

}
add_action('init', 'website_setup');

function custom_logos()
{
	add_theme_support(
		'custom-logo',
		array(
			'flex-height' => true,
			'flex-width' => true,
			'header-text' => array('site-title', 'site-description'),
			'unlink-homepage-logo' => true,
		)
	);

	add_theme_support(
		'custom-header',
		array(
			'flex-height' => true,
			'flex-width' => true,
			'header-text' => array('site-title', 'site-description'),
			'unlink-homepage-logo' => true,
		)
	);

	add_theme_support('post-thumbnails');
}

function website_scripts()
{
	// not necessary
	wp_enqueue_script('tailwind', 'https://cdn.tailwindcss.com', array('jquery'));
	//wp_enqueue_script( 'forms', 'https://cdn.jsdelivr.net/npm/@tailwindcss/forms@0.5.4/src/index.min.js', array('jquery'));
	//wp_enqueue_script( 'aspect-ratio', 'https://cdn.jsdelivr.net/npm/@tailwindcss/aspect-ratio@0.4.2/src/index.min.js', array('jquery'));
	wp_enqueue_script('custom', get_template_directory_uri() . '/js/custom.js', array('jquery'));
	wp_enqueue_script('service-worker-reg', get_template_directory_uri() . '/js/service-worker-reg.js', [], null, true);
	wp_enqueue_script('one-signal', "https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js", [], null, true);

	
}
add_action('wp_enqueue_scripts', 'website_scripts');


function website_styles()
{
	wp_enqueue_style('style', get_stylesheet_uri());
	//wp_enqueue_style('tailwind', "https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/tailwind.min.css");
	wp_enqueue_style('main-font', "https://rsms.me/inter/inter.css");
	wp_enqueue_style('flags', get_template_directory_uri() . "/css/flags32-both.css");
}
add_action('wp_enqueue_scripts', 'website_styles');


function no_cache_headers() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}
add_action('init', 'no_cache_headers');

//PL_ref_code
function check_ref(){
	$ref_code = isset($_GET["ref"]) ? sanitize_text_field($_GET["ref"]) : null;
	if(isset($ref_code)){
		$_SESSION["PL_ref_code"] = $ref_code;
		Utility_PageBase::redirect("/");
	}
}
add_action('init', 'check_ref');

function pl_theme_switch(){
    $current = wp_get_theme();
    if($current->name == "Lemon Core"){
		copyManifestandHtaccessLine();
        create_ref_tables();
        create_ref_ordered();
    }
}


add_action("after_switch_theme", "pl_theme_switch");

/////////////////// Meta BOX

////// product_dp
function product_dp_meta_box()
{

	$screens = get_post_types();

	foreach ($screens as $screen) {
		if ($screen != "product")
			continue;
		add_meta_box(
			'product_dp',
			'Product DP (product_dp)',
			'product_dp_meta_box_callback',
			$screen
		);
	}
}

add_action('add_meta_boxes', 'product_dp_meta_box');

function product_dp_meta_box_callback($post)
{

	//wp_nonce_field('product_id_nonce', 'product_id_nonce');
	$value = get_post_meta($post->ID, 'product_dp', true);
	echo '<textarea style="width:100%" id="product_dp" name="product_dp">' . esc_attr($value) . '</textarea>';
	//delete_post_meta( $post->ID, 'product_DP', "value" );
	//var_dump(get_post_meta( $post->ID));
}

/* Save post meta on the 'save_post' hook. */
function product_dp_meta_box_data($post_id)
{
	$meta_key = 'product_dp';
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!isset($_POST[$meta_key])) {
		return;
	}

	$new_product_DP = sanitize_text_field($_POST[$meta_key]);
	$old_product_DP = get_post_meta($post_id, $meta_key, true);

	$result = update_post_meta($post_id, $meta_key, $new_product_DP, $old_product_DP);

}
add_action('save_post', 'product_dp_meta_box_data');

///// product short content
function product_short_content_meta_box()
{

	$screens = get_post_types();

	foreach ($screens as $screen) {
		if ($screen != "product")
			continue;
		add_meta_box(
			'product_short_content',
			'Short Content (product_short_content)',
			'product_short_content_meta_box_callback',
			$screen
		);
	}
}

add_action('add_meta_boxes', 'product_short_content_meta_box');

function product_short_content_meta_box_callback($post)
{

	$value = get_post_meta($post->ID, 'product_short_content', true);
	echo '<textarea maxlength="500" style="width:100%" id="product_dp" name="product_short_content">' . esc_attr($value) . '</textarea>';
}

/* Save post meta on the 'save_post' hook. */
function product_short_content_box_data($post_id)
{
	$meta_key = 'product_short_content';
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!isset($_POST[$meta_key])) {
		return;
	}

	$new_product_DP = sanitize_text_field($_POST[$meta_key]);
	$old_product_DP = get_post_meta($post_id, $meta_key, true);

	$result = update_post_meta($post_id, $meta_key, $new_product_DP, $old_product_DP);

}
add_action('save_post', 'product_short_content_box_data');

///// is product in store
function product_is_store_meta_box()
{

	$screens = get_post_types();

	foreach ($screens as $screen) {
		if ($screen != "product")
			continue;
		add_meta_box(
			'product_is_store',
			'In stock and ready to ship (product_is_store)',
			'product_is_store_meta_box_callback',
			$screen
		);
	}
}

add_action('add_meta_boxes', 'product_is_store_meta_box');

function product_is_store_meta_box_callback($post)
{

	//wp_nonce_field('product_id_nonce', 'product_id_nonce');
	$value = get_post_meta($post->ID, 'product_is_store', true);
	?>
	<p>Is product In stock and ready to ship:</p>
	<input type="radio" id="Yes" name="product_is_store" value="yes" <?= isset($value) ? "checked" : "" ?> 	<?= isset($value) && $value == "yes" ? "checked" : "" ?>> <label for="Yes">Yes</label><br>
	<input type="radio" id="No" name="product_is_store" value="no" <?= isset($value) && $value == "no" ? "checked" : "" ?>>
	<label for="No">No</label><br>
	<?php
}

/* Save post meta on the 'save_post' hook. */
function product_is_store_meta_box_data($post_id)
{
	$meta_key = 'product_is_store';
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!isset($_POST[$meta_key])) {
		return;
	}

	$new_product_DP = sanitize_text_field($_POST[$meta_key]);
	$old_product_DP = get_post_meta($post_id, $meta_key, true);

	$result = update_post_meta($post_id, $meta_key, $new_product_DP, $old_product_DP);

}
add_action('save_post', 'product_is_store_meta_box_data');


////////////////

///// how_to_use content
function product_how_to_use_meta_box()
{

	$screens = get_post_types();

	foreach ($screens as $screen) {
		if ($screen != "product")
			continue;
		add_meta_box(
			'product_how_to_use',
			'How to Use (product_how_to_use)',
			'product_how_to_use_meta_box_callback',
			$screen
		);
	}
}

add_action('add_meta_boxes', 'product_how_to_use_meta_box');

function product_how_to_use_meta_box_callback($post)
{

	$value = get_post_meta($post->ID, 'product_how_to_use', true);
	echo '<textarea maxlength="500" style="width:100%" id="product_how_to_use" name="product_how_to_use">' . esc_attr($value) . '</textarea>';
}

/* Save post meta on the 'save_post' hook. */
function product_how_to_use_box_data($post_id)
{
	$meta_key = 'product_how_to_use';
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!isset($_POST[$meta_key])) {
		return;
	}

	$new_product_DP = sanitize_text_field($_POST[$meta_key]);
	$old_product_DP = get_post_meta($post_id, $meta_key, true);

	$result = update_post_meta($post_id, $meta_key, $new_product_DP, $old_product_DP);

}
add_action('save_post', 'product_how_to_use_box_data');

/////////////////////////////////


function my_phpmailer_smtp( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host = SMTP_server;
    $phpmailer->SMTPAuth = SMTP_AUTH;
    $phpmailer->Port = SMTP_PORT;
    $phpmailer->Username = SMTP_username;
    $phpmailer->Password = SMTP_password;
    $phpmailer->SMTPSecure = SMTP_SECURE;
    $phpmailer->From = SMTP_FROM;
    $phpmailer->FromName = SMTP_NAME;
}
add_action( 'phpmailer_init', 'my_phpmailer_smtp' );

function restrict_admin_actions() {
    // Check if the current user is logged in and has the admin username
    if (is_admin() && is_user_logged_in()) {
        $current_user = wp_get_current_user();

        // Restrict actions if the user is not 'Admin'
        if ($current_user->user_login !== 'admin') {
            // Remove the capability to install/uninstall plugins and themes
            remove_menu_page('plugins.php'); // Removes Plugins menu
            remove_submenu_page('themes.php', 'themes.php'); // Removes Themes menu
            remove_submenu_page('themes.php', 'theme-install.php'); // Removes Add New Theme option

            // Prevent access to specific admin pages related to plugins, themes, and users
            $restricted_pages = [
                'plugins.php',
                'plugin-install.php',
                'plugin-editor.php',
                'themes.php',
                'theme-install.php',
                'theme-editor.php',
                'users.php',
                'user-new.php'
            ];

            // Redirect to dashboard if trying to access restricted pages
            global $pagenow;
            if (in_array($pagenow, $restricted_pages)) {
                wp_redirect(admin_url());
                exit;
            }

            // Remove capability to add new users
            add_filter('editable_roles', function($roles) {
                unset($roles['administrator']);
                return $roles;
            });
        }
    }
}
add_action('admin_init', 'restrict_admin_actions');
