jQuery(($) => {
	const requestUrl = `${wp_pharmacywire.plugin_url}request.php`;

	function addError(field, errMsg = '') {
		const errorMsg = (errMsg !== '') ? errMsg : 'Required field.';
		jQuery(field).closest('div').addClass('error');
		jQuery(field).closest('div').append(`<small class="error">${errorMsg}</small>`);
	}

	function initializeRecentOrders() {
		const getData = 'recent-orders';
		$.getJSON(
			requestUrl,
			getData,
			(data) => {
				const orderRow = doT.template($('#recent-orders-tmpl').html());
				let recentOrderRow = '';
				$.each(data.RecentOrders, (key, value) => {
					recentOrderRow += orderRow(value);
				});
				const thead = $('#recent-orders-list thead')[0].outerHTML;
				$('#recent-orders-list').html(thead + recentOrderRow);
			},
		).done(() => {
			$('#recent-orders [data-tooltip]').each((i, tipEl) => new Foundation.Tooltip($(tipEl)));
		});
	}

	function initializeProfileInfo() {
		const getData = 'profile-info';
		$.getJSON(
			requestUrl,
			getData,
			(data) => {
				const profileTemplate = doT.template($('#profile-info-tmpl').html());
				let profileRow = '';
				$.each(data.ProfileInfo, (key, value) => {
					profileRow += profileTemplate(value);
				});
				$('#profile-info').html(profileRow);
			},
		);
	}

	function initializeAddress() {
		const getData = 'address-manager';

		$.ajax({
			dataType: 'json',
			url: requestUrl,
			data: getData,
			success: (data) => {
				const addressListTemplate = doT.template($('#address-list-tmpl').html());
				let addressList = '';

				const billingAddress = data.Address.AddressList.BillingAddress;
				$.each(billingAddress, (key, value) => {
					const newVal = value;
					newVal.controls = '<button class="edit-address small button">Edit</button>';
					addressList += addressListTemplate(newVal);
				});

				const shippingAddress = data.Address.AddressList.ShippingAddress;
				$.each(shippingAddress, (key, value) => {
					const newVal = value;
					newVal.controls = '<button class="edit-address small button">Edit</button> <button class="remove-address small button">Remove</button>';
					addressList += addressListTemplate(newVal);
				});

				$('#select-shipping-address').html(addressList);
			},
		});
	}

	function updateCountryProvince(callingElement, country, province) {
		let provinceDropdown = '#shipping_region';
		let provinceDropdownName = 'shipping_region';
		let provincePostalCodeInput = '#shipping_regionCode';
		if ($(callingElement).attr('id') === 'billing_country') {
			provinceDropdown = '#billing_region';
			provinceDropdownName = 'billing_region';
			provincePostalCodeInput = '#billing_regionCode';
		}

		let getData = 'country-code=';

		if (country) {
			getData += country;
		} else {
			getData += $(callingElement).val();
		}

		$(provinceDropdown).attr('disabled', true);

		$.getJSON(
			`${wp_pharmacywire.plugin_url}request.php`,
			getData,
			(data) => {
				const regionList = data.Region;

				if (regionList.length) {
					$(provinceDropdown).replaceWith(`<select id="${provinceDropdownName}" name="${provinceDropdownName}" class="${provinceDropdownName}" />`);
					$.each(regionList, (key, val) => {
						$(provinceDropdown).append(`<option value="${val.region_code}">${val.region_name}</option>`);
					});
					// sort regions by their text value rather than default region code
					// which doesn't work for Philipinnes, etc.
					$(provinceDropdown).html($(provinceDropdown).find('option').sort((x, y) => ($(x).text() < $(y).text() ? -1 : 1)));
					$(provinceDropdown).prepend('<option value="">State/Province</option>');
					$(provinceDropdown).prop('selectedIndex', 0);
				} else {
					$(provinceDropdown).replaceWith(`<input type="text" id="${provinceDropdownName}" name="${provinceDropdownName}" class="${provinceDropdownName}" value="" size="30" maxlength="150">`);
				}

				$(provinceDropdown).attr('disabled', false);

				if (country) callingElement.val(country);
				if (province) $(provinceDropdown).val(province);

				if ($(callingElement).find('option:selected').val() === 'USA') {
					$(provincePostalCodeInput).prop('placeholder', 'Zip Code');
				} else {
					$(provincePostalCodeInput).prop('placeholder', 'Postal Code');
				}
			},
		);
	}

	function validateForm(formId) {
		const myForm = $(formId);
		let valid = true;

		$('input, select', myForm).each((index, elem) => {
			$(elem).parent('div').removeClass('error');
			$(elem).parent('div').children('small.error').remove();

			if (!$(elem).val()) {
				if ($(elem).hasClass('optional') || $(elem).is(':hidden')) {
					// skip to next iteration
					return true;
				}
				addError(elem);
				valid = false;
			}
			return true;
		});

		if ($('input[name="Sex"]').length) {
			const sexInput = $('input[name="Sex"]:checked').val();
			if (!sexInput || ((sexInput !== 'M') && (sexInput !== 'F'))) {
				addError('input#sexm');
				valid = false;
			}
		}

		if ($('select#HeightFeet').val() === '-1') {
			addError('select#HeightFeet');
			valid = false;
		}

		if (myForm.find('input.username').val() !== myForm.find('input.confirm-username').val()) {
			addError('input.username, input.confirm-username', 'Email addresses must match.');
			valid = false;
		}

		return valid;
	}

	$('.pw-pharmacy-wrap').on('click', '#update-profile', () => {
		const formInput = $('#profile-form').find(':input');
		const validForm = validateForm('#profile-form');

		if (validForm) {
			let aData = $('#profile-form').serialize();
			aData += '&action=update-account';

			return $.ajax({
				type: 'POST',
				data: aData,
				beforeSend: () => {
					$(formInput).addClass('disabled');
					$(formInput).attr('disabled', true);
				},
				success: (data) => {
					const response = $(data);

					$(formInput).attr('disabled', false);
					$(formInput).removeClass('disabled');

					$('#profile-info').replaceWith(response.find('#profile-info'));

					$('#page-register-form').hide();
					$('#profile-info').show();
				},
			});
		}
		return false;
	});

	function initializeMedicalQuestionnaire() {
		const r = 'get-medical-questions';
		const medicalQuestionnaire = $('.medical-questionnaire');
		const myForm = $('.medical-questionnaire-form');
		const medicalQuestions = myForm.find('.medical-questions');
		const medicalQR = medicalQuestionnaire.find('.medical-questionnaire-response');
		medicalQuestions.empty();
		medicalQuestions.pwireSpinner();

		$('.submit-medical-questions').hide();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (data) => {
				medicalQuestions.pwireSpinner().stop();
				if (data.success === 1) {
					if (data.show_medical_questionnaire && data.medical_questions.length) {
						// extra empty after request successful to stop duplicate
						// questions being listed if the tabs are switched
						// back and forth quickly
						medicalQuestions.empty();

						const medQuestionsSorted = data.medical_questions.sort((a, b) => {
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
						$('.submit-medical-questions').show();
						myForm.show();
					} else {
						myForm.hide();
						medicalQR
							.removeClass('alert success callout')
							.text('You have no medical questions to be answered at this time.')
							.show();
					}
				}
			},
			complete: (jqXHR, textStatus) => {
				if (textStatus === 'success') {
					Foundation.reInit('Abide');
				}
			},
		});
	}

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

	$('.medical-questionnaire-form').on('submit', (ev) => {
		ev.preventDefault();
		return false;
	});

	$('.medical-questionnaire').on('formvalid.zf.abide', (ev) => {
		ev.preventDefault();
		const myForm = $('form.medical-questionnaire-form');
		const medicalQR = $('#medical-questionnaire').find('.medical-questionnaire-response');
		medicalQR.empty();

		medicalQR
			.removeClass('alert success callout')
			.text('Submitting questionnaire responses...')
			.addClass('submitting-questionnaire spinner')
			.show();

		myForm.hide();

		const r = 'set-medical-answers';
		const d = myForm.serializeArray();

		$.ajax({
			type: 'POST',
			data: d,
			dataType: 'json',
			url: `${pw_json.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
			success: (data) => {
				if (data.success === 1) {
					medicalQR
						.addClass('success callout')
						.text('Your medical questionnaire responses have been submitted.')
						.removeClass('submitting-questionnaire spinner')
						.show();
				} else {
					let message = 'There was an error submitting your responses, please try again.<br />';

					if (Array.isArray(data.messages)) {
						$.each(data.messages, (k, v) => {
							if (v.type === 'error') {
								message += `${v.content}<br />`;
							}
						});
					}

					medicalQR
						.addClass('alert callout')
						.html(message)
						.removeClass('submitting-questionnaire spinner')
						.show();

					myForm.show();
				}
			},
		});
	});

	$('.pw-pharmacy-wrap').on('click', '.recent-order-summary', (ev) => {
		ev.preventDefault();
		const orderRow = ev.target;

		if ($(`#order-details-view-${$(orderRow).attr('order-id')} .order-container`).is(':visible')) {
			$(`#order-details-view-${$(orderRow).attr('order-id')} .order-container`).slideUp('slow');
		} else {
			const getData = `recent-order&recent-order-id=${$(orderRow).attr('order-id')}`;

			if ($(`#order-details-view-${$(orderRow).attr('order-id')} .order-container`).is(':empty')) {
				$('.tableRecentOrder').pwireSpinner();

				$.getJSON(
					requestUrl,
					getData,
					(data) => {
						const recentOrderTemplate = doT.template($('#recent-order-tmpl').html());
						$(`#order-details-view-${data.id} .order-container`).html(recentOrderTemplate(data));

						const recentOrderLineItemsTemplate = doT.template($('#recent-order-line-items-tmpl').html());
						let lineItems = '';
						$.each(data.items, (key, value) => {
							lineItems += recentOrderLineItemsTemplate(value);
						});

						const oli = `tbody#order-line-items-${data.id}`;
						$(oli).html(lineItems);

						$('.tableRecentOrder').pwireSpinner().stop();

						$('.order-container').slideUp('slow');

						$(`#order-details-view-${$(orderRow).attr('order-id')} .order-container`).slideDown('slow');
					},
				);
			} else {
				$('.order-container').slideUp('slow');
				$(`#order-details-view-${$(orderRow).attr('order-id')} .order-container`).slideDown('slow');
			}
		}
	});

	$('.pw-pharmacy-wrap').on('click', '#select-shipping-address .ship-to-address', (ev) => {
		const shipToID = $(ev.target).parents('li.address-line').attr('shipping-value');
		ev.preventDefault();
		$('#shipping-address-id').val(shipToID);
		$('#frmSelectShipping #action').val('SHIPTO');
		$('#frmSelectShipping').trigger('submit');
	});

	function populateSelectAddress(shipToID) {
		const countryVal = $(`#address-id-${shipToID} span.country`).text();
		const provinceVal = $(`#address-id-${shipToID} span.province`).text();

		$('input#Address1').val($(`#address-id-${shipToID} span.address1`).text());
		$('input#Address2').val($(`#address-id-${shipToID} span.address2`).text());
		$('input#City').val($(`#address-id-${shipToID} span.city`).text());
		$('input#PostalCode').val($(`#address-id-${shipToID} span.postalcode`).text());
		$('input#phoneAreaCode').val($(`#address-id-${shipToID} span.areacode`).text());
		$('input#phone').val($(`#address-id-${shipToID} span.phone`).text());

		updateCountryProvince($('#shipping_country'), countryVal, provinceVal);

		$('#shipping-address-form input').each((index, elem) => {
			if (document.getElementById(elem.id).value === $(elem).attr('placeholder') || !document.getElementById(elem.id).value) {
				$(elem).addClass('placeholder');
			} else {
				$(elem).removeClass('placeholder');
			}
		});
	}

	$('.pw-pharmacy-wrap').on('click', '#frmSelectShipping .edit-address', (ev) => {
		ev.preventDefault();
		$('#shipping-update-container').show();
		$('#frmShippingUpdate :input').attr('disabled', false);
		$('#submit-address').attr('action-state', 'edit');
		const shipToID = $(ev.target).parents('li.address-line').attr('shipping-value');
		$('#edit-shipping-address').val(shipToID);
		$('#shipping-address-form #shipping-form-title, #shipping-address-form #submit-address').text('Save Address');
		$("#frmShippingUpdate input:hidden[name='action']").val('EDIT');

		populateSelectAddress(shipToID);
		$('#select-shipping-container .add-address.button').show();
		$('html, body').animate({
			scrollTop: $('#shipping-update-container').offset().top,
		}, 500);
	});

	$('.pw-pharmacy-wrap').on('click', '#frmSelectShipping .add-address', (ev) => {
		ev.preventDefault();
		$('#shipping-update-container').show();
		$('#submit-address').attr('action-state', 'add');
		$('#select-shipping-container .add-address.button').hide();
		$('#shipping-address-form #shipping-form-title').text('Add Address');
		$('#shipping-address-form #submit-address').text('Add Address');
		$('#shipping-address-form input').val('');
		$('#shipping-address-form input').addClass('placeholder');
		$("#frmShippingUpdate input:hidden[name='action']").val('ADD');
	});

	$('.pw-pharmacy-wrap').on('click', '#select-shipping-address .remove-address', (ev) => {
		ev.preventDefault();
		const shipToID = $(ev.target).parents('li.address-line').attr('shipping-value');
		const confirmationMsg = 'Address will permanently be deleted. Are you sure?';
		let displayShipToButton = '';

		if ($('#display-ship-to-button-remove').val() === 'false') {
			displayShipToButton = '&display-ship-to-button=false';
		}

		// eslint-disable-next-line no-restricted-globals, no-alert
		if (confirm(confirmationMsg)) {
			const aData = `action=DELETE&shipping-address-id=${shipToID}${displayShipToButton}`;
			const updateBlock = '#select-shipping-address';
			const editAddressUrl = $('#edit-address-url').val();

			return $.ajax({
				type: 'POST',
				data: aData,
				url: editAddressUrl,
				success: (data) => {
					const response = $(data);
					$(updateBlock).replaceWith(response.find(updateBlock));
				},
			});
		}
		return false;
	});

	$('.pw-pharmacy-wrap').on('click', '#frmShippingUpdate #submit-address', () => {
		let valid = true;

		$('input, select', '#frmShippingUpdate').each((index, elem) => {
			$(elem).parent('div').removeClass('error');
			$(elem).parent('div').children('small.error').remove();

			if (!$(elem).val()) {
				if ($(elem).hasClass('optional') || $(elem).is(':hidden')) {
					// skip to next iteration
					return true;
				}
				addError(elem);
				valid = false;
			}
			return true;
		});

		if (!(/^\s*\d{5}\s*$/.test($('#PostalCode').val())) && ($('#shipping_country').val() === 'USA')) {
			if (!$('input#PostalCode').closest('div').hasClass('error')) {
				addError('input#PostalCode', 'Invalid zipcode.');
			}
			valid = false;
		} else if (!(/^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$/i.test($('#PostalCode').val())) && ($('#shipping_country').val() === 'CAN')) {
			if (!$('input#PostalCode').closest('div').hasClass('error')) {
				addError('input#PostalCode', 'Invalid postal code.');
			}
			valid = false;
		}

		if (valid) {
			let aData = '';
			let updateState = 'EDIT';
			let updatedAddressId = 0;

			if ($('#submit-address[action-state="edit"]').length) {
				$("#frmShippingUpdate input:hidden[name='action']").val('EDIT');
				updatedAddressId = $('#edit-shipping-address').val();
			} else {
				$("#frmShippingUpdate input:hidden[name='action']").val('ADD');
				updateState = 'ADD';
			}

			$('input, select', $('#frmShippingUpdate')).each((index, elem) => {
				if (aData.length) aData += '&';
				aData += `${$(elem).attr('name')}=${$(elem).val()}`;
			});

			const updateBlock = '#select-shipping-container';
			const editAddressUrl = $('#edit-address-url').val();
			let updatedAddress = '';

			$('#frmShippingUpdate :input').attr('disabled', true);

			return $.ajax({
				type: 'POST',
				data: aData,
				url: editAddressUrl,
				success: (data) => {
					$(updateBlock).replaceWith($(data).find(updateBlock));

					if (updateState === 'EDIT') {
						updatedAddress = `#shipping-row-${updatedAddressId}`;
					} else {
						updatedAddress = `#${$('#select-shipping-address li').last().attr('id')}`;
					}
				},
			}).done(() => {
				$('#frmShippingUpdate input').addClass('placeholder');
				$('#frmShippingUpdate :input').attr('disabled', false);
				$('#shipping-update-container').hide();
				$(updatedAddress).switchClass('pw-transparent', 'pw-hilight', 500).delay(1000).switchClass('pw-hilight', 'pw-transparent', 2500);
			});
		}
		return false;
	});

	if (jQuery('.pw-pharmacy-wrap.pw-profile').length) {
		initializeProfileInfo();
		initializeAddress();
		if ($('#medical-questionnaire:visible').length) {
			initializeMedicalQuestionnaire();
		}

		$('input#sexm').on('click', (ev) => {
			// Fix display issue with valdation by applying checked="checked"
			$('input#sexf').attr('checked', false);
			$(ev.target).attr('checked', true);
		});

		$('input#sexf').on('click', (ev) => {
			// Fix display issue with valdation by applying checked="checked"
			$('input#sexm').attr('checked', false);
			$(ev.target).attr('checked', true);
		});
	}

	function loadTabContent() {
		if ($('#recent-orders:visible').length) {
			initializeRecentOrders();
		} else if ($('#profile:visible').length) {
			initializeProfileInfo();
		} else if ($('#address:visible').length) {
			initializeAddress();
		} else if ($('#medical-questionnaire:visible').length) {
			initializeMedicalQuestionnaire();
		}
	}

	$('#profile-tabs').on('change.zf.tabs', () => {
		loadTabContent();
	});
	$('.pw-pharmacy-wrap').on('click', 'button.edit-profile', () => {
		$('#page-register-form').show();
		$('#profile-info').hide();
	});
	$('.pw-pharmacy-wrap').on('click', '#cancel-profile', () => {
		$('#page-register-form').hide();
		$('#profile-info').show();
	});
	$('.pw-pharmacy-wrap:not(.pw_checkout)').on('change', '#shipping_country', (ev) => {
		updateCountryProvince(ev.target);
	});
});
