<div class="bg-white pt-12 sm:pt-12">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:max-w-none">
          <div class="text-center">
            <h2
              class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl"
            >
              How To Order
            </h2>
            <p class="mt-4 text-lg font-semibold leading-10 text-gray-800">
              We process all orders within one business day, each package is
              shipping insulated and kept cool with refrigerated express
              shipping
            </p>
          </div>
          <div class="mx-auto max-w-7xl px-6 py-12 sm:py-12 lg:px-8 lg:py-12">
            <div class="mx-auto max-w-4xl divide-y divide-gray-900/10">
              <h2 class="text-2xl font-bold leading-10 tracking-tight text-gray-900">
                4 ways to order
              </h2>
              <?php
// Pre-calculate dynamic phone and fax numbers
$phone_area = get_option('pw_phone_area');
$phone = get_option('pw_phone');
$fax_area = get_option('pw_fax_area');
$fax = get_option('pw_fax');

// Titles and contents arrays
$PL_Drower_titles = ['Order Online', 'Order By Phone', 'Order By Fax', 'Order Refills'];
$PL_Drower_contents = [
    "To order online, simply search for your medications using the ‘Search for medication’ field above. Once you find the correct medication and strength, use the ‘Buy’ button to add it to your cart for checkout. If you have multiple medications, use the search icon at the top of the page to search for additional medications and add them to your cart.
    
    Proceed through the checkout process, entering your contact details, payment details and address information. You will need to enter your email address and create a password so that you can login to the site again later to re-order and/or check your order status. If you have already created an account, you can login using your email address and password during checkout to retrieve your contact details.
    
    For prescription medication orders, you will need to send a scanned (or quality digital photo) copy of your prescription to us. You can upload it directly to this website after you have placed your order, or fax it to us at 1-$fax_area-$fax. If your order does not contain any prescription medications, your order can be processed without any additional documentation. NOTE: Should your prescription not be on file for your order, a customer service agent may contact you directly to follow up with the missing prescription.",
    "To order by phone (toll free: 1-$phone_area-$phone), simply call and speak directly to one of our patient specialists. Our friendly staff will review your order details directly with you and arrange for you to send your prescription to us.",
    "To order by fax, print and fill out our Medication Order Form, and then fax it along with a copy of your prescriptions to our toll free fax number: 1-$fax_area-$fax.
    
    Note – if your prescription is not on file when we process your prescription order, our staff will contact you directly to ensure that you send it to us.",
    "Some medications are prescribed by your doctor with multiple fills. If we still have some refills left on file from your previous prescriptions, you are able to create your order online and submit it to us without sending a copy of your prescription. To do this, go to the refills page, and login with the account details you used to first create your account. Once you login, you will be able to place a refill by going to the ‘Re-Order Products’ link on your account profile page. From there, just select the medication and proceed through the checkout process as you did when you first created your order."
];

// Concatenation of shortcodes
$temp_shortcode_string = '';
foreach ($PL_Drower_titles as $key => $value) {
    $temp_shortcode_string .= 'title' . ($key + 1) . '="' . $value . '" content' . ($key + 1) . '="' . htmlentities($PL_Drower_contents[$key], ENT_QUOTES) . '" ';
}

// Output shortcode
echo do_shortcode('[PL_Drower ' . $temp_shortcode_string . ']');
?>
              
            </div>
          </div>
        </div>
                   
      </div>
    </div>
    
    <div class="text-center">
    <button type="button" onclick="window.location.href='/contact-us';"
      class="mt-16 rounded-full content-center bg-[<?= MAIN_COLOR ?>] hover:bg-[<?= MAIN_COLOR_HOVER ?>] md:px-8 px-16 lg:px-8 xl:px-8 2xl:px-8 py-3.5 text-md sm:text-2xl md:text-2xl lg:text-2xl xl:text-2xl 2xl:text-2xl font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">
      Contact Us
    </button>
  </div>
      
        <div class="mx-auto px-6 lg:px-8 pb-16 pt-8">
          <div class="mx-auto max-w-2xl lg:text-center">
            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Buy Insulin Online From Canada</p>
            <p class="mt-6 text-lg leading-8 text-gray-600">We believe in affordable pricing. Here are more reasons why you choose to do business with us:
                <?php bloginfo('name'); ?>.com is a leading online prescription referral service located in Winnipeg, Canada, founded to save you money on your medications while delivering professional, personal service.</p>
          </div>
          <div class="mx-auto mt-16 max-w-4xl sm:mt-20 lg:mt-24 lg:max-w-7xl">
            <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
              <div class="relative pl-16">
                <dt class="text-base font-semibold leading-7 text-gray-900">
                  <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-[<?= MAIN_COLOR ?>]">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 17l-5.878 3.59 1.598-6.7-5.23-4.48 6.865-.55L12 2.5l2.645 6.36 6.866.55-5.231 4.48 1.598 6.7z" fill="white" />
                    </svg>
                  </div>
                  Huge Savings on Prescriptions
                </dt>
                <dd class="mt-2 text-base leading-7 text-gray-600"><?php bloginfo('name'); ?> is a leading online prescription referral service located in Central Canada, founded to save you money on your medications while delivering professional, personal service.</dd>
              </div>
              <div class="relative pl-16">
                <dt class="text-base font-semibold leading-7 text-gray-900">
                    <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-[<?= MAIN_COLOR ?>]">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 17l-5.878 3.59 1.598-6.7-5.23-4.48 6.865-.55L12 2.5l2.645 6.36 6.866.55-5.231 4.48 1.598 6.7z" fill="white" />
                        </svg>
                      </div>
                      Safe, Secure & Convenient Insulin
                </dt>
                <dd class="mt-2 text-base leading-7 text-gray-600">Your safety, security and convenience are important to us. When you order your medications from <?php bloginfo('name'); ?> you can feel secure - your safety is our top priority.</dd>
              </div>
              <div class="relative pl-16">
                <dt class="text-base font-semibold leading-7 text-gray-900">
                    <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-[<?= MAIN_COLOR ?>]">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 17l-5.878 3.59 1.598-6.7-5.23-4.48 6.865-.55L12 2.5l2.645 6.36 6.866.55-5.231 4.48 1.598 6.7z" fill="white" />
                        </svg>
                      </div>
                      Promptness & Courteousness
                </dt>
                <dd class="mt-2 text-base leading-7 text-gray-600">We pride ourselves on providing prompt and courteous service tailored to your individual needs. We are at your service and look forward to helping you as quickly as we can.</dd>
              </div>
              <div class="relative pl-16">
                <dt class="text-base font-semibold leading-7 text-gray-900">
                    <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-[<?= MAIN_COLOR ?>]">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 17l-5.878 3.59 1.598-6.7-5.23-4.48 6.865-.55L12 2.5l2.645 6.36 6.866.55-5.231 4.48 1.598 6.7z" fill="white" />
                        </svg>
                      </div>
                      Shipped right to your door
                </dt>
                <dd class="mt-2 text-base leading-7 text-gray-600">We ship orders in a timely manner to ensure your package arrives to you safely and securely.

                    At <?php bloginfo('name'); ?>.com we take the proper precautions to ensure the integrity of all orders when you buy insulin online from Canada.</dd>
              </div>
            </dl>
          </div>
        </div>
    