<?php

/**
 * Model_Entity_Drug
 */
class Model_Entity_Drug extends Utility_ModelEntityBase
{
	public function __construct()
	{
		$this->setIdFieldName('drug_id');
		parent::__construct();
	}

	public function setData($key, $value = null)
	{
		$this->_hasDataChanges = true;
		if (is_array($key)) {
			$this->_data = $key;
		} else {
			$this->_data[$key] = $value;
		}
		return $this;
	}

	/**
	 * Set preferred status of drug
	 *
	 * @return stdClass
	 */
	public function setPreferred($packages)
	{
		foreach ($packages as $package) {
			if ($package['preferred']) {
				$this->preferred = 1;
				break;
			}
		}
		return $this->preferred;
	}

	/**
	 * Get preferred status of drug
	 *
	 * @return stdClass
	 */
	public function getPreferred()
	{
		return $this->preferred;
	}


	/***
	 * Set packages for drug
	 *
	 * @param mixed $packages
	 */
	public function setPackages($packages)
	{
		$this->packages = $packages;
		foreach ($packages as $package) {
			$package->setDrug($this);
		}
	}

	/**
	 * Get schedule name
	 *
	 */
	public function getScheduleName()
	{
		$scheduleInfo = explode(XML_JOIN_SYMBOL, $this->schedule);
		return $scheduleInfo[0];
	}

	/**
	 * Get schedule country
	 *
	 */
	public function getScheduleCountry()
	{
		$scheduleInfo = explode(XML_JOIN_SYMBOL, $this->schedule);
		return $scheduleInfo[1];
	}

	/**
	 * Check whethere
	 *
	 */
	public function isScheduleReference()
	{
		$scheduleName = $this->getScheduleName();
		return $scheduleName == 'Reference';
	}
	public function isAvailable()
	{
		$scheduleName = $this->getScheduleName();
		$available = 0;
		switch ($scheduleName) {
			case "Combination":
				break;
			case "Controlled N/A":
				break;
			case "Not available in Canada":
				break;
			default:
				$available = 1;
				break;
		}
		return $available;
	}
	public function getScheduleMessage()
	{
		$scheduleName = $this->getScheduleName();

		switch ($scheduleName) {
			case "Combination":
				$message = "This is a combination product not available in Canada but each separate ingredient is available and would require a prescription for each.";
				break;
			case "Controlled N/A":
				$message = "Controlled N/A.";
				break;
			case "Not available in Canada":
				$message = "Not available for sale from Canada.";
				break;
			default:
				$message = 'Not available.';
				break;
		}
		return $message;
	}
	/**
	 * Get category name
	 *
	 */
	public function getCategoryName()
	{
		$categoryInfo = explode(XML_JOIN_SYMBOL, $this->category);
		return $categoryInfo[0];
	}
}
