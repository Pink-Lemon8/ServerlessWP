<?php

class PW_JSON_Search extends PW_JSON
{
	public function search()
	{
		$response = Shipping::getShippingJSON();
		echo json_encode($response);
	}
}
