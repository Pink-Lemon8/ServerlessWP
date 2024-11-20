<?php

if (isset($_POST["sendEmail"]) && $_POST["sendEmail"] == "yes") {
    $firstName = sanitize_text_field($_POST['first-name']);
    $lastName = sanitize_text_field($_POST['last-name']);
    $email = sanitize_text_field($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);
    $tempSubject = 'New ' . get_bloginfo() . ' message from ' . $firstName . ' ' . $lastName . ' (' . $email . ')';

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = 'From: ' . $firstName . ' ' . $lastName . ' <' . $email . '>';
    $to = SMTP_EMAIL;

    $mailContent = "<p>You have received a new message from your website contact form.</p>";
    $mailContent .= "<p><strong>First Name:</strong> $firstName</p>";
    $mailContent .= "<p><strong>Last Name:</strong> $lastName</p>";
    $mailContent .= "<p><strong>Email:</strong> $email</p>";
    $mailContent .= "<p><strong>Message:</strong></p>";
    $mailContent .= "<p>$message</p>";

    if(isset($_POST["captcha"]) && $_POST["captcha"] == strval($_SESSION["captcha"]))
    if (wp_mail($to, $tempSubject, $mailContent, $headers)) {
        echo '<script>alert("Thank you! Your message has been sent successfully.");</script>';
    } else {
        echo '<script>alert("Sorry, something went wrong. Please try again later.");</script>';
    }
    else
        echo '<script>alert("Sorry, Please re-enter captcha.");</script>';
    $_SESSION["captcha"] = null;    

}   

$captcha = rand(1000, 9999);
if(!isset($_SESSION["captcha"]))
    $_SESSION["captcha"] = $captcha;
$imageData = base64_encode(file_get_contents(home_url('/captcha.php?text='.$_SESSION["captcha"])));
$src = 'data: '. "image/png".';base64,'.$imageData;

?>

<div class="isolate bg-white px-6 py-24 sm:py-32 lg:px-8">
    <div class="absolute inset-x-0 top-[-10rem] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[-20rem]"
        aria-hidden="true">
        <div class="relative left-1/2 -z-10 aspect-[1155/678] w-[36.125rem] max-w-none -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[<?= MAIN_COLOR_CLOUD ?>] to-[<?= SECOND_COLOR_CLOUD ?>] opacity-30 sm:left-[calc(50%-40rem)] sm:w-[72.1875rem]"
            style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)">
        </div>
    </div>

    <div class="mx-auto max-w-2xl text-center">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Contact us</h2>
        <p class="mt-2 text-lg leading-8 text-black">Do you have a medical question or concern? We can help!</p>
        <p class="mt-2 text-lg leading-8 font-semibold text-black">Fill out the email form below or call us at <a
                href="tel:+1<?php echo get_option('pw_phone_area') . get_option('pw_phone'); ?>">1-<?php echo get_option('pw_phone_area') . "-" . get_option('pw_phone'); ?>
            </a></p>
    </div>
    <form method="post" action="" class="mx-auto mt-16 max-w-xl sm:mt-20">
        <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
            <div>
                <label for="first-name" class="block text-sm font-semibold leading-6 text-gray-900">First name</label>
                <div class="mt-2.5">
                    <input type="text" name="first-name" id="first-name" autocomplete="given-name" required
                        class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                </div>
            </div>
            <div>
                <label for="last-name" class="block text-sm font-semibold leading-6 text-gray-900">Last name</label>
                <div class="mt-2.5">
                    <input type="text" name="last-name" id="last-name" autocomplete="family-name" required
                        class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                </div>
            </div>
            <div class="sm:col-span-2">
                <label for="email" class="block text-sm font-semibold leading-6 text-gray-900">Email</label>
                <div class="mt-2.5">
                    <input type="email" name="email" id="email" autocomplete="email" required
                        class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                </div>
            </div>
            <div class="sm:col-span-2">
                <label for="message" class="block text-sm font-semibold leading-6 text-gray-900">Message</label>
                <div class="mt-2.5">
                    <textarea name="message" id="message" rows="4"
                        class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"></textarea>
                </div>
            </div>
            <div class="flex gap-x-4 sm:col-span-2">
                <div class="flex h-6 items-center">
                    <input type="checkbox" required name="pl_policy" id="pl_policy" class="mt-4 -mr-2" value="yes" />
                </div>
                <label class="text-sm leading-6 text-gray-600" for="pl_policy">
                    By selecting this, you agree to our
                    <a href="/privacy-policy" target="_blank"
                        class="font-semibold text-[<?= MAIN_COLOR ?>] hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">privacy&nbsp;policy</a>.
                </label>
            </div>
            <div class="flex flex-row items-center justify-center space-x-2">
                <img src="<?= $src ?>" class="rounded-md" width="100" height="48" />
                <input type="text" name="captcha" id="captcha" required
                    class="block w-full rounded-md border-0 mb-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
            </div>
        </div>
        <span class="text-sm font-bold text-gray-400">* Please enter captcha</span>
        <div class="mt-10">
            <button type="submit" id="submitButton" name="sendEmail" value="yes"
                class="block w-full rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Let's
                talk</button>
        </div>
    </form>
</div>