<?php

/**
 * XmlApi_ParseData_Coupon_GetList
 */
class XmlApi_ParseData_GetForwardPrescriptionOptions extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_GetForwardPrescriptionOptions
	 */
	public function __construct($xml_content)
	{
		parent::__construct($xml_content);
	}

	/**
	 * Override the base function
	 *
	 */
	public function _parseXml()
	{
		parent::_parseXml();
		$reply = new Model_Entity_Reply();
		$reply->status = (string)$this->_xml_doc->status;
		$reply->type = (string)$this->_xml_doc->type;

		$optionsArray = array();

		if ((string)$reply->status === XML_STATUS_SUCCESS) {
			$nodeFwdRxOptions = $this->_getChildByName($this->_xml_doc, 'options', XML_PWIRE);
			$nodeOption = $this->_getChildrenByName($nodeFwdRxOptions, 'option', XML_PWIRE);

			foreach ($nodeOption as $index => $fwdRxOptions) {
				$nodeLabel = $this->_getChildrenByName($fwdRxOptions, 'label', XML_PWIRE);
				$attrs = $fwdRxOptions->attributes(XML_NS_MOMEX);
				$pwireAttrs = $fwdRxOptions->attributes(XML_NS_PWIRE);

				$optionStatus = (string) $pwireAttrs['status'];
				$optionValue = (string) $pwireAttrs['value'];
				$optionFrontendVisiblity = (string) $pwireAttrs['visibility-frontend'];
				$optionSortOrder = (string) $pwireAttrs['sort-order'];

				$labelFrontend = '';
				foreach ($nodeLabel as $label) {
					$labelPwireAttrs = $label->attributes(XML_NS_PWIRE);
					if ($labelPwireAttrs['type'] == 'frontend' || empty($labelFrontend)) {
						$labelFrontend = (string) $label;
					}
				}

				if ($optionStatus == 'active' && $optionFrontendVisiblity == 'show') {
					$optionsArray[$optionValue] = array(
						'value' => $optionValue,
						'label' => $labelFrontend,
						'sort_order' => $optionSortOrder,
					);
				}
			}
			uasort($optionsArray, function ($a, $b) {
				return $a['sort_order'] <=> $b['sort_order'];
			});
		}

		$reply->forward_prescription_options = $optionsArray;

		return $reply;
	}
}
