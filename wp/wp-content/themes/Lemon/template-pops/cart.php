<div class="relative z-10" role="dialog" aria-modal="true">
        <!--
      Background backdrop, show/hide based on modal state.
  
      Entering: "ease-out duration-300"
        From: "opacity-0"
        To: "opacity-100"
      Leaving: "ease-in duration-200"
        From: "opacity-100"
        To: "opacity-0"
    -->
        <div id="backdrop" class="fixed sm:inset-0 sm:block sm:bg-gray-500 sm:bg-opacity-75 sm:transition-opacity">
        </div>



        <div id="modal"
            class="fixed inset-0 z-10 overflow-y-auto opacity-100 transform scale-100 sm:transition-opacity sm:transition-transform">


            <div class="flex min-h-full items-stretch justify-center text-center sm:items-center sm:px-6 lg:px-8">
                <!--
          Modal panel, show/hide based on modal state.
  
          Entering: "ease-out duration-300"
            From: "opacity-0 scale-105"
            To: "opacity-100 scale-100"
          Leaving: "ease-in duration-200"
            From: "opacity-100 scale-100"
            To: "opacity-0 scale-105"
        -->
                <div class="flex w-full max-w-3xl transform text-left text-base transition sm:my-8">
                    <form
                        class="relative flex w-full flex-col overflow-hidden bg-white pb-8 pt-6 sm:rounded-lg sm:pb-6 lg:py-8">
                        <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8">
                            <h2 class="text-2xl font-semibold text-gray-900">Your Cart</h2>
                            <button type="button" class="text-gray-400 hover:text-gray-500" id="closeModalBtn">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <section aria-labelledby="cart-heading">
                            <h2 id="cart-heading" class="sr-only">Items in your shopping cart</h2>

                            <ul role="list" class="divide-y divide-gray-200 px-4 sm:px-6 lg:px-8">
                                <li class="flex py-8 text-sm sm:items-center">
                                    <img src="https://tailwindui.com/img/ecommerce-images/shopping-cart-page-04-product-03.jpg"
                                        alt="Front of zip tote bag with white canvas, black canvas straps and handle, and black zipper pulls."
                                        class="h-24 w-24 flex-none rounded-lg border border-gray-200 sm:h-32 sm:w-32">
                                    <div
                                        class="ml-4 grid flex-auto grid-cols-1 grid-rows-1 items-start gap-x-5 gap-y-3 sm:ml-6 sm:flex sm:items-center sm:gap-0">
                                        <div class="row-end-1 flex-auto sm:pr-6">
                                            <h3 class="font-medium text-gray-900">
                                                <a href="#">Zip Tote Basket</a>
                                            </h3>
                                            <p class="mt-1 text-gray-500">White and black</p>
                                        </div>
                                        <p
                                            class="row-span-2 row-end-2 font-medium text-gray-900 sm:order-1 sm:ml-6 sm:w-1/3 sm:flex-none sm:text-right">
                                            $140.00</p>
                                        <div class="flex items-center sm:block sm:flex-none sm:text-center">
                                            <label for="quantity-0" class="sr-only">Quantity, Zip Tote Basket</label>
                                            <div class="relative inline-block text-left w-32 mr-4 mt-4">
                                                <input type="number" name="quantity" min="1" value="1"
                                                    class="block w-full px-3 py-2 rounded-md bg-white text-md font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" />
                                            </div>



                                            <button type="button"
                                                class="ml-4 font-medium text-red-600 hover:text-red-500 sm:ml-0 sm:mt-2">
                                                <span>Remove</span>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <li class="flex py-8 text-sm sm:items-center">
                                    <img src="https://tailwindui.com/img/ecommerce-images/shopping-cart-page-04-product-01.jpg"
                                        alt="Salmon orange fabric pouch with match zipper, gray zipper pull, and adjustable hip belt."
                                        class="h-24 w-24 flex-none rounded-lg border border-gray-200 sm:h-32 sm:w-32">
                                    <div
                                        class="ml-4 grid flex-auto grid-cols-1 grid-rows-1 items-start gap-x-5 gap-y-3 sm:ml-6 sm:flex sm:items-center sm:gap-0">
                                        <div class="row-end-1 flex-auto sm:pr-6">
                                            <h3 class="font-medium text-gray-900">
                                                <a href="#">Throwback Hip Bag</a>
                                            </h3>
                                            <p class="mt-1 text-gray-500">Salmon</p>
                                        </div>
                                        <p
                                            class="row-span-2 row-end-2 font-medium text-gray-900 sm:order-1 sm:ml-6 sm:w-1/3 sm:flex-none sm:text-right">
                                            $90.00</p>
                                        <div class="flex items-center sm:block sm:flex-none sm:text-center">
                                            <label for="quantity-1" class="sr-only">Quantity, Throwback Hip Bag</label>
                                            <div class="relative inline-block text-left w-32 mr-4 mt-4">
                                                <input type="number" name="quantity" min="1" value="1"
                                                    class="block w-full px-3 py-2 rounded-md bg-white text-md font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" />
                                            </div>


                                            <button type="button"
                                                class="ml-4 font-medium text-red-600 hover:text-red-500 sm:ml-0 sm:mt-2">
                                                <span>Remove</span>
                                            </button>
                                        </div>
                                    </div>
                                </li>

                                <div class="flex items-center mt-4">
                                    <button id="toggleCouponBtn" type="button"
                                        class="mt-4 rounded-md bg-green-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                        Use a coupon
                                    </button>
                                    <div id="couponField" class="flex items-center ml-4 mt-4 hidden">
                                        <input type="text" placeholder="Enter coupon"
                                            class="border px-3 py-2 rounded-md bg-white text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        <button type="button"
                                            class="ml-2 rounded-md bg-green-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600">
                                            Apply coupon
                                        </button>
                                    </div>
                                </div>


                            </ul>


                        </section>

                        <section aria-labelledby="summary-heading" class="mt-4 sm:px-6 lg:px-8">
                            <div class="bg-gray-50 p-6 sm:rounded-lg sm:p-8">
                                <h2 id="summary-heading" class="sr-only">Order summary</h2>

                                <div class="flow-root">
                                    <dl class="-my-4 divide-y divide-gray-200 text-sm">
                                        <div class="flex items-center justify-between py-4">
                                            <dt class="text-gray-600">Subtotal</dt>
                                            <dd class="font-medium text-gray-900">$262.00</dd>
                                        </div>
                                        <div class="flex items-center justify-between py-4">
                                            <dt class="text-gray-600">Shipping</dt>
                                            <dd class="font-medium text-gray-900">$5.00</dd>
                                        </div>
                                        <div class="flex items-center justify-between py-4">
                                            <dt class="text-gray-600">Tax</dt>
                                            <dd class="font-medium text-gray-900">$53.40</dd>
                                        </div>
                                        <div class="flex items-center justify-between py-4">
                                            <dt class="text-base font-medium text-gray-900">Order total</dt>
                                            <dd class="text-base font-medium text-gray-900">$320.40</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </section>

                        <div class="mt-8 flex justify-end px-4 sm:px-6 lg:px-8">
                            <button type="submit"
                                class="rounded-md border border-transparent bg-green-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-gray-50">Continue
                                to Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            function toggleModal() {
                const modal = document.getElementById('modal');
                const backdrop = document.getElementById('backdrop');

                if (modal.classList.contains('opacity-100')) {
                    // Hide modal
                    modal.classList.remove('opacity-100', 'scale-100');
                    modal.classList.add('opacity-0', 'scale-105');
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');

                    setTimeout(() => {
                        modal.style.display = 'none';
                        backdrop.style.display = 'none';
                    }, 300); // Hide after the transition ends
                } else {
                    // Show modal
                    modal.style.display = 'block';
                    backdrop.style.display = 'block';

                    setTimeout(() => {
                        modal.classList.remove('opacity-0', 'scale-105');
                        modal.classList.add('opacity-100', 'scale-100');
                        backdrop.classList.remove('opacity-0');
                        backdrop.classList.add('opacity-100');
                    }, 10); // Give a short delay for the styles to apply before adding transition classes
                }
            }

            // Attach event listeners
            document.getElementById('backdrop').addEventListener('click', toggleModal);

            // Assuming you have a button with ID 'openModalBtn' to open the modal
            // document.getElementById('openModalBtn').addEventListener('click', toggleModal);

            // For closing the modal, assuming your close button has an ID 'closeModalBtn'
            document.getElementById('closeModalBtn').addEventListener('click', toggleModal);

        });

        function toggleDropdown(buttonElement) {
            // First, close any other open dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                if (dropdown !== buttonElement.parentElement.nextElementSibling) {
                    dropdown.classList.add('hidden');
                }
            });

            // Toggle the clicked dropdown
            const dropdownContent = buttonElement.parentElement.nextElementSibling;
            dropdownContent.classList.toggle('hidden');
        }

        function selectOption(optionElement) {
            const buttonLabel = optionElement.closest('.relative').querySelector('.dropdown-label');
            buttonLabel.textContent = optionElement.textContent;
            optionElement.closest('.dropdown-content').classList.add('hidden');
        }

        // Global click listener to close dropdown if clicked outside
        document.addEventListener('click', function (event) {
            const isInsideDropdownButton = event.target.closest('.dropdown-button');
            const isInsideDropdownContent = event.target.closest('.dropdown-content');

            if (!isInsideDropdownButton && !isInsideDropdownContent) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });

        document.getElementById('toggleCouponBtn').addEventListener('click', function () {
            const couponField = document.getElementById('couponField');
            if (couponField.classList.contains('hidden')) {
                couponField.classList.remove('hidden');
            } else {
                couponField.classList.add('hidden');
            }
        });

    </script>