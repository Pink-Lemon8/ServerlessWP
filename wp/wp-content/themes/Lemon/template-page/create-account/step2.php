<div id="part2" style="display: none;">
    <h2 style="display: none">Shipping and Billing Address</h2>

    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
        <h3 class="font-semibold text-lg text-gray-900 sm:col-span-2">Shipping Address</h3>

        <div class="sm:col-span-2">
            <label for="shippingStreet" class="block text-lg font-medium leading-6 text-gray-900">Street
                Address</label>
            <div class="mt-2">
                <input type="text" name="shipping_address1" id="shippingStreet"
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                    placeholder="Street Address"
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
                    placeholder="City"
                    value='<?= isset($_POST['shipping_city']) ? $_POST['shipping_city'] : '' ?>' />
            </div>
        </div>

        <div>
            <label for="shippingState" class="block text-lg font-medium leading-6 text-gray-900">State</label>
            <div class="mt-2">
                <select name="shipping_region" id="shippingState"
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
            <label for="shippingCountry"
                class="block text-lg font-medium leading-6 text-gray-900">Country</label>
            <div class="mt-2">
                <input type="text" id="shippingCountry"
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                    placeholder="Country" value="USA" disabled />
                <input type="hidden" name="shipping_country" value="USA">
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
                    placeholder="Area Code"
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
                    placeholder="Phone Number"
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

    <div class="flex justify-center space-x-4 mt-4 mb-4">
        <button type="button" id="back1"
            class="rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>]">
            Back
        </button>

        <button type="button" id="next2"
            class="rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>]">
            Next
        </button>
    </div>
</div>