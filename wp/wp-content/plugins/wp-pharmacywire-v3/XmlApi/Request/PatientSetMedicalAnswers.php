<?php

/**
 * XmlApi_Request_PatientSetMedicalAnswers
 */
class XmlApi_Request_PatientSetMedicalAnswers extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$pw = ALIAS_PW;
		$momex = ALIAS_MOMEX;
		$mt = ALIAS_MT;
		$transaction = $this->_beginTransaction('SetMedicalAnswers');

		$patientID = null;
		if (WebUser::isLoggedIn()) {
			$patientID = WebUser::GetUserID();

			$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $patientID));
			$medQ = $this->_prepareNode($transaction, "$pw:medical-questionnaire");

			if (!empty($data->response)) {
				$responseKeys = array_keys($data->response);

				foreach ($data->response as $key => $value) {

					$questionNode = $this->_prepareNode($medQ, "$pw:question", null, array("$momex:id" => $key));
					if (!empty($data->response[$key])) {
						$responseNode = $this->_prepareNode($questionNode, "$mt:response", $data->response[$key]);
					}

					if (!empty($data->comment[$key])) {
						$commentNode = $this->_prepareNode($questionNode, "$momex:comment", stripcslashes($data->comment[$key]));
					}
				}
			}
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientSetMedicalAnswers($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
