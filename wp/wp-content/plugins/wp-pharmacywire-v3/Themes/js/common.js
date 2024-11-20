window.pwireui = {};

// Disabling Dropzone autoDiscover - https://www.dropzonejs.com
if (typeof Dropzone !== 'undefined') {
	Dropzone.autoDiscover = false;
}

// Set shopping cart action used to apply cart elements to order and self-redirect
// eslint-disable-next-line no-unused-vars
function setAction(status) {
	const frmCheckout = document.getElementById('frmCheckout');
	const { action } = frmCheckout.elements;
	action.value = status;

	// If invalid coupon on cart
	if (jQuery('.coupons-line-item.invalid').length) {
		const couponCode = jQuery('.coupons-line-item.invalid').attr('data-coupon-code');
		// eslint-disable-next-line no-alert
		alert(`Coupon ${couponCode} on your order can not be used. Please remove the coupon before continuing.`);
		return false;
	}

	frmCheckout.submit();

	return true;
}


jQuery(($) => {
	$(document).ajaxComplete((event, request) => {
		// Get the raw header string
		const headers = request.getAllResponseHeaders();

		// Convert the header string into an array
		// of individual headers
		const arr = headers.trim().split(/[\r\n]+/);

		// Create a map of header names to values
		const headerMap = {};
		arr.forEach((line) => {
			const parts = line.split(': ');
			const header = parts.shift();
			const value = parts.join(': ');
			headerMap[header] = value;
		});
		const xPwireTokenStatus = request.getResponseHeader('x-pwire-token-status');
		if (xPwireTokenStatus === 'expired') {
			// show popup and reload current page?
			window.location.href = pw_json.login_url;
			// trigger page reload if popup
		}
	});

	// For tier pricing, setup proper product-id on the drug-row
	$('.pw-search-detail[data-tier-pricing="1"]').find('.drug-row.drug-package').each((index, elem) => {
		const drugPackage = $(elem);
		const selectedDrug = drugPackage.find('[name="drug-package-dropdown"] option:selected');
		drugPackage.attr('package-id', selectedDrug.attr('data-package-id'));
	});

	$('.pw-search-detail, .pw-reorder').on('click', '.add-drugpackage-to-cart', (ev) => {
		const $drugPackage = $(ev.target).closest('.drug-package');
		const $selectedTier = $drugPackage.find(':selected, :checked');
		ev.preventDefault();

		// If drug package exists and not using a custom add to cart event
		if (typeof $drugPackage !== 'undefined' && ($drugPackage.data('add-to-cart') !== 'custom-event')) {
			const packageId = $drugPackage.attr('package-id');
			const formId = $drugPackage.attr('form-id');
			const $selectedForm = $(`#frmDetail-${formId}`);
			const $objPackage = $(`#package-${formId}`);
			const $objQty = $(`#qty-${formId}`);
			$objPackage.val(packageId);

			// if tier pricing is on, and not a dropdown, make sure to setup tiered qty
			if ((parseInt($('.pw-search-detail').attr('data-tier-pricing'), 10) === 1) && !$drugPackage.hasClass('drug-dropdown')) {
				// find the related form and set the package-id and qty
				$selectedForm.find(`#package-${formId}`).val(packageId);
				$selectedForm.find(`#qty-${formId}`).val($drugPackage.data('tier-qty'));
				$selectedForm.find(`.drug-package-qty-${formId}`).val($drugPackage.data('tier-qty'));
			}

			// if there is a dropdown within the drug package for qty, use that
			// otherwise use the drug form qty for initial qty value
			const $qty = ($drugPackage.find('.drug-package-qty').length) ? $drugPackage.find('.drug-package-qty') : $selectedForm.find('.drug-package-qty');

			// original method, use value set when dropdown is switched etc.
			// originally done this way to support a separate qty dropdown and other implementations
			if ($qty && $qty.val() >= 1) {
				$objQty.val($qty.val());
			// new method, if selected input has tier qty set on it - use that
			} else if (!Number.isNaN(parseInt($selectedTier.attr('data-tier-qty'), 10))) {
				$objQty.val(parseInt($selectedTier.attr('data-tier-qty'), 10));
			} else {
				$objQty.val(1);
			}

			$selectedForm.trigger('submit');
		}
	});

	$('.pw-search-detail, .pw-reorder').on('change', '.drug-package-dropdown', (ev) => {
		if (parseInt($('.pw-search-detail').attr('data-tier-pricing'), 10) === 1) {
			const $selectedTier = $(ev.target).find('option:selected');
			const $drugPackage = $(ev.target).closest('.drug-package');
			// find that drug tier's package, package id and form id
			const drugPackageId = $selectedTier.data('package-id');
			const formId = $selectedTier.data('form-id');

			$drugPackage.attr('package-id', drugPackageId);

			// find the related form and set the package-id and qty
			const $selectedForm = $(`form#frmDetail-${formId}`);
			$selectedForm.find(`#package-${formId}`).val(drugPackageId);
			$selectedForm.find(`#qty-${formId}`).val($selectedTier.data('tier-qty'));
			$selectedForm.find(`.drug-package-qty-${formId}`).val($selectedTier.data('tier-qty'));
		} else {
			const packageId = $(ev.target).val();
			$(ev.target).closest('.drug-package').attr('package-id', packageId);
		}
	});

	function escapeRegExp(string) {
		return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
	}

	// Highlight matched text
	$.widget('app.autocomplete', $.ui.autocomplete, {
		// Which class get's applied to matched text in the menu items.
		_renderItem: function _renderItem(ul, item) {
			// Replace the matched text with a custom span. This
			// span uses the class found in the 'highlightClass' option.
			const searchTerm = escapeRegExp(this.term);
			const re = new RegExp(`(${searchTerm})`, 'gi');
			const cls = this.options.highlightClass;
			const template = `<b class="${cls}">$1</b>`;
			const label = item.label.replace(re, template);
			const $li = $('<li/>').appendTo(ul);
			// Create and return the custom menu item content.
			$('<span/>').html(label).appendTo($li);
			return $li;
		},
	});

	// search suggestions
	$('.pw-search-autocomplete').each((index, elem) => {
		const searchForm = $(elem);
		const searchField = searchForm.find('input[name=drugName]');
		const searchFieldContainer = searchField.closest('.pwire-search-name');

		searchField.autocomplete({
			delay: 300,
			highlightClass: 'highlight-match',
			minLength: 3,
			source: (request, response) => {
				$.ajax({
					type: 'POST',
					url: `${wp_pharmacywire.plugin_url}search.php`,
					data: searchForm.serialize(),
					dataType: 'json',
					async: true,
					beforeSend: () => {
						// loading animation element
						const loaderEl = $('<span></span>').addClass('pw-loader-element');
						const loaderContainer = $('<div></div>').addClass('pw-loader-container');
						loaderContainer.append(loaderEl);
						searchFieldContainer.append(loaderContainer);
					},
					success: (data) => {
						response($.map(data['drug-lookup-results'], v => v.name));
					},
					complete: () => {
						searchFieldContainer.find('.pw-loader-container').remove();
					},
				});
			},
			select: (e, ui) => {
				if (ui.item.value) {
					searchField.val(ui.item.value);
					searchForm.trigger('submit');
				}
			},
		});
	});

	// https://www.dropzonejs.com
	if ((typeof Dropzone !== 'undefined') && $('#prescriptionUpload').length) {
		// eslint-disable-next-line no-unused-vars
		const prescriptionUploadDropzone = new Dropzone('#prescriptionUpload', {
			url: pw_json.upload_url,
			drop: () => {
				$('.upload-rx-response').removeClass('success error').html('');
			},
			init: function init() {
				this.on('success', (file, response) => {
					let msg = '';
					// handle pharmacywire response or dropzone response
					if (typeof response === 'object') {
						if (Object.prototype.hasOwnProperty.call(response, 'message')) {
							msg = response.message;
						}
					} else {
						msg = response;
					}
					$('.upload-rx-response').addClass('success').html(msg).fadeIn();
					$('.pw-pharmacy-wrap').trigger('pwire:document:uploadcomplete');
					if ($('.pw-upload-document').length) {
						$('.pw-pharmacy-wrap').trigger('pwire:document:uploadcomplete_frontend');
					}
					if ($('.checkout_form').length) {
						$('.pw-pharmacy-wrap').trigger('pwire:document:uploadcomplete_checkout');
					}
				});
				this.on('error', (file, response) => {
					let msg = '';
					// handle pharmacywire response or dropzone response
					if (typeof response === 'object') {
						if (Object.prototype.hasOwnProperty.call(response, 'message')) {
							msg = response.message;
						}
					} else {
						msg = response;
					}
					$('.upload-rx-response').addClass('error').html(msg).fadeIn();
				});
			},
			dictDefaultMessage: '<i class="fas fa-file-prescription"></i><i class="far fa-upload"></i><br />Click or drag Rx files here to upload',
			maxFilesize: 10, // MB
			acceptedFiles: 'image/*,application/pdf',
		});
	}
});

jQuery.fn.pwireSpinner = function pwireSpinner(spinOpt = {}) {
	const $this = jQuery(this);
	const topY = spinOpt.top || `${($this.outerHeight() / 2)}px`;
	const leftX = spinOpt.left || `${($this.outerWidth() / 2)}px`;
	const cssClass = spinOpt.classname || 'pwire-spinner';

	const opts = {
		lines: spinOpt.lines || 8, // The number of lines to draw
		length: spinOpt.length || 3, // The length of each line
		width: spinOpt.width || 8, // The line thickness
		radius: spinOpt.radius || 10, // The radius of the inner circle
		scale: spinOpt.scale || 1, // Scales overall size of the spinner
		corners: 1, // Corner roundness (0..1)
		color: spinOpt.color || '#787878', // CSS color or array of colors
		fadeColor: 'transparent', // CSS color or array of colors
		speed: 1, // Rounds per second
		rotate: 0, // The rotation offset
		animation: 'spinner-line-fade-quick', // The CSS animation name for the lines
		direction: 1, // 1: clockwise, -1: counterclockwise
		zIndex: 2e9, // The z-index (defaults to 2000000000)
		className: cssClass, // The CSS class to assign to the spinner
		top: topY, // Top position relative to parent
		left: leftX, // Left position relative to parent
		shadow: '0 0 1px transparent', // Box-shadow for the lines
		position: spinOpt.position || 'relative', // Element positioning
	};

	const response = {
		init: () => {
			if (!$this.find('.pwire-spinner').length) {
				$this.addClass(`${cssClass}-container`);
				// eslint-disable-next-line no-undef
				new Spin.Spinner(opts).spin($this.get(0));
			}
		},
		stop: () => {
			$this.find('> .pwire-spinner').remove();
			$this.removeClass(`${cssClass}-container`);
		},
	};

	response.init();

	return response;
};
