<?php

$catalogUpdateProgress = get_option('pw_catalog_update_progress');
$catalogLastUpdated = get_option('pw_catalog_last_update_time', '0');

// reset catalog status to completed after 10 minutes in case of failure
if (empty($catalogUpdateProgress)) {
	update_option('pw_catalog_update_progress', 'new');
} elseif ($catalogUpdateProgress != 'failed' && (is_numeric($catalogLastUpdated) && ($catalogLastUpdated < strtotime('-10 minutes')))) {
	update_option('pw_catalog_update_progress', 'failed');
}

?>

<script type="text/javascript">
	function disableScheduleTime(checkElement) {
		if (!checkElement.checked) {
			jQuery('#pw_schedule_time').attr("disabled", "disabled");
		} else {
			jQuery('#pw_schedule_time').removeAttr("disabled");
		}
	}

	var catalogStatusCheck = function() {};

	catalogStatusCheck = function() {
		jQuery.ajax({
			type: "GET",
			dataType: "json",
			url: '<?php echo PWIRE_PLUGIN_URL . 'request.php' ?>',
			data: [{
				'name': 'CatalogStatus',
				'value': 1
			}],
			success: function(data) {
				if (data.Status != 'new') {
					jQuery('.catalog-update-status .status').text(data.Status)
					jQuery('.catalog-update-status').show();
				}
				if (data.Status == 'new' || data.Status == 'completed' || data.Status == 'failed') {
					jQuery('#btnRefreshCache').prop('disabled', false);
					jQuery('.catalog-update-status .status-spinner').hide();
				} else {
					jQuery('#btnRefreshCache').prop('disabled', true);
					window.setTimeout(catalogStatusCheck, 10000);
				}
			}
		});
	};

	function refreshCache() {
		document.adminForm.btnRefreshCache.disabled = true;
		document.adminForm.isRefreshCache.value = 1;

		var catalogUpdateUrl = '<?php echo admin_url('admin.php?page=pharmacy_menu_catalog.php'); ?>';
		jQuery('.catalog-update-warning').hide();
		jQuery('.catalog-update-status .status').text('starting');
		jQuery('.catalog-update-status .status-spinner').show();
		jQuery('.catalog-update-status').show();
		window.setTimeout(
			function() {
				catalogStatusCheck.call();
			},
			20000
		);

		jQuery.ajax({
			type: "POST",
			url: catalogUpdateUrl,
			data: jQuery("#adminForm").serializeArray(),
			success: function(data) {
				// blocks until catalog is done importing
				1;
			}
		});
	}

	jQuery(document).ready(function() {
		var catalogUpdateStatus = jQuery('.catalog-update-status .status').text();

		if (catalogUpdateStatus == 'completed' || catalogUpdateStatus == 'failed') {
			1;
		} else {
			// disable the button if catalog progress already in place
			catalogStatusCheck.call();
		}
	});
</script>

<div class="wrap">
	<?php include 'options-head.php' ?>

	<div class="settingForm">

		<a href="http://www.pharmacywire.com" class="pharmacywire-catalog pharmacywire-plugin-header" target="pharmacywire">PharmacyWire Catalog</a>

		<form action="options.php" method="post" id="adminForm" name="adminForm">
			<?php settings_fields('pharmacy-catalog-group'); ?>
			<?php do_settings_sections('pharmacy-catalog-group'); ?>
			<fieldset>
				<?php
				if (get_option('pw_enable_schedule')) {
					$checked = 'checked="checked"';
					$disabled = '';
				} else {
					$checked = '';
					$disabled = 'disabled="disabled"';
				}
				?>
				<legend><input type="checkbox" name="pw_enable_schedule" id="pw_enable_schedule" <?php echo $checked; ?> onclick="disableScheduleTime(this)" />&nbsp;<label for="pw_enable_schedule">Enable set schedule for refreshing data from Pharmacy System</label></legend>
				<table>
					<tr>
						<td><label for="pw_schedule_time">Scheduled update time</label></td>
						<td><input type="text" name="pw_schedule_time" id="pw_schedule_time" size="10" value="<?php echo get_option('pw_schedule_time', '03:00'); ?>" <?php echo $disabled; ?> />&nbsp; (hh:mm)
							<?php
							if (wp_next_scheduled('buildcache_event')) {
								$sechuledUpdateTime = date('Y-m-d H:i:s', wp_next_scheduled('buildcache_event'));
								$sechuledUpdateTime = get_date_from_gmt($sechuledUpdateTime, 'D M jS, Y; g:ia');
								echo 'Next run scheduled for: ' . $sechuledUpdateTime;
							}
							?></td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend><b>Checkout URL</b></legend>
				<table>
					<tr>
						<td class="label"><label for="pw_checkout_url">URL</label></td>
						<td><input type="text" name="pw_checkout_url" id="pw_checkout_url" size="70" value="<?php echo get_option('pw_checkout_url', '/shopping-cart/checkout-login/'); ?>" /></td>
					</tr>
					<tr>
						<td colspan="2"><span class="description">Custom override URL for checkout to follow.</span></td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend><b>Continue Shopping URL</b></legend>
				<table>
					<tr>
						<td class="label"><label for="pw_continue_shopping_url">URL</label></td>
						<td><input type="text" name="pw_continue_shopping_url" id="pw_continue_shopping_url" size="70" value="<?php echo get_option('pw_continue_shopping_url'); ?>" /></td>
					</tr>
					<tr>
						<td colspan="2"><span class="description">Custom override URL for continue shopping button.</span></td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend><h3>Product Settings</h3></legend>
			</fieldset>
			<fieldset>
				<legend><label for="pw_buy_label"><b>Buy Button Label</b> <input type="text" name="pw_buy_label" id="pw_buy_label" size="35" value="<?php echo get_option('pw_buy_label', 'Add to Cart'); ?>" /> <span class="description">Used on search results page. Default is 'Add to Cart'</span></label></legend>
			</fieldset>
			<fieldset>
				<legend><b>External Buy Link</b></legend>
				<table>
					<tr>
						<td class="label"><label for="pw_externalbuy_url">URL</label></td>
						<td><input type="text" name="pw_externalbuy_url" id="pw_externalbuy_url" size="70" value="<?php echo get_option('pw_externalbuy_url'); ?>" /></td>
					</tr>
					<tr>
						<td colspan="2"><span class="description">Add Shortcode [PACKAGE] where you want Product ID placed in the URL. Example: https://www.url.com/addtocart?package=[PACKAGE]</span></td>
					</tr>
				</table>
			</fieldset>
			<hr />
			<fieldset>
				<legend><b>Post Type(s) to Search for Products</b></legend>
				<p>The PharmacyWire plugin will search all post types by default for custom product content/page overrides. If you wish to limit that search to specific post type(s), select the post type(s) to limit the product search to. For example: Selecting 'Product' the default product post type in the PharmacyWire plugin will ristrict the search to the product post type only.</p>
				<table>
					<tr>
						<td class="label"><label for="pw_product_search_post_type">Product Content Override<br>Post Type(s)</label></td>
						<td>
						<?php 
							$post_types = get_post_types(array('public' => true), 'objects', 'and');
							$selectedPostTypes = get_option('pw_product_search_post_type');
							$name='pw_product_search_post_type[]';
							$attribs = 'multiple="multiple" size="5"';
							$active = $selectedPostTypes;
							$postTypeSelect = Utility_Html::htmlSelect($post_types, $name, $attribs, 'name', 'label', $active, $name);
							echo $postTypeSelect;
						?>
						</td>
					</tr>
				</table>
			</fieldset>
			<hr />
			<fieldset>
				<legend><b>Detailed Search Results Options</b></legend>
				<table>
					<tr>
						<td class="label">
							<script>
								jQuery(document).on('ready', function($) {
									jQuery('[name=pw_detailresults_groupby]').on('change', function() {
										if (jQuery(this).val() == 'country') {
											jQuery('.group-by-country-codes').show();
										} else {
											jQuery('.group-by-country-codes').hide();
										}
									});
								});
							</script>
							<label for="pw_detailresults_groupby">Group by</label>
							<?php

							$selectedOption = null;

							// legacy support default to preferred
							$selectedOption = get_option('pw_detailresults_groupby', 'preferred');
							$name = 'pw_detailresults_groupby';
							$attribs = '';

							$groupByOptions = array(
								array('value' => 'none', 'text' => 'None'),
								array('value' => 'preferred', 'text' => 'Preferred'),
								array('value' => 'country', 'text' => 'Country')
							);

							$groupBySelect = Utility_Html::htmlSelect($groupByOptions, $name, $attribs, 'value', 'text', $selectedOption, $name);

							echo $groupBySelect;
							?>
						</td>
					</tr>
					<tr>
						<td colspan="2"><span class="description">Options to group drugs by <?php echo join(', ', array_column($groupByOptions, 'text')); ?></span></td>
					</tr>
					<?php
					$groupByCCVisibility = 'display: none;';

					if (get_option('pw_detailresults_groupby') == 'country') {
						$groupByCCVisibility = 'display: table-row;';
					}
					?>
					<tr class="group-by-country-codes" style="<?php echo $groupByCCVisibility; ?>">
						<td class="label">
							<label for="pw_detailresults_groupby_countrycodes">Country Codes</label>
							<input type="text" name="pw_detailresults_groupby_countrycodes" id="pw_detailresults_groupby_countrycodes" value="<?php echo get_option('pw_detailresults_groupby_countrycodes'); ?>" /><br />
							<span class="description">Add countries by <a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3" target="_blank">alpha-3 country code</a> in a comma separated list. E.g. 'CAN,USA,GBR'. The remaining countries will be sorted alphabetically.</span>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset>
				<legend><label for="pw_filter_by_tag">Show only drugs with a specific tag: <input type="text" name="pw_filter_by_tag" id="pw_filter_by_tag" size="35" value="<?php echo get_option('pw_filter_by_tag'); ?>" /></label> (Comma separated list ex. '#preferred, #anothertag')</legend>
			</fieldset>
			<fieldset>
				<legend><input type="checkbox" name="pw_unitprice_full_precision" id="pw_unitprice_full_precision" <?php checked('on', get_option('pw_unitprice_full_precision', false)) ?> />&nbsp;<label for="pw_unitprice_full_precision">Display unit price as up to 4 decimal place precision (the default is 2, which is derived from the tier price rounded).</label></legend>
			</fieldset>
			<fieldset>
				<?php
				if (get_option('pw_enable_bestprice')) {
					$checked = 'checked="checked"';
					$disabled = '';
				} else {
					$checked = '';
					$disabled = 'disabled="disabled"';
				}
				?>
				<legend><input type="checkbox" name="pw_enable_bestprice" id="pw_enable_bestprice" <?php echo $checked; ?> />&nbsp;<label for="pw_enable_bestprice">Only display best priced drugs?</label></legend>
			</fieldset>
			<fieldset>
				<legend><input type="checkbox" name="pw_drug_dropdown" id="pw_drug_dropdown" <?php checked('on', get_option('pw_drug_dropdown', 'on')) ?> />&nbsp;<label for="pw_drug_dropdown">Display drug packages in a dropdown? (or grouped custom implementation, radio buttons, etc.)</label></legend>
			</fieldset>
			<fieldset>
				<?php /* Noted: pw_drug_dropdown_seperate_str 
				- separate misspelling in variable - leaving as is for now to not affect current options */ ?>
				<legend><input type="checkbox" name="pw_drug_dropdown_seperate_str" id="pw_drug_dropdown_seperate_str" <?php checked('on', get_option('pw_drug_dropdown_seperate_str', 'on')) ?> />&nbsp;<label for="pw_drug_dropdown_seperate_str">Separate dropdown by drug strength</label></legend>
			</fieldset>
			<fieldset>
				<legend><input type="checkbox" name="pw_cart_quantity_dropdown" id="pw_cart_quantity_dropdown" <?php checked('on', get_option('pw_cart_quantity_dropdown', '')) ?> />&nbsp;<label for="pw_cart_quantity_dropdown">Show quantity as dropdown for shopping cart (instead of the input with +/- buttons)?</label></legend>
			</fieldset>
			<fieldset>
				<?php
				if (get_option('pw_enable_splitdrugs')) {
					$checked = 'checked="checked"';
					$disabled = '';
				} else {
					$checked = '';
					$disabled = 'disabled="disabled"';
				}
				?>
				<legend><input type="checkbox" name="pw_enable_splitdrugs" id="pw_enable_splitdrugs" <?php echo $checked; ?> />&nbsp;<label for="pw_enable_splitdrugs">Split Rx / OTC items on drug summary pages?</label></legend>
			</fieldset>
			<fieldset>
				<?php
				if (get_option('pw_block_canucks')) {
					$checked = 'checked="checked"';
					$disabled = '';
				} else {
					$checked = '';
					$disabled = 'disabled="disabled"';
				}
				?>
				<legend><input type="checkbox" name="pw_block_canucks" id="pw_block_canucks" <?php echo $checked; ?> />&nbsp;<label for="pw_block_canucks">Show ONLY Canadian drugs to Canadian visitors?</label></legend>
			</fieldset>

			<fieldset>
				<label for="pw_canadian_IP_exceptions">Allowed Canadian IP Addresses</label><br />
				<textarea style="width:450px;height:100px" id="pw_canadian_IP_exceptions" name="pw_canadian_IP_exceptions" rows="3" cols="15"><?php

				$defaultMessage = '';

				$message = get_option('pw_canadian_IP_exceptions', $defaultMessage);

				if ($message === '') {
					echo $defaultMessage;
				} else {
					echo $message;
				}
				?></textarea><br /><span class="description">(Note: Enter Canadian IP addresses to be treated as US.)</span>
			</fieldset>

			<fieldset>
				<?php
				if (get_option('pw_display_package_name_on_search_results')) {
					$checked = 'checked="checked"';
					$disabled = '';
				} else {
					$checked = '';
					$disabled = 'disabled="disabled"';
				}
				?>
				<legend><input type="checkbox" name="pw_display_package_name_on_search_results" id="pw_display_package_name_on_search_results" <?php echo $checked; ?> />&nbsp;<label for="pw_display_package_name_on_search_results">Show package name on search results</label></legend>
			</fieldset>

			<fieldset>
				<legend><input type="checkbox" name="pw_display_ingredients_on_search_results" id="pw_display_ingredients_on_search_results" value="1" <?php checked(1, get_option('pw_display_ingredients_on_search_results', 0)) ?> />&nbsp;<label for="pw_display_ingredients_on_search_resultse">Show ingredients on search results</label></legend>
			</fieldset>

			<fieldset>
				<?php
				if (get_option('pw_treat_familyname_as_alternate_drugname') == 'on') {
					$checked = 'checked="checked"';
					$disabled = '';
				} else {
					$checked = '';
					$disabled = 'disabled="disabled"';
				}
				?>
				<legend><input type="checkbox" name="pw_treat_familyname_as_alternate_drugname" id="pw_treat_familyname_as_alternate_drugname" <?php echo $checked; ?> />&nbsp;<label for="pw_treat_familyname_as_alternate_drugname">Treat drug family name as an alternate drug name</label></legend>
			</fieldset>


			<fieldset>
				<?php
				if (get_option('pw_generic_finds_generic')) {
					$checked = 'checked="checked"';
					$disabled = '';
				} else {
					$checked = '';
					$disabled = 'disabled="disabled"';
				}
				?>
				<legend><input type="checkbox" name="pw_generic_finds_generic" id="pw_generic_finds_generic" <?php echo $checked; ?> />&nbsp;<label for="pw_generic_finds_generic">Generic search only displays generic results</label></legend>
			</fieldset>

			<hr />

			<fieldset>
				<legend><b>Product Page Schema Markup</b> (<a href="https://schema.org/" target="_blank">Schema.org</a>)</legend>
				<table>
					<tr>
						<?php
						if (get_option('pw_enable_product_schema', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<legend><input type="checkbox" name="pw_enable_product_schema" id="pw_enable_product_schema" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_enable_product_schema">Include <a href="https://schema.org/Product" target="_blank">Product Schema</a></label></legend>
						</td>
					</tr>
				</table>
				<table>
					<tr>
						<?php
						if (get_option('pw_enable_drug_schema', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>
						<td colspan="3">
							<legend><input type="checkbox" name="pw_enable_drug_schema" id="pw_enable_drug_schema" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_enable_drug_schema">Include <a href="https://schema.org/Drug" target="_blank">Drug Schema</a></label></legend>
						</td>
					</tr>
				</table>
			</fieldset>

			<hr />

			<fieldset>
				<legend><b>Plugin Display</b></legend>
				<table>
					<tr>

						<?php
						if (get_option('pw_enable_foundation', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>

						<td colspan="3">
							<legend><input type="checkbox" name="pw_enable_foundation" id="pw_enable_foundation" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_enable_foundation">Include Foundation 6 Stylesheets/Scripts - <a href="https://foundation.zurb.com/" target="_blank">http://foundation.zurb.com/</a></label></legend>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset>
				<legend></legend>
				<table>
					<tr>

						<?php
						if (get_option('pw_default_plugin_styles', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>

						<td colspan="3">
							<legend><input type="checkbox" name="pw_default_plugin_styles" id="pw_default_plugin_styles" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_default_plugin_styles">Include default plugin stylesheets (CSS)</label></legend>
						</td>
					</tr>
				</table>
			</fieldset>

			<fieldset>
				<legend></legend>
				<table>
					<tr>

						<?php
						if (get_option('pw_default_json_theme', 1)) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						?>

						<td colspan="3">
							<legend><input type="checkbox" name="pw_default_json_theme" id="pw_default_json_theme" value="1" <?php echo $checked; ?> />&nbsp;<label for="pw_default_json_theme">Use default plugin json js/css</label></legend>
						</td>
					</tr>
				</table>
			</fieldset>

			<hr />

			<fieldset>
				<legend><b>Plugin Cache Options</b></legend>
				<table>
					<tr>
						<td colspan="3">
							<p>The plugin has <b>beta</b> support for the following popular cache plugins: <a href="https://wp-rocket.me/" target="_blank">WP Rocket</a> / <a href="https://en-ca.wordpress.org/plugins/w3-total-cache/" target="_blank">W3 Total Cache</a> / <a href="https://en-ca.wordpress.org/plugins/wp-super-cache/" target="_blank">WP Super Cache</a> / <a href="https://en-ca.wordpress.org/plugins/comet-cache/" target="_blank">Comet Cache</a>. Other cache solutions you would need to ensure you exclude products from being cached or manually/programmatically clear them after catalog updates and not cache pages with customer data.</p>
							
							<p><b>Note:</b> Take care that if you have multiple versions of pages. For example, selling Canadian only drugs to Canadian customers, you will need to implement country-based caching so that each page is cached once per country / page variation.</p>

							<script>
								jQuery(function($) {
									$('.notes-toggle').on('click', (ev) => {
										let notesToggle = $(ev.target);
										if (notesToggle.text() == 'Show') {
											notesToggle.text('Hide');
											$('.cache-dev-notes').show();
										} else {
											notesToggle.text('Show');
											$('.cache-dev-notes').hide();
										}
									});
								});
							</script>

							<p><b>Additional Dev Notes</b> (<a class="notes-toggle" style="cursor: pointer;">Show</a>)<br />
							<ul class="cache-dev-notes ul-disc" style="display: none;">
								<li>The following widgets assist with cache:<br />
									<ul>
										<li><b>PharmacyWire Ajax Shopping Cart</b> widget dynamically pulls in the cart details after the page has finished loading via ajax.</li>
										<li><b>PharmacyWire Ajax Account Tools</b> widget dynamically pulls in the account menu (Create account/account, login/logout, & the cart link with item count) after the page has finished loading via ajax as well as after an order is submitted it will update to show that you are now logged in & cart empty. Elements can be shown/hidden within the widget settings and the menu displayed as vertical or horizontal.</li>
										<li>New shortcode for alternate method of displaying these widgets. See: <a href="https://www.pharmacywire.com/integrations/pharmacywire-shortcodes/" target="_blank">PharmacyWire Shortcodes</a></li>
									</ul>
								</li>
								<li>The <b>pw_catalog_update_complete</b> WordPress action hook is triggered when the product catalog has been updated.</li>
								<li><b>pwire_logged_in</b> cookie is set when a patient is logged in.</li>
								<li><b>DONOTCACHEPAGE</b>, <b>DONOTCACHEOBJECT</b>, <b>DONOTCACHEDB</b> as well as <b>no-cache</b> headers are set on the shopping cart page, account page, checkout pages, register, product, search and re-order pages for plugins that support them.</li>
								<li>Cloudflare page cache will not work due to how it is implemented.</li>
								<li><p><b>Note:</b> Page caching is not officially supported at this time. Due to the number of caching plugins available, if you do wish to try out caching, <b>please configure and test accordingly to ensure that data you do not wish to be cached is not being cached.</b> Depending on your approach, you may need to manually exclude the above outlined pages and some plugins allow you to exclude caching when the <b>pwire_logged_in</b> cookie is set.</p> When in doubt, leave caching disabled.</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<legend><input type="checkbox" name="pw_enable_product_cache" id="pw_enable_product_cache" value="1" <?php checked(1, get_option('pw_enable_product_cache', 0)) ?> />&nbsp;<label for="pw_enable_product_cache">Enable product cache support for 3rd party cache plugins.</label></legend>
						</td>
					</tr>
				</table>
			</fieldset>

			<p class="submit">
				<input type="submit" class="button-primary button" id="btnSave" name="btnSave" value="Save" />
				<input type="button" class="button-primary button" id="btnRefreshCache" name="btnRefreshCache" value="Refresh Catalog" onclick="refreshCache()" />
				<input type="hidden" id="isRefreshCache" name="isRefreshCache" value="0" />

				<span class="catalog-update-status" style="display: none;">
					<img class="status-spinner" src="/wp-admin/images/loading.gif" /> <span class="status"><?php echo $catalogUpdateProgress; ?></span>
				</span>

				<?php if (((get_option('pw_catalog_update_progress') == 'completed') || (get_option('pw_catalog_update_progress') == 'new')) && get_option('pw_catalog_last_update_time')) {
					$lastUpdateTime = date('Y-m-d H:i:s', get_option('pw_catalog_last_update_time'));
					$lastUpdateTime = get_date_from_gmt($lastUpdateTime, 'D M jS, Y; g:ia');
					echo '<br /><p>Catalog last updated: ' . $lastUpdateTime . '<br />';
				} ?>
				<?php
				$updateMsgDisplayState = 'none';
				if ($catalogUpdateProgress == 'failed') :
					$updateMsg = "Last catalog update failed, initiated on: " . date('D, M j Y @ H:i:s e', $catalogLastUpdated) . ".";
					$updateMsgDisplayState = 'inline-block;';
				?>
					<span class="catalog-update-warning" style="display: <?php echo $updateMsgDisplayState; ?>;">
						<?php echo $updateMsg; ?>
					</span>
				<?php endif; ?>

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

	.status-spinner {
		position: relative;
		top: 0.2rem;
	}
</style>

<?php

if (isset($_POST['isRefreshCache']) && $_POST['isRefreshCache'] == 1) {
	$status = buildcache();
	displayMessage($status);
}

if ($_SERVER['PHP_SELF'] === '/wp-admin/admin.php') {
	$configuration = new Utility_Configuration();
	$configuration->setupSchedule();
}

function displayMessage($status)
{
	$message = '';
	if ($status->status == 'success') {
		$message = 'Cache rebuild is successful';
	} else {
		$message = $status->messages[0]->content;
	}
	printf('<script type="text/javascript">alert("%s");</script>', $message);
}
?>