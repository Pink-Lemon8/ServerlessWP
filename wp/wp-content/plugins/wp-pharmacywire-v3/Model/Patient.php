<?php

/**
 * Model_Patient
 */
class Model_Patient extends Utility_ModelBase
{
	/**
	 * constructor of the handler - initialises Memcached object
	 *
	 * @return bool
	 */
	public function __construct()
	{
		$this->memcache = new Utility_Memcached;
		$this->lifeTime = intval(3600 * 12);

		return true;
	}

	/**
	 * Creating a New Patient
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function createPatient($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data->patient = $patient;

		// create the request via XmlApi Request
		$patientRequest = new XmlApi_Request_PatientCreate();
		$patientRequest->process($data);

		// return result
		$reply = $patientRequest->getData();

		// return result
		return $reply;
	}

	/**
	 * Updating Patient Details
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function setPatientInfo($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data->patient = $patient;

		// create the request via XmlApi Request
		$patientUpdate = new XmlApi_Request_PatientSetInfo();
		$patientUpdate->process($data);

		// Delete if patient exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patient->patient_id . ':getPatientInfo';
		$this->memcache->delete($searchKey);

		// return result
		$reply = $patientUpdate->getData();

		// return result
		return $reply;
	}

	/**
	 * Adding a New Shipping Address for a Patient
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function addShippingAddress($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $patient;

		// create the request via XmlApi Request
		$patientUpdate = new XmlApi_Request_PatientAddShippingAddress();
		$patientUpdate->process($data);

		// return result
		$reply = $patientUpdate->getData();

		// return result
		return $reply;
	}



	/**
	 * Edit a Shipping Address for a Patient
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function editShippingAddress($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $patient;

		// create the request via XmlApi Request
		$patientUpdate = new XmlApi_Request_PatientEditShippingAddress();
		$patientUpdate->process($data);

		// return result
		$reply = $patientUpdate->getData();

		// return result
		return $reply;
	}



	/**
	 * Delete a Shipping Address for a Patient
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function deleteShippingAddress($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $patient;

		// create the request via XmlApi Request
		$patientUpdate = new XmlApi_Request_PatientDeleteShippingAddress();
		$patientUpdate->process($data);

		// return result
		$reply = $patientUpdate->getData();

		// return result
		return $reply;
	}



	/**
	 * Requesting a Patient's Shipping Addresses
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function getShippingAddresses($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $patient;

		// create the request via XmlApi Request
		$orderShippingAddress = new XmlApi_Request_PatientGetShippingAddresses();
		$orderShippingAddress->process($data);

		// return result
		$reply = $orderShippingAddress->getData();

		// return result
		return $reply;
	}

	/**
	 * Setting a Patient's Password
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function setPatientPassword($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $patient;

		// create the request via XmlApi Request
		$requestNewPassword = new XmlApi_Request_PatientSetPassword();
		$requestNewPassword->process($data);

		// return result
		$reply = $requestNewPassword->getData();

		// return result
		return $reply;
	}

	/**
	 * Requesting Patient Details
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function getPatientInfo($patient)
	{
		// Check if patient exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patient->patientid . ':getPatientInfo';
		$reply = $this->memcache->get($searchKey);
		if ($reply) {
			return $reply;
		}

		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $patient;

		// create the request via XmlApi Request
		$requestPatientInfo = new XmlApi_Request_PatientGetInfo();
		$requestPatientInfo->process($data);

		// return result
		$reply = $requestPatientInfo->getData();
		$this->memcache->set($searchKey, $reply, $this->lifeTime);

		// return result
		return $reply;
	}

	/**
	 * Requesting Patient Details in JSON format
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function getPatientInfoJSON($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $patient;

		$reply = $data->toJson();

		// return result
		return $reply;
	}

	/**
	 * Authenticating a Patient
	 *
	 * @param mixed $patient Model_Entity_Patient
	 * @return Model_Entity_Reply
	 */
	public function authenticateUser($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = $patient;

		// create the request via XmlApi Request
		$requestAuthenUser = new XmlApi_Request_PatientAuthenticateUser();
		$requestAuthenUser->process($data);

		// return result
		$reply = $requestAuthenUser->getData();

		// return result
		return $reply;
	}

	/**
	 * Adding a Legal Agreement for a Patient
	 *
	 */
	public function addLegalAgreement($agreement)
	{
		$reply = new Model_Entity_Reply();
		// prepare data to execute XML request
		$data = $agreement;

		// create the request via XmlApi Request
		$requestAuthenUser = new XmlApi_Request_PatientAddLegalAgreement();
		$requestAuthenUser->process($data);

		// return result
		$reply = $requestAuthenUser->getData();

		// return result
		return $reply;
	}

	/**
	 * Reset password for a Patient
	 *
	 */
	public function resetPassword($patient)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = $patient;

		// create the request via XmlApi Request
		$requestResetPassword = new XmlApi_Request_PatientResetPassword();
		$requestResetPassword->process($data);

		// return result
		$reply = $requestResetPassword->getData();

		// return result
		return $reply;
	}

	/**
	 * Get recent orders
	 *
	 */
	public function getRecentOrders($patientId, $limit = 5)
	{
		// Check if patient orders exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getRecentOrders";
		$reply = $this->memcache->get($searchKey);
		if ($reply) {
			return $reply;
		}

		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data->patientID = $patientId;
		$data->limitOrder = $limit;

		// create the request via XmlApi Request
		$recentOrders = new XmlApi_Request_PatientRecentOrders();
		$recentOrders->process($data);

		// return result
		$reply = $recentOrders->getData();

		// set memcache results for recent orders
		$this->memcache->set($searchKey, $reply, $this->lifeTime);

		// return result
		return $reply;
	}

	public function getRecentOrdersJson($patientId, $limit = 5)
	{
		// Check if patient orders exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getRecentOrders";
		$reply = $this->memcache->get($searchKey);
		if ($reply) {
			return $reply;
		}

		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data->patientID = $patientId;
		$data->limitOrder = $limit;

		// create the request via XmlApi Request
		$recentOrders = new XmlApi_Request_PatientRecentOrders();
		$recentOrders->process($data);

		// return result
		$reply = $recentOrders->getData();

		$reply = $reply->toJson();

		// set memcache results for recent orders
		$this->memcache->set($searchKey, $reply, $this->lifeTime);

		// return result
		return $reply;
	}

	/**
	 *
	 * resets the memcache of recent orders (needed when new order added)
	 *
	 */
	public function resetRecentOrders($patientId)
	{
		// Check if patient orders exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getRecentOrders";
		$this->memcache->delete($searchKey);
	}

	/**
	 * Get Refill Info
	 *
	 */
	public function getRefillInfo($patientId)
	{
		// Check if patient refill info exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getRefillInfo";
		$reply = $this->memcache->get($searchKey);
		if ($reply) {
			return $reply;
		}

		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data->patientID = $patientId;

		// create the request via XmlApi Request
		$reorderOrders = new XmlApi_Request_Refill();
		$reorderOrders->process($data);

		// return result
		$reply = $reorderOrders->getData();

		// set memcache results for refill info
		if (is_countable($reply->prescriptions)) {
			$this->memcache->set($searchKey, $reply, $this->lifeTime);
		}

		// return result
		return $reply;
	}

	/**
	 * Get recent orders
	 *
	 */
	public function getOrders($patientId, $orderId)
	{
		$recent_orders = $this->getRecentOrders($patientId);
		$recent_orders_by_id = array();
		foreach ($recent_orders->getData('orders') as $order) {
			$recent_orders_by_id[$order->id] = $order;
		}

		if (!(array_key_exists($orderId, $recent_orders_by_id))) {
			return;
		}

		// Check if patient orders exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getOrders:" . $orderId;
		$reply = $this->memcache->get($searchKey);
		if ($reply) {
			return $reply;
		}

		// prepare data to execute XML request
		$data = array($orderId);

		// create the request via XmlApi Request
		$getOrders = new XmlApi_Request_GetOrders();
		$getOrders->process($data);

		// return result
		$reply = $getOrders->getData();


		// set memcache results for recent orders
		$this->memcache->set($searchKey, $reply, $this->lifeTime);

		// return result
		return $reply;
	}

	public function getOrdersJson($patientId, $orderId)
	{
		$recent_orders = $this->getRecentOrders($patientId);
		$recent_orders_by_id = array();

		foreach ($recent_orders->getData('orders') as $order) {
			$recent_orders_by_id[$order->id] = $order;
		}

		if (!(array_key_exists($orderId, $recent_orders_by_id))) {
			return;
		}

		// Check if patient orders exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getOrders:" . $orderId;

		$reply = new Model_Entity_Reply();
		$reply = $this->memcache->get($searchKey);

		if ($reply) {
			$orders = $reply->getData('orders');

			$order = new Model_Entity_Order();
			$order = $orders[0];
			$reply = $order->toJson();
		} else {
			// prepare data to execute XML request
			$data = array($orderId);

			// create the request via XmlApi Request
			$getOrders = new XmlApi_Request_GetOrders();
			$getOrders->process($data);

			// return result
			$reply = $getOrders->getData();
			$orders = $reply->getData('orders');

			$order = new Model_Entity_Order();
			$order = $orders[0];

			// set memcache results for recent orders
			$this->memcache->set($searchKey, $reply, $this->lifeTime);

			$reply = $order->toJson();
		}

		// return result
		return $reply;
	}


	public function getInvoiceDocument($patientId, $InvoiceId, $return_mime_type = "application/pdf", $return_method = "inline")
	{
		$order_id_parts = explode('-', $InvoiceId);
		$orderId = $order_id_parts[0];
		$recent_orders = $this->getRecentOrders($patientId);
		$recent_orders_by_id = array();
		foreach ($recent_orders->getData('orders') as $order) {
			$recent_orders_by_id[$order->id] = $order;
		}

		// check to see if we have access to the invoice by checking the order.
		if (!(array_key_exists($orderId, $recent_orders_by_id))) {
			return;
		}

		// check to see if we can serve up the invoice, locked == true means that the invoice won't be changing.
		$invoices = $recent_orders_by_id[$orderId]->invoices;
		if (array_key_exists($InvoiceId, $invoices) && $invoices[$InvoiceId]['locked']) {
			1;
		} else {
			return;
		}

		// Check if patient orders exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getInvoiceDocument:" . $InvoiceId . ":return_mime_type:" . $return_mime_type . ":return_method:" . $return_method;
		if ($return_method === "inline") { // $return_method of 'url' is not worth caching becuase the urls are one time use.
			$reply = $this->memcache->get($searchKey);
		}
		if ($reply) {
			return $reply;
		}

		// prepare data to execute XML request

		$data = array();
		$data[] = array('invoice_id' => $InvoiceId, 'return_mime_type' => $return_mime_type, 'return_method' => $return_method);

		// create the request via XmlApi Request
		$getInvoiceDocument = new XmlApi_Request_GetInvoiceDocument();
		$getInvoiceDocument->process($data);

		// return result
		$reply = $getInvoiceDocument->getData();


		// set memcache results for recent orders
		if ($return_method === "inline") {
			$this->memcache->set($searchKey, $reply, $this->lifeTime);
		}

		// return result
		return $reply;
	}
}
