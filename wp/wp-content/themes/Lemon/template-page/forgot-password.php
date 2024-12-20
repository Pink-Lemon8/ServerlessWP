<?php if (!$_POST): ?>
  <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Forgot Password</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
      <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
        <form class="space-y-6" method="post" name="forgotPass" class="custom">
          <div>
            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Enter your email address</label>
            <div class="mt-2">
              <input id="email" name="username" type="email" autocomplete="email" required
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
            </div>
          </div>

          <div>
            <button type="submit" value="Submit" name="action"
              class="flex w-full justify-center rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">
              Send me an email
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
<?php
else:
    if ($_POST["action"] == 'Submit') {
      alert_and_link("An email was sent to you with instructions to reset your password. Please change your password and sign in.", home_url() . "/login","Sign in","green");
    } else {
      alert("There was an error with your submission. Please try again.");
    }
endif;
?>

