<?php

/**
 * Utility_Messages
 */
class Utility_Messages
{
	public $_PageTitle;

	/**
	 * Get the error message by key
	 *
	 * @param mixed $key Error key
	 * @return string The content of message
	 */
	public static function getErrorMessage($key)
	{
		static $errorMessages; // cache the message
		if (($errorMessages == null) || count($errorMessages) == 0) {
			$errorMessages = array();
			$errorMessages['ERROR_EMAIL']					= PC_genErrorMessage('Please provide a valid email address.');
			$errorMessages['ERROR_PASSWORD']				= PC_genErrorMessage('Password is a required field.');
			$errorMessages['ERROR_REPASSWORD']				= PC_genErrorMessage('Please re-type Password.');
			$errorMessages['ERROR_FIRSTNAME']				= PC_genErrorMessage('First name is a required field.');
			$errorMessages['ERROR_LASTNAME']				= PC_genErrorMessage('Last name is a required field.');
			$errorMessages['ERROR_ADDRESS']					= PC_genErrorMessage('Address is a required field.');
			$errorMessages['ERROR_CITY']					= PC_genErrorMessage('City is a required field.');
			$errorMessages['ERROR_PROVINCE']				= PC_genErrorMessage('The state or province is incorrect for the country chosen.');
			$errorMessages['ERROR_POSTALCODE']				= PC_genErrorMessage('Zip/postal code is a required field.');
			$errorMessages['ERROR_AREACODEPHONE']			= PC_genErrorMessage('Area code is a required field.');
			$errorMessages['ERROR_PHONE']					= PC_genErrorMessage('Home phone is a required field.');
			$errorMessages['ERROR_SEX']						= PC_genErrorMessage('Gender info is required.');
			$errorMessages['ERROR_BIRTHDATE']				= PC_genErrorMessage('A valid birth date is required.');
			$errorMessages['ERROR_WEIGHT']					= PC_genErrorMessage('Weight is a required field.');
			$errorMessages['ERROR_HEIGHT']					= PC_genErrorMessage('Height is a required field.');
			$errorMessages['ERROR_AGREEMENT']				= PC_genErrorMessage('Order cannot be submitted until you have agreed to the terms of use.');
			$errorMessages['ERROR_SHIPPING_DESCRIPTION']	= PC_genErrorMessage('Please enter a description for the shipping location');
			$errorMessages['ERROR_SHIPPING_ADDRESS']		= PC_genErrorMessage('Please specify shipping address');
			$errorMessages['ERROR_SHIPPING_CITY']			= PC_genErrorMessage('Please specify city for shipping destination');
			$errorMessages['ERROR_SHIPPING_PROVINCE']		= PC_genErrorMessage('The state or province is incorrect for the country chosen.');
			$errorMessages['ERROR_SHIPPING_COUNTRY']		= PC_genErrorMessage('Please specify country for shipping destination');
			$errorMessages['ERROR_SHIPPING_POSTALCODE']		= PC_genErrorMessage('Please specify zip/postal code for shipping destination');
			$errorMessages['ERROR_SHIPPING_PHONE']			= PC_genErrorMessage('Please enter a phone number for this address');
		}

		return $errorMessages[$key];
	}

	public function setNotification($type, $message)
	{
		$notifications = $_SESSION['NotificationMessages'];

		if (($notifications != null) && count($notifications)) {
			$messages = $notifications[$type];
			if (!count($messages)) {
				$messages = array();
			}
		} else {
			$notifications = array();
			$messages = array();
		}
		$messages[] = $message;
		$notifications[$type] = $messages;
		$_SESSION['NotificationMessages'] = $notifications;
	}

	public function renderNotifications()
	{
		$notifications = '';
		$keys = array();
		if (!empty($_SESSION['NotificationMessages'])) {
			$notifications = $_SESSION['NotificationMessages'];
			$keys = array_keys($notifications);
		}
		if (!count($keys)) {
			return null;
		}
		$notificationString = '';
		foreach ($keys as $key) {
			$notifications = $_SESSION['NotificationMessages'][$key];
			$notificationString .= '<div class="notification notification-' . strtolower($key) . ' callout alert">';
			foreach ($notifications as $notification) {
				$notificationString .= "<div class=\"notification-item\">$notification</div>";
			}
			$notificationString .= '</div>';
		}
		$_SESSION['NotificationMessages'] = array();
		return $notificationString;
	}

	public function setPageTitle($PageTitle)
	{
		$_SESSION['PageTitle'] = $PageTitle;
	}

	public function getPageTitle()
	{
		$PageTitle = $_SESSION['PageTitle'];
		$_SESSION['PageTitle'] = null;
		return $PageTitle;
	}
}
