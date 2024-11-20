<?php
require_once UTILITY_FOLDER . 'ModelBase.php';
require_once MODEL_RESOURCE_FOLDER . 'Country.php';

/**
 * Model_Country
 */
class Model_Country extends Utility_ModelBase
{
	/**
	 * getCountryList
	 *
	 */
	public function getCountryList()
	{
		$resource = new Model_Resource_Country();
		return $resource->getCountryList();
	}

	/**
	 * getCountryCodes
	 *
	 */
	public function getCountryCodes()
	{
		$resource = new Model_Resource_Country();
		return $resource->getCountryCodes();
	}

	/**
	 * getAllowedCountryList
	 * ARRAY_N, ARRAY_A
	 */
	public function getAllowedCountryList($output = ARRAY_N)
	{
		$resource = new Model_Resource_Country();
		return $resource->getAllowedCountryList($output);
	}

	/**
	 * getAllowedCountryRegionsList
	 *
	 */
	public function getAllowedCountryRegionsList()
	{
		$resource = new Model_Resource_Country();
		return $resource->getAllowedCountryRegionsList();
	}

	/**
	 * getRegionsByCountry
	 *
	 * @param mixed $countryCode
	 */
	public function getRegionsByCountry($countryCode)
	{
		$resource = new Model_Resource_Country();
		return $resource->getRegionsByCountry($countryCode);
	}

	public static function getCountryByCode($code)
	{
		$resource = new Model_Resource_Country();
		if (empty($code)) {
			return;
		}
		$country = $resource->getCountryByCode($code);
		return $country->country_name;
	}
}
