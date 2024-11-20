<?php

class PW_JSON
{

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * The Constructor.
	 */
	public function __construct()
	{
		if (function_exists('__autoload')) {
			spl_autoload_register('__autoload');
		}

		spl_autoload_register(array($this, 'autoload'));

		$this->include_path = untrailingslashit(dirname(__FILE__)) . '/includes/';
	}

	/**
	 * Initialize method
	 */
	public function init()
	{
		$pwJsonShortcodes = new PW_JSON_Shortcodes();
		$pwJsonShortcodes->init();

		// If using default json cart, load it's scripts
		if (get_option('pw_default_json_theme', 1)) {
			$this->default_json_enqueue();
		}
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class Class name.
	 * @return string
	 */
	private function get_file_name_from_class($class)
	{
		return 'class-' . str_replace('_', '-', $class) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path File path.
	 * @return bool Successful or not.
	 */
	private function load_file($path)
	{
		if ($path && is_readable($path)) {
			include_once $path;
			return true;
		}
		return false;
	}

	/**
	 * Auto-load PW classes on demand to reduce memory consumption.
	 *
	 * @param string $class Class name.
	 */
	public function autoload($class)
	{
		$class = strtolower($class);

		if (0 !== strpos($class, 'pw_')) {
			return;
		}

		$file = $this->get_file_name_from_class($class);
		$path = '';

		if (0 === strpos($class, 'pw_json')) {
			$path = $this->include_path;
		}

		if (empty($path) || !$this->load_file($path . $file)) {
			$this->load_file($this->include_path . $file);
		}
	}

	/**
	 * Enqueue PW js/css
	 *
	 * @param string $class Class name.
	 */
	public function default_json_enqueue()
	{
		$jsonCheckoutJS = array(
			'handle' => 'json-checkout-js',
			'src' => JSON_TEMPLATE_URL . 'js/json_checkout.js',
			'deps' => array('jquery', 'cleave-js'),
			'ver' => '1.0',
			'in_footer' => true
		);
		$jsonLoginJS = array(
			'handle' => 'json-login-js',
			'src' => JSON_TEMPLATE_URL . 'js/json_login.js',
			'deps' => array('jquery'),
			'ver' => '1.0',
			'in_footer' => true
		);
		// Javascript libraries cart depends on
		// https://github.com/nosir/cleave.js       
		$cartVendorJS = array(
			'handle' => 'cleave-js',
			'src' => PWIRE_PLUGIN_URL . 'Themes/vendor/cleave/js/cleave.min.js',
			'deps' => array('jquery'),
			'ver' => '1.0',
			'in_footer' => true
		);

		$jsFiles = array($jsonCheckoutJS, $jsonLoginJS, $cartVendorJS);

		$this->pwire_json_js($jsFiles);

		$this->default_json_css_enqueue();
	}

	public function default_json_css_enqueue()
	{
		/************* ENQUEUE CSS *****************/
		function pw_json_styles()
		{
			wp_register_style('json-checkout-css', JSON_TEMPLATE_URL . 'css/json_checkout.css', array(), '1.0', 'all');
			wp_enqueue_style('json-checkout-css');
		}

		add_action('wp_enqueue_scripts', 'pw_json_styles');
	}

	public static function pwire_localize_script($jsFileHandle)
	{
		$json_properties = array(
			'template_url' => JSON_TEMPLATE_URL,
			'request_url' => JSON_REQUEST_URL,
			'plugin_url' => PWIRE_PLUGIN_URL,
			'login_url' => PC_getLoginUrl(),
			'logout_url' => PC_getLogoutUrl(),
			'register_url' => PC_getRegisterUrl(),
			'shopping_cart_url' => PC_getShoppingURL(),
			'checkout_url' => PC_getJSONCheckout(),
			'account_url' => PC_getProfileUrl(),
			'upload_url' => PC_getUploadDocumentUrl(),
			'nonce' => wp_create_nonce('pw-nonce'),
		);

		wp_localize_script($jsFileHandle, 'pw_json', $json_properties);
	}

	public function pwire_json_js($jsFiles)
	{

		/************* ENQUEUE JS *************************/
		function pw_json_js()
		{
			if (empty($jsFiles)) {
				$jsonCheckoutJS = array(
					'handle' => 'json-checkout-js',
					'src' => JSON_TEMPLATE_URL . 'js/json_checkout.js',
					'deps' => array('jquery', 'cleave-js'),
					'ver' => '1.0',
					'in_footer' => true
				);
				$jsonLoginJS = array(
					'handle' => 'json-login-js',
					'src' => JSON_TEMPLATE_URL . 'js/json_login.js',
					'deps' => array('jquery'),
					'ver' => '1.0',
					'in_footer' => true
				);
				// Javascript libraries cart depends on
				// https://github.com/nosir/cleave.js       
				$cartVendorJS = array(
					'handle' => 'cleave-js',
					'src' => PWIRE_PLUGIN_URL . 'Themes/vendor/cleave/js/cleave.min.js',
					'deps' => array('jquery'),
					'ver' => '1.0',
					'in_footer' => true
				);

				$jsFiles = array($jsonCheckoutJS, $jsonLoginJS, $cartVendorJS);
			}

			foreach ($jsFiles as $file) {
				wp_register_script($file['handle'], $file['src'], $file['deps'], $file['ver'], $file['in_footer']);
			}

			PW_JSON::pwire_localize_script('json-checkout-js');
			PW_JSON::pwire_localize_script('json-login-js');
			// enqued in pwireJSONShortcode
			// wp_enqueue_script( 'json-checkout-js' );
		}

		add_action('wp_enqueue_scripts', 'pw_json_js');
	}
}

new PW_JSON();
