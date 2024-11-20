<?php

class PW_JSON_Billing extends PW_JSON
{
	public function getBilling()
	{
		$response = Billing::getBillingJSON();
		echo json_encode($response);
	}

	public function setBilling()
	{
		$data = array();
		$data = $_POST;

		$reply = new Model_Entity_Reply();

		if ($data['ShippingAddressID']) {
			$modelPatient = new Model_Patient();
			$address = new stdClass();

			// To Do

			// $address->patientID = $data['PatientId'];
			// $address->shippingAddressID = $data['ShippingAddressID'];

			// $address-> = $data['firstName'];
			// $address-> = $data['lastName'];

			// $address->address1 = $data['Address1'];
			// $address->address2 = $data['Address2'];
			// $address->address3 = '';
			// $address->city = $data['City'];
			// $address->province = $data['Province'];
			// $address->country = $data['Country'];
			// $address->postalcode = $data['PostalCode'];
			// $address->areacode = $data['AreaCode'];
			// $address->phone = $data['phone'];

			// $reply = $modelPatient->editShippingAddress($address);
		}

		echo $reply->toJson();
	}
}
