<?php

class PW_JSON_Shipping extends PW_JSON
{
	public function getShipping()
	{
		$response = Shipping::getShippingJSON();
		echo json_encode($response);
	}

	public function setShipping($shippingData = null)
	{
		$data = array();
		$data = $shippingData ?? $_POST;

		$reply = new Model_Entity_Reply();
		$reply = '';

		if (is_int($data['shipping_addressRefID'])) {
			// shipping address passed so switch to using the ref shipping address
			Shipping::setAddressRef($data['shipping_addressRefID']);
		} else {
			$reply = $this->addShippingAddress($data);
		}

		echo $reply->toJson();
	}

	public function addShippingAddress($data)
	{
		// new shipping address
		$modelPatient = new Model_Patient();
		$address = new stdClass();
		$address->patientID = WebUser::getUserID();
		$address->address1 = $data['shipping_address1'];
		$address->address2 = $data['shipping_address2'];
		$address->address3 = '';
		$address->city = $data['shipping_city'];
		$address->province = $data['shipping_region'];
		$address->country = $data['shipping_country'];
		$address->postalcode = $data['shipping_regionCode'];

		if (!empty($data['shipping_phoneAreaCode']) && !empty($data['shipping_phone'])) {
			$address->areacode = $data['shipping_phoneAreaCode'];
			$address->phone = $data['shipping_phone'];
		} else {
			$address->areacode = $_SESSION['Account_phoneAreaCode'];
			$address->phone = $_SESSION['Account_phone'];
		}

		$reply = $modelPatient->addShippingAddress($address);

		return $reply;
	}

	public function editShipping()
	{
		$data = array();
		$data = $_POST;

		$reply = new Model_Entity_Reply();
		$reply = '';

		if ($data['shipping_addressRefID']) {
			$modelPatient = new Model_Patient();
			$address = new stdClass();

			$address->patientID = WebUser::getUserID();
			$address->shippingAddressID = $data['shipping_addressRefID'];

			$address->address1 = $data['shipping_address1'];
			$address->address2 = $data['shipping_address2'];
			$address->address3 = '';
			$address->city = $data['shipping_city'];
			$address->province = $data['shipping_region'];
			$address->country = $data['shipping_country'];
			$address->postalcode = $data['shipping_regionCode'];
			$address->areacode = $data['shipping_phoneAreaCode'];
			$address->phone = $data['shipping_phone'];

			$reply = $modelPatient->editShippingAddress($address);
		}

		echo $reply->toJson();
	}

	public function deleteShipping()
	{
		$data = array();
		$data = $_POST;

		$reply = new Model_Entity_Reply();
		$reply = '';

		if ($data['shipping_addressRefID']) {
			$modelPatient = new Model_Patient();
			$address = new stdClass();

			$address->patientID = WebUser::getUserID();
			$address->shippingAddressID = $data['shipping_addressRefID'];

			$reply = $modelPatient->deleteShippingAddress($address);
		}

		echo $reply->toJson();
	}
}
