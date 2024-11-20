<?php

/**
 * Model_Resource_TierPrices
 */
class Model_Resource_TierPrices extends Utility_ModelResourceBase
{
	/**
	 * Get the entity table name
	 *
	 */
	public function getEntityTable()
	{
		global $wpdb;
		return "{$wpdb->prefix}pw_packages_tierprice";
	}

	/**
	 * Get the entity id field
	 *
	 */
	public function getEntityIdField()
	{
		return 'package_id';
	}

	/**
	 * Get the updated column for the entity table
	 *
	 */
	public function getStaticFields()
	{
		return array('package_id', 'price', 'quantity');
	}

	public function getData($quantity = null)
	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $this->getEntityTable() . ' WHERE ' . $this->getEntityIdField() . "= '%s'";
		// if ($packageID) {
		// 	$sql .= " AND quantity = '%s'";
		// }
		$results = $wpdb->get_results($wpdb->prepare($sql, $this->getId(), $quantity));
		if ($quantity) {
			if (count($results)) {
				return $results[0]->price;
			} else {
				return 0;
			}
		}
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
	protected function _processSaveData($tierValues)
	{
		global $wpdb;

		foreach ($tierValues as $tier) {
			$tier['package_id'] = $this->getId();
			$tier['created'] = current_time('mysql');
			$wpdb->insert($this->getEntityTable(), $tier);
		}

		return $this;
	}
}
