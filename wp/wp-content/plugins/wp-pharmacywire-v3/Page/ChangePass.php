<?php
class Page_ChangePass extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		if (!(WebUser::isLoggedIn()) && !(WebUser::isTempLogin())) {
			$this->redirect(PC_getLoginUrl());
		}

		$this->setTemplate("page_change_password");
		$this->processChangePass();
	}

	// process when user on changePass page
	public function processChangePass()
	{
		$action = strtoupper(trim($this->_getRequest("action")));
		$this->_populateForm();
		if ($action == "UPDATE") {
			$valid = $this->validData();
			if ($valid) {
				$user = $this->_checkPassword();
				if ($user->authenticated == 'true' || $user->authenticated == 'reset-password') {
					$save = $this->_updatePassword();
					if ($save->status == 'success') {

						// remove the temp login info and now that password has been updated
						// log in the user
						WebUser::removeTempLoginInfo();
						if ($user->type === 'Patient') {
							WebUser::setLogin($user);
						}

						$url = PC_getProfileUrl();

						$this->redirect($url);
						return;
					} else {
						$this->listError['ERROR_CURRENTPASS'] = '<label class="form-error" data-form-error-for="oldpass" style="display: block;">' . PC_genErrorMessage($save->messages) . '</label>';
					}
				} else {
					$this->listError['ERROR_CURRENTPASS'] = '<label class="form-error" data-form-error-for="oldpass"  style="display: block;">' . PC_genErrorMessage('The current password is not correct.') . '</label>';
				}
			}
		}
		if ($action == "CANCEL") {
			$url = PC_getProfileUrl();
			$this->redirect($url);
		}

		$this->parseErrorMessage();
		$this->displayChangePass();
	}

	public function displayChangePass()
	{
		$this->parse('CHANGEPASS');
	}

	public function validData()
	{
		$valueCurrentPass = (string)$this->_getRequest('oldpass');
		$valueNewPass = (string)$this->_getRequest('newpass');
		$valueConfirmPassword = (string)$this->_getRequest('confirmpassword');
		if (empty($valueCurrentPass)) {
			$this->listError['ERROR_CURRENTPASS'] = '<label class="form-error" data-form-error-for="oldpass" style="display: block;">Current Password is a required field.</label>';
		}
		if (empty($valueNewPass)) {
			$this->listError['ERROR_NEWPASS'] = '<label class="form-error" data-form-error-for="newpass" style="display: block;">New Password is a required field.</label>';
		}
		if (strlen($valueNewPass) < 6) {
			$this->listError['ERROR_NEWPASS'] = '<label class="form-error" data-form-error-for="newpass" style="display: block;">Password must be greater than 7 characters.</label>';
		}
		if (empty($valueConfirmPassword)) {
			$this->listError['ERROR_CONFIRMPASS'] = '<label class="form-error" data-form-error-for="confirmpassword" style="display: block;">Verify password is a required field.</label>';
		} elseif ($valueConfirmPassword != $valueNewPass) {
			$this->listError['ERROR_CONFIRMPASS'] = '<label class="form-error" data-form-error-for="confirmpassword" style="display: block;">Verify Password not equal New Password.</label>';
		}
		return !count($this->listError);
	}

	public function _updatePassword()
	{
		$patientModel 	= new Model_Patient();
		$newPassword 	= (string)$this->_getRequest('confirmpassword');
		// prepare input data
		$patient = new Model_Entity_Patient();

		if (WebUser::isTempLogin()) {
			$patientID = WebUser::getTempUserID();
		} else {
			$patientID = WebUser::getUserID();
		}
		$patient->patientid = $patientID;
		$patient->newpass = stripslashes($newPassword);

		// call method
		$result = $patientModel->setPatientPassword($patient);
		// check if reply is failure
		$this->displayErrorRequest($result);
		return $result;
	}

	public function _checkPassword()
	{
		$patientModel 	= new Model_Patient();
		$oldPassword 	= (string)$this->_getRequest('oldpass');
		// prepare input data
		$patient = new Model_Entity_Patient();

		if (WebUser::isTempLogin()) {
			$patientUserName = WebUser::getTempUserName();
		} else {
			$patientUserName = WebUser::getUserName();
		}

		$patient->username = $patientUserName;
		$patient->password = stripslashes($oldPassword);
		$listPatient[] = $patient;

		// call method
		$result = $patientModel->authenticateUser($listPatient);
		// check if reply is failure
		$this->displayErrorRequest($result);
		$user = $result->users[0];

		return $user;
	}

	public function _populateForm()
	{
		$oldPass = (string)$this->_getRequest('oldpass');
		$newPass = (string)$this->_getRequest('newpass');
		$renewPass = (string)$this->_getRequest('confirmpassword');
		$this->assign('VALUE_OLDPASS', $oldPass);
		$this->assign('VALUE_NEWPASS', $newPass);
		$this->assign('VALUE_CONFIRMPASS', $renewPass);
	}
}
