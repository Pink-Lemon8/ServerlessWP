<?php

class PW_JSON_Session extends PW_JSON
{
	public static function setAutosave($datasetName, $data)
	{
		unset($_SESSION['pw_autosave'][$datasetName]);

		$d = html_entity_decode(stripslashes($data));
		$dataset = json_decode($d, true);

		foreach ($dataset as $key => $value) {
			if (($value['name'] == 'Password') || ($value['name'] == 'ConfirmPassword')) {
				continue;
			}

			$nameKey = preg_replace('/(\[)(\d+)(\])/', '\\\[${2}\\\]', $value['name']);

			$_SESSION['pw_autosave'][$datasetName][$nameKey] = $value['value'];
		}
	}

	public static function getAutosave($datasetName = null)
	{
		if (isset($datasetName) && isset($_SESSION['pw_autosave'][$datasetName])) {
			return $_SESSION['pw_autosave'][$datasetName];
		} elseif (isset($_SESSION['pw_autosave'])) {
			return $_SESSION['pw_autosave'];
		}
	}

	public static function deleteAutosave()
	{
		unset($_SESSION['pw_autosave']);
	}

	public static function deleteAutosaveBilling()
	{
		$checkoutSession = $_SESSION['pw_autosave']['checkout'];

		foreach ($checkoutSession as $sessionKey => $sessionValue) {
			if (preg_match('/^billing/', $sessionKey)) {
				// Leave name, remove rest of billing info
				if ($sessionKey != 'billing_firstName' && $sessionKey != 'billing_lastName') {
					unset($_SESSION['pw_autosave']['checkout'][$sessionKey]);
				}
			}
		}
	}

	public static function jsonSessionDataStore()
	{
		// General session info passed to JSON for use
		$orderTag = new Model_OrderTag;
		$jsonSessionData = array(
			'order_tags' => $orderTag->getTagSession()
		);

		return $jsonSessionData;
	}
}
