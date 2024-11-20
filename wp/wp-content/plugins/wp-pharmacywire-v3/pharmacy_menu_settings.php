<script type="text/javascript">
	function refreshCache() {
		document.adminForm.btnRefreshCache.disabled = true;
		document.adminForm.btnSave.disabled = true;
		document.adminForm.isRefreshCache.value = 1;
		document.adminForm.setAttribute('action', 'admin.php?page=<?php echo PWIRE_PLUGIN_FOLDERNAME; ?>/wp-pharmacywire.php');
		document.adminForm.submit();
	}
</script>
<div class="wrap">
	<?php include 'options-head.php';
	$configuration = new Utility_Configuration(); ?>

	<div class="settingForm">
		<a href="http://www.pharmacywire.com" class="pharmacywire-settings pharmacywire-plugin-header" target="pharmacywire">PharmacyWire Plugin</a>

		<form action="options.php" method="post" id="adminForm" name="adminForm">
			<?php settings_fields('pharmacy-settings-group'); ?>
			<?php do_settings_sections('pharmacy-settings-group'); ?>
			<fieldset>
				<legend><b>Connection Information</b></legend>
				<table>
					<tr>
							<?php
							if (get_option('pw_is_dev_site_connection')) {
								$checked = 'checked="checked"';
							} else {
								$checked = '';
							}
							?>
							<td class="label"><input type="checkbox" name="pw_is_dev_site_connection" id="pw_is_dev_site_connection" <?php echo $checked; ?> /></td>
							<td><label for="pw_is_dev_site_connection">Allow Non-SSL connection (for dev sites)</label></td>
						</tr>
					<tr>
						<td class="label"><label for="pw_url">URL</label></td>
						<td><input type="text" name="pw_url" id="pw_url" size="70" value="<?php echo get_option('pw_url'); ?>" spellcheck="off" autocomplete="off" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_user_id">UserID</label></td>
						<td><input type="text" name="pw_user_id" id="pw_user_id" size="30" value="<?php echo get_option('pw_user_id'); ?>" spellcheck="off" autocomplete="off" /></td>
					</tr>
					<tr>
						<td class="label"><label for="pw_passkey">Passkey</label></td>
						<td><input type="password" name="pw_passkey" id="pw_passkey" size="30" value="<?php if (!empty(get_option('pw_passkey'))) { echo '**************'; } ?>" spellcheck="off" autocomplete="off" /></td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend><b>Memcached Configuration</b></legend>
				<table class="memcache-config">
					<tr>
						<td class="server-label label"><label for="pw_memcached_servers">Server</label></td>
						<td class="server-address"><input type="text" name="pw_memcached_servers" id="pw_memcached_servers" size="90" spellcheck="off" autocomplete="off" value="<?php echo get_option('pw_memcached_servers'); ?>" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
							<?php
							$memcached = new Utility_Memcached();
							if ($memcached->memcache && !empty(get_option('pw_memcached_servers'))) :
								$statsByServerArray = $memcached->getStats();
								if (is_array($statsByServerArray)) {
									print '<div class="memcached-stats success"><div class="stats"><h3 class="stats-title">Active Memcached Servers</h3>';
									foreach ($statsByServerArray as $server => $statsArray) {
										print '<div class="memcached-server"><h3 class="server">Server: ' . $server . '</h3>';
										$memcacheHitRatio = 0;
										if ($statsArray['cmd_get'] > 0) {
											$memcacheHitRatio = round($statsArray['get_hits'] / $statsArray['cmd_get'] * 100, 0);
										}
										print '<p class="message"><b>Memcache Hit Ratio: ' . $memcacheHitRatio . '%</p></div>';
									}
								} else {
									print '<div class="memcached-stats error"><div class="stats"><h3 class="stats-title">No Active Memcached Servers</h3>';
									print '<p class="message">Invalid Memcached server provided or memcached is not running.</p>';
								}
								print "</div></div>";
							elseif (!empty((get_option('pw_memcached_servers')))) :
								print '<div class="memcached-stats error"><div class="stats"><h3 class="stats-title">Memcached PHP Extension Inactive</h3>';
								print '<p class="message">You need to install the <a href="https://www.php.net/manual/en/book.memcached.php" target="_blank">PHP Memcached</a> extension. </div>';
							endif; ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend><b>Plugin Update License</b><br />
					Please request a license from PharmacyWire (<a href="mailto:support@pharmacywire.com">support@pharmacywire.com</a>) if you don't have one.</legend>
				<table>
					<tr>
						<td class="label"><label for="pw_update_license">Key</label></td>
						<td><input type="password" name="pw_update_license" id="pw_update_license" size="90" value="<?php if (!empty(get_option('pw_update_license'))) { echo '**************'; } ?>" /></td>
					</tr>
				</table>
			</fieldset>
			<p class="submit">
				<input type="submit" class="button-primary button" id="btnSave" name="btnSave" value="Save" />
				<?php if (get_option('pw_url') && get_option('pw_user_id') && get_option('pw_passkey')) { ?>
					<input type="button" class="button-primary button" id="btnRefreshCache" name="btnRefreshCache" value="Refresh Catalog" onclick="refreshCache()" />
					<input type="hidden" id="isRefreshCache" name="isRefreshCache" value="0" />
				<?php } ?>
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

<?php
if (isset($_POST['isRefreshCache']) && $_POST['isRefreshCache'] == 1) {
	$status = buildcache();
	displayMessage($status);
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