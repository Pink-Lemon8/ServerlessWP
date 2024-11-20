jQuery(($) => {
	function updateCartForm(updatedFields) {
		let aData = 'action=update&';

		aData += $('#frmCheckout').find(':not(input[name=action])').serialize();

		return $.ajax({
			type: 'POST',
			data: aData,
			success: (data) => {
				const response = $(data);

				if (typeof updatedFields !== 'undefined') {
					$('.grand-total .value').add(updatedFields).stop(true, true).addClass('pw-transparent')
						.switchClass('pw-transparent', 'pw-hilight', 500)
						.delay(1000)
						.switchClass('pw-hilight', 'pw-transparent', 2500);
				}

				const currentLineItems = $('#frmCheckout .line-items');
				const responseLineItems = response.find('#frmCheckout .line-items');
				currentLineItems.replaceWith(responseLineItems);

				const currentCouponLines = $('#frmCheckout .coupons-line-item');
				const responseCouponLines = response.find('#frmCheckout .coupons-line-item');
				// remove all but the first coupon-line left as placeholder for replace
				currentCouponLines.slice(1).remove();
				currentCouponLines.replaceWith(responseCouponLines);

				$('#frmCheckout .cart-value').each((index, elem) => {
					const elemID = elem.id;
					const newElem = response.find(`#${elemID}`);
					if (newElem.length) {
						$(`#${elemID}`).html(response.find(`#${elemID}`).html());
					}
				});

				if ($('#pwire-shopping-cart-widget').length > 0) {
					$('#pwire-shopping-cart-widget').html(response.find('#pwire-shopping-cart-widget').html());
				}

				// add cart event trigger to listen on in themes
				// eg. $('.pw-pharmacy-wrap').on('pwire:cart:updateCartForm pwire:cart:removeLineItem',
				//  function () {
				$('.pw-pharmacy-wrap').trigger('pwire:cart:updateCartForm');
			},
		});
	}

	function removeLineItem(packageId, prompt = 1) {
		let answer = true;
		if (prompt) {
			// eslint-disable-next-line no-alert
			answer = window.confirm('Are you sure you want to delete this item?');
		}
		if ((answer && prompt) || (!prompt)) {
			$(`#qty\\[${packageId}\\]`).val(0);
			const cartData = updateCartForm($(`#row\\[${packageId}\\]`));
			cartData.done((data) => {
				const response = $(data);
				$('#frmCheckout').html(response.find('#frmCheckout').html());

				// if line item ID matches shipping addon ID, uncheck the addon
				if ($(`.shipping-addon[data-product-id='${packageId}']`).length) {
					$(`.shipping-addon[data-product-id='${packageId}']`).prop('checked', false);
					updateCartForm($('.shipping-fee .value'));
				}

				// add cart event trigger to listen on in themes
				// eg. $('.pw-pharmacy-wrap').
				//  on('pwire:cart:updateCartForm pwire:cart:removeLineItem', function () {
				$('.pw-pharmacy-wrap').trigger('pwire:cart:removeLineItem');
			});
			return true;
		}
		return false;
	}

	$('.pw-pharmacy-wrap').on('click', '.cart-remove-quantity', (ev) => {
		const packageId = $(ev.target).data('drug-package-id');
		const packageRow = $(`#row\\[${packageId}\\]`);
		const qtyInput = $(`#qty\\[${packageId}\\]`);
		const curQty = parseInt(qtyInput.val(), 10);
		const amountToRemove = parseInt(packageRow.data('multiple-item-factor'), 10) || 1;

		ev.preventDefault();

		$(`#qty\\[${packageId}\\]`).val(parseInt(curQty, 10) - amountToRemove);
		if (curQty <= 1) {
			if (!removeLineItem(packageId)) {
				qtyInput.val(qtyInput[0].defaultValue);
			}
		} else {
			updateCartForm($(`#subtotal\\[${packageId}\\], #orderqty\\[${packageId}\\]`).parent());
		}
	});

	// supports old and new template formats
	$('.pw-pharmacy-wrap').on('click', '#frmCheckout .remove, .remove-btn, .remove-button', (ev) => {
		const packageId = ev.target.id.match(/rem\[(.*?)\]/) || $(ev.target).attr('id').match(/remove\[(.*?)\]/);
		ev.preventDefault();
		removeLineItem(packageId[1]);
	});

	$('.pw-pharmacy-wrap').on('click', '.cart-add-quantity', (ev) => {
		const packageId = $(ev.target).data('drug-package-id');
		const packageRow = $(`#row\\[${packageId}\\]`);
		const qtyInput = $(`#qty\\[${packageId}\\]`);
		const curQty = parseInt(qtyInput.val(), 10);
		const amountToAdd = parseInt(packageRow.attr('data-multiple-item-factor'), 10) || 1;
		const newQty = parseInt(curQty, 10) + amountToAdd;
		let orderLimit = 99;

		ev.preventDefault();

		if ($(`#row\\[${packageId}\\]`).attr('data-order-limit')) {
			orderLimit = parseInt($(`#row\\[${packageId}\\]`).attr('data-order-limit'), 10);
		}

		if (newQty <= orderLimit) {
			$(`#qty\\[${packageId}\\]`).val(newQty);
			updateCartForm($(`#subtotal\\[${packageId}\\], #orderqty\\[${packageId}\\]`).parent());
		} else if (newQty >= orderLimit) {
			// eslint-disable-next-line no-alert
			alert(`You may only order up to ${orderLimit} of this product.`);
			$(`#qty\\[${packageId}\\]`).val(orderLimit);
			updateCartForm($(`#subtotal\\[${packageId}\\], #orderqty\\[${packageId}\\]`).parent());
		}
	});

	let prevQtySelIndex;
	$('.pw-pharmacy-wrap').on('focus', 'select.cart-value', (ev) => {
		prevQtySelIndex = ev.target.selectedIndex;
	}).on('change', 'select.cart-value', (ev) => {
		const qtySelect = ev.target;
		const packageId = $(qtySelect).attr('id').match(/qty\[(.*?)\]/)[1];
		if ($(qtySelect).val() <= 0) {
			if (!removeLineItem(packageId)) {
				qtySelect.selectedIndex = prevQtySelIndex;
			}
		} else {
			const updatedFields = $(`#row\\[${packageId}\\] .subtotal, #row\\[${packageId}\\] .subtotal span`);
			updateCartForm(updatedFields);
		}
	});

	$('.pw-pharmacy-wrap').on('change', 'form#frmCheckout input.qty', (ev) => {
		const qtyInput = ev.target;
		const packageId = $(qtyInput).attr('id').match(/qty\[(.*?)\]/)[1];
		const packageRow = $(`#row\\[${packageId}\\]`);
		const multipleItemFactor = packageRow.attr('data-multiple-item-factor');
		const maxOrderLimit = packageRow.attr('data-order-limit');
		const orderLimit = maxOrderLimit || 99;
		let curQty = parseInt($(qtyInput).val(), 10);

		if (curQty <= 0) {
			removeLineItem(packageId);
		} else if (curQty <= orderLimit) {
			if (multipleItemFactor && (curQty % multipleItemFactor !== 0)) {
				// eslint-disable-next-line no-alert
				alert(`This product must be ordered in multiples of ${multipleItemFactor}`);
				curQty = Math.ceil(curQty / multipleItemFactor) * multipleItemFactor;
			}
			$(qtyInput).val(curQty);
			updateCartForm($(`#subtotal\\[${packageId}\\], #orderqty\\[${packageId}\\]`).parent());
		} else {
			// eslint-disable-next-line no-alert
			alert(`You may only order up to ${orderLimit} of this product.`);
			$(qtyInput).val(orderLimit);
			const updatedFields = $(`#row\\[${packageId}\\] .subtotal, #row\\[${packageId}\\] .subtotal span`);
			updateCartForm(updatedFields);
		}
	});

	$('.pw-pharmacy-wrap').on('keypress', 'form#frmCheckout input.qty', (ev) => {
		const packageId = $(ev.target).attr('id').match(/qty\[(.*?)\]/);
		if (ev.which === 13) {
			ev.preventDefault();
			const updatedFields = $(`#row\\[${packageId}\\] .subtotal, #row\\[${packageId}\\] .subtotal span`);
			updateCartForm(updatedFields);
		}
	});

	$('.pw-pharmacy-wrap').on('change', 'form#frmCheckout input#pw_express_shipping', () => {
		updateCartForm($('.shipping-fee .value'));
	});

	$('.pw-pharmacy-wrap').on('change', 'form#frmCheckout [name="pw_shipping_options"], form#frmCheckout .shipping-addon', () => {
		updateCartForm($('.shipping-fee .value'));
	});

	// COUPONS
	function removeCouponAction(couponCode) {
		if (couponCode) {
			const couponLineItem = $(`.coupons-line-item[data-coupon-code="${couponCode}"]`);
			const couponOuterContainer = $('.coupon-outer-container');
			const couponResponseContainer = couponOuterContainer.find('.coupon-response-container');
			const couponResponse = couponResponseContainer.find('.coupon-response');

			let couponNonce = '';
			if (couponOuterContainer.data('coupon-nonce')) {
				couponNonce = couponOuterContainer.data('coupon-nonce');
			} else {
				couponNonce = couponLineItem.data('coupon-nonce');
			}

			const getData = `coupon-code=${couponCode}&coupon_nonce=${couponNonce}&remove-coupon=1&pw_nonce=${pw_json.nonce}`;

			$.getJSON(
				`${wp_pharmacywire.plugin_url}request.php`,
				getData,
				(data) => {
					if (data.status === 'success') {
						// hide rather than remove line item, left as placeholder for replaceWith cart updates
						couponLineItem.hide();
						couponResponse.text(`Coupon ${couponCode} removed from order.`);
						couponResponse.removeClass('success fail');
						updateCartForm();
					} else {
						couponResponse.text('Remove coupon failed.');
						couponResponse.addClass('fail').removeClass('success');
					}
				},
			);
		}
	}

	function submitCouponAction() {
		let getData = 'coupon-code=';
		const couponOuterContainer = $('.coupon-outer-container');
		const couponContainer = couponOuterContainer.find('.coupon-container');
		const couponResponse = couponOuterContainer.find('.coupon-response');
		const couponLineItem = $('#frmCheckout').find('.coupons-line-item');
		const couponInput = couponContainer.find('.coupon-code');

		let couponCode = couponInput.val();
		getData += couponCode;

		// coupons currently on cart
		getData += '&active-coupons=';
		const activeCoupons = [];
		couponLineItem.each((index, elem) => {
			activeCoupons.push($(elem).data('coupon-code'));
		});
		getData += activeCoupons.join();

		const couponNonce = couponOuterContainer.data('coupon-nonce');
		getData += `&coupon_nonce=${couponNonce}&pw_nonce=${pw_json.nonce}`;

		if (couponCode === '') {
			couponResponse.html('Please enter a coupon code to be submitted.').addClass('fail');
			return false;
		}
		couponInput.prop('disabled', true);
		couponInput.parent().pwireSpinner();

		// clear any existing response messages
		couponResponse.text('');

		// Submit apply coupon request
		$.getJSON(
			`${wp_pharmacywire.plugin_url}request.php`,
			getData,
			(data) => {
				if (data.status === 'success') {
					// update couponCode to match case of coupon returned
					couponCode = data['coupon-code'];

					let couponResponseText = `Coupon ${couponCode} has been applied to your order.`;
					if (data.coupons[couponCode].description.length) {
						couponResponseText = `Coupon ${couponCode} ("<span class='coupon-description'>${data.coupons[couponCode].description}</span>") has been applied to your order.`;
					}
					couponResponse.html(couponResponseText);
					couponResponse.addClass('success').removeClass('fail');

					couponInput.val('');

					updateCartForm();
				} else {
					couponResponse.text(`The coupon '${couponCode}' can not be used. Please enter the coupon exactly; otherwise contact Customer Services for assistance.`);
					couponResponse.addClass('fail').removeClass('success');
				}

				couponInput.prop('disabled', false);
				couponInput.parent().pwireSpinner().stop();
			},
		);

		return false;
	}

	function removeTagAction(tagCode) {
		if (tagCode) {
			const tagOuterContainer = $('.tag-outer-container');
			const tagContainer = tagOuterContainer.find(`.tag-container[data-tag-code="${tagCode}"]`);
			const tagCodeInput = tagContainer.find('.tag-code');
			const tagButton = tagContainer.find('.tag-button');
			const tagLabel = tagContainer.data('tag-label');
			const tagValue = tagCodeInput.val();
			let tagNonce = '';
			if (tagOuterContainer.data('tag-nonce')) {
				tagNonce = tagOuterContainer.data('tag-nonce');
			}

			const tagResponseContainer = tagContainer.find('.tag-response-container');
			const tagResponse = tagResponseContainer.find('.tag-response');

			const getData = {
				'tag-code': tagCode,
				'remove-tag': 1,
				tag_nonce: tagNonce,
				pw_json: pw_json.nonce,
			};

			$.getJSON(
				`${wp_pharmacywire.plugin_url}request.php`,
				getData,
				(data) => {
					if (data.status === 'success') {
						tagResponse.text(`${tagLabel} ${tagValue} removed from order.`);
						tagResponse.removeClass('success fail');
						tagButton.removeClass('remove-tag').addClass('apply-tag');
						tagButton.attr('value', 'apply');
						tagCodeInput.prop('disabled', false);
						tagCodeInput.val('');
					} else {
						tagResponse.text(`Remove ${tagLabel} ${tagCode} failed.`);
						tagResponse.addClass('fail').removeClass('success');
					}
				},
			);
		}
	}

	function submitTagAction(ev) {
		const tagSubmitButton = $(ev.target);
		const tagContainer = tagSubmitButton.closest('.tag-container');
		const tagOuterContainer = tagContainer.closest('.tag-outer-container');
		const tagResponse = tagContainer.find('.tag-response');
		const tagCodeInput = tagContainer.find('.tag-code');
		const tagValue = tagCodeInput.val();
		const tagLabel = tagContainer.data('tag-label');
		const tagNonce = tagOuterContainer.data('tag-nonce');
		let tagCode = tagContainer.data('tag-code');

		if (tagValue === '') {
			tagResponse.html(`Please enter the ${tagLabel} to be submitted.`).addClass('fail');
			return false;
		}

		tagCodeInput.prop('disabled', true);
		tagCodeInput.parent().pwireSpinner();

		// clear any existing response messages
		tagResponse.text('');

		const getData = {
			'tag-code': tagCode,
			'tag-value': tagValue,
			tag_nonce: tagNonce,
			pw_nonce: pw_json.nonce,
		};

		// Submit apply tag request
		$.getJSON(
			`${wp_pharmacywire.plugin_url}request.php`,
			getData,
			(data) => {
				if (data.status === 'success') {
					// update tagCode to match case of tag returned
					tagCode = data['tag-code'];

					let tagResponseText = `data.tags[tagCode]['label'] ${data.tags[tagCode].value} has been applied to your order.`;
					if (data.tags[tagCode].label.length) {
						tagResponseText = `<span class="tag-description">${data.tags[tagCode].label}</span>: <span class="tag-value">${data.tags[tagCode].value}</span> has been applied to your order.`;
					}

					const tagButton = tagContainer.find('.tag-button');
					tagButton.removeClass('apply-tag').addClass('remove-tag');
					tagButton.attr('value', 'remove');

					tagResponse.html(tagResponseText);
					tagResponse.addClass('success').removeClass('fail');
				} else {
					tagResponse.text(`The ${tagLabel} ${tagValue} can not be used. Please enter the tag exactly; otherwise contact Customer Services for assistance.`);
					tagResponse.addClass('fail').removeClass('success');

					tagCodeInput.prop('disabled', false);
				}
				tagCodeInput.parent().pwireSpinner().stop();
			},
		);

		return false;
	}

	$('.pw-pharmacy-wrap').on('click', '.coupon-button', () => {
		submitCouponAction();
		return false;
	});

	$('.pw-pharmacy-wrap').on('keypress', 'input.coupon-code', (ev) => {
		if (ev.which === 13) {
			submitCouponAction();
			return false;
		}
		return true;
	});

	$('.pw-pharmacy-wrap').on('click', '.coupons-line-item .remove-coupon', (ev) => {
		const couponCode = $(ev.target).data('coupon-code');
		removeCouponAction(couponCode);
		return false;
	});

	// TAGS

	$('.pw-pharmacy-wrap').on('click', '.apply-tag', (ev) => {
		submitTagAction(ev);
		return false;
	});

	$('.pw-pharmacy-wrap').on('keypress', 'input.tag-code', (ev) => {
		if (ev.which === 13) {
			submitTagAction(ev);
			return false;
		}
		return true;
	});

	$('.pw-pharmacy-wrap').on('click', '.remove-tag', (ev) => {
		const tagContainer = $(ev.target).closest('.tag-container');
		const tagCode = tagContainer.data('tag-code');
		removeTagAction(tagCode);
		return false;
	});
});
