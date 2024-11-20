<?php

/**
 * Page_Base
 */
require_once UTILITY_FOLDER . 'xtemplate.class.php';

if (!class_exists('Mobile_Detect')) {
	require_once VENDOR_FOLDER . 'mobile-detect/Mobile_Detect.php';
}

abstract class Utility_PageBase extends XTemplate
{
	public $listError = array();
	public function __construct()
	{
		parent::__construct('page_empty.phtml');
	}

	/**
	 * Render the output
	 *
	 */
	public function render()
	{
		$this->_process();

		// process output page block
		parent::parse('page');

		// return $this->text('page');
		return $this->outWP('page');
	}

	/**
	 * Render the output as JSON formatted data
	 *
	 */
	public function render_json()
	{
		return $this->_process('json');
	}

	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	abstract public function _process();

	public function parse($blockName)
	{
		parent::parse('page.' . $blockName);
	}

	public function setTemplate($template)
	{

		if (!is_array($template)) {
			$template = [$template];
		}

		$tmlMatchFound = 0;
		foreach ($template as $tmlName) {
			$fileName = null;
			$desktopFileName = $tmlName . '.phtml';
			$mobileFileName = $tmlName . '-mobile.phtml';
			$themeDirectory = get_stylesheet_directory() . '/pharmacywire/templates/';
			$pluginDirectory = XTPL_DIR;

			$fileDirectory = $pluginDirectory;
			// if on mobile and mobileFileName exists, use it as the fileName
			if (class_exists('Mobile_Detect')) {
				$detect = new Mobile_Detect;
				if ($detect->isMobile()) {
					// check if mobile template exists in theme
					if (file_exists($themeDirectory . $mobileFileName)) {
						$tmlMatchFound = 1;
						$fileName = $mobileFileName;
						$fileDirectory = $themeDirectory;
						// check if mobile template exists in plugin
					} elseif (file_exists($pluginDirectory . $mobileFileName)) {
						$tmlMatchFound = 1;
						$fileName = $mobileFileName;
						$fileDirectory = $pluginDirectory;
					}
				}
			}

			// if not on mobile, or mobile template not found - use desktop
			if ($fileName === null) {
				$fileName = $desktopFileName;
				if (file_exists($themeDirectory . $desktopFileName)) {
					$tmlMatchFound = 1;
					$fileDirectory = $themeDirectory;
				} elseif (file_exists($pluginDirectory . $desktopFileName)) {
					$tmlMatchFound = 1;
					$fileDirectory = $pluginDirectory;
				}
			}

			// if a templatematch is found use that and stop looking for more
			if ($tmlMatchFound === 1) {
				$this->restart($fileName, $fileDirectory);
				break;
			}
		}
		return;
	}

	/**
	 * Get request variable
	 *
	 * @param mixed $varName
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function _getRequest($varName, $defaultValue = '')
	{
		global $wp_query;
		if (isset($wp_query->query_vars[$varName]) && ($wp_query->query_vars[$varName])) {
			$var_value = $wp_query->query_vars[$varName];
		} elseif (isset($_REQUEST[$varName])) {
			$var_value = $_REQUEST[$varName];
		} elseif (isset($_POST[$varName])) {
			$var_value = $_POST[$varName];
		} else {
			return $defaultValue;
		}
		$var_value = preg_replace('/[<>].+/', '', $var_value);
		return $var_value;
	}

	// redirect to url
	public static function redirect($url, $status = 302)
	{
		// don't follow redirect if from json request eg. shortcode while editing/saving WP page
		if (!wp_is_json_request()) {
			if (headers_sent()) {
				echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
			} else {
				wp_redirect($url, $status);
			}
			exit;
		}
	}

	// parse error message
	public function parseErrorMessage()
	{
		foreach ($this->listError as $key => $value) {
			$this->assign($key, $value);
		}
	}
	//display error when request xml
	public function displayErrorRequest($result)
	{
		if (!Utility_Common::isReplySuccess($result)) {
			$messages = new Utility_Messages;
			$html = Utility_Html::displayResult($result);
			$messages->setNotification('Error', $html);
			//				$this->assign('RESULT_MESSAGES', $html);
		}
	}
}
