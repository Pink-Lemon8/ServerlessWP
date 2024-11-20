<?php

/**
 * XmlApi_Request_Order
 **/
class XmlApi_Request_OrderSubmit extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('SubmitOrder');
		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientid));

		$listItems = $data->items;
		$coupons = $data->coupons;
		$tags = $data->tags;
		$comments = $data->comments;
		$paymentinfo = $data->paymentinfo;
		$value_clientip = '';
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$value_clientip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["REMOTE_ADDR"])) {
			$value_clientip = $_SERVER["REMOTE_ADDR"];
		}

		$order = $this->_prepareNode($transaction, "$tr:order");
		$items = $this->_prepareNode($order, "$tr:items");

		// add products
		foreach ($listItems as $orderItem) {
			$item = $this->_prepareNode($items, "$tr:item", null, array("$momex:id" => $orderItem->productID));
			$this->_prepareNode($item, "$tr:quantity", $orderItem->quantity);
			$this->_prepareNode($item, "$tr:price", $orderItem->price);
		}

		if (is_array($coupons)) {
			foreach ($coupons as $couponCode => $couponData) {
				$this->_prepareNode($items, "$tr:item", null, array("$momex:coupon-code" => $couponCode));
			}
		}

		if (isset($data->shippingAddressRef) && ($data->shippingAddressRef > 0)) {
			$this->_prepareNode($order, "$tr:shippingaddress", null, array("$momex:id" => $data->shippingAddressRef));
		} elseif (isset($data->shipping_address1) && !empty($data->shipping_address1)) {
			$shippingAddress = $this->_prepareNode($order, "$tr:shippingaddress");
			$value_phone = (!empty($data->shipping_phone)) ? $data->shipping_phone : $data->phone;
			$value_areacode = (!empty($data->shipping_phoneAreaCode)) ? $data->shipping_phoneAreaCode : $data->phoneAreaCode;

			$this->_prepareNode($shippingAddress, "$momex:address1", $data->shipping_address1);
			$this->_prepareNode($shippingAddress, "$momex:address2", $data->shipping_address2);
			$this->_prepareNode($shippingAddress, "$momex:city", $data->shipping_city);
			$this->_prepareNode($shippingAddress, "$momex:province", $data->shipping_region);
			$this->_prepareNode($shippingAddress, "$momex:country", $data->shipping_country);
			$this->_prepareNode($shippingAddress, "$momex:postalcode", $data->shipping_regionCode);
			$this->_prepareNode($shippingAddress, "$momex:phone", $value_phone);
			$this->_prepareNode($shippingAddress, "$momex:areacode", $value_areacode);
		}

		$this->_prepareNode($order, "$tr:shippingfee", $data->shippingfee);
		
		$strPaymentType = strtolower($data->billing_type);
		switch ($strPaymentType) {
			case 'creditcard':
				$payment = $this->_prepareNode($order, "$tr:payment", null, array("$tr:type" => "creditcard"));
				$this->_prepareNode($payment, "$tr:cardtype", $paymentinfo->creditCard_type);
				$this->_prepareNode($payment, "$tr:cardnumber", $paymentinfo->creditCard_number);
				$this->_prepareNode($payment, "$tr:cvv", $paymentinfo->creditCard_cvv);
				$this->_prepareNode($payment, "$tr:expirymonth", $paymentinfo->expiryMonth);
				$this->_prepareNode($payment, "$tr:expiryyear", $paymentinfo->expiryYear);
				$this->_prepareNode($payment, "$tr:firstname", $paymentinfo->firstName);
				$this->_prepareNode($payment, "$tr:middlename", $paymentinfo->middleName);
				$this->_prepareNode($payment, "$tr:lastname", $paymentinfo->lastName);
				$this->_prepareNode($payment, "$tr:address", $paymentinfo->address1);
				$this->_prepareNode($payment, "$tr:address2", $paymentinfo->address2);
				$this->_prepareNode($payment, "$tr:city", $paymentinfo->city);
				$this->_prepareNode($payment, "$tr:state", $paymentinfo->region);
				$this->_prepareNode($payment, "$tr:country", $paymentinfo->country);
				$this->_prepareNode($payment, "$tr:postalcode", $paymentinfo->regionCode);
				$this->_prepareNode($payment, "$tr:areacode", $paymentinfo->phoneAreaCode);
				$this->_prepareNode($payment, "$tr:phone", $paymentinfo->phone);
				$this->_prepareNode($payment, "$tr:clientip", $value_clientip);
				break;
			case "custom":
					// fall through to same logic as draft
			case 'draft':
				$payment = $this->_prepareNode($order, "$tr:payment", null, array("$tr:type" => "draft"));
				if (isset($paymentinfo->amount) && !empty($paymentinfo->amount)) {
					$this->_prepareNode($payment, "$tr:amount", $paymentinfo->amount);
				}
				$this->_prepareNode($payment, "$tr:draftnumber", $paymentinfo->draftnumber);
				$this->_prepareNode($payment, "$tr:firstname", $paymentinfo->firstname);
				$this->_prepareNode($payment, "$tr:middlename", $paymentinfo->middlename);
				$this->_prepareNode($payment, "$tr:lastname", $paymentinfo->lastname);
				if (isset($paymentinfo->institution) && !empty($paymentinfo->institution)) {
					$paymentInstitution = $paymentinfo->institution;
					$iKey = strtolower($paymentinfo->institution);
					$paymentMethods = PC_getPaymentMethodsUnfiltered();
					if (!empty($paymentMethods["custom"]) && !empty($paymentMethods["custom"][$iKey])) {
						$paymentInstitution = $paymentMethods["custom"][$iKey]["label"];
					}
					$this->_prepareNode($payment, "$tr:institution", $paymentInstitution);
				}
				break;
			case 'eft':
				$payment = $this->_prepareNode($order, "$tr:payment", null, array("$tr:type" => "eft"));
				$this->_prepareNode($payment, "$tr:bankname", $paymentinfo->bankName);
				$this->_prepareNode($payment, "$tr:bankcity", $paymentinfo->bankCity);
				$this->_prepareNode($payment, "$tr:bankstate", $paymentinfo->bankState);
				$this->_prepareNode($payment, "$tr:fullname", $paymentinfo->nameOnCheque);
				$this->_prepareNode($payment, "$tr:transit", $paymentinfo->branchTransit);
				$this->_prepareNode($payment, "$tr:account", $paymentinfo->chequeAccount);
				$this->_prepareNode($payment, "$tr:chequenumber", $paymentinfo->chequeNumber);
				$this->_prepareNode($payment, "$tr:street", $paymentinfo->address);
				$this->_prepareNode($payment, "$tr:city", $paymentinfo->city);
				$this->_prepareNode($payment, "$tr:state", $paymentinfo->state);
				$this->_prepareNode($payment, "$tr:country", $paymentinfo->country);
				$this->_prepareNode($payment, "$tr:zip", $paymentinfo->postalcode);
				$this->_prepareNode($payment, "$tr:areacode", $paymentinfo->areacode);
				$this->_prepareNode($payment, "$tr:phone", $paymentinfo->phone);
				$this->_prepareNode($payment, "$tr:idnumber", $paymentinfo->idnumber);
				$this->_prepareNode($payment, "$tr:idtype", $paymentinfo->idtype);
				$this->_prepareNode($payment, "$tr:idstatecode", $paymentinfo->idstatecode);
				$this->_prepareNode($payment, "$tr:checktype", $paymentinfo->checktype);
				$this->_prepareNode($payment, "$tr:dob", $paymentinfo->dob);
				$this->_prepareNode($payment, "$tr:clientip", $value_clientip);
				break;
		}

		$this->_prepareNode($order, "$pw:Rx-forwarding", $data->rx_forwarding);

		$useChildResistantPackaging = $data->child_resistant_packaging;
		if (empty($useChildResistantPackaging)) {
			$useChildResistantPackaging = get_option('pw_child_resistant_pkg_default', 'Yes');
		}
		$this->_prepareNode($order, "$pw:child-resistant-packaging", $useChildResistantPackaging);

		if (isset($data->contact_patient) && !empty($data->contact_patient)) {
			$this->_prepareNode($order, "$pw:contact-patient", $data->contact_patient);
		}

		if (isset($data->special_handling) && !empty($data->special_handling)) {
			$this->_prepareNode($order, "$pw:special-handling", $data->special_handling);
		}

		if (!empty($comments)) {
			$commentsNode = $this->_prepareNode($order, "$tr:comments");
			foreach ($comments as $commentItem) {
				$this->_prepareNode($commentsNode, "$tr:comment", $commentItem);
			}
		}

		if (!empty($tags) && is_array($tags)) {
			$tagsNode = $this->_prepareNode($order, "$tr:tags");
			foreach ($tags as $tagCode => $tagData) {
				$this->_prepareNode($tagsNode, "$tr:tag", $tagData['value'], array("$tr:name" => $tagCode));
			}
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_OrderSubmit($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
