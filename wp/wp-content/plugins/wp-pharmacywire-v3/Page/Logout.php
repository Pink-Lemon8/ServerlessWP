<?php
class Page_Logout extends Utility_PageBase
{
	public function __construct()
	{
		parent::__construct();
	}
	public function _process()
	{
		$this->processLogout();
		return;
	}

	//process logout
	public function processLogout()
	{
		if (WebUser::isLoggedIn()) {
			$FullName = WebUser::getFullName();
			WebUser::logOut();
		}

		// delete information in varible session
		Cart::removeAllItems();
		Billing::resetInfo();
		Shipping::resetInfo();
		Model_Affiliate::resetInfo();
		PW_JSON_Session::deleteAutosave();
		Model_OrderTag::removeTagSessionAll();

		$_SESSION['Account_phoneAreaCode'] = null;
		$_SESSION['Account_phone'] = null;

		// redirect to home page.
		// $messages = new Utility_Messages;
		// $messages->setNotification('Information', "Thank you for visiting us $FullName! You have now been logged out");
		$url = PC_getHomePageURL();
		$this->redirect($url);

		return;
	}

	public function processLogoutJSON()
	{
		$result = new stdClass();
		$redirect = '';
		$messages = '';

		if (WebUser::isLoggedIn()) {
			// delete information in varible session
			$FullName = WebUser::getFullName();
			WebUser::logOut();
			Cart::removeAllItems();
			Billing::resetInfo();
			Shipping::resetInfo();
			Model_Affiliate::resetInfo();
			PW_JSON_Session::deleteAutosave();
			Model_OrderTag::removeTagSessionAll();

			$_SESSION['Account_phoneAreaCode'] = null;
			$_SESSION['Account_phone'] = null;

			// redirect to home page.
			$messages = new Utility_Messages;
			$messages->setNotification('Information', "Thank you for visiting us $FullName! You have now been logged out");
			$url = PC_getHomePageURL();
			$redirect = $url;
		}

		$result->info = $_SESSION;
		$result->success = 1;
		$result->message = $messages;
		$result->redirect = $redirect;

		return json_encode($result);
	}
}
