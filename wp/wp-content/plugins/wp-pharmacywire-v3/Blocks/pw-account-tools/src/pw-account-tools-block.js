/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
 import { __ } from '@wordpress/i18n';

 /**
	* React hook that is used to mark the block wrapper element.
	* It provides all the necessary props like the class name.
	*
	* @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
	*/
 import { useBlockProps } from '@wordpress/block-editor';

 (($) => {

	const accountWidget = $('.pwire-account-tools-widget');
	const accountWidgetContent = accountWidget.find('.account-tools-widget-content');
	accountWidgetContent.hide();
	
	function updateAccountToolsWidget() {
		let r = 'get-account-tools-info';
		const cartRequest = $.ajax({
			type: 'POST',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (response) => {
				const pwireResponse = JSON.parse(response);

				if (pwireResponse.success === 1) {
					if (pwireResponse.cart.cart_item_count > 0) {
						accountWidget.find('.cart-quantity').addClass('cart-has-items').text(pwireResponse.cart.cart_item_count);
					} else {
						accountWidget.find('.cart-quantity').removeClass('cart-has-items').text('');
					}
				}

				if (pwireResponse.logged_in === 1) {
					accountWidget.find('.login-link').html($("<a>").attr('href', pw_json.logout_url).text('Log Out'));
					accountWidget.find('.account-link').html($("<a>").attr('href', pw_json.account_url).text('Account'));
				} else {
					accountWidget.find('.login-link').html($("<a>").attr('href', pw_json.login_url).text('Login'));
					accountWidget.find('.account-link').html($("<a>").attr('href', pw_json.register_url).text('Create Account'));
				}
			}
		});

		cartRequest.done(() => {
			accountWidgetContent.show();
		});
	}

	updateAccountToolsWidget();

	// update cart widget on cart change
	$('.pw-pharmacy-wrap').on('pwire:cart:updateCartForm pwire:cart:removeLineItem pwire:cart:orderSubmitted', () => {
		updateAccountToolsWidget();
	});
})(jQuery);