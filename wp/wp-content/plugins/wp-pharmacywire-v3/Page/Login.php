<?php
class Page_Login extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('page_login');
	}
	public function _process()
	{
		$action = strtoupper(trim($this->_getRequest("action")));

		switch ($action) {
			case "LOGOUT":
				$this->processLogout();
				break;
			default:
				$this->processLogin();
				break;
		}
		$this->displayLoginForm();
	}
	public function displayLoginForm()
	{
		$url_register 	= PC_getRegisterUrl();
		$url_forgot		= PC_getForgotUrl();

		$this->assign('URL_REGISTER', $url_register);
		$this->assign('URL_FORGOT', $url_forgot);
		$this->parse('LOGIN_FORM');
	}

	// processLogin (jsonRequest true for json response)
	public function processLogin($jsonRequest = 0, $onSuccessRedirect = '')
	{
		if ($jsonRequest == 1) {
			$jsonResponse = new StdClass();
			$jsonResponse->success = 0;
			$jsonResponse->redirect = '';
			$jsonResponse->message = '';
		}

		WebUser::removeTempLoginInfo();

		if (empty($onSuccessRedirect)) {
			$onSuccessRedirect = PC_getProfileUrl();
		}

		if (WebUser::isLoggedIn()) {
			if ($jsonRequest == 1) {
				$jsonResponse->success = 1;
				$jsonResponse->redirect = $onSuccessRedirect;
				return json_encode($jsonResponse);
			}
			$this->redirect($onSuccessRedirect);
			return;
		}

		$loginError = 0;
		$username = trim($this->_getRequest('username'));
		$password = $this->_getRequest('password');
		$this->assign('MESSAGE_DISPLAY', 'none');
		$this->assign("MESSAGE", 'Invalid email and/or password entered.');

		if (!empty($username)) {

			$requestState = new Model_Request_State();
			$authReqState = $requestState->requestStateCheck('AuthenticateUser');

			if (!empty($password) && ($authReqState->status != 'failure')) {
				$patientModel = new Model_Patient();
				$patient = new Model_Patient();
				$patient->username = $username;
				$patient->password = $password;
				$listPatient[] = $patient;

				$loginResult = $patientModel->authenticateUser($listPatient);
				$user = !empty($loginResult->users[0]) ? $loginResult->users[0] : null;

				if (($loginResult->status === 'success') && (!empty($user) && ($user->type === 'Patient'))) {

					$requestState->clearRequestState('AuthenticateUser');
					$requestState->clearRequestState('ResetPassword');
					
					if ($user->authenticated === 'reset-password') {
						WebUser::setTempUserID($user->id);
						WebUser::setTempUserName($user->name);

						$change_pass_url = PC_getChangePassUrl();
						if ($jsonRequest == 1) {
							$jsonResponse->success = 1;
							$jsonResponse->redirect = $change_pass_url;
							return json_encode($jsonResponse);
						}
						$this->redirect($change_pass_url);
						return;
					} else {
						WebUser::setLogin($user);

						// check session for active coupon
						$couponSession = new Model_Coupon();
						$currentCoupons = $couponSession->getCouponSession();

						if (!empty($currentCoupons)) {
							$couponSession->revalidateCouponSession();
							$coupons = $couponSession->getCouponSession();

							foreach ($coupons as $couponCode => $couponData) {
								// if invalid coupon found redirect to cart
								if ($couponData['usable'] == 'false') {
									$edit_cart_url = PC_getShoppingURL();
									if ($jsonRequest == 1) {
										$jsonResponse->success = 1;
										$jsonResponse->redirect = $edit_cart_url;
										return json_encode($jsonResponse);
									}
									$this->redirect($edit_cart_url);
									return;
								}
							}
						}
					}

					if ($jsonRequest == 1) {
						$jsonResponse->success = 1;
						$jsonResponse->redirect = $onSuccessRedirect;
						return json_encode($jsonResponse);
					}
					$this->redirect($onSuccessRedirect);
					return;
				} else {
					$loginError = 1;
				}
			} else {
				$loginError = 1;
			}

			if ($loginError == 1) {
				// if not already banned, log the failure/login attempt
				if ($authReqState->status != 'failure') {
					$requestState->logRequestAttempt('AuthenticateUser');
				}

				$message = 'Invalid email and/or password entered.';

				if ($jsonRequest == 1) {
					$jsonResponse->success = 0;
					$jsonResponse->message = $message;
					return json_encode($jsonResponse);
				}

				$this->assign("MESSAGE", $message);
				$this->assign('MESSAGE_DISPLAY', 'block');
			}
		}

		if ($jsonRequest == 1) {
			return json_encode($jsonResponse);
		}
	}

	//process login
	public function processLoginJSON($onSuccessRedirect = '')
	{
		// alias for processLogin(1) JSON call
		return $this->processLogin(1, $onSuccessRedirect);
	}

	//process logout
	public function processLogout()
	{
		$logout = new Page_Logout();
		$logout->processLogout();
	}

	public function processLogoutJSON()
	{
		$logout = new Page_Logout();
		return $logout->processLogoutJSON();
	}
}
