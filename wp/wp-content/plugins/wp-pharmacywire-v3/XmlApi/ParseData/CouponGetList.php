<?php

/**
 * XmlApi_ParseData_Coupon_GetList
 */
class XmlApi_ParseData_Coupon_GetList extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_Coupon_GetList
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

		$nodeCoupons = $this->_getChildByName($this->_xml_doc, 'coupons', XML_PWIRE);
		$nodeCouponList = $this->_getChildrenByName($nodeCoupons, 'coupon', XML_PWIRE);

		$coupon = array();

		foreach ($nodeCouponList as $node) {
			$attrs = $node->attributes(XML_NS_MOMEX);
			$pwireAttrs = $node->attributes(XML_NS_PWIRE);

			// get main attributes off node
			$couponCode = (string) $pwireAttrs['coupon-code'];
			$coupon[$couponCode]['coupon-code'] = $couponCode;
			$coupon[$couponCode]['id'] = (int) $attrs['id'];
			$coupon[$couponCode]['patient_id'] = (int) $pwireAttrs['for-patient'];

			// get/setup coupon child node data
			$couponNodes = array(
				'description',
				'discount',
				'discount-method',
				'max-discount',
				'expiry',
				'global-number-of-uses',
				'number-of-uses-per-customer',
				'how-many-used',
				'status',
				'includes-free-shipping',
				'deletable',
				'expired',
				'use-on-first-order-only',
				'usable',
				'comments',
				'status-message',
				'is-administrative-coupon',
				'min-order-amount',
			);

			foreach ($couponNodes as $couponNode) {
				$coupon[$couponCode][$couponNode] = (string) $this->_getChildByName($node, $couponNode, XML_PWIRE);
			}

			$billingOptionsNode = $this->_getChildByName($node, 'billing-options', XML_PWIRE);

			if (is_object($billingOptionsNode)) {
				$billingOptionNodes = $this->_getChildrenByName($billingOptionsNode, 'billing-option', XML_PWIRE);

				$billingOptions = array();
				foreach ($billingOptionNodes  as $billingOptNodes) {
					$bOptAttrs = $billingOptNodes->attributes(XML_NS_PWIRE);
					array_push($billingOptions, (string) $bOptAttrs['id']);
				}
				$coupon[$couponCode]['billing-options'] = $billingOptions;
			}
		}

		$reply->coupon = $coupon;

		return $reply;
	}
}
