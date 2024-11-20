<?php

/**
 * Model_Resource_Attributes
 */
class Model_Resource_Attributes extends Utility_ModelResourceBase
{
	/**
	 * Get the entity table name
	 *
	 */
	public function getEntityTable()
	{
		global $wpdb;
		return "{$wpdb->prefix}pw_attributes";
	}

	/**
	 * Get the entity id field
	 *
	 */
	public function getEntityIdField()
	{
		return 'attribute_id';
	}

	/**
	 * Get the updated column for the entity table
	 *
	 */
	public function getStaticFields()
	{
		return array('attribute_id', 'attribute_key', 'attribute_value');
	}

	public function getData($attributeKey = null)
	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $this->getEntityTable() . ' WHERE ' . $this->getEntityIdField() . "= '%s'";
		if ($attributeKey) {
			$sql .= " AND attribute_key = '%s'";
			$results = $wpdb->get_results($wpdb->prepare($sql, $this->getId(), $attributeKey));
		} else {
			$results = $wpdb->get_results($wpdb->prepare($sql, $this->getId()));
		}

		if ($attributeKey) {
			if (count($results)) {
				return $results[0]->attribute_value;
			} else {
				return 0;
			}
		}
		return $results;
	}

	// *disabled* attributes that start with a '#' are considered public
	// There is now an option in PharmacyWire to set display permissions. So all attributes
	// that are sent via XML Connect are considered public
	public function getPublicAttributes()
	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $this->getEntityTable() . ' WHERE ' . $this->getEntityIdField() . "= '%s'";
		// $sql .= " AND attribute_key LIKE '%s' ";
		// $results = $wpdb->get_results($wpdb->prepare($sql, $this->getId(), '#%'));
		$results = $wpdb->get_results($wpdb->prepare($sql, $this->getId()));

		return $results;
	}

	//check exist in database
	public function isExist()
	{
		if (count($this->getData()) > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Save object collected data
	 *
	 * @param mixed $saveData
	 */
	protected function _processSaveData($arrValues)
	{
		global $wpdb;

		$insertEntity   = true;
		$entityIdFields = $this->getEntityIdField();

		foreach ($arrValues as $attrKey => $attrValues) {
			$attribute = array();
			$attribute['attribute_id'] = $this->getId();
			$attribute['attribute_key'] = $attrKey;
			foreach ($attrValues as $attrValue) {
				$attribute['attribute_value'] = $attrValue;
				$wpdb->insert($this->getEntityTable(), $attribute);
			}
		}

		return $this;
	}
}
