<?php
$cart_ajax = json_decode(Cart::getCartJSON());
$cupon_object = new Model_Coupon();

// $allcart_raw = get_cart_raw();
// if(isset($_POST["pk_cart_remove"])){
//     $removed_package_id = $_POST["pk_cart_remove"];
//     if (isset($allcart_raw[$removed_package_id])){
//             $temp_pk_product_id = $allcart_raw[$removed_package_id]["PK_product_id"];
//         $remove_result = Cart::update($removed_package_id,0);
//         if($remove_result)
//             add_cart_overlay_alert(get_the_title($temp_pk_product_id), $content = 'Removed from your cart.',$temp_pk_product_id,"remove");
//         else
//         add_cart_overlay_alert(get_the_title($temp_pk_product_id), "Something is wrong. Try Again !!!", $temp_pk_product_id,"remove");
//     }
// }

// if(isset($_POST["pk_product_update"])){
//     $update_package_ids = $_POST["pk_product_update"];
//     foreach ($update_package_ids as $key => $item) {
//         if(isset($allcart_raw) && $allcart_raw[$key]["amount"] != $item){
//             $update_result = Cart::update($key,$item);
//             $temp_pk_product_id = $allcart_raw[$key]["PK_product_id"];
//             if($update_result)
//                 add_cart_overlay_alert(get_the_title($temp_pk_product_id), $content = 'Quantity updated.',$temp_pk_product_id,"update");
//             else
//                 add_cart_overlay_alert(get_the_title($temp_pk_product_id), "Something is wrong. Try Again !!!", $temp_pk_product_id,"updates");
            
//         }
//     }
// }
//var_dump($_SESSION["cart"]);

//$tt= "BI_TEST_Free_Ship";
// $result = $cupon_object->applyCoupon($tt);
// $result = $cupon_object->removeCouponSession($tt);

?>

<div class="bg-white">
        <div class="mx-auto max-w-2xl px-4 pb-24 pt-16 sm:px-6 lg:max-w-7xl lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Shopping Cart</span></h1>
            <form class="mt-12 lg:grid lg:grid-cols-12 lg:items-start lg:gap-x-12 xl:gap-x-16" method="post" action="checkout">
                <section aria-labelledby="cart-heading" class="lg:col-span-<?= count($cart_ajax->items) != 0 ? "7" : "12" ?>">
                    <h2 id="cart-heading" class="sr-only">Items in your shopping cart</h2>
                    <div id="pl_ajax_result"></div>
                    <ul role="list" class="divide-y divide-gray-200 border-b border-t border-gray-200">
                        <?php foreach (Cart::getListItems() as $cart):
                            $allcart_raw = get_cart_raw();
                            $quantity_fixed = packagequantity_fixer($cart->packagequantity);
                        ?>
                        <li id="list-<?=$cart->package_id?>" class="flex py-6 sm:py-10">
                            <div class="flex-shrink-0">
                                <?= get_the_post_thumbnail($allcart_raw[$cart->package_id]["PK_product_id"], 'small', array('class' => 'h-24 w-24 rounded-md object-cover object-center sm:h-48 sm:w-48')) ?>
                            </div>

                            <div class="ml-4 flex flex-1 flex-col justify-between sm:ml-6">
                                <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                    <div>
                                        <div class="flex justify-between">
                                            <h3 class="text-sm">
                                                <a target="_blank" href="<?= get_permalink($allcart_raw[$cart->package_id]["PK_product_id"]) ?>" title="<?= get_the_title($allcart_raw[$cart->package_id]["PK_product_id"]); ?>"  class="font-medium text-gray-700 hover:text-gray-800 focus:text-[<?= MAIN_COLOR_FOCUS ?>]"><?= get_the_title($allcart_raw[$cart->package_id]["PK_product_id"]); ?></a>
                                            </h3>
                                        </div>
                                        <div class="mt-1 flex text-sm">
                                            <p class="text-gray-500"><?= $quantity_fixed->string ?></p>
                                            <p class="ml-4 border-l border-gray-200 pl-4 text-gray-500"><?= $cart->strengthfreeform ?></p>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-gray-900">
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
                                    <div class="mt-4 sm:mt-0 sm:pr-9">
                                        <label for="quantity-0" class="sr-only">Quantity, Basic Tee</label>
                                        <div class="relative inline-block text-left w-16 mr-4">
                                            <input type="number" name="pk_product_update[<?= $cart->package_id ?>]" onkeypress="event.keyCode == 13 ? onchange(): void(0); return event.keyCode != 13;" onchange="pl_cart_change_quantity('<?= $cart->package_id ?>',this)" min="0" max="<?= isset($cart->maxqty) ? $cart->maxqty : "10" ?>" value="<?= $cart->amount ?>"
                                                class="block w-full px-3 py-2 rounded-md bg-white text-md font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" />
                                        </div>

                                        <div class="absolute right-0 top-0">
                                            <button type="submit" onclick="pl_remove_from_cart('<?= $cart->package_id ?>');return false;" name="pk_cart_remove" value="<?= $cart->package_id ?>"
                                                class="-m-2 inline-flex p-2 text-gray-400 hover:text-gray-500">
                                                <span class="sr-only">Remove</span>
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path
                                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php if($cart->prescriptionrequired == "1"):  ?>
                                <p class="mt-4 flex space-x-2 text-sm text-gray-700">
                                    <svg class="h-5 w-5 flex-shrink-0 text-yellow-300" viewBox="0 0 20 20"
                                        fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M19.64 16.36L11.53 2.3A1.85 1.85 0 0 0 10 1.21 1.85 1.85 0 0 0 8.48 2.3L.36 16.36C-.48 17.81.21 19 1.88 19h16.24c1.67 0 2.36-1.19 1.52-2.64zM11 16H9v-2h2zm0-4H9V6h2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>RX Required</span>
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-md font-semibold text-red-700 ring-1 ring-inset ring-red-600/10">Product of Canada</span>

                                </p>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; if (count($cart_ajax->items) == 0): ?>
                            <li id="list-empty" class="flex py-6 sm:py-10">
                            Empty
                            </li>
                        <?php endif; ?>
                    </ul>
                    <br>
                    <br>
                    <span id="_GUARANTEE_Kicker" name="_GUARANTEE_Kicker" type="Kicker Custom Mobile"></span>
                </section>
                
                <?php 
                if(count($cart_ajax->items) != 0):
                ?>                   
                <!-- Order summary -->
                <section id="pl_cart_summary" aria-labelledby="summary-heading"
                    class="shadow-lg mt-16 rounded-lg bg-gray-50 px-4 py-6 sm:p-6 lg:col-span-5 lg:mt-0 lg:p-8 lg:sticky lg:top-40 max-h-screen lg:overflow-y-auto">
                    <h2 id="summary-heading" class="text-lg font-medium text-gray-900">Order summary</h2>

                    <dl class="mt-6 space-y-4">

                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-600">Subtotal</dt>
                            <dd id="pl_cart_subtotal" class="text-sm font-medium text-gray-900"><?= "$".$cart_ajax->sub_total ?></dd>
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                            <dt class="flex items-center text-sm text-gray-600">Shipping estimate</dt>
                            <dd id="pl_cart_shipping" class="text-sm font-medium text-gray-900"><?= $cart_ajax->shipping_cost != 0 ? "$".$cart_ajax->shipping_cost : "Free" ?></dd>
                        </div>
                        <?php 
                        $coupon_count = 0;
                        $temp_coupon = null;
                        foreach ($cupon_object->getCouponSession() as $key => $value) {
                            $coupon_count++;
                            $temp_coupon = $value;
                        }
                        ?>
                        <div id="coupon-show" class = "<?= $coupon_count == 0 ? "hidden": "" ?>">
                            <div  class="flex items-center justify-between border-t border-gray-200 pt-4">
                                <dt class="flex items-center text-sm text-gray-600">
                                <button type="button" id="remove-copon" onclick="remove_copon_button(this)" data-copon="<?= $coupon_count != 0 ? $temp_coupon['coupon-code'] : ''  ?>" class="bg-white rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>]">
                                <span class="sr-only">Remove Coupon</span>
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                </button>
                                <div id="coupon-text" class="ml-2">
                                    <?= $coupon_count != 0 ? $temp_coupon["coupon-code"] : ""  ?>
                                </div>
                                </dt>
                                <dd  id="coupon-value" class="text-sm font-medium text-gray-900"><?= $coupon_count != 0 ? $temp_coupon["discount-human"] : ""  ?></dd>
                            </div>
                        </div>
                       
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                            <dt class="text-base font-medium text-gray-900">Order total</dt>
                            <dd id="pl_cart_total" class="text-base font-medium text-gray-900"><?= "$".$cart_ajax->total ?></dd>
                        </div>

                    </dl>

                    <div class="mt-6">
                        <button type="submit"
                            class="w-full rounded-md border border-transparent bg-[<?= MAIN_COLOR ?>] px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                    </div>

                    <div class="flex items-center justify-center mt-4">

                        <button id="toggleCouponBtn" type="button"
                            class="mt-4 rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">
                            Use a coupon
                        </button>
                        
                        <div id="couponField" class="flex justify-center mt-4 hidden">
                            <div class="flex justify-center mt-4">
                                <input id="copon_text_field" type="text" onkeypress="event.keyCode == 13 ? apply_copon_button(): void(0); return event.keyCode != 13;" placeholder="Enter coupon"
                                    class="border px-3.5 py-2.5 rounded-md bg-white text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            </div>
                            <div class="flex justify-center mt-4">
                                <button type="button" onclick="apply_copon_button()"
                                    class="apply-coupon-btn ml-2 -mt-4 rounded-md bg-[<?= MAIN_COLOR ?>] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>]">
                                    Apply
                                </button>
                            </div>
                        </div>
                        <div id="loading-copon" class="flex justify-center mt-4 hidden">
                            <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-600 fill-[<?= MAIN_COLOR ?>]" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </section>
                 <?php endif; ?>           
            </form>
        </div>
    </div>