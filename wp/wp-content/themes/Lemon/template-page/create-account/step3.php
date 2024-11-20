<div id="part3" style="display: none">
    <h2 style="display: none">Personal Information</h2>
    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
        <!-- Date of Birth -->
        <div class="sm:grid sm:grid-cols-3 sm:gap-3">
            <!-- Day -->
            <div>
                <label for="dobDay" class="block text-lg font-medium leading-6 text-gray-900">Birth
                    Day</label>
                <div class="mt-2">
                    <input type="number" min="1" max="31" required name="BirthDate_DAY" id="dobDay"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                        value='<?= isset($_POST['BirthDate_DAY']) ? $_POST['BirthDate_DAY'] : '' ?>' />
                </div>
            </div>

            <!-- Month -->
            <div>
                <label for="dobMonth" class="block text-lg font-medium leading-6 text-gray-900">Birth
                    Month</label>
                <div class="mt-2">
                    <select name="BirthDate_MONTH" id="dobMonth"
                        class="block w-full pl-2 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
            </div>

            <!-- Year -->
            <div>
                <label for="dobYear" class="block text-lg font-medium leading-6 text-gray-900">Birth
                    Year</label>
                <div class="mt-2">
                    <input type="number" min="1900" required max="2023" name="BirthDate_YEAR" id="dobYear"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                        value='<?= isset($_POST['BirthDate_YEAR']) ? $_POST['BirthDate_YEAR'] : '' ?>' />
                </div>
            </div>
        </div>


        <!-- Gender -->
        <div>
            <label class="block text-lg font-medium leading-6 text-gray-900">Gender</label>
            <div class="mt-2">
                <select name="Sex" id="gender"
                    class="block pl-2 w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                    <option value="M" <?= (isset($_POST["Sex"]) && $_POST["Sex"] == 'M' ? "selected" : "") ?>>
                        Male
                    </option>
                    <option value="F" <?= (isset($_POST["Sex"]) && $_POST["Sex"] == 'F' ? "selected" : "") ?>>
                        Female</option>
                </select>
            </div>
        </div>

        <!-- Height -->
        <div class="sm:grid sm:grid-cols-2 sm:gap-3">
            <!-- Feet -->
            <div>
                <label for="Feet" class="block text-lg font-medium leading-6 text-gray-900">Height (in
                    feet)</label>
                <div class="mt-2">
                    <select name="HeightFeet" id="heightFeet"
                        class="block w-full pl-2 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                        <option value="3">3 feet</option>
                        <option value="4">4 feet</option>
                        <option value="5">5 feet</option>
                        <option value="6">6 feet</option>
                        <option value="7">7 feet</option>
                    </select>
                </div>
            </div>

            <!-- Inches -->
            <div>
                <label for="Inch" class="block text-lg font-medium leading-6 text-gray-900">Height (in
                    inches)</label>
                <div class="mt-2">
                    <select name="HeightInches" id="heightInches"
                        class="block w-full pl-2  rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                        <option value="0">0 inches</option>
                        <option value="1">1 inch</option>
                        <option value="2">2 inches</option>
                        <option value="3">3 inches</option>
                        <option value="4">4 inches</option>
                        <option value="5">5 inches</option>
                        <option value="6">6 inches</option>
                        <option value="7">7 inches</option>
                        <option value="8">8 inches</option>
                        <option value="9">9 inches</option>
                        <option value="10">10 inches</option>
                        <option value="11">11 inches</option>
                    </select>
                </div>
            </div>
        </div>



        <!-- Weight -->
        <div>
            <label for="weight" class="block text-lg font-medium leading-6 text-gray-900">Weight (in
                lbs)</label>
            <div class="mt-2">
                <input type="number" name="Weight" required id="weight"
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6"
                    placeholder="Weight" value='<?= isset($_POST['Weight']) ? $_POST['Weight'] : '' ?>' />
            </div>
        </div>

        <!-- Child resistant packaging -->
        <div class="sm:col-span-2">
            <label class="block text-lg font-medium leading-6 text-gray-900">Do you require child resistant
                packaging?</label>
            <div class="mt-2">
                <input type="radio" name="child_resistant_packaging" id="childPackagingYes" value="Yes"
                    class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] bg-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" /><label
                    for="childPackagingYes" class="ml-2">Yes</label>
                <input type="radio" name="child_resistant_packaging" id="childPackagingNo" value="No"
                    class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" /><label
                    for="childPackagingNo" class="ml-2">No</label>
            </div>
        </div>



        <!-- Call/Email for refills -->
        <div class="sm:col-span-2">
            <label class="block text-lg font-medium leading-6 text-gray-900">Call/Email for refills?</label>
            <div class="mt-2">
                <input type="radio" name="call_for_refills" id="refillsYes" value="Yes"
                    class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" /><label
                    for="refillsYes" class="ml-2">Yes</label>
                <input type="radio" name="call_for_refills" id="refillsNo" value="No"
                    class="form-radio h-4 w-4 text-[<?= MAIN_COLOR ?>] transition duration-150 ease-in-out" /><label
                    for="refillsNo" class="ml-2">No</label>
            </div>
        </div>
    </div>
    <div class="flex justify-center space-x-4 mt-4 mb-4">
        <button type="button" id="back2"
            class="rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>]">
            Back
        </button>

        <button type="submit" value="PL Create Account"
            class="rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>]"
            name="action">Submit</button>
    </div>
</div>