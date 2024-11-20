<?php

class PW_JSON_Shortcodes extends PW_JSON
{
	public function init()
	{
		add_shortcode('PwireJSON', array(&$this, 'pwireJSONShortcode'));
	}

	public function pwireJSONShortcode($atts)
	{
		$a = shortcode_atts(array(
			'template' => '',
			'action' => '',
		), $atts);

		if (!empty($a['template'])) {
			require_once UTILITY_FOLDER . 'xtemplate.class.php';

			switch ($a['template']) {
				case 'checkout':
					// if there are no items on the cart redirect to the shopping cart summary
					if (!Cart::haveItems()) {
						Utility_PageBase::redirect(PC_getShoppingURL());
					}

					wp_enqueue_script('json-checkout-js');

					$xtemplateFile = 'quick_checkout.phtml';
					$templateDirectory = $this->_getJSONTemplateDir($xtemplateFile);

					$xtpl = new XTemplate($xtemplateFile, $templateDirectory);
					$xtpl->assign('JSON_REQUEST_URL', JSON_REQUEST_URL);

					if (!WebUser::isLoggedIn()) {
						$xtpl->parse('PAGE.NEW_ACCOUNT');
					} else {
						$xtpl->parse('PAGE.EXISTING_ACCOUNT');
					}
					$xtpl->parse('PAGE.SUBMIT_ORDER');

					$xtpl->assign('JSON_TEMPLATE_URL', JSON_TEMPLATE_URL);

					$xtpl->parse('PAGE');

					return $xtpl->outWP('PAGE');

					break;

				case 'login':

					// if already logged in go straight to checkout
					if (WebUser::isLoggedIn()) {
						Utility_PageBase::redirect($a['action']);
					}

					wp_register_script('json-login-js', JSON_TEMPLATE_URL . 'js/json_login.js', array('jquery'), '1.0', true);

					$json_properties = array(
						'template_url' => JSON_TEMPLATE_URL,
						'request_url' => JSON_REQUEST_URL,
						'plugin_url' => PWIRE_PLUGIN_URL,
						'account_url' => PC_getProfileUrl(),
						'login_action' => $a['action'],
					);

					wp_localize_script('json-login-js', 'pw_json_login', $json_properties);
					wp_enqueue_script('json-login-js');

					$xtemplateFile = 'quick_login.phtml';
					$templateDirectory = $this->_getJSONTemplateDir($xtemplateFile);

					$xtpl = new XTemplate($xtemplateFile, $templateDirectory);

					$xtpl->assign('JSON_REQUEST_URL', JSON_REQUEST_URL);
					$xtpl->assign('ACTION', $a['action']);

					$xtpl->parse('PAGE');

					return $xtpl->outWP('PAGE');

					break;
			}
		}
	}

	public function _getJSONTemplateDir($fileName)
	{
		$themeDirectory = get_stylesheet_directory() . '/pharmacywire/templates/';

		// check theme and parent theme for pharmacywire template directory
		if (!file_exists($themeDirectory . $fileName)) {
			$themeDirectory = get_template_directory() . '/pharmacywire/templates/';
			if (!file_exists($themeDirectory . $fileName)) {
				$themeDirectory = JSON_TEMPLATE_FOLDER;
			}
		}

		$template = $themeDirectory;

		return $template;
	}
}
