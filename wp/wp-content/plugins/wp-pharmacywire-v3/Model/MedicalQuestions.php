<?php

/**
 * Medical_Questions
 **/
class Medical_Questions extends Utility_ModelBase
{
	public function getMedicalQuestions()
	{

		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();

		// create the request via XmlApi Request to get questions
		$medicalQuestions = new XmlApi_Request_PatientGetMedicalQuestions();
		$medicalQuestions->process($data);

		// return result
		$reply = $medicalQuestions->getData();

		return $reply;
	}

	public function setMedicalAnswers()
	{

		// prepare data to execute XML request
		$data = new stdClass();
		$data = (object) $_POST;

		// create the returned object
		$reply = new Model_Entity_Reply();

		// create the request via XmlApi Request to get list of coupons
		$medicalAnswers = new XmlApi_Request_PatientSetMedicalAnswers();
		$medicalAnswers->process($data);

		// return result
		$reply = $medicalAnswers->getData();

		return $reply;
	}
}
