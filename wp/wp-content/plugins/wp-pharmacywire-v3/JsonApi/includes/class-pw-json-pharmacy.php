<?php

class PW_JSON_Pharmacy extends PW_JSON
{

	/* Pharmacy components used to generate the shopping cart */
	public function getPharmacyComponents()
	{
		$countryModel = new Model_Country();
		$countries = $countryModel->getAllowedCountryRegionsList();

		$allowedCountries = array_keys($countries);
		$defaultCountry = key($countries);
		if (in_array('USA', $allowedCountries)) {
			$defaultCountry = 'USA';
		}

		$rxMethods = '';

		// fix the way payment options are saved

		$billingTypes = PC_getPaymentMethods();
		$billingTypesUnfiltered = PC_getPaymentMethodsUnfiltered();

		$pwJSONSession = new PW_JSON_Session();
		$pwAutosave = $pwJSONSession->getAutosave();
		$jsonSessionData = $pwJSONSession->jsonSessionDataStore();
		$email = get_option('pw_email', '');
		$email_rx = (!empty(get_option('pw_email_rx'))) ? get_option('pw_email_rx') : $email;

		$pwireXMLDataCacheJSON = get_option('pw_xml_pwire_data_cache');
		$pwireXMLDataCache = json_decode($pwireXMLDataCacheJSON);
		$pwJsonCart = new PW_JSON_Cart;

		$response = array(
			'email' => $email,
			'email_rx' => $email_rx,
			'phone' =>  '1-' . get_option('pw_phone_area') . '-' . get_option('pw_phone'),
			'fax' =>  '1-' . get_option('pw_fax_area') . '-' . get_option('pw_fax'),
			'name' => get_option('pw_name', ''),
			'pharmacy_name' => get_option('pw_pharmacy', get_option('pw_name', '')),
			'business_name' => get_option('pw_business_name', get_option('pw_name', '')),
			'address' => array(
				'name' => get_option('pw_name', ''),
				'street' => get_option('pw_address', ''),
				'city' => get_option('pw_city', ''),
				'region' => get_option('pw_province', ''),
				'region_code' => get_option('pw_postal_code', ''),
				'country' => get_option('pw_country', ''),
			),
			'pw_autosave' => $pwAutosave,
			'countries' => array(
				'default' => $defaultCountry,
				'details' => $countries,
			),
			'cart' => array(
				'order_questions' => $pwJsonCart->getOrderQuestions()
			),
			'payment' => array(
				'methods' => $billingTypes,
				'methods_unfiltered' => $billingTypesUnfiltered,
				'draft_cart_message' => get_option('pw_draft_intro_message', 'Please mail a check, draft, or money order to:'),
			),
			'prescription' => array(
				'forward_prescription_options' => $pwireXMLDataCache->forward_prescription_options
			),
			'session' => $jsonSessionData,
		);

		echo json_encode($response);

		return;
	}

	public function getAllowedCountryRegionsList()
	{
		$countryModel = new Model_Country();
		$countryRegions = $countryModel->getAllowedCountryRegionsList();

		return json_encode($countryRegions);
	}

	public function getRegionsByCountry()
	{
		$countryModel = new Model_Country();
		$countryCode = 'CAN';
		$provinces = $countryModel->getRegionsByCountry($countryCode);

		return json_encode(array('Region' => $provinces));
	}

	public function getCountries()
	{
		$countryModel = new Model_Country();
		$countries = $countryModel->getAllowedCountryList();

		return json_encode($countries);
	}
}
