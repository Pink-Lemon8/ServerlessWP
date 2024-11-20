<div class="wrap">
	<?php include 'options-head.php' ?>


	<div class="settingForm">

		<a href="http://www.pharmacywire.com" class="pharmacywire-email pharmacywire-plugin-header" target="pharmacywire">Email Options</a>

		<form action="options.php" method="post" id="adminForm" name="adminForm">
			<?php settings_fields('pharmacy-email-group'); ?>
			<?php do_settings_sections('pharmacy-email-group'); ?>

			<fieldset>
				<h3>
					<legend>Send Email?</legend>
				</h3>
				<p><b>Options for emails sent from your website via the PharmacyWire plugin (wp_mail()/SMTP) using the below email template.</b><br />
					<em>(Note: only the new patient Welcome email and Forgot Password emails come from this plugin.<br /> 
						All other emails from PharmacyWire are handled separately within PharmacyWire.)</em></p>
				<table class="form-table">
					<tr>
						<td>
							<input type="checkbox" name="pw_email_welcome" id="pw_email_welcome" <?php checked('on', get_option('pw_email_welcome', 'on')) ?> />&nbsp;<label for="pw_drug_dropdown">Send 'Welcome' new customer email from your website via the PharmacyWire plugin to new customers?</label>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="pw_email_forgot_pwd" id="pw_email_forgot_pwd" <?php checked('on', get_option('pw_email_forgot_pwd', 'on')) ?> />&nbsp;<label for="pw_drug_dropdown">Send 'Forgot Password' email from your website via the PharmacyWire plugin to new customers?</label>
						</td>
					</tr>
					<tr>
						<td>
							<b><em>When disabling these emails from being sent by the WordPress plugin, make sure that the emails setup and configured to be sent from within PharmacyWire.</em></b>
						</td>
					</tr>
				</table>
			</fieldset>
			<hr />
			<fieldset>
				<h3>
					<legend>Email Template</legend>
				</h3>
				<table class="form-table">
					<tr valign="top">
						<th class="label"><label for="pw_emailLogo">Email Logo</label></th>
						<td>
							<input id="pw_emailLogo" type="text" size="36" name="pw_emailLogo" value="<?php echo get_option('pw_emailLogo') ?>" />
							<input id="pw_emailLogo_button" class="button" type="button" value="Upload Image" />
							<br /><span class="description">Enter an URL or upload an image for the logo (should have a transparent background).</span></td>
					</tr>

					<tr>
						<th class="label"><label for="pw_emailHead">Email head</label></th>
						<td>
							<textarea style="width:600px;height:200px" id="pw_emailHead" name="pw_emailHead" rows="3" cols="15">
							<?php
							$defaultHead = '<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
					<body><div>
							<table border="0" cellspacing="0" cellpadding="0" style="width:654px;border:none;margin:0 auto">
								<tr>
									<td><img src="' . THEME_URL . 'images/email/email-head.png" style="float:left"></td>
								</tr>
							</table>
							<table border="0" cellspacing="0" cellpadding="0" style="width:629px;border:none;margin:0 auto">
								<tr>
									<td style="padding-right:20px;padding-left:20px;background-color:#f6f5f5;font-family:arial;font-size:13px;color:#333">
										';

							$customHead = get_option('pw_emailHead', $defaultHead);

							if ($customHead == '') {
								echo $defaultHead;
							} else {
								echo $customHead;
							}

							?></textarea><br /><span class="description">(Note: Saving an empty email message will restore the default message.<br /> Available email head variables: $pharmacyName, $loginURL, $siteURL, $siteName, $phoneAreaCode, $phoneNumber, $currentYear)</span></td>
					</tr>
					<tr>
						<th class="label"><label for="pw_emailFoot">Email foot</label></th>
						<td>
							<textarea style="width:600px;height:200px" id="pw_emailFoot" name="pw_emailFoot" rows="3" cols="15">
							<?php
							$defaultFoot = '			</td>
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="0" style="width:654px;border:none;margin:0 auto">
							<tr>
								<td><img src="' . THEME_URL . 'images/email/email-foot.png" style="float:left"></td>
							</tr>
							<tr>
								<td style="padding-left:40px;padding-right:40px;font-family:arial;color:#999;font-size:11px">Copyright &copy; $currentYear <a href="$siteURL" target="_blank" style="color: #999;">$siteName</a> | <a href="http://www.pharmacywire.com/" style="color: #999;">Powered by PharmacyWire</a></td>
							</tr>
						</table>
					</div>
					</body></html>';

							$customFoot = get_option('pw_emailFoot', $defaultFoot);

							if ($customFoot == '') {
								echo $defaultFoot;
							} else {
								echo $customFoot;
							}

							?></textarea><br /><span class="description">(Note: Saving an empty email message will restore the default message.<br /> Available email foot variables: $pharmacyName, $loginURL, $siteURL, $siteName, $phoneAreaCode, $phoneNumber, $currentYear)</span></td>
					</tr>
				</table>
				<h3>
					<legend>New Patient Email</legend>
				</h3>
				<table class="form-table">
					<tr>
						<th><label for="pw_newPatientEmail">Email message</label></th>
						<td>
							<textarea style="width:600px;height:200px" id="pw_newPatientEmail" name="pw_newPatientEmail" rows="3" cols="15">
							<?php
							$defaultMessage = 'Thank you for choosing $pharmacyName. To log in when visiting our site just click <a href="$loginURL">Login</a> and then enter your e-mail address and password.<br /><br />
					If you have any questions please feel free to contact us at $email or by phone at ($phoneAreaCode) $phoneNumber. <br />';

							$message = get_option('pw_newPatientEmail', $defaultMessage);

							if ($message == '') {
								echo $defaultMessage;
							} else {
								echo $message;
							}

							?></textarea><br /><span class="description">(Note: Saving an empty email message will restore the default message.<br /> Available message variables: $pharmacyName, $loginURL, $username, $password, $siteURL, $siteName, $phoneAreaCode, $phoneNumber, $currentYear)</span></td>
					</tr>
				</table>
			</fieldset>


			<p class="submit">
				<input type="submit" class="button-primary" id="btnSave" name="btnSave" value="Save" />
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