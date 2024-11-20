<?php

/**
 * Model_Tag
 **/
class Model_OrderTag extends Utility_ModelBase
{
	public $validTags = array();

	public function __construct()
	{
		if (!empty(get_option('pw_order_tags'))) {
			$pwTagsJsonString = get_option('pw_order_tags');
			if (is_json($pwTagsJsonString)) {
				$pwTags = json_decode($pwTagsJsonString, true);
				$this->validTags = $pwTags;
			} else {
				error_log('PharmacyWire - invalid pw_order_tags: not valid JSON');
			}
		}
	}

	public function applyTag($tagData, $validateTag = true)
	{
		$tagsResponse = [];

		$tagValid = true;

		// Validate tag and if valid for use, save to session and setup json response
		if ($validateTag) {
			$tagValid = $this->validateTagData($tagData);
		}

		if ($tagValid) {
			$tagCode = $tagData['tag-code'];
			$tagValue = $tagData['tag-value'];
			$tagLabel = $this->validTags[$tagCode]['label'];
			$tagsResponse['status'] = 'success';
			$tagsResponse['tag-code'] = $tagCode;
			$tagsResponse['tags'][$tagCode]['code'] = $tagCode;
			$tagsResponse['tags'][$tagCode]['label'] = $tagLabel;
			$tagsResponse['tags'][$tagCode]['value'] = $tagValue;

			$this->setTagSession($tagData);
		}

		return json_encode($tagsResponse);
	}

	public function validateTagData($tagData)
	{
		$tagValid = true;

		if (!isset($tagData['tag-code']) || !isset($tagData['tag-value'])) {
			$tagValid = false;
		} elseif (!isset($this->validTags[$tagData['tag-code']])) {
			// check if it's a valid tag found in the get_option pw_order_tags config
			$tagValid = false;
		}

		return $tagValid;
	}

	public function setTagSession($tagData)
	{
		$tagCode = $tagData['tag-code'];
		$tagValue = $tagData['tag-value'];
		$tagLabel = $this->validTags[$tagCode]['label'];
		$_SESSION['order_tags'][$tagCode] = array(
			'code' => $tagCode,
			'label' => $tagLabel,
			'value' => $tagValue,
		);
	}

	public function getTagSession()
	{
		$validSessionTags = array();

		if (isset($_SESSION['order_tags'])) {
			foreach ($_SESSION['order_tags'] as $tagCode => $tagData) {
				$validSessionTags[$tagCode] = $tagData;
			}

			return $validSessionTags;
		}

		return false;
	}

	public function getValidTags()
	{
		return $this->validTags;
	}

	public static function removeTagSession($tagCode)
	{
		$status = 'fail';
		if (isset($_SESSION['order_tags'][$tagCode])) {
			unset($_SESSION['order_tags'][$tagCode]);

			// If after removing individual tag there are no tags left in session, unset tags
			if (!count($_SESSION['order_tags'])) {
				unset($_SESSION['order_tags']);
			}

			$status = 'success';
		}
		return json_encode(array('status' => $status));
	}

	public static function removeTagSessionAll()
	{
		$status = 'fail';
		if (isset($_SESSION['order_tags'])) {
			unset($_SESSION['order_tags']);
			$status = 'success';
		}
		return json_encode(array('status' => $status));
	}
}
