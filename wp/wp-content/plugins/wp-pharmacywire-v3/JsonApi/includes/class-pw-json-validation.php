<?php

// https://github.com/Wixel/GUMP
// GUMP is a standalone PHP data validation and filtering class that makes validating any data easy and painless without the reliance on a framework.

class PW_JSON_Validation extends GUMP
{

	// For filter methods, prepend the method name with "filter_".
	// public function filter_myfilter($value, $param = NULL) {}
	// For validator methods, prepend the method name with "validate_".
	// public function validate_myvalidator($field, $input, $param = NULL) {}

	/**
	 * Determine if the provided value is a valid international area code.
	 * Split off from full regex, see validate_phone_number
	 *
	 * Usage: '<index>' => 'area_code'
	 *
	 * @param string $field
	 * @param array  $input
	 *
	 * @return mixed
	 *
	 * override function to handle international numbers
	 * https://stackoverflow.com/questions/2113908/what-regular-expression-will-match-valid-international-phone-numbers
	 */
	protected function validate_area_code($field, array $input, array $params = [], $value)
	{
		if (!isset($input[$field]) || empty($input[$field])) {
			return;
		}

		$regex = '/^\+?((?:9[679]|8[035789]|6[789]|5[90]|42|3[578]|2[1-689])|9[0-58]|8[1246]|6[0-6]|5[1-8]|4[013-9]|3[0-469]|2[70]|7|1)(?:\W*\d){0,13}\d$/i';
		if (!preg_match($regex, $input[$field])) {
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule' => __FUNCTION__,
				'param' => $params,
			);
		}
	}

	/**
	 * Determine if the provided value is a valid international phone number.
	 * Split off from full regex, see validate_area_code
	 *
	 * Usage: '<index>' => 'phone_number'
	 *
	 * @param string $field
	 * @param array  $input
	 *
	 * @return mixed
	 *
	 * override function to handle international numbers
	 * https://stackoverflow.com/questions/2113908/what-regular-expression-will-match-valid-international-phone-numbers
	 */
	protected function validate_phone_number($field, array $input, array $params = [], $value)
	{
		if (!isset($input[$field]) || empty($input[$field])) {
			return;
		}

		$regex = '/^(?:\W*\d){0,13}\d$/i';
		if (!preg_match($regex, $input[$field])) {
			return array(
				'field' => $field,
				'value' => $input[$field],
				'rule' => __FUNCTION__,
				'param' => $params,
			);
		}
	}
}
