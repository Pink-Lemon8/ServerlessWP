<div id="part1">
                <h2 style="display: none">Contact and Login Information</h2>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">

                    <div>
                        <label for="firstName" class="block text-lg font-semibold leading-6 text-gray-900">First
                            Name</label>
                        <div class="mt-2">
                            <input type="text" name="firstName" id="firstName"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                placeholder="Your First Name"
                                value='<?= isset($_POST['firstName']) ? $_POST['firstName'] : '' ?>' />
                        </div>
                    </div>

                    <div>
                        <label for="lastName" class="block text-lg font-semibold leading-6 text-gray-900">Last Name</label>
                        <div class="mt-2">
                            <input type="text" name="lastName" id="lastName"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                placeholder="Your Last Name"
                                value='<?= isset($_POST['lastName']) ? $_POST['lastName'] : '' ?>' />
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-lg font-semibold leading-6 text-gray-900">
                            Email
                        </label>
                        <div class="mt-2">
                            <input type="email" name="Username" id="email"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                placeholder="Your Email"
                                value='<?= isset($_POST['Username']) ? $_POST['Username'] : '' ?>' />
                        </div>
                    </div>

                    <div>
                        <label for="confirm_email" class="block text-lg font-semibold leading-6 text-gray-900">
                            Confirm Email
                        </label>
                        <div class="mt-2">
                            <input type="email" name="ConfirmUsername" id="confirm_email"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                placeholder="Confirm Your Email" />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-lg font-semibold leading-6 text-gray-900">Password</label>
                        <div class="mt-2">
                            <input type="password" name="Password" id="password"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                placeholder="Password" />
                        </div>
                    </div>

                    <div>
                        <label for="confirmPassword" class="block text-lg font-semibold leading-6 text-gray-900">Confirm
                            Password</label>
                        <div class="mt-2">
                            <input type="password" name="ConfirmPassword" id="confirmPassword"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                placeholder="Confirm Password" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label for="phoneAreaCode" class="block text-lg font-semibold leading-6 text-gray-900">
                                Area Code
                            </label>
                            <div class="mt-2">
                                <input type="text" name="phoneAreaCode" id="Areacode"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                    placeholder="Area Code"
                                    value='<?= isset($_POST['phoneAreaCode']) ? $_POST['phoneAreaCode'] : '' ?>' />
                            </div>
                        </div>
                        <div>
                            <label for="Phone Number" class="block text-lg font-semibold leading-6 text-gray-900">
                                Phone Number
                            </label>
                            <div class="mt-2">
                                <input type="text" name="phone" id="Phonenumber"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                    placeholder="Phone Number"
                                    value='<?= isset($_POST['phone']) ? $_POST['phone'] : '' ?>' />
                            </div>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label for="email" class="block text-lg font-semibold leading-6 text-gray-900">
                                FAX Area Code (Optional)
                            </label>
                            <div class="mt-2">
                                <input type="text" name="AreaCodeFax" id="Faxareacode"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                    placeholder="Area Code"
                                    value='<?= isset($_POST['AreaCodeFax']) ? $_POST['AreaCodeFax'] : '' ?>' />
                            </div>
                        </div>
                        <div>
                            <label for="Phone Number" class="block text-lg font-semibold leading-6 text-gray-900">
                                FAX Number (Optional)
                            </label>
                            <div class="mt-2">
                                <input type="text" name="Fax" id="Faxnumber"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                                    placeholder="Phone Number" value='<?= isset($_POST['Fax']) ? $_POST['Fax'] : '' ?>' />
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="next1"
                    class="mt-4 mb-4 rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-lg font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] mx-auto block">
                    Next
                </button>

            </div>