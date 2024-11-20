<?php

class PW_JSON_Patient extends PW_JSON
{
	public function login($onSuccessUrl = '')
	{
		$login = new Page_Login();
		$response = $login->processLoginJSON($onSuccessUrl);
		return $response;
	}

	public function logout()
	{
		$login = new Page_Login();
		$response = $login->processLogoutJSON();
		return $response;
	}

	public function getPatient()
	{
		$patientInfo = new Model_Entity_Reply();

		if (WebUser::isLoggedIn()) {
			$patientInfo = WebUser::getUserInfo();
		}

		return $patientInfo->toJSON();
	}

	/**
	 * Create User Account/Patient via JSON
	 * Save shipping address can be disabled if the patient
	 * creation is being created in conjuction with an order
	 * submit that would also otherwise save the address
	 */
	public function createPatient($saveShippingAddress = 1)
	{
		$register = new Page_Register();
		$response = $register->registerSubmitJSON($saveShippingAddress);
		return $response;
	}

	public function getAddresses()
	{
		if (WebUser::isLoggedIn()) {
			$patientModel = new Model_Patient();
			$patient = new stdClass();
			$patient->patientid = WebUser::getUserID();

			$addresses = $patientModel->getShippingAddresses($patient)->address;

			return json_encode($addresses);
		}

		return '{}';
	}
}
