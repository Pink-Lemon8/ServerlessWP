// eslint-disable-next-line no-unused-vars
function usingShippingCheckBox(check) {
	if (jQuery(check).is(':checked')) {
		jQuery('.billing-address-form :input').attr('disabled', true);
		jQuery('#useShipping').attr('disabled', false);
		jQuery('.billing-address-form').hide();
		jQuery('.billing-address-preview').show();
		jQuery('#useShipping').attr('checked', true);
		jQuery('#billing_useShippingAddress').attr('value', 'yes');
	} else {
		jQuery('.billing-address-form :input').attr('disabled', false);
		jQuery('.billing-address-form').show();
		jQuery('.billing-address-preview').hide();
		jQuery('#useShipping').attr('checked', false);
		jQuery('#billing_useShippingAddress').attr('value', 'no');
	}
}

jQuery(($) => {
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

	// pw_checkout has it's own code
	$('.pw-pharmacy-wrap:not(.pw_checkout)').on('change', '#billing_country, #shipping_country', (ev) => {
		updateCountryProvince(ev.target);
	});

	// https://github.com/dropbox/zxcvbn
	$('.pw-register').on('change blur keydown', '#password', (ev) => {
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
