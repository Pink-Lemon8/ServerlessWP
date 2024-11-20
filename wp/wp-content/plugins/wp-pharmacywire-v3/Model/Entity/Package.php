<?php

/**
 * Model_Entity_Package
 */
class Model_Entity_Package extends Utility_ModelEntityBase
{
	public function __construct()
	{
		$this->setIdFieldName('package_id');
		parent::__construct();
	}


	/**
	 * Get schedule country
	 *
	 */
	public function getScheduleCountry()
	{
		if (get_option('pw_package_country') == 'on') {
			return $this->origin_country_code;
		}
		if ( empty($this->drug) ) {
			$searchModel = new Model_Resource_Search();
			$drug = $searchModel->getDrugByPackageId($this->package_id);
			return $drug->getScheduleCountry();
		}
		return $this->drug->getScheduleCountry();
	}


	public function getVendorCountryCode()
	{
		$vendorCountryCode = explode(XML_JOIN_SYMBOL, $this->vendor_country_code);
		return $vendorCountryCode[0];
	}

	public function getVendor()
	{
		$vendor = explode(XML_JOIN_SYMBOL, $this->vendor);
		return $vendor[0];
	}
}
