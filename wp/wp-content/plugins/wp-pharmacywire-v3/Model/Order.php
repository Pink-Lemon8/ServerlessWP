<?php

/**
 * Model_Order
 **/
class Model_Order extends Utility_ModelBase
{

	// Create function submitOrder
	public function submitOrder($order)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = $order;

		// create the request via XmlApi Request
		$neworder = new XmlApi_Request_OrderSubmit();

		$neworder->process($data);

		// return result
		$reply = $neworder->getData();

		// return result
		return $reply;
	}

	public function transferOrders()
	{
		//$object = new XmlApi_Request_Order();
	}

	// Create function addComment
	public function addComment($order)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $order;

		// create the request via XmlApi Request
		$requestOrderAddComment = new XmlApi_Request_OrderAddComment();
		$requestOrderAddComment->process($data);

		// return result
		$reply = $requestOrderAddComment->getData();
		return $reply;
	}

	// Create function requestStatus
	public function requestStatus($order)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $order;

		// create the request via XmlApi Request
		$requestOrderRequestStatus = new XmlApi_Request_OrderRequestStatus();
		$requestOrderRequestStatus->process($data);

		// return result
		$reply = $requestOrderRequestStatus->getData();
		return $reply;
	}
}
