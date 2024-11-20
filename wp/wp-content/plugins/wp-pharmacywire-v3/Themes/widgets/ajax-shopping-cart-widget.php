<?php

class PWIRE_Ajax_ShoppingCart_Widget extends WP_Widget
{
	public function __construct()
	{
		$widget_ops = array(
			'classname' => 'pwire-ajax-cart-widget',
			'description' => 'PharmacyWire Ajax Shopping Cart',
		);
		parent::__construct('pwire_ajax_cart_widget', 'PharmacyWire Ajax Shopping Cart', $widget_ops);
	}

	// contents of widget, frontend
	public function widget($args, $instance)
	{
		if ( !is_admin() ) {
			if (Cart::haveItems()) {
				$cartState = 'has-items';
			} else {
				$cartState = 'empty';
			} 
			?>

				<script>
					jQuery(($) => {
						function updateCartWidget() {
							const r = 'get-cart';

							const ajaxCartWidget = $('.pwire-ajax-cart-widget:not(.pw-block-widget)');
							const ajaxCartWidgetLineItems = ajaxCartWidget.find('.line-items');
							ajaxCartWidget.pwireSpinner();

							return $.ajax({
								type: 'POST',
								url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
								success: (response) => {
									const pwireResponse = JSON.parse(response);
									// const alternativeAddresses = pwireResponse;
									if (pwireResponse.success === 1) {

										let lineItems = '';
										let couponLineItems = '';

										ajaxCartWidgetLineItems.empty();
										if (Object.prototype.hasOwnProperty.call(pwireResponse, 'items') && pwireResponse.items.length) {
											ajaxCartWidgetLineItems.empty();
											// Add cart line items
											$.each(pwireResponse.items, (key, item) => {
												let brandOrGeneric = (item.generic == 0) ? 'Brand' : 'Generic';
												let strength = item.strengthfreeform;
												if (!strength) {
													strength = item.strength + item.strength_unit;
												}
												
												let unitPrice = parseFloat(item.price);
												// If number has less than 2 decimal places, format number to 2
												// otherwise round tier-price to thousanths if full value is being passed
												if (unitPrice % 1 === 0) {
													unitPrice = unitPrice.toFixed(2);
												} else if (unitPrice.toFixed(4).split('.')[1].length > 4) {
													unitPrice = Math.round(unitPrice * 10000) / 10000;
												} else {
													unitPrice = parseFloat(unitPrice.toFixed(4));
													unitPrice = unitPrice.toFixed(Math.max(2, unitPrice.toString().split('.')[1].length));
												}

												lineItems += `<div id="widget-line-item-${item.package_id}" class="cart-widget-line-item widget-line-item">
													<div class="grid-x">
														<div class="heading cell">
														<b>${item.drug_name} <span class="brand-or-generic">${brandOrGeneric}</span> - <span class="strength-quantity"><span class="product-strength">${strength}</span></span></b> <span class="product-quantity">${Number(item.package_quantity)} ${item.package_quantity_units}</span>
														</div>
													</div>
													<div class="grid-x">
														<div class="cell">	
															<span class="ordered-amount">${Number(item.amount)} x $${Number(unitPrice)}</span>
														</div>
													</div>
													<div class="grid-x">
														<div class="cell value">	
															$${Number(item.sub_amount).toFixed(2)}
														</div>
													</div>
												</div>`;
											});

											$(lineItems).appendTo(ajaxCartWidgetLineItems);
											ajaxCartWidget.addClass('pwire-cart-has-items')
										} else {
											ajaxCartWidget.addClass('pwire-cart-empty');
										}

										ajaxCartWidget.find('.cart-footer').empty();
										if (Object.prototype.hasOwnProperty.call(pwireResponse, 'coupons')) {	
											// Add cart coupons
											$.each(pwireResponse.coupons, (key, coupon) => {
												let couponLineItemClass = (coupon.usable == 'false') ? 'invalid' : 'valid';
												
												let couponLabel = '';
												let couponDescription = coupon.description;
												if (coupon.removable != 'false') {
													couponLabel = `Coupon: ${coupon['coupon-code']}`;
												} else {
													couponLabel = couponDescription;
													couponDescription = '';
												}
												let couponDiscount = coupon['discount-human'];
												if (coupon.discount > 0) {
													couponDiscount = ` - ${coupon['discount-human']}`;
												} else if (coupon.discount < 0) { 
													couponDiscount = ` + ${coupon['discount-human']}`;
												}
												couponLineItems += `<div id="widget-line-item-${coupon['coupon-code']}" class="coupon-line-item widget-line-item coupon-usable-${couponLineItemClass}">
													<div class="grid-x">
														<div class="heading cell">${couponLabel}</div>
													</div>
													<div class="grid-x">
														<div class="coupon-discount cell">
															<div class="description">${couponDescription}</div>
															<div class="discount value">${couponDiscount}</div>
														</div>
													</div>
												</div>`;
											});

											$(couponLineItems).appendTo(ajaxCartWidgetLineItems);
										}

										if (Object.prototype.hasOwnProperty.call(pwireResponse, 'items')) {
											ajaxCartWidget.find('.cart-footer').empty();
											let discountLineItem = '';
											if (Object.prototype.hasOwnProperty.call(pwireResponse, 'coupons') && pwireResponse.discount_total != 0) {
												let couponTotalDiscount = Number(pwireResponse.discount_total).toFixed(2);
												if (pwireResponse.discount_total > 0) {
													couponTotalDiscount = ` - $${couponTotalDiscount}`;
												} else if (pwireResponse.discount_total < 0) {
													couponTotalDiscount = couponTotalDiscount.replace('-', '');
													couponTotalDiscount = ` + $${couponTotalDiscount}`;
												} else {
													couponTotalDiscount = `$${couponTotalDiscount}`;
												}
												discountLineItem = `<div class="coupons grid-x"><div class="small-6 cell heading">Discount:</div> <div class="small-6 cell value">${couponTotalDiscount}</div></div>`;
											}
											// Add cart footer
											let footerContent = `<div class="sub-total grid-x"><div class="small-12 medium-6 cell heading">Subtotal:</div> <div class="small-12 medium-6 cell value">$${Number(pwireResponse.sub_total).toFixed(2)}</div></div>${discountLineItem}<div class="shipping grid-x"><div class="small-12 medium-6 cell heading">Shipping:</div> <div class="small-12 medium-6 cell value">${pwireResponse.shipping_cost_human}</div></div><div class="total grid-x"><div class="small-12 medium-6 heading">Total (USD):</div> <div class="small-12 medium-6 cell value">$${Number(pwireResponse.total).toFixed(2)}</div></div>`;

											$(footerContent).appendTo('.pwire-ajax-cart-widget .cart-footer');

											if ((window.location.href != pw_json.shopping_cart_url) && (window.location.href != pw_json.checkout_url)) {
												const checkoutButton = `<div class="action"><a href="${pw_json.shopping_cart_url}" class="button">View Cart</a></div>`;
												$(checkoutButton).appendTo('.pwire-ajax-cart-widget .cart-footer');
											}
										}
									} else {
										ajaxCartWidgetLineItems.empty();
										ajaxCartWidget.find('.cart-footer').empty();
									}
									
									ajaxCartWidget.pwireSpinner().stop();

								}
							});
						}

						updateCartWidget();

						// update cart widget on cart change
						$('.pw-pharmacy-wrap').on('pwire:cart:updateCartForm pwire:cart:removeLineItem pwire:cart:orderSubmitted', () => {
							updateCartWidget();
						});
					});
				</script>

				<div class="pwire-ajax-shopping-cart-widget block widget pwire-cart-widget pwire-cart pwire-ajax-cart-widget pwire-cart-<?php echo $cartState; ?>">
					<div class="grid-x block-title cart-widget-header">
						<h3>Shopping Cart</h3>
					</div>
					<div class="grid-x cart-widget-content">
						<div class="line-items cell"></div>
					</div>
					<div class="cart-footer">
					</div>
				</div>
			<?php
		} else {
			/* Simplified admin display - Now that block editor is enabled in WP by default, 
			this admin view avoids errors being generated by the block editor trying to render the frontend code */
			echo '<div style="border: 1px solid #555; padding: 0.5rem; border-radius: 2px;"><b><span class="dashicons dashicons-cart" style="padding-top: 0.1em; color: #999;"></span> PharmacyWire Ajax Shopping Cart</b></div>';
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

}

add_action('widgets_init', function () {
	register_widget('PWIRE_Ajax_ShoppingCart_Widget');
});
