<?php

/**
 * Using the wordpress database to insert/update data
 */
class Model_Resource_Country
{
	/**
	 * getCountryList
	 *
	 */
	public function getCountryList()
	{
		global $wpdb;
		$query = "SELECT * FROM {$wpdb->prefix}pw_countries AS c ORDER BY country_name";

		return $wpdb->get_results($query);
	}

	/**
	 * getCountryRegionList
	 *
	 */
	public function getAllowedCountryRegionsList()
	{
		global $wpdb;

		$where = '';
		if (get_option('pw_allowed_countries')) {
			$allowedCountries = array();
			$allowedCountriesArray = get_option('pw_allowed_countries');
			$allowedCountries = "'" . implode("|", array_values($allowedCountriesArray)) . "'";

			$where = "WHERE c.country_code REGEXP $allowedCountries";
		}

		$query = "SELECT c.country_name, c.country_code, cr.region_code, cr.region_name 
			FROM {$wpdb->prefix}pw_countries AS c
			LEFT OUTER JOIN {$wpdb->prefix}pw_country_region AS cr
			ON cr.country_code = c.country_code
			{$where}
			ORDER BY c.country_name, cr.region_name";

		$regions = $wpdb->get_results($query);

		$countryRegions = array();
		foreach ($regions as $region) {
			$countryRegions[$region->country_code]['country_name'] = $region->country_name;
			if (empty($countryRegions[$region->country_code]['regions'])) {
				$countryRegions[$region->country_code]['regions'] = array();
			}
			if (!empty($region->region_code)) {
				$countryRegions[$region->country_code]['regions'][$region->region_code] = array('region_name' => $region->region_name, 'region_code' => $region->region_code);
			}
		}

		return $countryRegions;
	}

	/**
	 * getCountryCodes
	 *
	 */
	public function getCountryCodes()
	{
		$countryList = $this->getCountryList();

		$countryCodes = array();

		foreach ($countryList as $countryO) {
			$countryCodes[] = $countryO->country_code;
		}

		return $countryCodes;
	}

	/**
	 * getAllowedCountryList
	 * ARRAY_N, ARRAY_A
	 */
	public function getAllowedCountryList($output)
	{
		global $wpdb;
		if (get_option('pw_allowed_countries')) {
			$allowedCountries = array();
			$allowedCountries = get_option('pw_allowed_countries');
			$allowedCountries = "'" . implode("|", array_values($allowedCountries)) . "'";
			$query = "SELECT * FROM {$wpdb->prefix}pw_countries AS c WHERE country_code REGEXP $allowedCountries ORDER BY country_name";
		} else {
			$query = "SELECT * FROM {$wpdb->prefix}pw_countries AS c ORDER BY country_name";
		}

		return $wpdb->get_results($query);
	}

	/**
	 * getRegionsByCountry
	 *
	 * @param mixed $countryCode
	 */
	public function getRegionsByCountry($countryCode)
	{
		global $wpdb;

		$query = "SELECT cr.region_code, cr.region_name 
					FROM {$wpdb->prefix}pw_country_region AS cr
					INNER JOIN {$wpdb->prefix}pw_countries AS c
					ON cr.country_code = c.country_code
					WHERE c.country_code = '" . $countryCode . "'";

		return $wpdb->get_results($query);
	}
	/**
	 * getContryByCode
	 * @param mixed $code
	 */
	public function getCountryByCode($code)
	{
		global $wpdb;
		$query = "SELECT * FROM {$wpdb->prefix}pw_countries WHERE country_code='" . $code . "'";

		return $wpdb->get_row($query);
	}
}
