jQuery.noConflict();

(($) => {
	function checkoutSessionExists() {
		if (window.pwire.pwire_components) {
			if (window.pwire.pwire_components.pw_autosave) {
				if (window.pwire.pwire_components.pw_autosave.checkout) {
					return true;
				}
			}
		}
		return false;
	}

	function isPatientLoggedIn() {
		if (window.pwire && window.pwire.patient && window.pwire.patient.id) {
			return true;
		}
		return false;
	}

	// https://codeburst.io/throttling-and-debouncing-in-javascript-646d076d0a44
	function debounced(delay, fn) {
		let timerId;
		return (...args) => {
			if (timerId) {
				clearTimeout(timerId);
			}
			timerId = setTimeout(() => {
				fn(...args);
				timerId = null;
			}, delay);
		};
	}

	function disableElement(target) {
		let elem = target;
		if ((typeof target === 'string') || !(target instanceof jQuery)) {
			elem = $(target);
		}
		if (!elem.is(':input')) {
			elem = elem.find(':input');
		}
		elem.attr('disabled', true);
		$.each(elem, (key, targetInput) => {
			if ($(targetInput).is(':visible')) {
				const lbl = `label[for="${targetInput.id}"]:not(.form-error)`;
				$(targetInput).add(lbl).fadeTo(0, 0.4);
			}
		});
	}

	function hideElement(target) {
		let elem = target;
		if ((typeof target === 'string') || !(target instanceof jQuery)) {
			elem = $(target);
		}
		if (!elem.is(':input')) {
			elem = elem.find(':input');
		}
		elem.hide();
		$.each(elem, (key, targetInput) => {
			const lbl = `label[for="${targetInput.id}"]:not(.form-error)`;
			$(targetInput).add(lbl).hide();
		});
	}

	function enableElement(target) {
		let elem = target;
		if ((typeof target === 'string') || !(target instanceof jQuery)) {
			elem = $(target);
		}
		if (!elem.is(':input')) {
			elem = elem.find(':input');
		}
		elem.attr('disabled', false);
		$.each(elem, (key, targetInput) => {
			const lbl = `label[for="${targetInput.id}"]:not(.form-error)`;
			$(targetInput).add(lbl).fadeTo(0, 1);
		});
	}

	// Not used currently
	//
	// function showElement(target) {
	//   let elem = target;
	//   if ((typeof target === 'string') || !(target instanceof jQuery)) {
	//     elem = $(target);
	//   }
	//   if (!elem.is(':input')) {
	//     elem = elem.find(':input');
	//   }
	//   elem.show();
	//   const lbl = $(`label[for="${elem.attr('id')}"]`);
	//   $(elem).add(lbl).show();
	// }

	function submitDoctorInformation(orderID, doctorObj) {
		const r = 'set-order-comment';
		let d = null;
		let comment = '';

		if (orderID && doctorObj) {
			comment = `Rx Contact Doctor -- Name: ${doctorObj.name}, Phone: ${doctorObj.phoneAreaCode}-${doctorObj.phone}`;
			if (doctorObj.fax) {
				comment += `, Fax: ${doctorObj.faxAreaCode}-${doctorObj.fax}`;
			}

			d = [{ name: 'orderComment', value: comment }, { name: 'orderID', value: orderID }];

			$.ajax({
				type: 'POST',
				data: d,
				url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
				success: (response) => {
					const pwireResponse = JSON.parse(response);

					let messages = '';
					$('.doctor-contact-response').html('');
					$('.doctor_contact_form [data-form-error-for]').hide();

					if (pwireResponse.status !== 'success') {
						// eslint-disable-next-line no-unused-vars
						$.each(pwireResponse.messages, (index, message) => {
							const { content } = message;
							messages += `${content}<br />`;
						});

						if (messages !== '') {
							$('.doctor-contact-response').html(`${messages}`).addClass('alert callout').show();
						}
					} else {
						$('.doctor_contact_form').hide();
						$('.doctor-contact-response').html("Thank you for submitting your doctor's contact information. We will contact them for your prescription.").addClass('success callout').show();
					}
				},
			});
		}
	}

	// form input fields (by name) to exclude from autosave
	function excludedFieldsFromAutoSave() {
		const excludedFields = [
			'billing_creditCard_number',
			'billing_creditCard_cvv',
		];
		return excludedFields;
	}

	function setPWAutoSave(datasetName, formData) {
		const r = 'set-pw-autosave';
		const d = [{ name: 'dataset', value: JSON.stringify(formData) }, { name: 'datasetName', value: datasetName }];
		return $.ajax({
			type: 'POST',
			data: d,
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
		});
	}

	function saveCheckoutStateToSession() {
		const myForm = $('form.pw_autosave');
		const excludedFields = excludedFieldsFromAutoSave();
		let serializedForm = myForm.serializeArray();
		if (excludedFields.length) {
			serializedForm = serializedForm.filter(item => !excludedFields.includes(item.name));
		}
		return setPWAutoSave('checkout', serializedForm);
	}

	function updateCountrySelect(targetInput) {
		const countries = window.pwire.pwire_components.countries.details;
		const countryCodes = Object.keys(countries);
		const defaultCountryCode = window.pwire.pwire_components.countries.default || 'USA';

		$(targetInput).empty();

		$(countryCodes).each((key, val) => {
			const countryCode = val;
			const countryName = countries[countryCode].country_name;

			let isSelected = null;
			if (countryCode === defaultCountryCode) {
				isSelected = 'selected';
			}

			const countryOption = $('<option/>', {
				value: countryCode,
				text: countryName,
				selected: isSelected,
			});

			$(targetInput).append(countryOption);
		});

		if (defaultCountryCode === 'USA') {
			$('.regionCode').prop('placeholder', 'Zip Code');
		} else {
			$('.regionCode').prop('placeholder', 'Postal Code');
		}
	}

	function formatAddress(address) {
		let addressFormatted = '';

		addressFormatted += `${address.address1}<br />`;
		if (address.address2.length) {
			addressFormatted += `${address.address2}<br />`;
		}
		if (address.address3.length) {
			addressFormatted += `${address.address3}<br />`;
		}

		addressFormatted += `${address.city}, ${address.province} ${address.postalcode}<br />`;
		addressFormatted += `${address.country}<br />`;
		addressFormatted += `Ph. ${address.areacode}-${address.phone}<br />`;

		return addressFormatted;
	}

	function updateRegionSelect(targetInputSelector, countryCodeParam) {
		const countries = window.pwire.pwire_components.countries.details;
		let defaultCountryCode = window.pwire.pwire_components.countries.default;
		defaultCountryCode = defaultCountryCode || 'USA';
		let countryCode = countryCodeParam || defaultCountryCode;
		const targetInputSelectorArray = targetInputSelector.split(',');
		$.each(targetInputSelectorArray, (i, targetRegionInput) => {
			let regionCodes = [];

			if (countries[countryCode]) {
				regionCodes = Object.keys(countries[countryCode].regions);
			} else {
				// get first country code
				[countryCode] = Object.keys(countries);
				regionCodes = Object.keys(countries[countryCode].regions);
			}

			const targetID = $(targetRegionInput).prop('id');
			const targetName = $(targetRegionInput).prop('name');
			const targetClasses = $(targetRegionInput).prop('class');
			const targetDisabled = $(targetRegionInput).prop('disabled');

			if (regionCodes.length) {
				let currentRegionIndex = 0;
				if ($(targetRegionInput).find('option').length > 0) {
					currentRegionIndex = $(targetRegionInput).get(0).selectedIndex;
				}

				$(targetRegionInput).empty();
				let targetState = '';
				if (targetDisabled) {
					targetState = 'disabled="disabled"';
				}
				$(targetRegionInput).replaceWith(`<select id="${targetID}" name="${targetName}" class="${targetClasses}" ${targetState} required></select>`);

				$(regionCodes).each((key, val) => {
					if (val) {
						const regionCode = val;
						const regionName = countries[countryCode].regions[regionCode].region_name;
						const regionOption = $('<option/>', {
							value: regionCode,
							text: regionName,
						});

						$(targetRegionInput).append(regionOption);
					}
				});

				// sort regions by their text value rather than default region code
				// which doesn't work for Philipinnes, etc.
				$(targetRegionInput).html($(targetRegionInput).find('option').sort((x, y) => ($(x).text() < $(y).text() ? -1 : 1)));
				$(targetRegionInput).prepend('<option value="">State/Province</option>');
				$(targetRegionInput).prop('selectedIndex', currentRegionIndex);
			} else {
				// no regions
				$(targetRegionInput).replaceWith(`<input type="text" id="${targetID}" name="${targetName}" class="${targetClasses}" value="" maxlength="250" required>`);
			}
		});

		if (typeof Foundation === 'object') {
			Foundation.reInit('abide');
		}
	}

	function getShippingRefID() {
		let shippingRefID = 0;
		if ($('input[name=shippingAddressRef]').length) {
			shippingRefID = $('input[name=shippingAddressRef]').val();
		} else if (checkoutSessionExists()) {
			shippingRefID = pwire.pwire_components.pw_autosave.checkout.shippingAddressRef || 0;
		}
		return shippingRefID;
	}

	function setShippingRefID(shippingAddressRef) {
		if (parseInt(shippingAddressRef, 10) > 0) {
			if ($('input[name=shippingAddressRef]').length) {
				$('input[name=shippingAddressRef]').val(shippingAddressRef);
				saveCheckoutStateToSession();
			} else {
				$('<input type="hidden" />').attr({
					id: 'shippingAddressRef',
					name: 'shippingAddressRef',
					value: shippingAddressRef,
				}).appendTo('.checkout_form');
				saveCheckoutStateToSession();
			}
		} else {
			$('.checkout_form').find('input[name=shippingAddressRef]').remove();
		}
	}

	function enableEditAddressFields() {
		$('.address-form').find('input, select').prop('disabled', false);
	}

	function disableEditAddressFields() {
		$('.address-form').find('input, select').prop('disabled', true);
	}

	function shippingAddressFormState(display) {
		if (display === 1) {
			$('.selected-address-container').show();
			$('.address-form').show();
			enableEditAddressFields();
			$('.change-address').hide();
		} else if (display === 0) {
			$('.selected-address-container').show();
			$('.address-form').hide();
			disableEditAddressFields();
			$('.change-address').show();
		}
		return $('.selected-address-container').is(':visible');
	}

	function addCustomPaymentMethods(customPaymentMethods) {
		// add custom payment methods to form
		const formBillingMethodRadioGroup = $('.billing_method_radiogroup');

		$.each(customPaymentMethods, (index, value) => {
			if ($(`.billing_method_select_${value.code}`).length === 0) {
				$('<div />', {
					class: `billing_method_select billing_method_select_${value.code}`,
				}).insertAfter(formBillingMethodRadioGroup.find('.billing_method_select').last());

				$('<input />', {
					type: 'radio',
					id: `billing_${value.code}`,
					name: 'billing_type',
					value: 'custom',
					'data-custom-method': value.code,
					required: true,
				}).appendTo(`.billing_method_select_${value.code}`);

				$('<label />', {
					for: `billing_${value.code}`,
					text: value.label,
				}).appendTo(`.billing_method_select_${value.code}`);

				if ($('#payment-method-selected').find(`.billing_method.billing_method_${value.code}`).length === 0) {
					$('<div />', {
						class: `billing_method billing_method_${value.code} grid-x grid-margin-x`,
						html: $('<div />').addClass('cell billing_method_content'),
						css: { display: 'none' },
					}).insertAfter($('#payment-method-selected').find('.billing_method').last());
					jQuery('.pw-pharmacy-wrap').trigger(`pwire:cart:billingMethod_${value.code}`);
				}
				if (typeof Foundation === 'object') {
					Foundation.reInit('abide');
				}
			}
		});

		if ($('input[name=billing_institution][type=hidden]').length === 0) {
			const customPayInstitution = document.createElement('input');
			customPayInstitution.type = 'hidden';
			customPayInstitution.name = 'billing_institution';
			customPayInstitution.value = '';
			$('.checkout_form').append(customPayInstitution);
		}
	}

	function billingMethodState(billingMethods = null) {
		const paymentMethods = billingMethods || window.pwire.pwire_components.payment.methods;
		const formBillingMethodRadioGroup = $('.billing_method_radiogroup');
		const formBillingMethodOptions = formBillingMethodRadioGroup.find(':input[type="radio"]');
		const formBillingMethodContainers = $('.billing .billing_method');

		disableElement(formBillingMethodRadioGroup);
		hideElement(formBillingMethodRadioGroup);
		disableElement(formBillingMethodContainers);
		formBillingMethodContainers.hide();

		// show valid payment options in radio group
		$.each(formBillingMethodOptions, (index, value) => {
			const targetField = value.id.replace('billing_', '');
			if (targetField.toLowerCase() in paymentMethods) {
				enableElement(`#${value.id}`);
			} else if (Object.prototype.hasOwnProperty.call(paymentMethods, 'custom') && (targetField.toLowerCase() in paymentMethods.custom)) {
				enableElement(`#${value.id}`);
			}
		});

		// setup which payment option should be selected
		let selectedMethod = null;
		let customMethod = null;
		const billingMethod = $('[name=billing_type]:checked').val();
		if (billingMethod) {
			selectedMethod = billingMethod;
			if (selectedMethod === 'custom') {
				customMethod = $('[name=billing_type]:checked').attr('data-custom-method');
			}
		} else if (checkoutSessionExists()) {
			selectedMethod = window.pwire.pwire_components.pw_autosave.checkout.billing_type;
			if (selectedMethod === 'custom') {
				customMethod = window.pwire.pwire_components.pw_autosave.checkout.billing_institution;
			}
		}

		// if selectedMethod is null or not in the allowed payment methods
		// don't do anything, otherwise select and show/enable the selectedMethod
		if (selectedMethod != null) {
			if (Object.prototype.hasOwnProperty.call(paymentMethods, selectedMethod.toLowerCase())) {
				let targetMethod = selectedMethod;
				if (selectedMethod === 'custom') {
					targetMethod = customMethod;
				}
				jQuery(`#billing_${targetMethod}`).prop('checked', true);
				// show selected payment method info/form
				$(`.billing_method_${targetMethod}`).show();
				enableElement(`.billing_method_${targetMethod}`);
			}
		}
	}

	function billingAddressFormState(useShippingAddress = 0) {
		const useShippingForBilling = useShippingAddress || $('[name=billing_useShippingAddress]').is(':checked');
		if (useShippingForBilling) {
			$('[name=billing_useShippingAddress]').prop('checked', true);
			disableElement('.billing-address-form');
			$('.billing-address-form').hide();
		} else {
			$('[name=billing_useShippingAddress]').prop('checked', false);
			enableElement('.billing-address-form');
			$('.billing-address-form').show();
		}
		billingMethodState();
	}

	function billingState() {
		const selectedBillingMethod = $('[name=billing_type]:checked');
		const billingMethod = selectedBillingMethod.val();
		$('.billing_method').hide();
		disableElement($('.billing_method'));

		if (billingMethod) {
			let billingMethodContainer = `.billing_method_${billingMethod}`;
			if (billingMethod === 'custom') {
				billingMethodContainer = `.billing_method_${selectedBillingMethod.attr('data-custom-method')}`;
				$('input[name=billing_institution]').val(selectedBillingMethod.attr('data-custom-method'));
			} else {
				$('input[name=billing_institution]').val('');
			}
			$(billingMethodContainer).show();
			enableElement(billingMethodContainer);

			if ((billingMethod === 'draft' || billingMethod === 'custom') && isPatientLoggedIn()) {
				// if paying by cheque and patient is logged in,
				// don't collect billing info again
				$('#billingAddress').hide();
				// use shipping address for billing / disable billing form
				billingAddressFormState(1);
			} else {
				$('#billingAddress').show();
				billingAddressFormState();
			}
		} else {
			$('#billingAddress').hide();
			billingAddressFormState();
		}
	}

	function setupRxSubmissionMethod(rxSubmissionMethod) {
		const rxSubmissionContainer = $('.rx-submission-container');
		rxSubmissionContainer.find('.rx-submission-method').hide();
		if (rxSubmissionMethod === 'upload') {
			const rxUploadContainer = $(rxSubmissionContainer).find('.rx-submission-upload');
			const rxUploadForm = rxUploadContainer.find('form#prescriptionUpload');
			rxUploadForm.attr('action', pw_json.upload_url);
			rxUploadContainer.show();
		} else if (rxSubmissionMethod === 'email') {
			const rxEmailContainer = $(rxSubmissionContainer).find('.rx-submission-email');
			const emailRx = window.pwire.pwire_components.email_rx;
			const emailMessage = `Please email a copy of your prescription to: <a href="mailto:${emailRx}">${emailRx}</a>.`;
			rxEmailContainer.find('.submission-instructions').html(emailMessage);
			rxEmailContainer.show();
		} else if (rxSubmissionMethod === 'fax') {
			const rxFaxContainer = $(rxSubmissionContainer).find('.rx-submission-fax');
			const faxNumber = window.pwire.pwire_components.fax;
			const faxMessage = `Please fax a copy of your prescription to: <a href="tel:${faxNumber}">${faxNumber}</a>.`;
			rxFaxContainer.find('.submission-instructions').html(faxMessage);
			rxFaxContainer.show();
		} else if (rxSubmissionMethod === 'doctor') {
			const rxDoctorContainer = $(rxSubmissionContainer).find('.rx-submission-doctor');
			rxDoctorContainer.show();
		}
		rxSubmissionContainer.show();
	}

	function updateShippingComponents(selectedAddressID = 0) {
		const index = parseInt(selectedAddressID, 10);
		const shippingAddresses = window.pwire.patient.addresses;
		// Default to shipping address if one exists
		let shippingAddress = '';
		let shippingAddressID;

		if (Array.isArray(shippingAddresses) && shippingAddresses.length) {
			if (index === 0) {
				shippingAddress = window.pwire.patient.addresses[index];
				shippingAddressID = shippingAddress.id;
			} else {
				const addressesArray = window.pwire.patient.addresses;
				// shippingAddress = addressesArray.find( function (obj) { return obj.id == index } );
				shippingAddress = addressesArray.find(obj => (parseInt(obj.id, 10) === index));
				shippingAddressID = shippingAddress.id;
			}

			shippingAddress = formatAddress(shippingAddress);

			setShippingRefID(shippingAddressID);
			shippingAddressFormState(0);
		} else {
			if (isPatientLoggedIn()) {
				// if logged in, but no shipping address, use account address
				const accountAddress = window.pwire.patient.address;
				shippingAddress = formatAddress(accountAddress);
				shippingAddressFormState(0);
			} else {
				shippingAddressFormState(1);
			}
			setShippingRefID(0);
		}
		$('.selected-address-container').html(shippingAddress);
	}

	function updateCreditCardNumberIcon(type = 'unknown') {
		const paymentMethods = pwire.pwire_components.payment.methods;
		if (Object.prototype.hasOwnProperty.call(paymentMethods, 'creditcard')) {
			const ccContainer = $('.billing_method_creditCard');
			const ccNumberInput = ccContainer.find('[name="billing_creditCard_number"]');
			const ccNumberContainer = ccNumberInput.parent('.credit-card-number');
			const allowedCreditCards = pwire.pwire_components.payment.methods.creditcard.types;
			const allowedCreditCardsList = Object.keys(allowedCreditCards);
			const allowedCreditCardsListLabels = allowedCreditCardsList.map(
				currentValue => allowedCreditCards[currentValue].label,
			);
			let allowedCreditCardsMessage;
			if (allowedCreditCardsListLabels.length >= 1) {
				allowedCreditCardsMessage = `${pwire.pwire_components.pharmacy_name} accepts ${allowedCreditCardsListLabels.join(', ')}`;
			}

			ccNumberContainer.find('.credit-card-icon').remove();
			ccNumberContainer.find('.credit-card-error').remove();

			if (Object.prototype.hasOwnProperty.call(allowedCreditCards, type)) {
				ccNumberInput.attr('data-credit-card-type', type);
				ccNumberContainer.append(`<i class="credit-card-icon ${type}"></i>`);
			} else {
				let enteredCreditCardLabel = '';
				ccNumberInput.attr('data-credit-card-type', '');
				if (type !== 'unknown') {
					if (Object.prototype.hasOwnProperty.call(allowedCreditCards, type)) {
						ccNumberContainer.append(`<i class="credit-card-icon ${type}"></i>`);
						enteredCreditCardLabel = `${allowedCreditCards[type].label} is not supported. `;
					}
				}
				$('.credit-card-number .credit-card-error').remove();
				ccNumberContainer.append(`<span class="credit-card-error form-error is-visible">${enteredCreditCardLabel}${allowedCreditCardsMessage}</span>`);
			}
		}
	}

	function updateCreditCardForm() {
		const ccContainer = $('.billing_method_creditCard');
		const paymentMethods = pwire.pwire_components.payment.methods;
		if (Object.prototype.hasOwnProperty.call(paymentMethods, 'creditcard')) {
			const allowedCreditCards = pwire.pwire_components.payment.methods.creditcard.types;
			const allowedCreditCardsList = Object.keys(allowedCreditCards);

			const supportedCreditCardIcons = ccContainer.find('.supported-credit-card-icons');
			supportedCreditCardIcons.empty();

			allowedCreditCardsList.forEach((value) => {
				supportedCreditCardIcons.append(`<i class="credit-card-icon ${value}"></i>`);
			});

			const creditCardExpiryYearSelect = $('[name="billing_creditCard_expiryYear"]');
			const creditCardExpiryYearSelectOptions = creditCardExpiryYearSelect.find('option').length;
			if (creditCardExpiryYearSelect.length && !creditCardExpiryYearSelectOptions) {
				const year = new Date().getFullYear();
				const yearOptions = [];
				for (let y = year; y <= (year + 10); y += 1) {
					yearOptions.push(`<option value="${y}">${y}</option>`);
				}
				creditCardExpiryYearSelect.html(yearOptions);
			}
		}
	}

	function updateFormValues(scope = '.pw_checkout') {
		const updateScope = $(scope);

		if (checkoutSessionExists()) {
			const pwireFormData = window.pwire.pwire_components.pw_autosave.checkout;

			updateRegionSelect('.shipping_region', pwireFormData.shipping_country);
			updateRegionSelect('.billing_region', pwireFormData.billing_country);

			$.each(pwireFormData, (index, value) => {
				const targetInput = updateScope.find(`[name="${index}"]`);

				if (value.length && targetInput.length) {
					const inputType = targetInput.prop('type');
					if (inputType === 'radio') {
						if ((index === 'billing_type') && (value === 'custom')) {
							const customBillingType = pwireFormData.billing_institution;
							targetInput.filter(`#billing_${customBillingType}`).prop('checked', true);
						} else {
							targetInput.val([value]);
						}
					} else if (inputType === 'select-one' || inputType === 'select-multiple') {
						targetInput.find(`option[value=${value}]`).attr('selected', 'selected');
						if (index.match(/_country$/)) {
							let targetRegionCode = '.shipping_regionCode';
							if (index === 'billing_country') {
								targetRegionCode = '.billing_regionCode';
							}
							const countryCode = targetInput.find('option:selected').val();
							if (countryCode === 'USA') {
								$(targetRegionCode).prop('placeholder', 'Zip Code');
							} else {
								$(targetRegionCode).prop('placeholder', 'Postal Code');
							}
						}
					} else if (index === 'shippingAddressRef') {
						setShippingRefID(value);
						shippingAddressFormState(1);
					} else if (index === 'billing_useShippingAddress') {
						const useShippingForBilling = (value === 'yes') ? 1 : 0;
						billingAddressFormState(useShippingForBilling);
					} else if (index === 'billing_creditCard_number') {
						targetInput.val(value);
						targetInput.data('cleave').setRawValue(value);
					} else {
						targetInput.val(value);
					}
				}
			});
		}
	}

	function getPharmacyComponents() {
		const r = 'get-pharmacy-components';

		return $.ajax({
			type: 'POST',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (response) => {
				const pwireResponse = JSON.parse(response);
				window.pwire.pwire_components = pwireResponse;
			},
		});
	}

	function getCart() {
		const r = 'get-cart';

		return $.ajax({
			type: 'POST',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (response) => {
				const pwireResponse = JSON.parse(response);
				window.pwire.cart = pwireResponse;
			},
		});
	}

	function getPatientInfo() {
		const r = 'get-patient';

		return $.ajax({
			type: 'POST',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (response) => {
				const pwireResponse = JSON.parse(response);
				window.pwire.patient = pwireResponse;
			},
		});
	}

	function populateAddressForm(addressObject, type = 'shipping') {
		updateRegionSelect('.shipping_region', addressObject.country);
		$(Object.keys(addressObject)).each((key, val) => {
			const newValue = addressObject[val];
			if (Object.prototype.hasOwnProperty.call(addressObject, val)) {
				if (($(`#${type}_${val}`).prop('type') === 'select-one') && ($(`#${type}_${val}`).find(`option[value='${newValue}']`).length === 0)) {
					// if it's a select and value doesn't exist, choose the first one
					$(`#${type}_${val}`).prop('selectedIndex', 0);
				} else {
					$(`#${type}_${val}`).val(newValue);
				}
			}
		});
		$(`#${type}_phoneAreaCode`).val(addressObject.areacode);
		$(`#${type}_regionCode`).val(addressObject.postalcode);

		let targetRegionCode = '.shipping_regionCode';
		if (type === 'billing') {
			targetRegionCode = '.billing_regionCode';
		}
		if ($(`#${type}_regionCode`).val() === 'USA') {
			$(targetRegionCode).prop('placeholder', 'Zip Code');
		} else {
			$(targetRegionCode).prop('placeholder', 'Postal Code');
		}
	}

	function editShippingAddress(addressSerialized) {
		const r = 'edit-shipping';

		let formData = new FormData();
		formData = addressSerialized;

		return $.ajax({
			type: 'POST',
			data: formData,
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
		});
	}

	function addShippingAddress(addressSerialized) {
		const r = 'set-shipping';

		let formData = new FormData();
		formData = addressSerialized;

		return $.ajax({
			type: 'POST',
			data: formData,
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
		});
	}

	function deleteShippingAddress(addressRefID) {
		const r = 'delete-shipping';
		const formData = { shipping_addressRefID: addressRefID };

		return $.ajax({
			type: 'POST',
			data: formData,
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
		});
	}

	function updateShippingAddressBook() {
		const shippingRefID = getShippingRefID();
		const alternativeAddresses = window.pwire.patient.addresses;

		$('.alternative-addresses').empty();
		// eslint-disable-next-line no-unused-vars
		$.each(alternativeAddresses, (index, address) => {
			const addressFormatted = formatAddress(address);
			let addressInfo = `<li class="address" data-address-id="${address.id}"><b class="address-description">${address.description}</b><br />`;
			addressInfo += addressFormatted;
			if (shippingRefID === address.id) {
				addressInfo += '<div class="button-group"><button class="edit button small">Edit</button></div>';
			} else {
				addressInfo += '<button class="ship-to button">Ship to this address</button><br />';
				addressInfo += '<div class="button-group"><button class="edit button small">Edit</button>';
				addressInfo += '<button class="delete button small">Delete</button></div></li>';
			}
			$('.alternative-addresses').append(addressInfo);
		});
	}

	function getPatientAddresses() {
		const r = 'get-patient-addresses';

		$('.selected-address-container').pwireSpinner();

		return $.ajax({
			type: 'POST',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (response) => {
				const pwireResponse = JSON.parse(response);
				const alternativeAddresses = pwireResponse;
				if (Object.prototype.hasOwnProperty.call(window, 'pwire')) {
					window.pwire.patient.addresses = alternativeAddresses;
				}
				const addressRefID = getShippingRefID() || 0;
				updateShippingComponents(addressRefID);
				updateShippingAddressBook(alternativeAddresses);
				$('.selected-address-container').pwireSpinner().stop();
			},
		});
	}

	function updateShippingAddress() {
		const addressID = $('#update-shipping-address').attr('data-address-id');
		let addressSerialized = `shipping_addressRefID=${addressID}&`;
		const shippingAddressForm = $('.update-shipping-address-form');
		addressSerialized += shippingAddressForm.find('input, select').serialize();
		disableElement(shippingAddressForm);
		disableElement('.selected-address-container');

		let patientAddressUpdate;
		let newAddressRefID;
		$('.update-shipping-address-response').hide();
		$('.update-shipping-address-response .message').html('');

		if (addressID) {
			patientAddressUpdate = editShippingAddress(addressSerialized);
		} else {
			patientAddressUpdate = addShippingAddress(addressSerialized);
		}
		let updatedAddressRefID;
		patientAddressUpdate.done((response) => {
			const pwireResponse = JSON.parse(response);
			if (pwireResponse.status === 'success') {
				newAddressRefID = pwireResponse.shippingaddress_id;
				updatedAddressRefID = newAddressRefID || addressID;
			}
		});

		patientAddressUpdate.done((response) => {
			const pwireResponse = JSON.parse(response);
			if (pwireResponse.status === 'success') {
				enableElement(shippingAddressForm);
				const patientAddressRequest = getPatientAddresses();
				patientAddressRequest.done(() => {
					updateShippingComponents(updatedAddressRefID);
					enableElement('.selected-address-container');
				});
				disableEditAddressFields();
				$('#update-shipping-address').foundation('close');
			} else {
				enableElement(shippingAddressForm);
				let messages = '';
				$.each(pwireResponse.messages, (index, message) => {
					const content = message.content.value || message.content.field || message.content;
					messages += `${content}<br />`;
				});
				$('.update-shipping-address-response').show();
				$('.update-shipping-address-response .message').html(`${messages}`);
			}
		});
	}

	function updateMailingAddress(addressContainerSelector) {
		const pwireComponents = window.pwire.pwire_components;
		const { address } = pwireComponents;
		const checkName = pwireComponents.business_name || address.name;
		let mailingAddress = `<strong class="pharmacy-name">${checkName}</strong><br />`;
		mailingAddress += `<span class="street">${address.street}</span><br />`;
		mailingAddress += `<span class="city">${address.city}</span>, <span class="region">${address.region}</span> <span class="region-code">${address.region_code}</span><br />`;
		mailingAddress += `<span class="country">${address.country}</span>`;

		$(addressContainerSelector).html(mailingAddress);
	}

	function setupGeneralCartMessages() {
		const cartMessagesContainer = $('.general-cart-messages');
		const pwireComponents = window.pwire.pwire_components;
		const { session } = pwireComponents;

		if (pwireComponents && session) {
			if (session.order_tags) {
				cartMessagesContainer.append('<ul class="order-tags"></ul>');
				const orderTags = cartMessagesContainer.find('.order-tags');
				$.each(session.order_tags, (index, tag) => {
					orderTags.append(`<li class="tag">${tag.label} ${tag.value} has been applied to your order.</li>`);
				});
			}
		}
		if (cartMessagesContainer.is(':empty')) {
			cartMessagesContainer.hide();
		} else {
			cartMessagesContainer.show();
		}
	}

	function initializeMedicalQuestionnaire() {
		const r = 'get-medical-questions';
		const medicalQuestionnaire = $('.medical-questionnaire');
		const myForm = $('.medical-questionnaire-form');
		const medicalQuestions = myForm.find('.medical-questions');
		const medicalQR = medicalQuestionnaire.find('.medical-questionnaire-response');
		medicalQR.hide();
		medicalQuestions.empty();
		myForm.show();

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (data) => {
				if (data.success === 1) {
					const medQuestions = data.medical_questions;
					if (data.show_medical_questionnaire && medQuestions.length) {
						medicalQuestionnaire.show();
					} else {
						medicalQuestionnaire.hide();
						return;
					}

					const medQuestionsSorted = medQuestions.sort((a, b) => {
						const x = a.label;
						const y = b.label;
						if (x < y) {
							return -1;
						}
						if (x > y) {
							return 1;
						}
						return 0;
					});

					$.each(medQuestionsSorted, (key, question) => {
						const answers = ['Yes', 'No'];
						const medicalQuestionListItem = $(`<li class="medical-question cell" data-question-id="${question.id}" />`);

						medicalQuestionListItem.append(`<div class="question">${question.question}</div>`);

						$.each(answers, (k) => {
							let radioSelected = '';

							if (question.response === answers[k]) {
								radioSelected = 'checked';
							}

							const medicalQuestionComponents = $(`<input type="radio" id="${question.id}_${answers[k]}" name="response[${question.id}]" value="${answers[k]}" ${radioSelected} required> <label for="${question.id}_${answers[k]}">${answers[k]}</label>`);
							medicalQuestionListItem.append(medicalQuestionComponents);
						});

						let commentAnswer = '';
						let commentState = 'disabled';
						if (question.response === 'Yes') {
							commentState = '';
							commentAnswer = question.comment;
						}
						medicalQuestionListItem.append(`<textarea name="comment[${question.id}]" data-question-id="${question.id}" placeholder="If 'Yes', please provide details." required ${commentState}>${commentAnswer}</textarea><span class="form-error">Select an answer. If 'Yes', please provide details.</span>`);
						$('.medical-questionnaire .medical-questions').append(medicalQuestionListItem);
					});
				}
			},
			complete: (jqXHR, textStatus) => {
				// update values from session after load
				const data = JSON.parse(jqXHR.responseText);

				if ((textStatus === 'success') && data.show_medical_questionnaire && data.medical_questions.length) {
					updateFormValues('.medical-questionnaire');
					if (typeof Foundation === 'object') {
						Foundation.reInit('abide');
					}

					$('.medical-questionnaire-form input[type="radio"]:checked').each((key, target) => {
						const targetInput = jQuery(target);
						const questionContainer = targetInput.closest('.medical-question');

						if (targetInput.val() === 'Yes') {
							questionContainer.find('textarea').prop('disabled', false);
						}
					});

					$('.medical-questionnaire-form').on('change', 'input[type="radio"]', (ev) => {
						const questionContainer = $(ev.target).closest('.medical-question');
						const answer = $(ev.target).val();
						if (answer === 'Yes') {
							questionContainer.find('textarea').prop('disabled', false);
						} else {
							questionContainer.find('textarea').val('');
							questionContainer.find('textarea').prop('disabled', true);
						}
					});
				}
			},
		});
	}

	function initializeOrderQuestions() {
		const pwireComponents = window.pwire.pwire_components;
		const orderQuestions = pwireComponents.cart.order_questions;
		const myForm = $('.checkout_form');
		const orderQuestionnaire = myForm.find('.order-questionnaire');

		if (orderQuestions.length) {
			orderQuestionnaire.show();

			$.each(orderQuestions, (key, question) => {
				const answers = ['Yes', 'No'];
				const orderQuestionListItem = $(`<div class="order-question medium-6 cell" data-question-id="${question.id}" />`);

				orderQuestionListItem.append(`<div class="question">${question.question}</div>`);

				$.each(answers, (k) => {
					const orderQuestionComponents = $(`<input type="radio" id="${question.id}_${answers[k]}" name="${question.id}" value="${answers[k]}" required> <label for="${question.id}_${answers[k]}">${answers[k]}</label>`);
					orderQuestionListItem.append(orderQuestionComponents);
				});

				$('.order-questionnaire .order-questions').append(orderQuestionListItem);

				updateFormValues('.order-questionnaire');
				if (typeof Foundation === 'object') {
					Foundation.reInit('abide');
				}
			});
		}
	}

	function updateCustomTextValues() {
		const pwireComponents = window.pwire.pwire_components;
		const draftIntroMessage = pwireComponents.payment.draft_cart_message;
		if ($('.billing_method_draft .intro-message').length) {
			$('.billing_method_draft .intro-message').html(draftIntroMessage);
		}
	}

	function updateForwardPrescriptionOptions() {
		const pwireComponents = window.pwire.pwire_components;
		const fwdRxOptions = pwireComponents.prescription.forward_prescription_options;
		const rxSubmissionMethod = $('.checkout_form').find('.rxSubmission_method');
		rxSubmissionMethod.html('');
		$.each(fwdRxOptions, (key, option) => {
			const method = option.value.toLowerCase();
			rxSubmissionMethod.append(`<input type="radio" id="rx_submission_${method}" name="rx_forwarding" value="${method}" required><label for="rx_submission_${method}" data-sort-order="${option.sort_order}">${option.label}</label>`);
		});
	}

	function updatePaymentComponents() {
		updateCreditCardForm();

		const paymentMethods = window.pwire.pwire_components.payment.methods;
		if (Object.prototype.hasOwnProperty.call(paymentMethods, 'custom')) {
			addCustomPaymentMethods(paymentMethods.custom);
		}
	}

	function setupHtmlElements() {
		updateCustomTextValues();
		updateCountrySelect('.shipping_country, .billing_country');
		updateRegionSelect('.shipping_region, .billing_region');
		updateMailingAddress('.mailing-address');
		updatePaymentComponents();
		updateForwardPrescriptionOptions();
		initializeOrderQuestions();
		updateFormValues();
		initializeMedicalQuestionnaire();
		setupGeneralCartMessages();
	}

	$(document).ready(() => {
		window.pwire = {};
		window.pwire.patient = {};

		const pharmacyComponentsRequest = getPharmacyComponents();
		pharmacyComponentsRequest.done(() => {
			setupHtmlElements();

			const patientInfoRequest = getPatientInfo();
			patientInfoRequest.done(() => {
				// ensure hidden form elements are disabled
				billingState();
			});

			getPatientAddresses();

			// get cart info and set window.pwire.cart
			const pharmacyCartRequest = getCart();
			pharmacyCartRequest.always((jqXHR, textStatus) => {
				if (textStatus === 'success') {
					const rxComponents = $('.checkout_form').find('> .prescription');
					const cartResponse = JSON.parse(jqXHR);
					if (cartResponse.rx_required === 0) {
						disableElement(rxComponents);
						rxComponents.hide();
					} else {
						enableElement(rxComponents);
						rxComponents.show();
					}
				}
			});
		});

		$('.shipping_country').on('change', (ev) => {
			updateRegionSelect('.shipping_region', $(ev.target).val());
			$('.shipping_region').prop('selectedIndex', 0);
			if ($(ev.target).val() === 'USA') {
				$('.shipping_regionCode').prop('placeholder', 'Zip Code');
			} else {
				$('.shipping_regionCode').prop('placeholder', 'Postal Code');
			}
		});

		$('.billing_country').on('change', (ev) => {
			updateRegionSelect('.billing_region', $(ev.target).val());
			$('.billing_region').prop('selectedIndex', 0);
			if ($(ev.target).val() === 'USA') {
				$('.billing_regionCode').prop('placeholder', 'Zip Code');
			} else {
				$('.billing_regionCode').prop('placeholder', 'Postal Code');
			}
		});

		$('#alternate-shipping-addresses').on('click', '.alternative-addresses .edit', (ev) => {
			const address = $(ev.target).closest('.address');
			const addressID = parseInt(address.data('address-id'), 10);
			const addressesArray = window.pwire.patient.addresses;
			const addressObject = addressesArray.find(obj => (parseInt(obj.id, 10) === addressID));

			populateAddressForm(addressObject);

			const updateShippingElements = $('.update-shipping-address-form').find(':input');
			updateShippingElements.removeClass('invalid-field valid-field');
			$('.update-shipping-address-response').hide();
			$('.address-form').appendTo('.update-shipping-address-form .form-fields').show();
			$('#update-shipping-address').attr('data-address-id', addressID);
			$('#update-shipping-address').foundation('open');
			enableElement($('.update-shipping-address-form'));
		});

		$('#alternate-shipping-addresses').on('click', '.alternative-addresses .delete', (ev) => {
			const targetAddress = $(ev.target).closest('.address');
			const addressRefID = targetAddress.attr('data-address-id');
			const addressesArray = window.pwire.patient.addresses;
			const addressObject = addressesArray.find(obj => (obj.id === addressRefID));
			const addressFormatted = formatAddress(addressObject);
			const confirmAddressContainer = $('#confirm-shipping-address-delete');
			const addressPreview = `<ul><li class="address" data-address-id="${addressRefID}">${addressFormatted}</li></ul>`;
			confirmAddressContainer.find('.address-preview').html(addressPreview);
			confirmAddressContainer.find('.confirm').attr('data-address-id', addressRefID);
			confirmAddressContainer.find('.delete-shipping-address-response').hide();
			confirmAddressContainer.foundation('open');
		});

		$('#confirm-shipping-address-delete').on('click', '.confirm', (ev) => {
			const confirmButton = $(ev.target);
			const deleteAddressRefID = confirmButton.attr('data-address-id');
			const deleteRequest = deleteShippingAddress(deleteAddressRefID);
			$('.delete-shipping-address-response').hide();
			$('.delete-shipping-address-response .message').html('');

			deleteRequest.done((delResponse) => {
				const deleteResponse = JSON.parse(delResponse);
				if (deleteResponse.status === 'success') {
					const patientAddressRequest = getPatientAddresses();
					patientAddressRequest.done(() => {
						let newAddressRefID = getShippingRefID() || 0;
						if (newAddressRefID === deleteAddressRefID) {
							newAddressRefID = 0;
						}
						updateShippingComponents(newAddressRefID);
						$('#confirm-shipping-address-delete').foundation('close');
					});
				} else {
					let messages = '';
					$.each(deleteResponse.messages, (index, message) => {
						const content = message.content.value || message.content.field || message.content;
						messages += `${content}<br />`;
					});
					$('.delete-shipping-address-response').show();
					$('.delete-shipping-address-response .message').html(`${messages}`);
				}
			});
		});

		$('#confirm-shipping-address-delete').on('click', '.cancel', () => {
			$('#confirm-shipping-address-delete').foundation('close');
		});

		$('#alternate-shipping-addresses').on('click', '.alternative-addresses .ship-to', (ev) => {
			const address = $(ev.target).closest('.address');
			const addressID = parseInt(address.data('address-id'), 10);
			const addressesArray = window.pwire.patient.addresses;
			const addressObject = addressesArray.find(obj => (parseInt(obj.id, 10) === addressID));
			const shippingAddress = formatAddress(addressObject);

			setShippingRefID(addressID);

			$('.selected-address-container').html(shippingAddress);
			$('#alternate-shipping-addresses').foundation('close');
		});

		$('#update-shipping-address').on('click', '.update-address', () => {
			const shippingAddressForm = $('.update-shipping-address-form');
			const shippingRegion = shippingAddressForm.find('#shipping_region');
			shippingRegion.trigger('change');

			let formValid = true;
			// stop validation if dropdown is empty/no value selected
			if (shippingRegion.val() === '') {
				formValid = false;
			}
			if (formValid) {
				shippingAddressForm.trigger('submit');
			}
			return false;
		});

		$('#update-shipping-address').on('closed.zf.reveal', () => {
			$('.update-shipping-address-form')[0].reset();
			disableElement($('.update-shipping-address-form'));
		});

		$('#alternate-shipping-addresses').on('click', '.add-new-address', () => {
			$('.address-form').appendTo('.update-shipping-address-form .form-fields').show();
			const updateShippingElements = $('.update-shipping-address-form').find(':input');
			updateShippingElements.removeClass('invalid-field valid-field');
			$('.update-shipping-address-form')[0].reset();
			updateRegionSelect('.shipping_region', $('.address-form').find('.shipping_country').val());
			enableElement($('.update-shipping-address-form'));

			$('#update-shipping-address').removeAttr('data-address-id');
			$('.update-shipping-address-response').hide();
			$('#update-shipping-address').foundation('open');
		});

		$('.pw_checkout').on('click', '.change-address', () => {
			updateShippingAddressBook();
			$('#alternate-shipping-addresses').foundation('open');
		});

		$('.pw_checkout').on('submit', (ev) => {
			ev.preventDefault();
			return false;
		});

		$('#update-shipping-address').on('submit', '.update-shipping-address-form', (ev) => {
			ev.preventDefault();
			return false;
		});

		// field element is valid
		$('.pw_checkout, #update-shipping-address').on('valid.zf.abide', (ev, elem) => {
			elem.addClass('valid-field').removeClass('invalid-field');
		});

		// field element is invalid
		$('.pw_checkout, #update-shipping-address').on('invalid.zf.abide', (ev, elem) => {
			elem.addClass('invalid-field').removeClass('valid-field');
		});

		$('.pw_checkout').on('formvalid.zf.abide', (ev, frm) => {
			if (frm.attr('id') !== 'checkout_form') {
				// adding listener to form directly gets lots on reInit('abide')
				// so bind higher and check what form is being called
				return;
			}

			ev.preventDefault();
			const orderForm = $('.checkout_form');
			const r = 'submit-order';
			const d = orderForm.serialize();

			// store disabled fields
			const allElements = orderForm.find(':input');
			allElements.removeClass('invalid-field valid-field');
			const disabledElements = orderForm.find(':input:disabled');
			disableElement(allElements);
			$('.cart-response')
				.removeClass('alert callout')
				.text('Submitting order...')
				.addClass('submitting-order')
				.show();
			$('.cart-response').pwireSpinner({ left: '-30px' });
			$('.checkout-error').hide();

			$.ajax({
				type: 'POST',
				data: d,
				url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
				success: (response) => {
					const pwireResponse = JSON.parse(response);
					window.pwire.checkout_response = pwireResponse;

					let messages = '';
					$('.cart-response').html('');
					$('.checkout_form [data-form-error-for]').hide();

					if (pwireResponse.status !== 'success') {
						// eslint-disable-next-line no-unused-vars
						$.each(pwireResponse.messages, (index, message) => {
							const content = message.content.value || message.content.field || message.content;
							messages += `Invalid data:<br /> ${content}<br />`;

							$(`#${message.content.field}`).addClass('.is-invalid-input');

							const errorLabelSelector = `[data-form-error-for=${message.content.field}]`;
							// if an abide error msg is setup, show it
							if ($(errorLabelSelector).length) {
								$(errorLabelSelector).addClass('is-visible');
							} else {
								// no default error, add a generic message
								$(`#${message.content.field}`).parent().append(`<label class="form-error generated" data-form-error-for="${message.content.field}">Required field.</label>`);
								$(errorLabelSelector).addClass('is-visible');
							}
						});
						$('.checkout-error').show();
						if (messages !== '') {
							$('.cart-response').html(`${messages}`).addClass('alert callout').show();
						}
					}
				},
				complete: (jqXHR, textStatus) => {
					if (textStatus === 'success') {
						const pwireResponse = JSON.parse(jqXHR.responseText);
						$('.cart-response')
							.removeClass('submitting-order')
							.pwireSpinner().stop();

						if (pwireResponse.cart) {
							if (pwireResponse.cart.new_patient_created && (pwireResponse.cart.new_patient_created === 'json')) {
								jQuery('.pw-pharmacy-wrap').trigger('pwire:account:newAccount');
								jQuery('.pw-pharmacy-wrap').trigger('pwire:account:newAccount_checkout');
							}
						}

						if (pwireResponse.status === 'success') {
							// scroll top top of page to display Thank You message.
							window.scrollTo(0, 0);

							$('.checkout_form').hide();
							// $('.cart-extended, .cart-mini').hide();

							const accountUrl = pw_json.account_url;
							// Display Thank You
							$('.cart-response').html(`<h1>Thank You</h1><p>Your order <a href="${accountUrl}">#${pwireResponse.order}</a> has been submitted for processing.<br /><br />You can view your order status at any time by logging in and going to your <a href="${accountUrl}">account page</a>.</p>`).removeClass('alert callout').show();

							// Display chosen RX submission
							const rxSubmissionMethod = pwireResponse.cart.rx_forwarding;
							setupRxSubmissionMethod(rxSubmissionMethod);

							// Hook for post order scripts
							$('.checkout_form').trigger('pwire:cart:orderSubmitted', [pwireResponse]);
						} else {
							enableElement(allElements.not(disabledElements));
						}
					}
				},
			});
		});

		$('.pw_checkout').on('valid.zf.abide', debounced(200, () => {
			saveCheckoutStateToSession();
		}));

		$('.pw_checkout').on('change', '[name=billing_type]', () => {
			billingState();
		});

		$('.pw_checkout').on('change', '[name=rx_forwarding]', () => {
			if ($('[name=rx_forwarding]:checked').length) {
				$('label[data-form-error-for=rx_submission_upload]').removeClass('is-visible is-invalid-label').hide();
			}
		});

		$('.pw_checkout').on('change', '.billing_useShippingAddress', () => {
			billingAddressFormState();
		});

		$('.pw_checkout').on('submit', '.doctor_contact_form', (ev) => {
			ev.preventDefault();
			return false;
		});

		$('#update-shipping-address').on('formvalid.zf.abide', '.update-shipping-address-form', (ev) => {
			ev.preventDefault();
			updateShippingAddress();
			return false;
		});

		$('.pw_checkout').on('formvalid.zf.abide', (ev, frm) => {
			if (frm.attr('id') !== 'doctor_contact_form') {
				// adding listener to form directly gets lost on reInit('abide')
				// so bind higher and check what form is being called
				return;
			}
			ev.preventDefault();
			const doctorObj = {};
			doctorObj.name = $('[name="doctor_name"]').val();
			doctorObj.phoneAreaCode = $('[name="doctor_phoneAreaCode"]').val();
			doctorObj.phone = $('[name="doctor_phone"]').val();
			doctorObj.faxAreaCode = $('[name="doctor_faxAreaCode"]').val();
			doctorObj.fax = $('[name="doctor_fax"]').val();
			const orderID = pwire.checkout_response.order_id;
			submitDoctorInformation(orderID, doctorObj);
		});

		$('[name="billing_creditCard_number"]').each((index, elem) => {
			// Cleave.js used to detect type
			// abideCreditCardValidator in init-foundation.js to validate
			const ccNumberInput = $(elem);
			const creditCardCleave = new Cleave(ccNumberInput.get(0), {
				creditCard: true,
				delimiter: '',
				onCreditCardTypeChanged: (type) => {
					updateCreditCardNumberIcon(type);

					const allowedCreditCards = pwire.pwire_components.payment.methods.creditcard.types;
					let typeFullName = type;
					if (Object.prototype.hasOwnProperty.call(allowedCreditCards, type)) {
						typeFullName = allowedCreditCards[type].label;
					}

					// setup hidden input billing_creditCard_type to submit full cc type name
					// (e.g. 'American Express') for v4 support primarily
					const creditCartTypeInput = $('#payment-method-selected .billing_method_creditCard').find('.billing_creditCard_type');
					if (creditCartTypeInput.length) {
						creditCartTypeInput.val(typeFullName);
					} else {
						$('<input>').prop({
							type: 'hidden',
							name: 'billing_creditCard_type',
							class: 'billing_creditCard_type',
							value: typeFullName,
						}).appendTo('#payment-method-selected .billing_method_creditCard');
					}

					window.pwire.creditCardType = type;
				},
			});
			ccNumberInput.data('cleave', creditCardCleave);
		});

		// https://github.com/dropbox/zxcvbn
		$('.pw_checkout').on('change blur keydown', '#password', (ev) => {
			const pwdInput = $(ev.target);
			const pwd = pwdInput.val();
			const passwordStrength = zxcvbn(pwd);
			const progressBar = pwdInput.siblings('.progress');

			switch (passwordStrength.score) {
			case 0:
				progressBar.removeClass('success warning alert').addClass('secondary');
				progressBar.attr({ 'aria-valuenow': 0 });
				progressBar.find('.progress-meter').css({ width: '0%' });
				progressBar.find('.progress-meter-text').text('');
				break;
			case 1:
				progressBar.removeClass('success warning alert').addClass('secondary');
				progressBar.attr({ 'aria-valuenow': 25 });
				progressBar.find('.progress-meter').css({ width: '25%' });
				progressBar.find('.progress-meter-text').text('weak');
				break;
			case 2:
				progressBar.removeClass('secondary success warning').addClass('alert');
				progressBar.attr({ 'aria-valuenow': 50 });
				progressBar.find('.progress-meter').css({ width: '50%' });
				progressBar.find('.progress-meter-text').text('weak');
				break;
			case 3:
				progressBar.removeClass('secondary success alert').addClass('warning');
				progressBar.attr({ 'aria-valuenow': 75 });
				progressBar.find('.progress-meter').css({ width: '75%' });
				progressBar.find('.progress-meter-text').text('good');
				break;
			case 4:
				progressBar.removeClass('secondary warning alert').addClass('success');
				progressBar.attr({ 'aria-valuenow': 100 });
				progressBar.find('.progress-meter').css({ width: '100%' });
				progressBar.find('.progress-meter-text').text('strong');
				break;
			default:
				break;
			}
		});
	});
})(jQuery);
