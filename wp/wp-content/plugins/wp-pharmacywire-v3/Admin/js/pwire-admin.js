jQuery(document).ready(function ($) {

	$('#pw_emailLogo_button').on('click', () => {
		formfield = $('#pw_emailLogo');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		window.send_to_editor = function (html) {
			imgurl = $(html).attr('src');
			formfield.val(imgurl);
			tb_remove();
		}
		return false;
	});

	var fieldVal = ''
	$('input[name=pw_passkey], input[name=pw_update_license]').on('focus', (ev) => {
		const field = $(ev.target);
		fieldVal = field.val();
		field.prop('type', 'text');
		if (fieldVal.match(/^\*+$/)) {
			field.val('');
		}
	}).on('blur', (ev) => {
		const field = $(ev.target);
		if (field.val().length === 0) {
			field.val(fieldVal);
		}
		field.prop('type', 'password');
		fieldVal = '';
	});
});