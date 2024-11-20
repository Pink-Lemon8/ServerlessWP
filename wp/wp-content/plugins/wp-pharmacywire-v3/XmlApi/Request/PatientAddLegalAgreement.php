<?php

/**
 * XmlApi_Request_PatientAddLegalAgreement
 */
class XmlApi_Request_PatientAddLegalAgreement extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;
		$mt = ALIAS_MT;
		$transaction = $this->_beginTransaction('LegalAgreement');

		// assign data patient
		$agreement = $this->_prepareNode($transaction, "$pw:agreement");
		$this->_prepareNode($agreement, "$pw:patient", null, array("$momex:id" => $data->patient_id));
		$this->_prepareNode($agreement, "$mt:fullname", $data->fullname);
		$this->_prepareNode($agreement, "$mt:agree", $data->agree);
		$this->_prepareNode($agreement, "$tr:date", $data->date);
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientAddLegalAgreement($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
