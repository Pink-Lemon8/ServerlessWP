<?php

$changePassPage = new Page_ChangePass();
$error = null;
if (isset($_POST["PL_action"])) {

  $valueCurrentPass = (string) $changePassPage->_getRequest('oldpass');
  $valueNewPass = (string) $changePassPage->_getRequest('newpass');
  $valueConfirmPassword = (string) $changePassPage->_getRequest('confirmpassword');

  if (empty($valueCurrentPass)) {
    $error = 'Current Password is a required field.';
  }
  if (empty($valueNewPass)) {
    $error = 'New Password is a required field.';
  }
  if (strlen($valueNewPass) < 6) {
    $error = 'Password must be greater than 7 characters.';
  }
  if (empty($valueConfirmPassword)) {
    $error = 'Verify password is a required field.';
  } elseif ($valueConfirmPassword != $valueNewPass) {
    $error = 'Verify Password not equal New Password.';
  }

  if ($error == null) {
    $checkOldPassword = $changePassPage->_checkPassword();
    // if ($checkOldPassword->authenticated == 'true') {
      $result = $changePassPage->_updatePassword();
    // } else {
      // $error = "Current Password is wrong.";
    // }
  }

}

?>

<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Change account password</h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">

    <?php

    if (isset($_POST["PL_action"]) && $error != null) {
      alert($error);
    } else if (isset($_POST["PL_action"]) && $error == null) {
      alert_and_link('Password Changed.',network_site_url('/') . "account",$url_text="Click here sign in with your new password",$color='green',$icon="success");

    }
    ?>

    <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
      <form class="space-y-6" name="login" method="post">

        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-[<?= MAIN_COLOR ?>]">Current Password</label>
          <div class="mt-2">
            <input id="oldpass" name="oldpass" type="password" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium leading-6 text-[<?= MAIN_COLOR ?>]">New Password</label>
          <div class="mt-2">
            <input id="newpass" name="newpass" type="password" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium leading-6 text-[<?= MAIN_COLOR ?>]">Confirm New Password</label>
          <div class="mt-2">
            <input id="confirmpassword" name="confirmpassword" type="password" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
          </div>
        </div>
        <div>
          <button type="submit" name="PL_action" value="PL"
            class="flex w-full justify-center rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">
            Update
          </button>
        </div>
      </form>

    </div>
  </div>
</div>