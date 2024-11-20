<?php
class Page_ForgotPassword extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$this->setTemplate("page_forgot_password");

		$pwdForm = '<div class="login-form forgot-password">
			<form method="post" name="forgotPass" class="custom">
				<p>Enter the email address that you signed up with and an email will be sent to you with information on how to reset your password.</p>
				<p><strong>Email Address</strong>&nbsp;<input type="text" maxlength="250" size="20" name="username" class="username" />&nbsp;<input type="submit" value="Submit" name="action" id="action" /></p>
			</form>
			</div>';

		$this->assign('PWD_FORM', $pwdForm);

		$this->processForgotPass();
	}

	// process when user on changePass page
	public function processForgotPass()
	{
		$action = strtoupper(trim($this->_getRequest("action")));
		if ($action == 'SUBMIT') {
			$valid = $this->validData();
			if ($valid) {
				$requestState = new Model_Request_State();
				$resetPwdReqState = $requestState->requestStateCheck('ResetPassword');

				if ($resetPwdReqState->status != 'failure') {
					$requestState->logRequestAttempt('ResetPassword');
					$checkStatus = $this->_checkStatusUser();
					$contactPhone = '1-' . get_option('pw_phone_area') . '-' . get_option('pw_phone');
					
					if (($checkStatus->status == 'Active')) {
						$forgotPass = $this->_resetPassword($checkStatus->id);

						// Send forgot password email if enabled which is the default setting.
						if (get_option('pw_email_forgot_pwd', 'on') == 'on') {				
							$this->sendEmailToPatient($forgotPass);
						}
					}
				}
					
				$newPwdMsg = "Your new password has been sent to your email address. If you do not find it within your inbox shortly, please check your blocked or 'spam' folders. If you are still unable to find it, please call us toll-free at $contactPhone for assistance.";

				$this->assign('PWD_FORM', '');

				$this->assign('MESSAGE', $newPwdMsg);
				
			}
		}
		$this->parseErrorMessage();
		$this->displayForgotPass();
	}

	public function displayForgotPass()
	{
		$this->parse('CONTENT');
	}

	public function validData()
	{
		$valueUserName 		= (string)$this->_getRequest('username');
		if (empty($valueUserName)) {
			$this->listError['MESSAGE'] = 'Username is a required field.';
		}
		return !count($this->listError);
	}
	public function _checkStatusUser()
	{
		$valueUserName = (string)$this->_getRequest('username');
		$userModel = new Model_User();

		$user = new Model_Entity_User();

		$user->username = $valueUserName;

		$listUser[] = $user;

		// call method
		$result = $userModel->requestStatus($listUser);
		// check if reply is failure
		$this->displayErrorRequest($result);

		$userResult = $result->users[0];
		return $userResult;
	}
	public function _resetPassword($id)
	{
		$patientModel 	= new Model_Patient();
		$patient = new Model_Entity_Patient();

		$patient->patientid = $id;

		// call method
		$result = $patientModel->resetPassword($patient);
		$patientResult = $result->patient;
		return $patientResult;
	}
	public function sendEmailToPatient($patient)
	{
		$to 		= $patient->username;
		$pharmacyName = get_option('pw_name');
		$siteURL = PC_getHomePageURL();
		$siteURL = rtrim($siteURL, "/");
		$loginURL = PC_getLoginUrl();
		$subject	= "$pharmacyName - New Password Requested";
		$message	= "Your new password for $pharmacyName is <strong>" . $patient->password . "</strong><br />You can change your password after you log in. <a href=\"$loginURL\">Login to your account.</a>";
		PC_sendEmail($to, $subject, $message);
	}
}
