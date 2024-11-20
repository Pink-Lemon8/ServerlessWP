<?php

$cart_ajax = json_decode(Cart::getCartJSON());
$cupon_object = new Model_Coupon();
$_SESSION["PL_recent_order_cart"] = get_cart_raw();
$model_country = new Model_Country();
$USAStates =$model_country->getRegionsByCountry("usa");
if (!WebUser::isLoggedIn()):
    require("login.php");
elseif(Cart::getItemCount() > 0):
    $result = null;
    if(isset($_POST["pl_comfirm_order"]) && $_POST["pl_comfirm_order"] == "submit_order"){
        setOrderData();
        $pwjCart = new PW_JSON_Cart();
        $submitOrderResponseJson = $pwjCart->submitOrder();
        $result = json_decode($submitOrderResponseJson);
        if($result->status == "success"){
            $_SESSION["PL_recent_order_post_datas"] = $_POST;
            $_SESSION["PL_recent_order_result"] = $result;
            $_SESSION["PL_recent_order_result_json"] = $submitOrderResponseJson;
            Utility_PageBase::redirect("/thank-you");
        }
        elseif($result->status == "failure"){
            foreach ($result->messages as $value) {
                alert($value->content);
                break;
            }
        }
    }
?>

            <div class="mx-auto max-w-2xl lg:max-w-none">
                <h1 class="sr-only">Checkout</h1>

                <form class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-8 xl:gap-x-12" method="post">
                <input type="hidden" id="billing_useShippingAddress" name="billing_useShippingAddress" value="<?= isset($_POST['billing_useShippingAddress']) ? $_POST['billing_useShippingAddress'] : 'yes' ?>">
                    <div class="min-h-[1vw] overflow-y-auto pr-4">
                        

                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                            <h3 class="font-semibold text-lg text-[<?= MAIN_COLOR ?>] sm:col-span-2">Shipping Address</h3>

                            <div class="sm:col-span-2">
                                <label for="shippingStreet" class="block text-lg font-medium leading-6 text-gray-900">Street
                                    Address</label>
                                <div class="mt-2">
                                    <input type="text" name="shipping_address1" id="shippingStreet"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Street Address" Required
                                        value='<?= isset($_POST['shipping_address1']) ? $_POST['shipping_address1'] : '' ?>' />
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="shippingApt" class="block text-lg font-medium leading-6 text-gray-900">Apartment or
                                    Suite Number (Optional)</label>
                                <div class="mt-2">
                                    <input type="text" name="shipping_address2" id="shippingApt"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Apartment or Suite Number"
                                        value='<?= isset($_POST['shipping_address2']) ? $_POST['shipping_address2'] : '' ?>' />
                                </div>
                            </div>

                            <div>
                                <label for="shippingCity" class="block text-lg font-medium leading-6 text-gray-900">City</label>
                                <div class="mt-2">
                                    <input type="text" name="shipping_city" id="shippingCity"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="City" Required
                                        value='<?= isset($_POST['shipping_city']) ? $_POST['shipping_city'] : '' ?>' />
                                </div>
                            </div>

                            <div>
                                <label for="shippingState" class="block text-lg font-medium leading-6 text-gray-900">State</label>
                                <div class="mt-2">
                                    <select name="shipping_region" id="shippingState" Required
                                        class="block w-full pl-2 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">

                                        <?php

                                        foreach ($USAStates as $region) {
                                            echo '<option value="' . $region->region_code . '" ' . (isset($_POST["shipping_region"]) && $_POST["shipping_region"] == $region->region_code ? "selected" : "") . ' >' . $region->region_name . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="shippingCountry"
                                    class="block text-lg font-medium leading-6 text-gray-900">Country</label>
                                <div class="mt-2">
                                    <input type="text" id="shippingCountry"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Country" value="USA" disabled />
                                    <input type="hidden" name="shipping_country" value="USA" Required >
                                </div>
                            </div>

                            <div>
                                <label for="shippingZip" class="block text-lg font-medium leading-6 text-gray-900">Zip Code</label>
                                <div class="mt-2">
                                    <input type="text" name="shipping_regionCode" id="shippingZip"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Zip Code"
                                        value='<?= isset($_POST['shipping_regionCode']) ? $_POST['shipping_regionCode'] : '' ?>' />
                                </div>
                            </div>

                            <div>
                                <label for="shipping_phoneAreaCode" class="block text-lg font-medium leading-6 text-gray-900">
                                    Phone Area Code
                                </label>
                                <div class="mt-2">
                                    <input type="text" name="shipping_phoneAreaCode" id="shipping_phoneAreaCode"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Area Code" Required
                                        value='<?= isset($_POST['shipping_phoneAreaCode']) ? $_POST['shipping_phoneAreaCode'] : '' ?>' />
                                </div>
                            </div>

                            <div>
                                <label for="shipping_phone" class="block text-lg font-medium leading-6 text-gray-900">
                                    Phone Number
                                </label>
                                <div class="mt-2">
                                    <input type="text" name="shipping_phone" id="shipping_phone"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Phone Number" Required
                                        value='<?= isset($_POST['shipping_phone']) ? $_POST['shipping_phone'] : '' ?>' />
                                </div>
                            </div>

                            <div class="sm:col-span-2 flex items-center">
                                <input type="checkbox" name="UseShipping" id="sameAsShipping"
                                    <?= isset($_POST['billing_useShippingAddress']) ? '' : 'checked' ?>
                                    <?= isset($_POST['billing_useShippingAddress']) && $_POST["billing_useShippingAddress"] == 'yes' ? 'checked' : '' ?>
                                    class="focus:ring-<?= MAIN_COLOR ?>-900 h-5 w-5 text-[<?= MAIN_COLOR ?>] border-gray-300 rounded" />
                                <label for="sameAsShipping" class="ml-2 -mt-4 text-lg leading-5 text-gray-900">Billing address same
                                    as shipping
                                    address?</label>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3" id="billingAddress"
                            <?= !isset($_POST['billing_useShippingAddress']) ? 'style="display : none"' : '' ?>
                            <?= isset($_POST['billing_useShippingAddress']) && $_POST["billing_useShippingAddress"] == 'yes' ? 'style="display : none"' : '' ?>>

                            <h3 class="sm:col-span-2 mt-4 font-semibold">Billing Address</h3>

                            <div class="sm:col-span-2">
                                <label for="billingStreet" class="block text-lg font-medium leading-6 text-gray-900">Street
                                    Address</label>
                                <div class="mt-2">
                                    <input type="text" name="billing_address1" id="billingStreet"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Street Address"
                                        value='<?= isset($_POST['billing_address1']) ? $_POST['billing_address1'] : '' ?>' />
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="billingApt" class="block text-lg font-medium leading-6 text-gray-900">Apartment or Suite
                                    Number (Optional)</label>
                                <div class="mt-2">
                                    <input type="text" name="billing_address2" id="billingApt"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Apartment or Suite Number"
                                        value='<?= isset($_POST['billing_address2']) ? $_POST['billing_address2'] : '' ?>' />
                                </div>
                            </div>

                            <div>
                                <label for="billingCity" class="block text-lg font-medium leading-6 text-gray-900">City</label>
                                <div class="mt-2">
                                    <input type="text" name="billing_city" id="billingCity"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="City"
                                        value='<?= isset($_POST['billing_city']) ? $_POST['billing_city'] : '' ?>' />
                                </div>
                            </div>

                            <div>
                                <label for="billingState" class="block text-lg font-medium leading-6 text-gray-900">State</label>
                                <div class="mt-2">
                                    <select name="billing_region" id="billingState"
                                        class="block w-full pl-2 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                                        <?php

                                        foreach ($USAStates as $region) {   
                                            echo '<option value="' . $region->region_code . '" ' . (isset($_POST["billing_region"]) && $_POST["billing_region"] == $region->region_code ? "selected" : "") . ' >' . $region->region_name . '</option>';
                                        }

                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="billingCountry"
                                    class="block text-lg font-medium leading-6 text-gray-900">Country</label>
                                <div class="mt-2">
                                    <input type="text" id="billingCountry"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Country" disabled />
                                    <input type="hidden" name="billing_country" value="USA">
                                </div>
                            </div>

                            <div>
                                <label for="billingZip" class="block text-lg font-medium leading-6 text-gray-900">Zip Code</label>
                                <div class="mt-2">
                                    <input type="text" name="billing_regionCode" id="billingZip"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Zip Code"
                                        value='<?= isset($_POST['billing_regionCode']) ? $_POST['billing_regionCode'] : '' ?>' />
                                </div>
                            </div>
                            <div>
                                <label for="billing_phoneAreaCode" class="block text-lg font-medium leading-6 text-gray-900">
                                    Phone Area Code
                                </label>
                                <div class="mt-2">
                                    <input type="text" name="billing_phoneAreaCode" id="billing_phoneAreaCode"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Area Code"
                                        value='<?= isset($_POST['billing_phoneAreaCode']) ? $_POST['billing_phoneAreaCode'] : '' ?>' />
                                </div>
                            </div>

                            <div>
                                <label for="billing_phone" class="block text-lg font-medium leading-6 text-gray-900">
                                    Phone Number
                                </label>
                                <div class="mt-2">
                                    <input type="text" name="billing_phone" id="billing_phone"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                        placeholder="Phone Number"
                                        value='<?= isset($_POST['billing_phone']) ? $_POST['billing_phone'] : '' ?>' />
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 mt-5">
                            <section aria-labelledby="payment-heading" class="mt-10">
                                <h2 id="payment-heading" class="text-lg font-semibold text-[<?= MAIN_COLOR ?>]">Payment details
                                </h2>

                                <div class="mt-6 grid grid-cols-3 gap-x-4 gap-y-6 sm:grid-cols-4">
                                    <div class="col-span-3 sm:col-span-4">
                                        <label for="name-on-card" class="block text-md font-medium text-black">Name on
                                            card</label>
                                        <div class="mt-1">
                                            <input type="text" id="name-on-card" name="name-on-card"
                                                autocomplete="cc-name" Required
                                                class="block w-full border-2 py-1 px-2 rounded-md border-[<?= MAIN_COLOR ?>] shadow-sm focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-lg">
                                        </div>
                                    </div>

                                    <div class="col-span-3 sm:col-span-4">
                                        <label for="card-number" class="block text-md font-medium text-black">Card
                                            number</label>
                                        <div class="relative mt-1">
                                            <input type="text" id="card-number" name="card-number"
                                                autocomplete="cc-number" Required
                                                class="block w-full border-2 py-1 px-2 rounded-md border-[<?= MAIN_COLOR ?>] shadow-sm focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-lg">
                                                <span class="absolute top-0 -mt-3 right-0 cc-types">
                                                    <i class="cc-types__img cc-types__img--visa w-[4rem]"></i>
		                                            <i class="cc-types__img cc-types__img--mastercard w-[4rem]"></i>
                                                </span>
                                        </div>
                                    </div>

                                    <div class="col-span-2 sm:col-span-3">
                                        <label for="expiration-date"
                                            class="block text-md font-medium text-black">Expiration
                                            date (MM/YY)</label>
                                        <div class="mt-1">
                                            <input type="text" max="5" name="expiration-date" id="expiration-date"
                                                autocomplete="cc-exp" Required
                                                class="block w-full border-2 py-1 px-2 rounded-md border-[<?= MAIN_COLOR ?>] shadow-sm focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-lg">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="cvc" class="block text-md font-medium text-gray-700">CVC</label>
                                        <div class="mt-1">
                                            <input type="text" name="cvc" id="cvc" max="3" autocomplete="csc" Required="true"
                                                class="block w-full border-2 py-1 px-2 rounded-md border-[<?= MAIN_COLOR ?>] shadow-sm focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-lg">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="border-t border-gray-200 mt-5">
                            <section aria-labelledby="extra-heading" class="mt-10">
                                <h2 id="shipping-heading" class="text-lg font-semibold text-[<?= MAIN_COLOR ?>]">Prescription and
                                    packaging
                                </h2>

                                <div class="mt-4 w-full">
                                    <label class="block text-lg font-medium leading-6 text-gray-900">How will you send
                                        in your
                                        Prescription?</label>
                                    <div class="flex flex-row mt-5 w-full">
                                        <div class="flex items-center">
                                            <input type="radio" name="rx_forwarding" id="pl_rx_submission_onfile" Required
                                                value="onfile"
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_rx_submission_onfile" class="ml-2 -mt-4">RX on file</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="rx_forwarding" id="pl_rx_submission_upload" Required
                                                value="upload"
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_rx_submission_upload" class="ml-2 -mt-4">Upload</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="rx_forwarding" id="pl_rx_submission_doctor" Required
                                                value="doctor"
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_rx_submission_doctor" class="ml-2 -mt-4">Call my doctor</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="rx_forwarding" id="pl_rx_submission_email" Required
                                                value="email"
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_rx_submission_email" class="ml-2 -mt-4">E-mail</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 w-full">
                                    <label class="block text-lg font-medium leading-6 text-gray-900">Do you require
                                        counselling
                                        from a pharmacist for the medications you are taking?</label>
                                    <div class="flex flex-row mt-5 w-full">
                                        <div class="flex items-center">
                                            <input type="radio" name="contact_patient" id="pl_contact_patient_Yes"
                                                value="Yes" Required
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_contact_patient_Yes" class="ml-2 -mt-4">Yes</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="contact_patient" id="pl_contact_patient_No"
                                                value="No" Required
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_contact_patient_No" class="ml-2 -mt-4">No</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 w-full">
                                    <label class="block text-lg font-medium leading-6 text-gray-900">Do you require
                                        child
                                        resistant packaging?</label>
                                    <div class="flex flex-row mt-5 w-full">
                                        <div class="flex items-center">
                                            <input type="radio" name="child_resistant_packaging" Required
                                                id="pl_child_resistant_packaging_Yes" value="Yes"
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_child_resistant_packaging_Yes" class="ml-2 -mt-4">Yes</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="child_resistant_packaging" Required
                                                id="pl_child_resistant_packaging_No" value="No"
                                                class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" />
                                            <label for="pl_child_resistant_packaging_No" class="ml-2 -mt-4">No</label>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <div>
                                 <label for="order_comments" class="block text-sm font-medium leading-6 text-gray-900">Order comments (optional)</label>
                                 <div class="mt-2">
                                 <textarea id="order_comments" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:border-[<?= MAIN_COLOR_FOCUS ?>]" placeholder="Order comments (optional) here..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-2 flex items-center mt-5">
                            <input type="checkbox" name="agree" id="pl_agree" value="true" Required
                                class="focus:ring-<?= MAIN_COLOR ?>-900 h-5 w-5 text-[<?= MAIN_COLOR ?>] border-gray-300 rounded" />
                            <label for="pl_agree" class="ml-2 -mt-4 text-lg leading-5 text-gray-900">
                                I have read and agree to the Terms of Use and Privacy Policy.
                            </label>
                        </div>

                    </div>
                    
                    <div class="mt-10 lg:mt-0 lg:sticky lg:top-40 lg:overflow-y-auto shadow-lg rounded-lg bg-gray-50 px-4 py-6 sm:p-6">
                        <h2 class="text-lg font-semibold text-[<?= MAIN_COLOR ?>]">Order summary</h2>

                        <div class="mt-4 rounded-lg bg-gray-50">
                            <h3 class="sr-only">Items in your cart</h3>
                            <ul role="list" class="divide-y divide-gray-200">

                            <?php foreach (Cart::getListItems() as $cart):
                            $allcart_raw = get_cart_raw();
                            $quantity_fixed = packagequantity_fixer($cart->packagequantity);
                            ?>
                                <li class="flex px-4 py-6 sm:px-6">
                                    <div class="flex-shrink-0">
                                    <?= get_the_post_thumbnail($allcart_raw[$cart->package_id]["PK_product_id"], 'small', array('class' => 'w-20 rounded-md')) ?>
                                    </div>

                                    <div class="ml-6 flex flex-1 flex-col">
                                        <div class="flex">
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-md">
                                                    <a target="_blank" href="<?= get_permalink($allcart_raw[$cart->package_id]["PK_product_id"]) ?>"
                                                        title="<?= get_the_title($allcart_raw[$cart->package_id]["PK_product_id"]); ?>"
                                                        class="font-medium text-gray-700 hover:text-gray-800 focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                                                        <?= get_the_title($allcart_raw[$cart->package_id]["PK_product_id"]); ?> x <?= $cart->amount ?>
                                                        </a>
                                                </h4>
                                                <p class="mt-1 text-md text-gray-500"><?= $quantity_fixed->string ?> | <?= $cart->strengthfreeform ?></p>
                                            </div>

                                            <div class="ml-4 flow-root flex-shrink-0">
                                            <?php if($cart->prescriptionrequired == "1"):  ?>
                                            <p class="mt-4 flex space-x-2 text-sm text-gray-700">
                                                <svg class="h-5 w-5 flex-shrink-0 text-yellow-300" viewBox="0 0 20 20"
                                                    fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M19.64 16.36L11.53 2.3A1.85 1.85 0 0 0 10 1.21 1.85 1.85 0 0 0 8.48 2.3L.36 16.36C-.48 17.81.21 19 1.88 19h16.24c1.67 0 2.36-1.19 1.52-2.64zM11 16H9v-2h2zm0-4H9V6h2z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span>RX Required</span>
                                            </p>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex flex-1 items-end justify-between pt-2">
                                            <p class="mt-1 text-md font-medium text-gray-900">
                                            <?php
                                            $temp_tier = tier_prices_fixer($cart->tier_prices);
                                            $temp_price = '';
                                            for ($i=0; $i < count($temp_tier->quantity); $i++) { 
                                               $temp_price = "$".$temp_tier->price[$i]." per ".$quantity_fixed->unit.".";
                                               if($temp_tier->quantity[$i] == floatval($cart->amount)) break;
                                            }
                                            echo $temp_price;
                                            ?>
                                            </p>
                                        </div>
                                    </div>

                                </li>
                                <?php endforeach; ?>

                            </ul>
                            <dl class="space-y-6 border-t border-gray-200 px-4 py-6 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <dt class="text-md">Subtotal</dt>
                                    <dd class="text-md font-medium text-gray-900"><?= "$".$cart_ajax->sub_total ?></dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-md">Shipping estimate</dt>
                                    <dd class="text-md font-medium text-gray-900"><?= $cart_ajax->shipping_cost != 0 ? "$".$cart_ajax->shipping_cost : "Free" ?></dd>
                                </div>
                                <?php
                                    $coupon_count = 0;
                                    $temp_coupon = null;
                                    foreach ($cupon_object->getCouponSession() as $key => $value) {
                                        $coupon_count++;
                                        $temp_coupon = $value;
                                    }
                                if ($coupon_count >0):
                                ?> 
                                <div class="flex items-center justify-between">
                                    <dt class="text-md">Coupon</dt>
                                    <dd class="text-md font-medium text-gray-900">(<?= $temp_coupon["coupon-code"]  ?>) <?= $coupon_count != 0 ? $temp_coupon["discount-human"] : ""  ?></dd>
                                </div>
                                <?php endif; ?>
                                <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                                    <dt class="text-base font-medium">Total Due</dt>
                                    <dd class="text-base font-medium text-gray-900"><?= "$".$cart_ajax->total ?></dd>
                                </div>
                            </dl>

                            

                            <div class="border-t border-gray-200 px-4 py-6 sm:px-6">
                                <p class="mt-4 mb-4 text-center text-md text-gray-500 sm:mt-0 sm:text-left">You won't be
                                    charged
                                    until your order is shipped.</p>
                                <button type="submit" name="pl_comfirm_order" value="submit_order"
                                    class="w-full rounded-md border border-transparent bg-[<?= MAIN_COLOR ?>] px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>]">Confirm
                                    order</button>
                                    <br>
                                    <br>
                                    <div class="flex justify-center">
                                        <span class="flex justify-center items-center" id="_GUARANTEE_Kicker" name="_GUARANTEE_Kicker" type="Kicker Custom Mobile"></span>
                                    </div>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
<script src="https://unpkg.com/imask"></script>
<script src="<?= get_template_directory_uri() . '/js/from-masker.js' ?>"></script>
<?php endif; ?>