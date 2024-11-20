<?php

/**
 * XmlApi_Coupon_GetCouponsVerify
 */
class XmlApi_Coupon_GetCouponsVerify extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$pw = ALIAS_PW;
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('VerifyCoupons');

		$patientID = $data->patientID;
		if (is_int($patientID) && $patientID > 0) {
			$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $patientID));
		}

		$coupons = $this->_prepareNode($transaction, "$pw:coupons");

		// setup node(s) for already active coupons
		if (isset($data->activeCoupons)) {
			$activeCoupons = $data->activeCoupons;
			if (!empty($activeCoupons)) {
				$activeCoupons = explode(",", $activeCoupons);
				foreach ($activeCoupons as $actCoupon) {
					$this->_prepareNode($coupons, "$pw:coupon", null, array("$pw:coupon-code" => $actCoupon));
				}
			}
		}

		// add new coupon last, so PharmacyWire knows which coupon was just added
		if (isset($data->couponCode)) {
			$this->_prepareNode($coupons, "$pw:coupon", null, array("$pw:coupon-code" => $data->couponCode));
		}

		// setup payment method if active
		if (!empty($data->paymentMethod)) {
			$payment = $this->_prepareNode($transaction, "$tr:payment", null, array("$tr:type" => $data->paymentMethod));
			$this->_prepareNode($payment, "$tr:firstname", $data->paymentFirstName);
			$this->_prepareNode($payment, "$tr:lastname", $data->paymentLastName);
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_Coupon_GetList($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}

		$retObject = $objParse->process();
		return $retObject;
	}
}
