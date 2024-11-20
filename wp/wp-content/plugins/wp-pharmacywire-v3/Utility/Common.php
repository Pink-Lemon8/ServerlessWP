<?php

/**
 * Utility_Common
 */
class Utility_Common
{
	// Load content of file
	public static function loadFileContent($path)
	{
		$content = "";
		$content = file_get_contents($path);
		return $content;
	}

	/**
	 * Load the files in folder
	 *
	 * @param mixed $folder
	 */
	public static function loadFileInFolder($folder)
	{
		$dir = opendir($folder);
		while ($file = readdir($dir)) {
			if (strpos($file, ".php")) {
				if (!strpos($file, "Common.php")) {
					$include_path = $folder . $file;
					include_once($include_path);
				}
			}
		}
		closedir($dir);
	}

	/**
	 * Convert a string to boolean
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public static function convertToBoolean($value)
	{
		if (strtoupper($value) == 'TRUE') {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * Get the value which compised by @ charater
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public static function getFullValue($value)
	{
		$result = str_replace(XML_JOIN_SYMBOL, ' ', $value);
		return $result;
	}

	public static function getStrength($value)
	{
		$arrItems = explode(XML_JOIN_SYMBOL, $value);
		$strResult = "";
		if ($arrItems[0] != "") {
			$strResult = ($arrItems[0] + 0) . ' ' . $arrItems[1];
		}

		return $strResult;
	}
	public static function getDoseType($value, $freeform)
	{
		$arrItems = explode(XML_JOIN_SYMBOL, $value);
		$doseType = "";

		if (preg_match('/^(\d*(\.\d+)?)\s*(\w+)/', $freeform, $matches)) {
			$doseType = $matches[3];
		} elseif ($arrItems[1] != "") {
			$doseType = $arrItems[1];
		}
		return $doseType;
	}
	public static function getBaseQuantity($value, $freeform)
	{
		$arrItems = explode(XML_JOIN_SYMBOL, $value);
		$qtyResult = "";

		if (preg_match('/^(\d*(\.\d+)?)\s*(\w+)/', $freeform, $matches)) {
			$qtyResult = $matches[1];
		} elseif ($arrItems[0] != "") {
			$qtyResult = ($arrItems[0] + 0);
		}
		return $qtyResult;
	}
	public static function getQuantity($value, $freeform, $tierQuantity = null)
	{
		$arrItems = explode(XML_JOIN_SYMBOL, $value);
		$qtyResult = "";

		if ($freeform != "") {
			$qtyResult = $freeform;
			if (is_numeric($tierQuantity) && $tierQuantity > 1) {
				$qtyResult = $tierQuantity . ' x ' . $freeform;
			}
		} elseif ($arrItems[0] != "") {
			$qty = ($arrItems[0] + 0);

			if (is_numeric($tierQuantity) && $tierQuantity > 1) {
				$qty = $qty * $tierQuantity;
			}

			$qtyResult = $qty . ' ' . $arrItems[1];
		}
		return $qtyResult;
	}
	public static function getOrderQuantity($value, $freeform, $qty, $include_units = true)
	{
		$arrItems = explode(XML_JOIN_SYMBOL, $value);
		$qtyResult = $typeResult = '';

		if (preg_match('/^(\d*(\.\d+)?)\s*(\w+)/', $freeform, $matches)) {
			$qtyResult = $matches[1];
			$typeResult = $matches[3];
		} elseif ($arrItems[0] != "") {
			$qtyResult = ($arrItems[0] + 0);
			$typeResult = $arrItems[1];
		}
		$qtyResult *= $qty;
		if (strlen($typeResult) && $include_units) {
			$qtyResult = $qtyResult . ' ' . $typeResult;
		}
		return $qtyResult;
	}
	public static function validTierQuantity($package, $tierQuantity)
	{
		$tierMinItemQty = $package->minitemqty;
		$tierMaxItemQty = $package->maxitemqty;
		$tierMultipleItemFactor = $package->multipleitemfactor;

		$validTQ = 1;

		if (is_numeric($tierMultipleItemFactor) && ($tierMultipleItemFactor > 0)) {
			if (($tierQuantity % $tierMultipleItemFactor) != 0) {
				// if not a multiple of tierMultipleFactor, skip tier
				$validTQ = 0;
			}
		}
		if (is_numeric($tierMinItemQty) && ($tierQuantity < $tierMinItemQty)) {
			// if tier quantity is less than minimum allowed item qty, skip tier
			$validTQ = 0;
		} elseif (is_numeric($tierMaxItemQty) && ($tierQuantity > $tierMaxItemQty)) {
			// if tier quantity is greater than maximum allowed item qty, skip tier
			$validTQ = 0;
		}

		return $validTQ;
	}
	/**
	 * Check if the email
	 *
	 * @param mixed $email
	 */
	public static function isEmail($email)
	{
		return is_email($email);
	}

	/**
	 * Get the number value
	 *
	 * @param mixed $value
	 * @return mixed The number if value is numberic, else empty
	 */
	public static function getNumberValue($value)
	{
		if (is_numeric($value)) {
			return $value;
		} else {
			return '';
		}
	}

	/**
	 * put your comment there...
	 *
	 * @param mixed $reply Model_Entity_Reply
	 */
	public static function isReplySuccess($reply)
	{
		return ($reply->status == 'success');
	}

	/**
	 * Simple function to get results of a URL
	 *
	 */

	public static function getUrl($url, $timeout = 3)
	{
		$Curl = curl_init();
		curl_setopt($Curl, CURLOPT_URL, $url);
		curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($Curl, CURLOPT_RETURNTRANSFER, 1);
		$Results = curl_exec($Curl);
		curl_close($Curl);
		return $Results;
	}

	/**
	 * Identify if a given IP address is in the Canadian IP exception list
	 *
	 */
	public static function verifyIP($testIP)
	{
		$cdnIP = explode(",", get_option('pw_canadian_IP_exceptions'));
		for ($i = 0; $i < count($cdnIP); $i++) {
			if (strpos($testIP, trim($cdnIP[$i])) === 0) {
				return true;
			}
		}
		return false;
	}

	public static function getClientIP()
	{
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$Remote = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["REMOTE_ADDR"])) {
			$Remote = $_SERVER["REMOTE_ADDR"];
		} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$Remote = $_SERVER["HTTP_CLIENT_IP"];
		}
		return $Remote;
	}

	/**
	 * Get the country code of the client (if possible)
	 *
	 */
	public static function getClientCountryCode()
	{
		$headers = apache_request_headers();
		if (isset($headers["CF-IPCountry"])) {

			if (get_option('pw_block_canucks')) {
				$Remote = $headers["CF-Connecting-IP"] ?? '';
				if (!empty($Remote) && get_option('pw_canadian_IP_exceptions') && Utility_Common::verifyIP($Remote)) {
					return 'US';
				}
			}
			// check if cloudflare is used and returning the 2-letter country code
			// if it is, then use that and save us a lookup
			return $headers["CF-IPCountry"];
		} elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$Remote = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["REMOTE_ADDR"])) {
			$Remote = $_SERVER["REMOTE_ADDR"];
		} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$Remote = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			return 'unknown';
		}

		if (get_option('pw_block_canucks')) {
			if (get_option('pw_canadian_IP_exceptions') && Utility_Common::verifyIP($Remote)) {
				return 'US';
			}
		}
		if (substr($Remote, 0, 3) == '10.') {
			return 'unknown';
		}
		$Client = explode(',', $Remote); // need to take the first argument - address may include a proxy
		$Results = Utility_Common::getUrl('http://www.metrex.net/momex/NavCode/ipaddress.txt/Server/' . $_SERVER['SERVER_NAME'] . '/IPAddress/' . $Client[0]);
		$Location = explode(',', $Results);
		return $Location[0];
	}

	/**
	 * Identify if only Canadian content should be shown
	 *
	 */
	public static function showOnlyCanadianDrugs()
	{
		if (get_option('pw_block_canucks') == 'on') {
			$CountryCode = $_SESSION['PW_Country_Code'] ?? 'unknown';
			if (empty($CountryCode) || $CountryCode == 'unknown') {
				$CountryCode = Utility_Common::getClientCountryCode();
				if (strlen($CountryCode) != 2) {
					$CountryCode = 'unknown';
				}
				$_SESSION['PW_Country_Code'] = $CountryCode;
			}
			if ($CountryCode == 'CA') {
				return 1;
			}
		}
		return 0;
	}

	/**
	 * Identify if only Canadian content should be shown
	 *
	 */
	public static function showPackageNameOnSearchResults()
	{
		if (get_option('pw_display_package_name_on_search_results') == 'on') {
			return 1;
		}
		return 0;
	}
}
