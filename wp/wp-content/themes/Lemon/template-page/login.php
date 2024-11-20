<?php

$loginPage = new Page_Login();
$result = null;
if (isset($_POST["PL_action"])) {
  $result = $loginPage->processLogin(1,"/account");
  $resultObject = json_decode($result);
  if(WebUser::isLoggedIn())
  echo "<script type='text/javascript'>
        window.location=document.location.href;
        </script>";
}
?>

<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Sign in to your account</h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">

    <?php
    if (isset($_POST["PL_action"]) && !WebUser::isLoggedIn())
      alert("Invalid email and/or password entered.");
    ?>

    <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
      <form class="space-y-6" name="login" method="post">
        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
          <div class="mt-2">
            <input id="email" name="username" type="email" autocomplete="email" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
          <div class="mt-2">
            <input id="password" name="password" type="password" autocomplete="current-password" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm leading-6">
            <a href="create-account/"
              class="font-semibold text-[<?= MAIN_COLOR ?>] hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_HOVER ?>]">Create
              account</a>
          </div>
          <div class="text-sm leading-6">
            <a href="forgot-password/"
              class="font-semibold text-[<?= MAIN_COLOR ?>] hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_HOVER ?>]">Forgot
              password?</a>
          </div>
        </div>



        <div>
          <button type="submit" name="PL_action" value="PL" onclick="login_loading();" required
            class="flex w-full justify-center rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Sign
            in</button>
        </div>
      </form>

    </div>
  </div>
</div>