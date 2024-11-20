<?php
if(isset($_SESSION["PL_recent_order_result"])){
  $orderDone = $_SESSION["PL_recent_order_result"];
  $orderPostAction = $_SESSION["PL_recent_order_post_datas"];
  $cartDone = $orderDone->cart;
  $patientDone = $orderDone->patient;
  check_ref_link($orderDone->order_id,$cartDone->sub_total);
  //send_order_email(WebUser::getUserName(),"Your order is being processed! - " . get_bloginfo( 'name' ));
?>
 <div class="mx-auto max-w-3xl">
    <div class="max-w-xl">
      <h1 class="text-base font-medium cursor-pointer text-[<?= MAIN_COLOR ?>]">Thank you!</h1>
      <p class="mt-2 text-4xl font-bold tracking-tight">Your order is being processed!</p>
      <p class="mt-2 text-base text-gray-500">Your order <span class="text-[<?= MAIN_COLOR ?>]" >#<?=  $orderDone->order_id  ?></span> has been placed and will be shipped soon. Please send / upload your prescription if you haven't already.</p>
<?php /*
      <dl class="mt-12 text-sm font-medium">
        <dt class="text-gray-900">Tracking number</dt>
        <dd class="mt-2 cursor-pointer text-[<?= MAIN_COLOR ?>]">51547878755545848512</dd>
      </dl>*/
      ?>
    </div>

    <section aria-labelledby="order-heading" class="mt-10 border-t border-gray-200">
      <h2 id="order-heading" class="sr-only">Your order</h2>
      <h3 class="sr-only">Items</h3>
      <?php foreach ($cartDone->items as $key => $item):
        $product = get_post($_SESSION["PL_recent_order_cart"][$item->productID]['PK_product_id']);
      ?>
      <div class="flex space-x-6 border-b border-gray-200 py-10">
      <?= get_the_post_thumbnail($product->ID, 'medium', array('class' => 'h-20 w-20 flex-none rounded-lg bg-gray-100 object-cover object-center sm:h-40 sm:w-40')) ?>
        <div class="flex flex-auto flex-col">
          <div>
            <h4 class="font-medium text-gray-900">
            <a href="<?= get_permalink($product->ID); ?>" title="<?= $product->post_title ?>"  class="hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
            <?= isset($product) ? $product->post_title : "" ?>
            </a>
            </h4>
            <p class="mt-2 text-sm text-gray-600">
              <?= get_post_meta($product->ID, 'product_short_content', true) ?>
            </p>
          </div>
          <div class="mt-6 flex flex-1 items-end">
            <dl class="flex space-x-4 divide-x divide-gray-200 text-sm sm:space-x-6">
              <div class="flex">
                <dt class="font-medium text-gray-900">Quantity</dt>
                <dd class="ml-2 text-gray-700"><?= $item->quantity ?></dd>
              </div>
              <div class="flex pl-4 sm:pl-6">
                <dt class="font-medium text-gray-900">Price</dt>
                <dd class="ml-2 text-gray-700">$<?= $item->price ?></dd>
              </div>
              <?php if($item->prescriptionrequired == "1"): ?>
              <div class="flex pl-4 sm:pl-6">
                <p class="flex space-x-2 text-sm text-gray-700">
                  <svg class="h-5 w-5 flex-shrink-0 text-yellow-300" viewBox="0 0 20 20" fill="currentColor"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                      d="M19.64 16.36L11.53 2.3A1.85 1.85 0 0 0 10 1.21 1.85 1.85 0 0 0 8.48 2.3L.36 16.36C-.48 17.81.21 19 1.88 19h16.24c1.67 0 2.36-1.19 1.52-2.64zM11 16H9v-2h2zm0-4H9V6h2z"
                      clip-rule="evenodd" />
                  </svg>
                  <span>RX Required</span>
                </p>
              </div>
              <?php endif; ?>
            </dl>
            
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="sm:ml-40 sm:pl-6">
        <h3 class="sr-only">Your information</h3>

        <h4 class="sr-only">Addresses</h4>
        <dl class="grid grid-cols-2 gap-x-6 py-10 text-sm">
          <div>
            <dt class="font-medium text-gray-900">Shipping address</dt>
            <dd class="mt-2 text-gray-700">
              <address class="not-italic">
                <span class="block"><?= $cartDone->shipping_address1 ?></span>
                <span class="block"><?= $cartDone->shipping_address2 ?></span>
                <span class="block"><?= $cartDone->shipping_city.", ".$cartDone->shipping_region." ".$cartDone->shipping_regionCode ?></span>
              </address>
            </dd>
          </div>
          <div>
            <dt class="font-medium text-gray-900">Billing address</dt>
            <dd class="mt-2 text-gray-700">
              <address class="not-italic">
                <span class="block"><?= isset($orderPostAction["billing_address1"]) ? $orderPostAction["billing_address1"] : $cartDone->shipping_address1 ?></span>
                <span class="block"><?= isset($orderPostAction["billing_address2"]) ? $orderPostAction["billing_address2"] : $cartDone->shipping_address2 ?></span>
                <span class="block"><?= isset($orderPostAction["billing_city"]) ? $orderPostAction["billing_city"] : $cartDone->shipping_city ?>, 
                                    <?= isset($orderPostAction["billing_region"]) ? $orderPostAction["billing_region"] : $cartDone->shipping_region ?> 
                                    <?= isset($orderPostAction["billing_regionCode"]) ? $orderPostAction["billing_regionCode"] : $cartDone->shipping_regionCode ?>
                                    </span>
              </address>
            </dd>
          </div>
        </dl>

        <h4 class="sr-only">Payment</h4>
        <dl class="grid grid-cols-2 gap-x-6 border-t border-gray-200 py-10 text-sm">
          <div>
            <dt class="font-medium text-gray-900">Payment method</dt>
            <dd class="mt-2 text-gray-700">
              <p><?= $cartDone-> billing_creditCard_type ?></p>
              <p><span aria-hidden="true">•••• </span><span class="sr-only">Ending in </span><?= substr($cartDone->{'card-number'}, -4) ?></p>
            </dd>
          </div>
          <div>
            <dt class="font-medium text-gray-900">Shipping method</dt>
            <dd class="mt-2 text-gray-700">
              <p>Express</p>
            </dd>
          </div>
        </dl>

        <h3 class="sr-only">Summary</h3>

        <dl class="space-y-6 border-t border-gray-200 pt-10 text-sm">
          <div class="flex justify-between">
            <dt class="font-medium text-gray-900">Subtotal</dt>
            <dd class="text-gray-700">$<?= $cartDone->sub_total ?></dd>
          </div>
          <?php foreach ($cartDone-> coupons as $key => $coupon): ?>
          <div class="flex justify-between">
            <dt class="flex font-medium text-gray-900">
              Discount
              <span class="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-600"><?= $key ?></span>
            </dt>
            
              <dd class="text-gray-700"><?= $coupon->{"discount-human"} ?></dd>
          </div>
          <?php break; endforeach; ?>

          <div class="flex justify-between">
            <dt class="font-medium text-gray-900">Shipping</dt>
            <dd class="text-gray-700"><?= intval($cartDone->shippingfee) != 0 ? "$".$cartDone->shippingfee : "Free" ?></dd>
          </div>
          <div class="flex justify-between">
            <dt class="font-medium text-gray-900">Total</dt>
            <dd class="text-gray-900">$<?= $cartDone->order_total ?></dd>
          </div>
        </dl>
      </div>
    </section>
  </div>
  
  <!-- BEGIN: BSG GUARANTEE -->
		<script type="text/javascript" src="//guarantee-cdn.com/SealCore/api/gjs?SN=969068574&t=10"></script>
		<script type="text/javascript">
		if (window._GUARANTEE && _GUARANTEE.Loaded) {
			_GUARANTEE.Guarantee.order    = "<?=  $orderDone->order_id  ?>";
			_GUARANTEE.Guarantee.subtotal = "<?=  $cartDone->sub_total  ?>";
			_GUARANTEE.Guarantee.currency = "USD";
			_GUARANTEE.Guarantee.email    = "<?= WebUser::getUserName() ?>";
			_GUARANTEE.WriteGuarantee();
		}
		</script>
		<!-- END: BSG GUARANTEE -->
	
  
  
<?php
unset($_SESSION["PL_recent_order_post_datas"]);
unset($_SESSION["PL_recent_order_result"]);
unset($_SESSION["PL_recent_order_result_json"]);
unset($_SESSION["PL_recent_order_cart"]);

}
else{
    Utility_PageBase::redirect("/");
}
?>