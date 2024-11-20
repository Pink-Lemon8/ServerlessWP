<?php

/**
 * Page_Register
 */
class Page_ViewOrder extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		if (!WebUser::isLoggedIn()) {
			$this->redirect(PC_getLoginUrl());
		}

		$this->_displayOrders();
	}

	public function _displayOrders()
	{
		$this->setTemplate('page_view_order');

		if (isset($_GET['orderid'])) {
			$orderId = htmlentities($_GET['orderid'], ENT_QUOTES, "UTF-8");
		} else {
			$msg = "No orders to display.";
			$this->assign('RESULT_MESSAGES', $msg);
			return;
		}

		$orders = new Model_Entity_Order();
		$orders = $this->_getPatientOrder($orderId);

		$invoicePDF = '';
		
		foreach ($orders->orders as $order) {
			$invoices = $order->invoices;

			foreach ($invoices as $invoice_id => $invoice_info) {
				if ($invoice_info['locked']) {
					$invoicePDF .= '<a class="view-invoice invoice-pdf button" href="' . PC_helperGetInvoiceDocumentUrl() . '?invoiceid=' . $invoice_id . '">Download Invoice</a>';
				}
			}
		}

		$this->assign('INVOICE_PDF', $invoicePDF);

		foreach ($orders->getData('orders') as $order) {
			$this->_parseOrderInfo($order);
			$this->_parseBillingInfo($order);
			$this->_parseShippingInfo($order);
			$this->_parseOrderDetails($order);
		}

		return;
	}

	public function _getPatientOrder($orderId)
	{
		$patientModel = new Model_Patient();

		$patient = new Model_Entity_Patient();
		$patient->patientid = $this->_getPatientId();
		//$result = $patientModel->getPatientOrders($orderId);
		$result = $patientModel->getOrders($patient->patientid, $orderId);

		return $result;
	}

	public function _getPatientId()
	{
		return WebUser::getUserID();
	}

	public function _parseOrderInfo($order)
	{
		/* ORDER INFO */
		$orderId = $order->getData('id');
		$status = $order->getData('visualstatus');
		$date = $order->getData('_date');
		$trackingId = $order->getData('trackingid');
		if (!isset($trackingId) || $trackingId == null) {
			$trackingId = 'n/a';
		}

		$this->assign('ORDER_ID', $orderId);
		$this->assign('STATUS', $status);
		$this->assign('DATE', $date);
		$this->assign('TRACKING_ID', $trackingId);

		$this->parse('orderInfo');
	}

	public function _parseBillingInfo($order)
	{
		/* BILLING INFO */
		$billing = $order->getData('billing');

		$firstName = $billing['firstname'];
		$lastName = $billing['lastname'];
		$address = $billing['address'];
		if (strlen($billing['address2']) > 0) {
			$address .= '<br />' . $billing['address2'];
		}
		if (strlen($billing['address3']) > 0) {
			$address .= '<br />' . $billing['address3'];
		}
		$city = $billing['city'];
		$state = $billing['state'];
		$country = $billing['country'];
		$postalCode = $billing['postalcode'];

		$this->assign('BILLING_FIRST_NAME', $firstName);
		$this->assign('BILLING_LAST_NAME', $lastName);
		$this->assign('BILLING_ADDRESS', $address);
		$this->assign('BILLING_CITY', $city);
		$this->assign('BILLING_STATE', $state);
		$this->assign('BILLING_COUNTRY', $country);
		$this->assign('BILLING_POSTAL_CODE', $postalCode);

		$this->parse('billingInfo');
	}

	public function _parseShippingInfo($order)
	{
		/* SHIPPING INFO */
		$shipping = $order->getData('shipping');

		$firstName = $shipping['firstname'];
		$lastName = $shipping['lastname'];
		$address = $shipping['address'];
		if (strlen($shipping['address2']) > 0) {
			$address .= '<br />' . $shipping['address2'];
		}
		if (strlen($shipping['address3']) > 0) {
			$address .= '<br />' . $shipping['address3'];
		}
		$city = $shipping['city'];
		$state = $shipping['state'];
		$country = $shipping['country'];
		$postalCode = $shipping['postalcode'];

		$this->assign('SHIPPING_FIRST_NAME', $firstName);
		$this->assign('SHIPPING_LAST_NAME', $lastName);
		$this->assign('SHIPPING_ADDRESS', $address);
		$this->assign('SHIPPING_CITY', $city);
		$this->assign('SHIPPING_STATE', $state);
		$this->assign('SHIPPING_COUNTRY', $country);
		$this->assign('SHIPPING_POSTAL_CODE', $postalCode);

		$this->parse('shippingInfo');
	}

	public function _parseOrderDetails($order)
	{
		/* ORDER DETAILS */
		$lineItems = $order->getData('items');

		foreach ($lineItems as $item) {
			$this->assign('ROW', array(
				'description' => $item['description'],
				'quantity' => $item['quantity'],
				'unitprice' => number_format($item['unitprice'], 2),
			));
			$this->parse('orderDetails');
		}

		$shippingCost = $order->getData('shippingcost');
		$subTotal = $order->getData('subtotal');
		$total = $order->getData('grandtotal');
		$this->assign('SHIPPING_COST', number_format($shippingCost, 2));
		$this->assign('SUBTOTAL', number_format($subTotal, 2));
		$this->assign('TOTAL', number_format($total, 2));
	}
}
