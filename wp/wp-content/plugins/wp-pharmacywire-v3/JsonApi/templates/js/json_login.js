jQuery.noConflict();

(($) => {
	function disableForm(targetForm) {
		targetForm.find('input, select, button').attr('disabled', true);
		targetForm.fadeTo(0, 0.4);
	}

	function enableForm(targetForm) {
		targetForm.find('input, select, button').attr('disabled', false);
		targetForm.fadeTo(0, 1);
	}

	$(document).ready(() => {
		$('.login_form').on('formvalid.zf.abide', (ev) => {
			ev.preventDefault();

			const loginForm = $('.login_form');
			const r = 'login';
			let d = loginForm.serialize();

			if (Object.prototype.hasOwnProperty.call(pw_json_login, 'login_action') && (pw_json_login.login_action !== '')) {
				const loginAction = encodeURI(pw_json_login.login_action);
				d += `&onsuccessredirect=${loginAction}`;
			}

			disableForm(loginForm);
			$('.response').addClass('callout secondary').removeClass('alert').text('Logging in...');

			$.ajax({
				type: 'POST',
				data: d,
				url: `${pw_json_login.request_url}?r=${r}&pw_nonce=${pw_json.nonce}`,
				success: (response) => {
					const pwireResponse = JSON.parse(response);
					if (pwireResponse.success) {
						if (Object.prototype.hasOwnProperty.call(pwireResponse, 'redirect') && (pwireResponse.redirect !== '')) {
							window.location.href = pwireResponse.redirect;
						} else {
							window.location.href = pw_json_login.login_action;
						}
					} else {
						$('.response').removeClass('secondary').addClass('alert callout').html(pwireResponse.message);
					}
				},
				complete: () => {
					enableForm(loginForm);
				},
			});
		});

		$('.login_form').on('submit', (ev) => {
			ev.preventDefault();
			return false;
		});
	});
})(jQuery);
