<?php

class PW_JSON_MedicalQuestions extends PW_JSON
{
	public function getMedicalQuestions()
	{
		$medicalQuestions = new Medical_Questions();
		$medicalQuestions = $medicalQuestions->getMedicalQuestions();

		$questions = array();
		$medQ = $medicalQuestions->medical_questions;

		if (!empty($medQ)) {
			foreach ($medQ as $question) {
				$medicalQuestion = array();
				$medicalQuestion['id'] = (string)$question->id;
				$medicalQuestion['label'] = (string)$question->label;
				$medicalQuestion['question'] = (string)$question->question;
				$medicalQuestion['response'] = (string)$question->response;
				$medicalQuestion['comment'] = (string)$question->comment;
				$questions[] = $medicalQuestion;
			}
		}

		$reply = new Model_Entity_Reply();
		$reply->medical_questions = $questions;
		$reply->show_medical_questionnaire = ($medicalQuestions->show_medical_questionnaire == 1) ? 1 : 0;
		$reply->success = ($medicalQuestions->status == 'success') ? 1 : 0;

		echo $reply->toJSON();

		return;
	}

	public function setMedicalAnswers()
	{
		$medicalQuestions = new Medical_Questions();
		$medicalAnswers = $medicalQuestions->setMedicalAnswers();

		$reply = new Model_Entity_Reply();
		$reply->success = ($medicalAnswers->status == 'success') ? 1 : 0;
		$reply->messages = $medicalAnswers->messages;

		echo $reply->toJSON();

		return;
	}
}
