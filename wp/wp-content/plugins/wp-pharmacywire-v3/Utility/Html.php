<?php
require_once MODEL_FOLDER . 'Country.php';

/**
 * Utility_Html
 */
class Utility_Html
{
	/**
	 * Display result message
	 *
	 * @param mixed $result Model_Entity_Reply
	 */
	public static function displayResult($result)
	{
		$html = '';
		if (($result->messages !== null) && count($result->messages)) {
			$html = '<ul id="messages">';
			foreach ($result->messages as $message) {
				$format = '<li class="message-%s">%s</li>';
				$html .= sprintf($format, $message->type, $message->content);
			}
			$html .= '</ul>';
		}

		return $html;
	}
	public static function displayMessage($mess)
	{
		$html = '';
		if (count($mess) > 0) {
			$html = '<ul id="messages">';
			foreach ($mess as $k => $v) {
				$format = '<li style="color:red;">%s</li>';
				$html .= sprintf($format, $v);
			}
			$html .= '</ul>';

			return $html;
		}
	}
	/**
	 * HTML select country
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectCountry($name, $active = null, $attribs = null)
	{
		$countryModel = new Model_Country();
		$countries = $countryModel->getAllowedCountryList();

		return self::htmlSelect($countries, $name, $attribs, 'country_code', 'country_name', $active, $name);
	}

	/**
	 * HTML select province
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectProvince($name, $active = null, $countryCode = null, $attribs = null)
	{
		$countryModel = new Model_Country();
		$arr = $countryModel->getRegionsByCountry($countryCode);

		if (count($arr) == 0) {
			return '<input type="Text" maxlength="150" size="30" value="' . $active . '" name="' . $name . '" id="' . $name . '" ' . $attribs . '>';
		} else {
			$emptyPlaceholder = new stdClass;
			$emptyPlaceholder->region_code = '';
			$emptyPlaceholder->region_name = 'State/Province';
			array_unshift($arr, $emptyPlaceholder);
			return self::htmlSelect($arr, $name, $attribs, 'region_code', 'region_name', $active, $name);
		}
	}

	/**
	 * HTML select gender
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectGender($name, $active = null, $attribs = null)
	{
		$arr = array();
		$arr[] = self::htmlOption('M', 'male');
		$arr[] = self::htmlOption('F', 'female');

		return self::radiolist($arr, $name, $attribs, 'value', 'text', $active);
	}

	/**
	 * HTML select drug packaging
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectDrugPackaging($name, $active = null, $attribs = null)
	{
		$arr = array();
		$arr[] = self::htmlOption('Yes', 'Yes');
		$arr[] = self::htmlOption('No', 'No');

		return self::radiolist($arr, $name, $attribs, 'value', 'text', $active, false, true);
	}

	/**
	 * HTML select drug packaging
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectCallforRefills($name, $active = null, $attribs = null)
	{
		$arr = array();
		$arr[] = self::htmlOption('True', 'Yes');
		$arr[] = self::htmlOption('False', 'No');

		return self::radiolist($arr, $name, $attribs, 'value', 'text', $active, false, true);
	}

	/**
	 * HTML select month
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectMonth($name, $active = null, $attribs = null)
	{
		$arr = array();
		$arr[] = self::htmlOption('1', 'January');
		$arr[] = self::htmlOption('2', 'February');
		$arr[] = self::htmlOption('3', 'March');
		$arr[] = self::htmlOption('4', 'April');
		$arr[] = self::htmlOption('5', 'May');
		$arr[] = self::htmlOption('6', 'June');
		$arr[] = self::htmlOption('7', 'July');
		$arr[] = self::htmlOption('8', 'August');
		$arr[] = self::htmlOption('9', 'September');
		$arr[] = self::htmlOption('10', 'October');
		$arr[] = self::htmlOption('11', 'November');
		$arr[] = self::htmlOption('12', 'December');

		return self::htmlSelect($arr, $name, $attribs, 'value', 'text', $active);
	}

	/**
	 * HTML select month
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectHeightFeet($name, $active = null, $attribs = null)
	{
		$arr = array();
		$arr[] = self::htmlOption('-1', 'feet');

		for ($i = 3; $i <= 7; $i++) {
			$arr[] = self::htmlOption($i, $i . ' feet');
		}

		return self::htmlSelect($arr, $name, $attribs, 'value', 'text', $active);
	}

	/**
	 * HTML select month
	 *
	 * @param mixed $name
	 * @param mixed $active
	 */
	public static function htmlSelectHeightInches($name, $active = null, $attribs = null)
	{
		return self::integerlist(0, 11, 1, $name, $attribs, $active, '', 'inches');
	}

	/**
	 * Generates an HTML select list
	 *
	 * @param    array    An array of objects
	 * @param    string    The value of the HTML name attribute
	 * @param    string    Additional HTML attributes for the <select> tag
	 * @param    string    The name of the object variable for the option value
	 * @param    string    The name of the object variable for the option text
	 * @param    mixed    The key that is selected (accepts an array or a string)
	 * @returns    string    HTML for the select list
	 */
	public static function htmlSelect($arr, $name, $attribs = null, $key = 'value', $text = 'text', $selected = null, $idtag = false)
	{
		if (is_array($arr)) {
			reset($arr);
		}

		$id = $name;

		if ($idtag) {
			$id = $idtag;
		}

		$id        = str_replace('[', '', $id);
		$id        = str_replace(']', '', $id);

		$html    = '<select name="' . $name . '" id="' . $id . '" ' . $attribs . '>';
		$html    .= self::htmlOptions($arr, $key, $text, $selected);
		$html    .= '</select>';

		return $html;
	}

	/**
	 * Generates just the option tags for an HTML select list
	 *
	 * @param    array    An array of objects
	 * @param    string    The name of the object variable for the option value
	 * @param    string    The name of the object variable for the option text
	 * @param    mixed    The key that is selected (accepts an array or a string)
	 * @returns    string    HTML for the select list
	 */
	public static function htmlOptions($arr, $key = 'value', $text = 'text', $selected = null)
	{
		$html = '';

		foreach ($arr as $i => $option) {
			$element = &$arr[$i]; // since current doesn't return a reference, need to do this

			$isArray = is_array($element);
			$extra     = '';
			if ($isArray) {
				$k         = $element[$key];
				$t         = $element[$text];
				$id     = (isset($element['id']) ? $element['id'] : null);
				if (isset($element['disable']) && $element['disable']) {
					$extra .= ' disabled="disabled"';
				}
			} else {
				$k         = $element->$key;
				$t         = $element->$text;
				$id     = (isset($element->id) ? $element->id : null);
				if (isset($element->disable) && $element->disable) {
					$extra .= ' disabled="disabled"';
				}
			}

			// This is real dirty, open to suggestions,
			// barring doing a propper object to handle it
			if ($k === '<OPTGROUP>') {
				$html .= '<optgroup label="' . $t . '">';
			} elseif ($k === '</OPTGROUP>') {
				$html .= '</optgroup>';
			} else {
				//if no string after hypen - take hypen out
				$splitText = explode(' - ', $t, 2);
				$t = $splitText[0];
				if (isset($splitText[1])) {
					$t .= ' - ' . $splitText[1];
				}

				//$extra = '';
				//$extra .= $id ? ' id="' . $arr[$i]->id . '"' : '';
				if (is_array($selected)) {
					foreach ($selected as $val) {
						$k2 = is_object($val) ? $val->$key : $val;
						if ($k == $k2) {
							$extra .= ' selected="selected"';
							break;
						}
					}
				} else {
					$extra .= ((string)$k == (string)$selected  ? ' selected="selected"' : '');
				}

				$html .= '<option value="' . $k . '" ' . $extra . '>' . $t . '</option>';
			}
		}

		return $html;
	}

	/**
	 * @param    string    The value of the option
	 * @param    string    The text for the option
	 * @param    string    The returned object property name for the value
	 * @param    string    The returned object property name for the text
	 * @return    object
	 */
	public static function htmlOption($value, $text = '', $value_name = 'value', $text_name = 'text', $disable = false)
	{
		$obj = new stdClass;
		$obj->$value_name    = $value;
		$obj->$text_name    = trim($text) ? $text : $value;
		$obj->disable        = $disable;
		return $obj;
	}

	/**
	 * Generates a select list of integers
	 *
	 * @param int The start integer
	 * @param int The end integer
	 * @param int The increment
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @param string The printf format to be applied to the number
	 * @returns string HTML for the select list
	 */
	public static function integerlist($start, $end, $inc, $name, $attribs = null, $selected = null, $format = "", $append = '')
	{
		$start     = intval($start);
		$end     = intval($end);
		$inc     = intval($inc);
		$arr     = array();

		for ($i = $start; $i <= $end; $i += $inc) {
			$fi = $format ? sprintf("$format", $i) : "$i";
			$arr[] = self::htmlOption($fi, $fi . ' ' . $append);
		}

		return self::htmlSelect($arr, $name, $attribs, 'value', 'text', $selected);
	}

	/**
	 * Generates an HTML radio list
	 *
	 * @param array An array of objects
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @param string The name of the object variable for the option value
	 * @param string The name of the object variable for the option text
	 * @returns string HTML for the select list
	 */
	public static function radiolist($arr, $name, $attribs = null, $key = 'value', $text = 'text', $selected = null, $idtag = false, $inline = true)
	{
		reset($arr);
		$html = '';

		$id_text = $name;
		if ($idtag) {
			$id_text = $idtag;
		}

		for ($i = 0, $n = count($arr); $i < $n; $i++) {
			$k    = (string) $arr[$i]->$key;
			$t    = $arr[$i]->$text;
			$id    = (isset($arr[$i]->id) ? @$arr[$i]->id : null);

			$extra    = '';
			$extra    .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
			if (is_array($selected)) {
				foreach ($selected as $val) {
					$k2 = is_object($val) ? (string) $val->$key : (string) $val;
					if ($k === $k2) {
						$extra .= " selected=\"selected\"";
						break;
					}
				}
			} else {
				$extra .= ((string)$k == (string)$selected ? " checked=\"checked\"" : '');
			}
			$id_text = strtolower($id_text);
			$className = strtolower($name);
			$lowerId = strtolower($id_text) . strtolower($k);
			$html .= "\n\t<span class=\"radio-input-container\"><label for=\"$lowerId\" class=\"$className $lowerId radio-button inline\"><input type=\"radio\" name=\"$name\" id=\"$lowerId\" value=\"" . $k . "\"$extra $attribs />&nbsp;$t</label></span>\n\t";
		}
		$html .= "\n";
		return $html;
	}

	/**
	 * Convert adderss in text format
	 *
	 * @param mixed $address
	 */
	public static function getAddressText($address)
	{
		$html = '<span id="address-id-' . $address->id . '">';
		$html .= '<span class="description">' . $address->description . '</span>: ';
		$html .= '<span class="address1">' . $address->address1 . '</span>, ';
		if (strlen($address->address2)) {
			$html .= '<span class="address2">' . $address->address2 . '</span>, ';
		}
		$html .= '<span class="city">' . $address->city . '</span>, ';
		$html .= '<span class="province">' . $address->province . '</span>, ';
		$html .= '<span class="country">' . $address->country . '</span>, ';
		$html .= '<span class="postalcode">' . $address->postalcode . '</span> ';
		$html .= '<span class="areacode">' . $address->areacode . '</span> ';
		$html .= '<span class="phone">' . $address->phone . '</span>';
		$html .= '</span>';

		return $html;
	}
}
