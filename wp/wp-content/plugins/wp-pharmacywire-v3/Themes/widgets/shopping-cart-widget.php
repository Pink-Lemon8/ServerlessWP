<?php

class PWIRE_ShoppingCart_Widget extends WP_Widget
{
	public function __construct()
	{
		$widget_ops = array(
			'classname' => 'pwire-cart-widget',
			'description' => 'PharmacyWire Shopping Cart',
		);
		parent::__construct('pwire_cart_widget', 'PharmacyWire Shopping Cart', $widget_ops);
	}

	// contents of widget, frontend
	public function widget($args, $instance)
	{
		if ( !is_admin() ) {
			if (Cart::haveItems()) {
				$cartState = 'has-items';
			} else {
				$cartState = 'empty';
			} ?>

			<div id="pwire-shopping-cart-widget" class="block widget pwire-cart-widget pwire-cart pwire-cart-<?php echo $cartState; ?>">
				<div class="grid-x block-title cart-widget-header">
					<h3>Shopping Cart</h3>
				</div>
				<div class="grid-x cart-widget-content">
					<div class="line-items cell">
						<?php

						// product line items
						$lstItems = Cart::getListItems();

						if (Cart::haveItems()) {
							foreach ($lstItems as $item) {
								$packageId	= $item->package_id;
								$brandOrGeneric = ($item->generic == 1) ? 'Generic' : 'Brand';
								if (get_option('pw_display_package_name_on_search_results')) {
									$productName = $item->product;
								} else {
									$productName = $item->drug_name;
								}
								$strength = Utility_Common::getFullValue($item->strength);
								$strengthUnit = $item->strength_unit;
								if ($strength != "") {
									$strength .= " $strengthUnit";
								} else {
									$strength = $item->strengthfreeform;
								}
								$productQuantity	= Utility_Common::getQuantity($item->packagequantity, $item->packagingfreeform);
								
								$unitsOrdered = $item->amount;

								$price = $item->price;
								// option to show full tier precision for unit price
								if (get_option('pw_unitprice_full_precision', 'off') == 'on') {
									$price = sprintf("%.4f", $price);
									$price = preg_replace('/(\.[0-9]*?)0+$/', '$1', $price);
									// if less than 2 decimal places, force 2 - but leave higher prevision as is
									if (round($price, 1) == $price) {
										$price = sprintf("%.2F", $price);
									}
								} else {
									$price = PC_formatPrice($item->price);
								}

								$unitPrice = str_replace('\$', '$', $price);

								$lineSubTotal	= PC_formatPrice($item->sub_amount);
								printf('<div id="widget-line-item-%s" class="cart-widget-line-item widget-line-item">
											
									<div class="grid-x">
										<div class="heading cell">
										<b>%s <span class="brand-or-generic">%s</span> - <span class="strength-quantity"><span class="product-strength">%s</span></span></b> <span class="product-quantity">%s</span>
										</div>
									</div>

									<div class="grid-x">
										<div class="cell">	
											<span class="ordered-amount">%d x $%01.2f</span>
										</div>
									</div>

									<div class="grid-x">
										<div class="cell value">	
											$%01.2f
										</div>
									</div>

									</div>', $packageId, $productName, $brandOrGeneric, $strength, $productQuantity, $unitsOrdered, $unitPrice, $lineSubTotal);
							}
							// coupon line items
							// check session for active coupon
							$couponSession = new Model_Coupon();
							$currentCoupons = $couponSession->getCouponSession();

							if (!empty($currentCoupons)) {
								// there is a coupon so setup display
								foreach ($currentCoupons as $couponCode => $couponData) {
									$couponLabel = '';
									$couponDiscountMethodHuman = $couponSession->getDiscountMethodHuman($couponCode);

									if ($couponData['removable'] != 'false') {
										$couponLabel = 'Coupon: "' . $couponCode . '"';
									} else {
										// Mandatory coupons use the description as the label, hide normal coupon wording
										$couponLabel = $couponData['description'];
										$couponData['description'] = '';
									}

									if (!empty($couponDiscountMethodHuman)) {
										if ($couponData['discount'] > 0) {
											$couponDiscountMethodHuman = ' - ' . $couponDiscountMethodHuman;
										} else {
											$couponDiscountMethodHuman = ' + ' . $couponDiscountMethodHuman;
										}
									}
									$couponLineItemClass = ($couponData['usable'] == 'false') ? 'invalid' : 'valid';
									$couponDescription = (!empty($couponData['usable'])) ? $couponData['description'] : '';

									printf('<div id="widget-line-item-%s" class="coupon-line-item widget-line-item coupon-usable-%s">
									
											<div class="grid-x">
												<div class="heading cell">%s</div>
											</div>
											
											<div class="grid-x">
												<div class="coupon-discount cell">
													<div class="description">%s</div>
													<div class="discount value">%s</div>
												</div>
											</div>
																		
											</div>', $couponCode, $couponLineItemClass, $couponLabel, $couponDescription, $couponDiscountMethodHuman);
								}
							}
						} else {
							echo '<div class="grid-x"><div class="cell">Your cart is empty.</div></div>';
						} ?>
					</div>
				</div>
				<?php if (Cart::haveItems()) { ?>
					<div class="cart-footer">
						<?php echo $this->cartFooter($lstItems) ?>
						<?php echo $this->cartCheckoutButton() ?>
					</div>
				<?php } ?>
			</div>
			<?php
		} else {
			/* Simplified admin display - Now that block editor is enabled in WP by default, 
			this admin view avoids errors being generated by the block editor trying to render the frontend code */
			echo '<div style="border: 1px solid #555; padding: 0.5rem; border-radius: 2px;"><b><span class="dashicons dashicons-cart" style="padding-top: 0.1em; color: #999;"></span> PharmacyWire Shopping Cart</b></div>';
		}
	}

	// admin options for widget
	public function form($instance)
	{
	}

	// processing widget options to be saved
	public function update($new_instance, $old_instance)
	{
	}

	public function cartFooter($lstItems)
	{
		$footerContent = "";
		//there is item on shopping cart;
		$items = Cart::getListItems();
		if (Cart::haveItems()) {
			$countItem = count($items);

			$sub_total = PC_formatPrice(Cart::getSubTotal());
			$shipping_fee = PC_formatPrice(Cart::calculateShippingFee($lstItems));
			$total = $sub_total + $shipping_fee;

			// check session for active coupon
			$couponSession = new Model_Coupon();
			$currentCoupons = $couponSession->getCouponSession_PublicInfo();

			$discount = "";
			if (!empty($currentCoupons)) {
				$discountAmount = str_replace('\$', '$', $couponSession->getDiscountHuman($total));
				$total = $couponSession->applyDiscount($sub_total) + $shipping_fee;

				$discount = '<div class="coupons grid-x"><div class="small-6 cell heading">Discount:</div> <div class="small-6 cell value">' . $discountAmount . '</div></div>';
			}

			$total = PC_formatPrice($total);

			$shipping_fee = ($shipping_fee == 0.00) ? 'FREE' : '$' . $shipping_fee;

			$footerContent = '<div class="sub-total grid-x"><div class="small-12 medium-6 cell heading">Subtotal:</div> <div class="small-12 medium-6 cell value">$' . $sub_total . '</div></div>' . $discount . '<div class="shipping grid-x"><div class="small-12 medium-6 cell heading">Shipping:</div> <div class="small-12 medium-6 cell value">' . $shipping_fee . '</div></div><div class="total grid-x"><div class="small-12 medium-6 heading">Total (USD):</div> <div class="small-12 medium-6 cell value">$' . $total . '</div></div>';
		}

		return $footerContent;
	}

	public function cartCheckoutButton()
	{
		$shoppingCartUrl = PC_getShoppingURL();
		$currentPage = PC_getCurrentURL();
		$isCheckoutPage = stristr($currentPage, $shoppingCartUrl);

		$buttonBlock = "";
		if (Cart::haveItems() && !$isCheckoutPage) {
			$buttonBlock .= '<div class="action">';
			$buttonBlock .= '<a href="' . $shoppingCartUrl . '" class="button">View Cart</a>';
			$buttonBlock .= '</div>';
		}
		
		return $buttonBlock;
	}
}

add_action('widgets_init', function () {
	register_widget('PWIRE_ShoppingCart_Widget');
});
