<?php

if (file_exists('../../../../wp/wp-load.php')) {
	require_once('../../../../wp/wp-load.php');
} else {
	require_once('../../../../wp-load.php');
}

require_once('../wp-pharmacywire.php');

if (!current_user_can('pharmacywire_reports')) {
	wp_die(__('You do not have sufficient permissions to access this page.'));
}

if (isset($_GET['orderid'])) {
	$orderId = htmlentities($_GET['orderid'], ENT_QUOTES, "UTF-8");
} else {
	print "No orders to display.";
	return;
}

$reply = new Model_Entity_Reply();
$reply = getOrder($orderId);
$orders = $reply->getData('orders');
$order = new Model_Entity_Order();
$order = $orders[0];

function getOrder($orderId)
{
	$data = array($orderId);

	// create the request via XmlApi Request
	$getOrders = new XmlApi_Request_GetOrders();
	$getOrders->process($data);

	$reply = $getOrders->getData();

	return $reply;
}

function parseOrderInfo($order)
{
	/* ORDER INFO */
	$orderId = $order->getData('id');
	$status = $order->getData('status');
	$date = $order->getData('_date');
	$trackingId = $order->getData('trackingid');
	if (!isset($trackingId) || $trackingId == null) {
		$trackingId = 'n/a';
	}

	echo "<dl><dt>Order Id:</dt><dd>$orderId</dd>
	<dt>Status:</dt><dd>$status</dd>
	<dt>Date:</dt><dd>$date</dd>
	<dt>Tracking Id:</dt><dd>$trackingId</dd><dl>";
}

function parseBillingInfo($order)
{
	/* BILLING INFO */
	$billing = $order->getData('billing');
	$firstName = $billing['firstname'];
	$lastName = $billing['lastname'];
	$city = $billing['city'];
	$state = $billing['state'];
	$country = $billing['country'];
	$postalCode = $billing['postalcode'];

	echo "$firstName $lastName<br />
	$city, $state $postalCode<br />
	$country<br />";
}

function parseShippingInfo($order)
{
	/* SHIPPING INFO */
	$shipping = $order->getData('shipping');
	$firstName = $shipping['firstname'];
	$lastName = $shipping['lastname'];
	$city = $shipping['city'];
	$state = $shipping['state'];
	$country = $shipping['country'];
	$postalCode = $shipping['postalcode'];

	echo "$firstName $lastName<br />
	$city, $state $postalCode<br />
	$country<br />";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
	<title>Order Info</title>
	<link media="all" type="text/css" href="<?php echo plugins_url('/css/admin-style.css', __FILE__); ?>" id="pharmacywireAdminStyles-css" rel="stylesheet">
</head>

<body>
	<!-- BEGIN: page -->
	<div class="pw-pharmacy-wrap pw-view-order" style="width: 725px;">

		<div id="view-order-header"></div>

		<!-- BEGIN: orderInfo -->
		<div id="order-info">
			<h2>Order Info:</h2>
			<?php parseOrderInfo($order); ?>
		</div>
		<!-- END: orderInfo -->

		<!-- BEGIN: billingInfo -->
		<div id="billing-info">
			<h2>Billed To:</h2>
			<?php parseBillingInfo($order); ?>
		</div>
		<!-- END: billingInfo -->

		<!-- BEGIN: shippingInfo -->
		<div id="shipping-info">
			<h2>Shipped To:</h2>
			<?php parseShippingInfo($order); ?>
		</div>
		<!-- END: shippingInfo -->

		<div id="order-details">
			<table cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr>
						<th class="source">&nbsp;</th>
						<th class="description">Description</th>
						<th class="quantity">Quantity</th>
						<th class="unit-price">Unit Price</th>
					</tr>
				</thead>
				<tbody>
					<!-- BEGIN: orderDetails -->

					<?php
					/* ORDER DETAILS */
					$lineItems = $order->getData('items');
					global $table_prefix;
					$packages_table = $table_prefix . 'pw_packages';
					$drugs_table    = $table_prefix . 'pw_drugs';

					$subTotal = 0;
					foreach ($lineItems as $item) {
						$sql_Select  = " SELECT P.*,D.* ";
						$sql_Select .= " FROM " . $packages_table . " P, " . $drugs_table . " D ";
						$sql_Select .= " WHERE P.drug_id=D.drug_id  and (P.package_id = '" . $item['part-id'] . "')";
						$packages = $wpdb->get_results($sql_Select);
						$package = $packages[0];
						$scheduleInfo = explode(XML_JOIN_SYMBOL, $package->schedule);
						$country = $scheduleInfo[1];
						$countryModel = new Model_Country();
						$countryCodeHuman = $countryModel->getCountryByCode($country);
						$countryCodeHuman = (empty($countryCodeHuman) ? 'unknown' : $countryCodeHuman);
						$countryFlag = ($countryCodeHuman == 'unknown') ? '' : '<img class="country-flag" src="' . THEME_URL . 'images/flags/' . $country . '.png" alt="Source: ' . $countryCodeHuman . '" title="Source: ' . $countryCodeHuman . '" />';

						echo '<tr><td class="source">' . $countryFlag . '</td>
								<td class="description">' . $item['description'] . '</td>
								<td class="quantity">' . $item['quantity'] . '</td>
								<td class="unit-price">$' . $item['unitprice'] . '</td></tr>';

						$subTotal = $subTotal + ($item['quantity'] * $item['unitprice']);
					}
					?>

					<!-- END: orderDetails -->

					<?php
					$shippingCost = number_format($order->getData('shippingcost'), 2);
					$total = number_format($order->getData('grandtotal'), 2);
					?>

					<tr>
						<th class="subtotal" colspan="3">Subtotal</th>
						<td>$<?php echo $subTotal ?></td>
					</tr>
					<tr>
						<th class="shipping-fee" colspan="3">Shipping Fee</th>
						<td>$<?php echo $shippingCost ?></td>
					</tr>
					<tr>
						<th class="total" colspan="3">Total</th>
						<td>$<?php echo $total ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<!-- END: page -->
</body>

</html>