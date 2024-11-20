<?php

/**
 * XmlApi_ParseData_PatientGetMedicalQuestions
 */
class XmlApi_ParseData_PatientGetMedicalQuestions extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_PatientGetMedicalQuestions
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

		$medicalQuestionnaireNode = $this->_getChildByName($this->_xml_doc, 'medical-questionnaire', XML_PWIRE);
		$medicalQuestionsNode = $this->_getChildrenByName($medicalQuestionnaireNode, 'question', XML_PWIRE);

		$medQAttrs = $medicalQuestionnaireNode->attributes();

		$medicalQuestions = array();
		if (!empty($medicalQuestionsNode)) {
			foreach ($medicalQuestionsNode as $node) {
				$attrs = $node->attributes(XML_NS_MOMEX);

				$medicalQuestion = new Model_Entity_Reply();
				$medicalQuestion->id = (string)$attrs['id'];

				$medicalQuestion->label = (string)$this->_getChildByName($node, 'label', XML_MT);
				$medicalQuestion->question = (string)$this->_getChildByName($node, 'text', XML_MT);
				$medicalQuestion->response = (string)$this->_getChildByName($node, 'response', XML_MT);
				$medicalQuestion->comment = (string)$this->_getChildByName($node, 'comment', XML_MOMEX);

				// if no label is set, then don't show/add the question
				if (!empty((string) $medicalQuestion->label)) {
					$medicalQuestions[] = $medicalQuestion;
				}
			}
		}

		$reply->medical_questions = $medicalQuestions;

		// if needs-review is not set or true Medical Questionnaire
		$needsReview = (string) $medQAttrs['needs-review'];
		$verified = (string) $medQAttrs['verified'];

		$reply->show_medical_questionnaire = 0;

		if (get_option('pw_show_medq_on_checkout', 1) && (empty($verified) || ($needsReview === 'true'))) {
			$reply->show_medical_questionnaire = 1;
		}

		// return parsed data
		return $reply;
	}
}
