<?php

/**
 * XmlApi_Request_GetAgents
 */
class XmlApi_Request_GetAgents extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('GetAgents');
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_GetAgents($xml_content);
		} elseif (($this->_getStatus() == XML_STATUS_INVALID) || ($this->_getStatus() == XML_STATUS_FAILURE)) {
			add_action('admin_notices', array('XmlApi_Request_GetAgents', 'pwire_config_notice__error'));
			return;
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}

	public function pwire_config_notice__error()
	{
		$class = 'notice notice-error';
		$message = __("Invalid XML Response. Please check the connection URL, UserID and Passkey, then save your settings again. The URL path must include '/momex/NavCode/xmlconnect' on the end. e.g.: https://[*yoursubdomain*].pharmacywire.com/momex/NavCode/xmlconnect", 'pwire');

		printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
	}
}
