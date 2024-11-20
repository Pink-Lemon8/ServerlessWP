<?php

/**
 * XmlApi_ParseData_PatientGetInfo
 */
class XmlApi_ParseData_PatientGetInfo extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_PatientGetInfo
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

		// get patient
		$patient = new Model_Entity_Patient();
		$node =  $this->_xml_doc;
		$patientNode = $this->_getChildByName($node, 'patient', XML_PW);
		$attrs = $patientNode->attributes(XML_NS_MOMEX);
		$patient->id = (string)$attrs['id'];
		$patient->username = (string)$attrs['username'];

		// get children
		$patient->firstname = (string)$this->_getChildByName($patientNode, 'firstname', XML_MOMEX);
		$patient->lastname = (string)$this->_getChildByName($patientNode, 'lastname', XML_MOMEX);
		$patient->dateofbirth = (string)$this->_getChildByName($patientNode, 'dateofbirth', XML_MOMEX);
		$patient->phone = (string)$this->_getChildByName($patientNode, 'phone', XML_MOMEX);
		$patient->areacode = (string)$this->_getChildByName($patientNode, 'areacode', XML_MOMEX);
		$patient->phone_day = (string)$this->_getChildByName($patientNode, 'phone-day', XML_MOMEX);
		$patient->areacode_day = (string)$this->_getChildByName($patientNode, 'areacode-day', XML_MOMEX);
		$patient->fax = (string)$this->_getChildByName($patientNode, 'fax', XML_MOMEX);
		$patient->areacode_fax = (string)$this->_getChildByName($patientNode, 'areacode-fax', XML_MOMEX);
		$patient->email = (string)$this->_getChildByName($patientNode, 'email', XML_MOMEX);
		$patient->sex = (string)$this->_getChildByName($patientNode, 'sex', XML_MOMEX);

		// get height
		$heightNode = $this->_getChildByName($patientNode, 'height', XML_MT);
		$attrs = $heightNode->attributes(XML_NS_MOMEX_TERMS);
		$patient->height = new stdClass();
		$patient->height->feet = (string)$attrs['feet'];
		$patient->height->inches = (string)$attrs['inches'];

		// get weight
		$weightNode = $this->_getChildByName($patientNode, 'weight', XML_MT);
		$attrs = $weightNode->attributes(XML_NS_MOMEX_TERMS);
		$patient->weight = new stdClass();
		$patient->weight->unit = (string)$attrs['unit'];
		$patient->weight->value = filter_var((string)$weightNode, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		$patient->child_resistant_packaging = (string)$this->_getChildByName($patientNode, 'child-resistant-packaging', XML_PW);
		$patient->call_for_refills = (string)$this->_getChildByName($patientNode, 'call-for-refills', XML_PW);

		// referral - if enabled in PharmacyWire config
		$referralNode = $this->_getChildByName($patientNode, 'referral', XML_MOMEX);
		if (isset($referralNode)) {
			$patient->referral = new stdClass();
			$patient->referral->referral_code = (string)$this->_getChildByName($referralNode, 'referral-code', XML_MOMEX);
			$patient->referral->referral_balance = (string)$this->_getChildByName($referralNode, 'referral-balance', XML_MOMEX);
		}

		// get preferred-vendor
		$preferredNode = $this->_getChildByName($patientNode, 'preferred-vendor', XML_MOMEX);
		$attrs = $preferredNode->attributes(XML_NS_MOMEX);
		$patient->preferred_vendor = new stdClass();
		$patient->preferred_vendor->id = (string)$attrs['id'];

		$default_delivery_address_node = $this->_getChildByName($patientNode, 'default-delivery-address', XML_MOMEX);
		if (isset($default_delivery_address_node)) {
			$default_delivery_address_attrs = $default_delivery_address_node->attributes(XML_NS_MOMEX);
			$patient->default_delivery_address_id = (string)$default_delivery_address_attrs['id'];
		} else {
			$patient->default_delivery_address_id = 0;
		}

		// get address
		$addressNode = $this->_getChildByName($patientNode, 'address', XML_MOMEX);
		$patient->address = new stdClass();
		$patient->address->address1 = (string)$this->_getChildByName($addressNode, 'address1', XML_MOMEX);
		$patient->address->address2 = (string)$this->_getChildByName($addressNode, 'address2', XML_MOMEX);
		$patient->address->address3 = (string)$this->_getChildByName($addressNode, 'address3', XML_MOMEX);
		$patient->address->city = (string)$this->_getChildByName($addressNode, 'city', XML_MOMEX);
		$patient->address->province = (string)$this->_getChildByName($addressNode, 'province', XML_MOMEX);
		$patient->address->region = (string)$this->_getChildByName($addressNode, 'province', XML_MOMEX);
		$patient->address->country = (string)$this->_getChildByName($addressNode, 'country', XML_MOMEX);
		$patient->address->postalcode = (string)$this->_getChildByName($addressNode, 'postalcode', XML_MOMEX);
		$patient->address->regioncode = (string)$this->_getChildByName($addressNode, 'postalcode', XML_MOMEX);
		$patient->address->phone = $patient->phone;
		$patient->address->areacode = $patient->areacode;

		// get ad-hoc attributes
		$attributes = array();
		$attributeNodes = $patientNode->xpath(XML_MOMEX . ':attributes/' . XML_MOMEX . ':attribute');
		foreach ($attributeNodes as $attributeNode) {
			$attribute = new stdClass();
			$attr_attrs = $attributeNode->attributes(XML_NS_MOMEX);
			$attribute->name = (string)$attr_attrs['name'];
			$attribute->type = (string)$attr_attrs['type'];
			if (!$attribute->type) {
				$attribute->type = 'text';
			}
			$attrvalues = $attributeNode->xpath(XML_MOMEX . ':value');
			$values = array();
			foreach ($attrvalues as $attrvalue) {
				$values[] = (string)$attrvalue;
			}
			if ($attribute->type === 'boolean') {
				$attribute->value = $values[0];
			} else {
				$attribute->values = $values;
			}
			$attributes[] = $attribute;
		}
		$patient->attributes = $attributes;

		// get patient-doctors

		// *** No reason to have this detail in the plugin currently - removing for now

		// $listDoctor = array();

		// $doctor = new Model_Entity_Doctor();
		// $nodeDoctors = $this->_getChildByName($patientNode, 'patient-doctors', XML_PW);
		// if (!is_null($nodeDoctors)) {
		// 	$nodeDoctorList = $this->_getChildrenByName($nodeDoctors, 'patient-doctor', XML_PW);
		// 	foreach ($nodeDoctorList as $nodeDoctor) {
		// 		$attrs = $nodeDoctor->attributes(XML_NS_MOMEX);
		// 		$doctor = new Model_Entity_Doctor();
		// 		$doctor->id = (string)$attrs['id'];
		// 		$attrs = $nodeDoctor->attributes(XML_NS_PWIRE);
		// 		$doctor->doctor_id = (string)$attrs['doctor-id'];
		// 		$doctor->specialty = (string)$this->_getChildByName($nodeDoctor, 'specialty', XML_PW);
		// 		$doctor->lastconsult = (string)$this->_getChildByName($nodeDoctor, 'lastconsult', XML_PW);
		// 		$doctor->consultreason = (string)$this->_getChildByName($nodeDoctor, 'consultreason', XML_PW);
		// 		$listDoctor[] = $doctor;
		// 	}
		// 	$patient->patient_doctors = $listDoctor;
		// }

		$reply->patient = $patient;

		// get messages
		$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
		$replyMessages = $messageParser->process();
		$reply->messages = null;
		if ($replyMessages instanceof Model_Entity_Reply) {
			$reply->messages = $replyMessages->messages;
		}

		// return parsed data
		return $reply;
	}
}
