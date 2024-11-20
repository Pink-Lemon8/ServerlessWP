<?php

/**
 * XmlApi_Request_PatientSetInfo
 */
class XmlApi_Request_PatientSetInfo extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$transaction = $this->_beginTransaction('SetPatientInfo');

		$momex = ALIAS_MOMEX;
		$mt = ALIAS_MT;
		$pw = ALIAS_PW;

		$patient = $this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patient->patient_id));

		// optional fields
		$this->_prepareNode($patient, "$momex:firstname", $data->patient->firstname);
		$this->_prepareNode($patient, "$momex:lastname", $data->patient->lastname);
		$this->_prepareNode($patient, "$momex:phone", $data->patient->phone);
		$this->_prepareNode($patient, "$momex:areacode", $data->patient->areacode);
		$this->_prepareNode($patient, "$momex:phone-day", $data->patient->phone_day);
		$this->_prepareNode($patient, "$momex:areacode-day", $data->patient->areacode_day);
		$this->_prepareNode($patient, "$momex:fax", $data->patient->fax);
		$this->_prepareNode($patient, "$momex:areacode-fax", $data->patient->areacode_fax);
		$this->_prepareNode($patient, "$momex:email", $data->patient->email);

		// If personal questions are disabled, don't validate/require these fields
		if (get_option('pw_disable_personal_questions', 0) != 1) {
			$this->_prepareNode($patient, "$momex:sex", $data->patient->sex);
			$this->_prepareNode($patient, "$momex:dateofbirth", $data->patient->dateofbirth);
			$this->_prepareNode($patient, "$mt:height", null, array("$mt:feet" => $data->patient->height->feet, "$mt:inches" => $data->patient->height->inches));
			$weight = filter_var($data->patient->weight->value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$this->_prepareNode($patient, "$mt:weight", $weight, array("$mt:unit" => $data->patient->weight->unit));
		}

		$useChildResistantPackaging = $data->patient->child_resistant_packaging;
		if (empty($useChildResistantPackaging)) {
			$useChildResistantPackaging = get_option('pw_child_resistant_pkg_default', 'Yes');
		}
		$this->_prepareNode($patient, "$pw:child-resistant-packaging", $useChildResistantPackaging);

		$callForRefills = $data->patient->call_for_refills;
		if (empty($callForRefills)) {
			$callForRefills = get_option('pw_call_for_refills_default', 'True');
		}
		$this->_prepareNode($patient, "$pw:call-for-refills", $callForRefills);

		$address = $this->_prepareNode($patient, "$momex:address");
		$this->_prepareNode($address, "$momex:address1", $data->patient->address->address1);
		$this->_prepareNode($address, "$momex:address2", $data->patient->address->address2);
		$this->_prepareNode($address, "$momex:address3", $data->patient->address->address3);
		$this->_prepareNode($address, "$momex:city", $data->patient->address->city);
		$this->_prepareNode($address, "$momex:province", $data->patient->address->province);
		$this->_prepareNode($address, "$momex:country", $data->patient->address->country);
		$this->_prepareNode($address, "$momex:postalcode", $data->patient->address->postalcode);
		$this->_prepareNode($patient, "$pw:referral-program", $data->patient->referral_program);
		$this->_prepareNode($patient, "$pw:referrer-phone-number", $data->patient->referrer_phone_number);
		$this->_prepareNode($patient, "$pw:secondary-contact", $data->patient->secondary_contact);
		$this->_prepareNode($patient, "$pw:secondary-contact-phone", $data->patient->secondary_contact_phone);

		if (isset($data->patient->default_delivery_address_id) && !empty($data->patient->default_delivery_address_id)) {
			$this->_prepareNode($patient, "$momex:default-delivery-address", null, array("$momex:id" => $data->patient->default_delivery_address_id));
		}

		if (isset($data->patient->comment) && !empty($data->patient->comment)) {
			$this->_prepareNode($patient, "$momex:comment", $data->patient->comment);
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientSetInfo($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
