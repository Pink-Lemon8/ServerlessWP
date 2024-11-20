<div class="wrap">
	<?php include 'options-head.php' ?>

	<div class="settingForm">

		<a href="http://www.pharmacywire.com" class="pharmacywire-store pharmacywire-plugin-header" target="pharmacywire">PharmacyWire Store</a>
		<br />
		<b>You must fill in the following fields or your orders may not submit properly.</b>
		<legend>
			<h3>Pharmacy Information</h3>
		</legend>
		<form action="options.php" method="post" id="adminForm" name="adminForm">
			<?php settings_fields('pharmacy-store-group'); ?>
			<?php do_settings_sections('pharmacy-store-group'); ?>
			<fieldset>
				<table class="form-table">
					<tr>
						<td class="label"><label for="pw_name">Name:</label></td>
						<td><input type="text" name="pw_name" id="pw_name" size="70" value="<?php echo get_option('pw_name'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="label"><label for="pw_pharmacy">Pharmacy Name:</label></td>
						<td><input type="text" name="pw_pharmacy" id="pw_pharmacy" size="70" value="<?php echo get_option('pw_pharmacy', get_option('pw_name')); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_license">License:</label></td>
						<td><input type="text" name="pw_license" id="pw_license" size="70" value="<?php echo get_option('pw_license'); ?>" /></td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend>
					<h3>Address Information</h3>
				</legend>
				<table class="form-table">
					<tr>
						<td class="label"><label for="pw_address">Address:</label></td>
						<td><input type="text" name="pw_address" id="pw_address" size="70" value="<?php echo get_option('pw_address'); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_city">City:</label></td>
						<td><input type="text" name="pw_city" id="pw_city" size="50" value="<?php echo get_option('pw_city'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="label"><label for="pw_province">Province:</label></td>
						<td><input type="text" name="pw_province" id="pw_province" size="50" value="<?php echo get_option('pw_province'); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_country">Country:</label></td>
						<td><input type="text" name="pw_country" id="pw_country" size="30" value="<?php echo get_option('pw_country'); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_postal_code">Postal Code/Zip:</label></td>
						<td><input type="text" name="pw_postal_code" id="pw_postal_code" size="7" value="<?php echo get_option('pw_postal_code'); ?>" /></td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend>
					<h3>Contact Information</h3>
				</legend>
				<table class="form-table">
					<tr>
						<td class="label"><label for="pw_phone">Phone:</label></td>
						<td>(<input type="text" name="pw_phone_area" id="pw_phone_area" size="3" value="<?php echo get_option('pw_phone_area'); ?>" />)<input type="text" name="pw_phone" id="pw_phone" size="10" value="<?php echo get_option('pw_phone'); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_fax">Fax:</label></td>
						<td>(<input type="text" name="pw_fax_area" id="pw_fax_area" size="3" value="<?php echo get_option('pw_fax_area'); ?>" />)<input type="text" name="pw_fax" id="pw_fax" size="10" value="<?php echo get_option('pw_fax'); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_email">Email:</label></td>
						<td><input type="text" name="pw_email" id="pw_email" size="50" value="<?php echo get_option('pw_email'); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_email_rx">Rx Submission Email:</label></td>
						<td><input type="text" name="pw_email_rx" id="pw_email_rx" size="50" value="<?php echo get_option('pw_email_rx'); ?>" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_hours_of_operation">Hours of Operation:</label></td>
						<td><textarea name="pw_hours_of_operation" id="pw_hours_of_operation" rows="5" cols="40"><?php echo get_option('pw_hours_of_operation'); ?></textarea></td>
					</tr>
				</table>
			</fieldset>

			<hr />

			<fieldset>
				<legend>
					<h3>Shipping Information</h3>
				</legend>
				<table class="form-table">
					<tr>
						<?php
						if (get_option('pw_fridge_express_shipping')) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_fridge_express_shipping" id="pw_fridge_express_shipping" <?php echo $checked; ?> />&nbsp;<label for="pw_fridge_express_shipping">Products marked '#fridge' or
								'#high-value' require express shipping</label>
						</td>
					</tr>
					<tr>
						<?php
						if (get_option('pw_international_express_allowed')) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_international_express_allowed" id="pw_international_express_allowed" <?php echo $checked; ?> />&nbsp;<label for="pw_international_express_allowed">Express shipping allowed for International (non-Canadian)
								orders</label>
						</td>
					</tr>
					<tr>
						<?php
						if (get_option('pw_localfill_only_expressshipping')) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_localfill_only_expressshipping" id="pw_localfill_only_expressshipping" <?php echo $checked; ?> />&nbsp;<label for="pw_localfill_only_expressshipping">Limit express shipping availability to Canadian filled
								orders.</label>
						</td>
					</tr>
					<tr>
						<?php
						if (get_option('pw_charge_shipping_per_country') || get_option('pw_split_multi_country_orders')) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_charge_shipping_per_country" id="pw_charge_shipping_per_country" <?php echo $checked; ?> />&nbsp;<label for="pw_charge_shipping_per_country">Charge shipping fee per
								source country (required when orders are split)</label>
						</td>
					</tr>
					<tr>
						<th width="150"><label for="pw_shipping_fee">Shipping fee:</label></th>
						<td><input type="text" name="pw_shipping_fee" id="pw_shipping_fee" size="10" value="<?php echo get_option('pw_shipping_fee', '9.99'); ?>" />&nbsp; (USD)</td>
						<td>Label: <input type="text" name="pw_shipping_fee_message" id="pw_shipping_fee_message" size="50" length="250" value="<?php echo get_option('pw_shipping_fee_message'); ?>" /></td>
					</tr>
					<tr>
						<th width="150"><label for="pw_express_shipping_fee">Express Shipping:</label></th>
						<td><input type="text" name="pw_express_shipping_fee" id="pw_express_shipping_fee" size="10" value="<?php echo get_option('pw_express_shipping_fee'); ?>" />&nbsp; (USD)</td>
						<td>Label: <input type="text" name="pw_express_shipping_message" id="pw_express_shipping_message" size="50" length="250" value="<?php echo get_option('pw_express_shipping_message'); ?>" /></td>
					</tr>
					<tr>
						<td></td>
						<th>Express shipping tags:</th>
						<td><input type="text" name="pw_express_shipping_on_tags" id="pw_express_shipping_on_tags" size="56" length="250" placeholder="e.g. #fridge, #insulin" value="<?php echo get_option('pw_express_shipping_on_tags'); ?>" /><br />
							<em>(When these tags are present, express shipping is forced on the order. Comma seperated list.)</em>
						</td>
					<tr>
						<th width="150"><label for="pw_intl_shipping_fee">International Shipping:</label></th>
						<td><input type="text" name="pw_intl_shipping_fee" id="pw_intl_shipping_fee" size="10" value="<?php echo get_option('pw_intl_shipping_fee'); ?>" />&nbsp; (USD)</td>
						<td>Label: <input type="text" name="pw_intl_shipping_message" id="pw_intl_shipping_message" size="50" length="250" value="<?php echo get_option('pw_intl_shipping_message'); ?>" /></td>
					</tr>
					<tr>
						<th class="alignleft">Display shipping options <br />list as a:</th>
						<td>
							<input type="radio" name="pw_shipping_option_display" id="pw_shipping_option_display_dropdown" <?php checked('dropdown', get_option('pw_shipping_option_display', 'dropdown')) ?> value="dropdown" /><label for="pw_shipping_option_display_dropdown">drop-down</label>
							<input type="radio" name="pw_shipping_option_display" id="pw_shipping_option_display_radio" <?php checked('radio', get_option('pw_shipping_option_display', 'dropdown')) ?> value="radio" /><label for="pw_shipping_option_display_radio">radio button</label>
						</td>
					</tr>
				</table>
			</fieldset>
			<hr />
			<fieldset>
				<legend>
					<h3>Allowed Countries</h3>
				</legend>
				<table class="form-table">
					<tr>
						<td><label for="pw_allowed_countries">Countries</label></td>
						<td>
							<?php
							if (get_option('pw_allowed_countries')) {
								/* Duplicate for the moment just so it shows up */
								$countryModel = new Model_Country();
								$countries = $countryModel->getCountryList();

								$selectedCountries = get_option('pw_allowed_countries');
								$name = 'pw_allowed_countries[]';
								$attribs = 'multiple="multiple" size="5"';
								$active = $selectedCountries;

								$countryList = Utility_Html::htmlSelect($countries, $name, $attribs, 'country_code', 'country_name', $active, $name);
								echo $countryList;
							} else {
								$countryModel = new Model_Country();
								$countries = $countryModel->getCountryList();
								$name = 'pw_allowed_countries[]';
								$attribs = 'multiple="multiple" size="5"';
								/* Default to all countries allowed */
								$active = array_values($countries);

								$countryList = Utility_Html::htmlSelect($countries, $name, $attribs, 'country_code', 'country_name', $active, $name);
								echo $countryList;
							}
							?>
						</td>
					</tr>
				</table>
			</fieldset>

			<hr />

			<?php if (get_option('pw_v4_legacy_mode', 0) != 1) : ?>
			<fieldset>
				<legend>
					<h3>Coupons</h3>
				</legend>
				<table class="form-table">
					<tr>
						<?php
						if (get_option('pw_enable_coupons', 0)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_enable_coupons" id="pw_enable_coupons" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_enable_coupons">Enable coupons</label>
						</td>
					</tr>
					<tr>
						<th>
							<label for="pw_coupons_mandatory">Mandatory Coupons:</label>
						</th>
						<td style="width: 200px;">
							<input type="text" name="pw_coupons_mandatory" id="pw_coupons_mandatory" length="250" value="<?php echo get_option('pw_coupons_mandatory'); ?>" pattern="^[a-zA-Z0-9_]+(,[ ]?[a-zA-Z0-9_]+)*$" title="Coupon code can only contain _, A-Z, a-z, and 0-9 and be entered in a comma seperated list." placeholder="e.g. coupon1,coupon2" style="width: 100%;" />
						</td>
						<td>
							<em>Coupon(s) to be applied to all orders, that can't be removed, for special circumstances.
								Enter in a comma seperated list if there are multiples, e.g. 'coupon1, coupon2'</em>
						</td>
					</tr>
				</table>
			</fieldset>
			<?php endif; ?>

			<fieldset>
				<hr />
				<h3>CHECKOUT SCREEN OPTIONS</h3>
				<h3>Billing Information</h3>

				<fieldset id="draft-options">
					<legend><b>Draft Options</b></legend>
					<table class="form-table">
						<tr>
							<td class="label"><label for="pw_business_name">Business Name/Check Payee:</label></td>
							<td><input type="text" name="pw_business_name" id="pw_business_name" size="70" value="<?php echo get_option('pw_business_name', get_option('pw_pharmacy')); ?>" /></td>
						</tr>
						<tr>
							<td class="label"><label for="pw_draft_intro_message">Draft cart message:</label></td>
							<td><input type="text" name="pw_draft_intro_message" id="pw_draft_intro_message" size="70" value="<?php echo get_option('pw_draft_intro_message', 'Please mail a check, draft, or money order to:'); ?>" /></td>
						</tr>
					</table>
				</fieldset>

				<fieldset id="allowed-payment-options">
					<legend><b>Allowed Payment Processing</b></legend>
					<table class="form-table">
						<tr>
							<?php // Note these options are inverted because the option value is to disable but the screen value is to allow
							if (get_option('pw_disable_eft') === 'on') {
								$checked = '';
							} else {
								$checked = 'checked="checked"';
							}
							?>
							<td colspan="3">
								<input type="checkbox" name="pw_disable_eft" id="pw_disable_eft" <?php echo $checked; ?> />&nbsp;<label for="pw_disable_eft">EFT / Electronic Check</label>
							</td>
						</tr>
						<tr>
							<?php // Note these options are inverted because the option value is to disable but the screen value is to allow
							if (get_option('pw_disable_amex') === 'on') {
								$checked = '';
							} else {
								$checked = 'checked="checked"';
							}
							?>
							<td colspan="3">
								<input type="checkbox" name="pw_disable_amex" id="pw_disable_amex" <?php echo $checked; ?> />&nbsp;<label for="pw_disable_amex">American Express</label>
							</td>
						</tr>
						<tr>
							<?php // Note these options are inverted because the option value is to disable but the screen value is to allow
							if (get_option('pw_disable_mastercard') === 'on') {
								$checked = '';
							} else {
								$checked = 'checked="checked"';
							}
							?>
							<td colspan="3">
								<input type="checkbox" name="pw_disable_mastercard" id="pw_disable_mastercard" <?php echo $checked; ?> />&nbsp;<label for="pw_disable_mastercard">MasterCard</label>
							</td>
						</tr>
						<tr>
							<?php // Note these options are inverted because the option value is to disable but the screen value is to allow
							if (get_option('pw_disable_visa') === 'on') {
								$checked = '';
							} else {
								$checked = 'checked="checked"';
							}
							?>
							<td colspan="3">
								<input type="checkbox" name="pw_disable_visa" id="pw_disable_visa" <?php echo $checked; ?> />&nbsp;<label for="pw_disable_visa">Visa</label>
							</td>
						</tr>
						<tr>
							<?php
							// Note these options are inverted because the option value is to disable but the screen value is to allow
							// Default to disabled
							if (get_option('pw_disable_discover', 'on') === 'on') {
								$checked = '';
							} else {
								$checked = 'checked="checked"';
							}
							?>
							<td colspan="3">
								<input type="checkbox" name="pw_disable_discover" id="pw_disable_discover" <?php echo $checked; ?> />&nbsp;<label for="pw_disable_discover">Discover</label>
							</td>
						</tr>
					</table>
					<table class="form-table">	
						<tr>
							<th>
								<label for="pw_payment_method_custom">Custom Payment Processor:</label>
							</th>
							<td>
								<input type="text" name="pw_payment_method_custom" id="pw_payment_method_custom" length="250" size="90" value="<?php echo get_option('pw_payment_method_custom'); ?>" pattern="^[a-zA-Z0-9_\s]+(,[ ]?[a-zA-Z0-9_\s]+)*$" title="Payment processors can only contain _, A-Z, a-z, spaces and 0-9 and be entered in a comma seperated list." placeholder="e.g. Payment Processor Name, Second Payment Processor" /><br />
								<?php
									if (!empty(get_option('pw_payment_method_custom'))) {
										$codeHelpString = '';
										$customPaymentMethod = explode(',', get_option('pw_payment_method_custom'));
										foreach ($customPaymentMethod as $index => $paymentM) {
											if (!empty($paymentM)) {
												$code = lcfirst(sanitize_html_class(ucwords($paymentM)));
												
												$paymentMethods["custom"][strtolower($code)] = array("code" => $code, "label" => trim($paymentM));
												$codeHelpString .= "<b>Payment Label:</b> " . trim($paymentM) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Payment Code:</b> " . $code . '<br />';
											}
										}
										echo "<p>" . $codeHelpString . "</p><br />";
									}
									
								?>
								<p>Custom payment process will be submitted as draft with an institution that matches the payment processor name entered.</p>
								<?php if (!empty(get_option('pw_payment_method_custom'))) : ?>
								<p>The checkout page `quick_checkout.phtml` will have a javascript generated div with the class name of `billing_method_{payment code}` and an inner content container of `billing_method_content`. This div ready/available by listening for the `pwire:cart:billingMethod_{payment code}` event trigger in javascript and calling your function from there.</p>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</fieldset>

				<?php if (get_option('pw_v4_legacy_mode', 0) != 1) : ?>
				<h3>Medical Questionnaire</h3>
				<table class="form-table">
					<tr>
						<?php
						if (get_option('pw_show_medq_on_checkout', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_show_medq_on_checkout" id="pw_show_medq_on_checkout" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_show_medq_on_checkout"> Show medical questionnaire on
								the checkout screen for new customers and yearly review.</label>
						</td>
					</tr>
				</table>
				<?php endif; ?>
			</fieldset>

			<h3>Order Questions</h3>
			<fieldset>
				<legend><b>Enable/disable order questions</b></legend>
				<table class="form-table">
					<tr>
						<?php
						if (get_option('pw_checkoutq_contact_patient', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_checkoutq_contact_patient" id="pw_checkoutq_contact_patient" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_checkoutq_contact_patient"> Do you require
								counselling from a pharmacist for the medications you are taking?</label>
						</td>
					</tr>
					<tr>
						<?php
						if (get_option('pw_checkoutq_child_resistant_packaging', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_checkoutq_child_resistant_packaging" id="pw_checkoutq_child_resistant_packaging" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_checkoutq_child_resistant_packaging"> Do you require child resistant packaging?</label>
						</td>
					</tr>
					<tr>
						<?php
						if (get_option('pw_checkoutq_call_for_refills', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<input type="checkbox" name="pw_checkoutq_call_for_refills" id="pw_checkoutq_call_for_refills" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_checkoutq_call_for_refills"> Call/Email for
								Refills?</label>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset>
				<legend><b>Set Default Values (for Disabled Questions)</b></legend>
				<table>
					<tr>
						<th class="alignleft">Use child resistant packaging?</th>
						<td>
							<input type="radio" name="pw_child_resistant_pkg_default" id="pw_child_resistant_pkg_default_yes" <?php checked('Yes', get_option('pw_child_resistant_pkg_default', 'Yes')) ?> value="Yes" /><label for="pw_child_resistant_pkg_default_yes">Yes</label>
							<input type="radio" name="pw_child_resistant_pkg_default" id="pw_child_resistant_pkg_default_no" <?php checked('No', get_option('pw_child_resistant_pkg_default', 'Yes')) ?> value="No" /><label for="pw_child_resistant_pkg_default_no">No</label>
						</td>
					</tr>
					<tr>
						<th class="alignleft">Call for Refills?</th>
						<td>
							<input type="radio" name="pw_call_for_refills_default" id="pw_call_for_refills_default_true" <?php checked('True', get_option('pw_call_for_refills_default', 'True')) ?> value="True" /><label for="pw_call_for_refills_default_true">Yes</label>
							<input type="radio" name="pw_call_for_refills_default" id="pw_call_for_refills_default_false" <?php checked('False', get_option('pw_call_for_refills_default', 'True')) ?> value="False" /><label for="pw_call_for_refills_default_false">No</label>
						</td>
					</tr>
				</table>
			</fieldset>

			<p class="submit">
				<input type="submit" class="button-primary button" id="btnSave" name="btnSave" value="Save" />
			</p>

		</form>
	</div>
</div>

<style>
	.settingForm fieldset {
		margin-top: 10px;
	}

	.settingForm fieldset legend {
		font-style: italic;
	}

	.settingForm table {
		margin-left: 20px;
	}

	.settingForm table td {
		padding: 3px 5px;
	}
</style>