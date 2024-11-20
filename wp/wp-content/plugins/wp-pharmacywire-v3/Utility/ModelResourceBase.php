<?php

/**
 * Using the wordpress database to insert/update data
 */
abstract class Utility_ModelResourceBase
{
	/**
	 * Get the entity table name
	 *
	 */
	abstract public function getEntityTable();

	/**
	 * Get the entity id field
	 *
	 */
	abstract public function getEntityIdField();

	public function getId()
	{
		return $this->_id;
	}
	public function setId($id)
	{
		$this->_id = $id;
	}
	/**
	 * Get the updated column for the entity table
	 *
	 */
	abstract public function getStaticFields();

	/**
	 * Save entity's attributes into the object's resource
	 *
	 * @param Model_Entity_Base $object
	 */
	public function save($arrValues)
	{
		/* if ($object->isDeleted()) {
                 return $this->delete();
             }
             */
		$this->_processSaveData($arrValues);

		return $this;
	}

	/**
	 * Delete entity using current object's data
	 *
	 * @param mixed $object
	 */
	public function delete($object = null)
	{
		global $wpdb;
		$id = $this->getId();
		$query = 'DELETE FROM ' . $this->getEntityTable() . ' WHERE ' . $this->getEntityIdField() . "= '%s'";

		$wpdb->query($wpdb->prepare($query, $id));

		return $this;
	}

	protected function _reverseData($arrInput)
	{
		$newData   = $arrInput;

		$staticFields   = $this->getStaticFields();

		// define result data
		$entityRow  = array();
		if (!is_array($arrInput)) {
			return $entityRow;
		}


		foreach ($newData as $k => $v) {

			/**
			 * Check attribute information
			 */
			if (is_numeric($k) || is_array($v)) {
				continue;
			}
			/**
			 * Check if data key is presented in static fields or attribute codes
			 */
			if (!in_array($k, $staticFields)) {
				continue;
			}

			$entityRow[$k] = $v;
		}

		$result = $entityRow;

		return $result;
	}
	/**
	 * Save object collected data
	 *
	 * @param mixed $saveData
	 */
	protected function _processSaveData($arrInput)
	{
		global $wpdb;

		$arrValues = $this->_reverseData($arrInput);

		$insertEntity   = true;
		$entityIdField  = $this->getEntityIdField();
		$entityId       = $this->getId();
		$condition      = $entityIdField . '="' . $entityId . '"';

		if (!empty($entityId)) {
			$select = 'SELECT COUNT(' . $entityIdField . ') FROM ' . $this->getEntityTable() . ' WHERE ' . $condition;

			if ($wpdb->get_var($select) > 0) {
				$insertEntity = false;
			}
		}

		/**
		 * Process base row
		 */
		if ($insertEntity) {
			$wpdb->insert($this->getEntityTable(), $arrValues);
		} else {
			$wpdb->update($this->getEntityTable(), $arrValues, array($entityIdField => $entityId));
		}

		return $this;
	}

	protected function _getLastInsertId()
	{
		return $this->_last_insert_id;
	}
}
