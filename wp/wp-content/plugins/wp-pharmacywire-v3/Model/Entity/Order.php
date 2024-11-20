<?php

/**
 * Model_Entity_Order
 */
class Model_Entity_Order extends Utility_ModelEntityBase
{
	public function __construct()
	{
		parent::__construct();
	}

	public function filterOrderVisualStatus($orderStatus) {
		$pwOrderStatusFilterJSON = get_option('pw_order_status');

		if (!empty($pwOrderStatusFilterJSON)) {
			if (is_json($pwOrderStatusFilterJSON)) {	
				$pwOrderStatusFilter = json_decode($pwOrderStatusFilterJSON, true);
				$filter = (array) $pwOrderStatusFilter['filter'];
				// ensure numeric associative array of filters passed, converts single instanec {} to [{}]
				// allows for [{"status" : "On Hold", "label" : "Ordered"},{"status" : "Shipping", "label" : "Being Delivered"}]
				// or a single {"status" : "Ordered", "label" : "Order Placed"}
				$keys = array_keys($filter);
				if (array_keys($keys) !== $keys) {
					$filter = [$filter];
				}
				
				foreach ($filter as $f) {
					if (is_array($f)) {
						if (array_key_exists('status', $f) && array_key_exists('label', $f)) {
							if (trim($f['status']) == trim($orderStatus)) {
								$orderStatus = $f['label'];
							}
						}
					}
				}
			} else {
				error_log('PharmacyWire - invalid pw_order_status: not valid JSON');
			}
		}
		
		return $orderStatus;
	}
}
