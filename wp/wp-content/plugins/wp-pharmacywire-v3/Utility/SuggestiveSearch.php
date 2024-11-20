<?php

/**
 * Gets suggestive search results as json or xml from RxNorm API
 * https://lhncbc.nlm.nih.gov/RxNav/APIs/api-Prescribable.getSpellingSuggestions.html
 **/

class SuggestiveSearch
{
	protected $format; // 'json' or 'xml' response;

	public function __construct($format = 'json')
	{
		$this->format = $this->setFormat($format);
	}

	private function setFormat($format = 'json')
	{
		$format = ($format == 'json') ? 'json' : 'xml';
		return $format;
	}

	private function getRxNormBaseUrl()
	{
		// set to the base URI for the RxNorm resources
		return 'https://rxnav.nlm.nih.gov/REST/';
	}

	private function rxNormRequest($slug, $params = '')
	{
		if (empty($slug)) { return; };
		$format = ($this->format == 'json') ? '.json' : '.xml';
		$baseUrl = $this->getRxNormBaseUrl();
		if (!empty($params) && is_array($params)) {
			$params = http_build_query($params);
		}
		$fullUrl = $baseUrl . $slug . $format . '?' . $params;
		if (!defined('IS_RELEASE_SITE')) {
			define('IS_RELEASE_SITE', (get_option('pw_is_dev_site_connection', 'off') == 'on' ? 0 : 1));
		}
		$ch = curl_init($fullUrl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, IS_RELEASE_SITE);
		// Changed to 2 as opt no longer accepts 1 as of cURL 7.28.1 - https://www.php.net/manual/en/function.curl-setopt.php
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, (IS_RELEASE_SITE ? 2 : 0));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		// Set The Response Format to Xml or Json
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/' . $this->format));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
		}
		curl_close($ch);
		if (isset($error_msg)) {
			error_log('PharmacyWire/Utility/SuggestiveSearch.php - curl error: ' . print_r($error_msg, 1));
		}
		return $result;
	}

	private function resultToArray($result) {
		$resultArray = [];
		if (!empty($result)) {
			if ($this->format != 'json') {
				$xml = simplexml_load_string($result);		
				// $xml = $xml->rxnormdata; // all return with rxnormdata which json result doesn't have
				$json = json_encode($xml); // simple conversion of xml to json
			} else {
				$json = $result;
			}
			$resultArray = json_decode($json, TRUE); // json to php array
		}
		return $resultArray;
	}

	/**
	 * Autocomplete suggestions
	 * @param mixed $terms 
	 * @return array 
	 */
	public function getAutocompleteSuggestions($terms)
	{
		if (empty($terms)) { return [];	}
		$suggestionsRxcui = [];
		$terms = (array) $terms;
		foreach ($terms as $term) {
			$resultsRxNorm = $this->getApproximateMatch($term);
			$resultsRxNormParsed = $this->parseApproximateMatch($resultsRxNorm);
			if (!empty($resultsRxNormParsed)) {
				$suggestionsRxcui = array_merge($suggestionsRxcui, $resultsRxNormParsed);
			}
		}
		if (empty($suggestionsRxcui)) { return []; }
		$suggestionsRxcui = array_unique($suggestionsRxcui);	
		$drugNames = [];
		if (!empty($suggestionsRxcui)) {
			// do proprietary lookup
			foreach($suggestionsRxcui as $rxcui) {
				$drugProprietaryInfo = $this->getProprietaryInformation($rxcui);
				$drugName = $this->parseProprietaryInformation($drugProprietaryInfo,'name');
				$drugNames = array_merge($drugName, $drugNames);
			}
			$drugNames = array_unique($drugNames);
		}
		return $drugNames;
	}

	/**
	 * ex: https://rxnav.nlm.nih.gov/REST/approximateTerm.xml?term=cipralex
	 * 
	 * @return array
	 */
	private function getApproximateMatch($term) {
		$approxMatches = [];
		if (!empty($term)) {
			$slug = 'approximateTerm';
			$params = [
				'term' => $term,
				'maxEntries' => 1
			];
			$approxMatches = $this->rxNormRequest($slug, $params);
		}
		return $approxMatches;
	}

	/**
	 * Parse response from RxNorm API
	 * documentation: https://lhncbc.nlm.nih.gov/RxNav/APIs/api-RxNorm.getSpellingSuggestions.html
	 *
	 * response example JSON:
	 * {"suggestionGroup":{"name":null,"suggestionList":{"suggestion":["ambien"]}}}
	 *
	 * response example XML:
	 * <!--?xml version="1.0" ?-->
	 * <rxnormdata>
	 * 		<approximateGroup>
	 * 		<inputTerm/>
	 * 		<candidate>
	 * 			<rxcui>196503</rxcui>
	 * 			<rxaui>1090485</rxaui>
	 * 			<score>6.337975978851318</score>
	 * 			<rank>1</rank>
	 * 		</candidate>
	 * 		</approximateGroup>
	 * 	</rxnormdata>
	 **/
	private function parseApproximateMatch($result)
	{
		$approxMatch = $this->resultToArray($result);
		if (!empty($approxMatch['approximateGroup']['candidate'])) {
			$approxMatch = array_map(function($v) {
				return (string) $v['rxcui']; 
			}, $approxMatch['approximateGroup']['candidate']);
		} else {
			return [];
		}
		return $approxMatch;
	}

	/**
	 * 
	 * ex: https://rxnav.nlm.nih.gov/REST/rxcui/404930/proprietary.xml?srclist=RXNORM+DRUGBANK
	 *
	 * @return array
	 */
	private function getProprietaryInformation($rxcui) {
		$slug = 'rxcui/' . $rxcui . '/proprietary';
		$params = [ 'srclist' => 'RXNORM DRUGBANK'];
		return $this->rxNormRequest($slug, $params);
	}

	private function parseProprietaryInformation($drugProprietaryInfo) {
		$proprietaryInfo = $this->resultToArray($drugProprietaryInfo);
		$propI = $proprietaryInfo['proprietaryGroup']['proprietaryInfo'] ?? [];
		if (!empty($propI)) {
			if ($this->format != 'json') {
				$proprietaryInfo = (array) $propI['name'] ?? []; // xml response is object
			} else {
				$proprietaryInfo = array_map(function($v) { // json responses is indexed array
					return (string) $v['name'];
				}, $propI);
			}
		} else {
			return [];
		}
		return $proprietaryInfo;
	}

	private function getRxSpellingSuggestionsUrl($term)
	{
		$endpoint = ($this->format == 'json') ? 'spellingsuggestions.json' : 'spellingsuggestions';
		// set to the spelling suggestions URI for the RxNorm resources
		$url = $this->getRxNormBaseUrl() . '/' . $endpoint . '?name=%s';
		$url = sprintf($url, urlencode($term));
		return $url;
	}

	private function getRxNormSpellingSuggestions($term) 
	{
		$slug = $this->getRxSpellingSuggestionsUrl($term, $this->format);
		return $this->rxNormRequest($slug);
	}

	/**
	 * More exact spelling suggestions
	 * @param mixed $terms 
	 * @return array 
	 */
	public function getSpellingSuggestions($terms)
	{
		$suggestions = [];
		if (!empty($terms)) {
			$terms = (array) $terms;
			foreach ($terms as $term) {
				$resultsRxNorm = $this->getRxNormSpellingSuggestions($term, $this->format);
				$resultsRxNormParsed = $this->parseRxNormSpellingSuggestion($resultsRxNorm, $this->format);
				if (!empty($resultsRxNormParsed)) {
					$suggestions = array_merge($suggestions, $resultsRxNormParsed);
				}
			}
			$suggestions = array_unique($suggestions);
		}
		return $suggestions;
	}	

	/**
	 * Parse response from RxNorm API
	 * documentation: https://lhncbc.nlm.nih.gov/RxNav/APIs/api-Prescribable.getSpellingSuggestions.html
	 *
	 * response example JSON:
	 * {"suggestionGroup":{"name":null,"suggestionList":{"suggestion":["ambien"]}}}
	 *
	 * response example XML:
	 * <!--?xml version="1.0" ?-->
	 * <rxnormdata>
	 *	<suggestiongroup>
	 *		<name></name>
	 *		<suggestionlist>
	 *			<suggestion>zocor</suggestion>
	 *		</suggestionlist>
	 *	</suggestiongroup>
	 *	</rxnormdata>
	 **/
	private function parseRxNormSpellingSuggestion($result)
	{
		if ($this->format != 'json') {
			$xml = simplexml_load_string($result);
			$json = json_encode($xml); // simple conversion of xml to json
		} else {
			$json = $result;
		}
		$suggestionResponse = json_decode($json, TRUE); // json to php array
		$suggestionGroup = $suggestionResponse['suggestionGroup'];
		$suggestionList = $suggestionGroup['suggestionList'];
		$suggestion = $suggestionList['suggestion'] ?? [];
		return $suggestion;
	}
}
