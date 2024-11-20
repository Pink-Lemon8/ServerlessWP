<?php 
$order = new Model_Patient();
$order_request = $order->getRecentOrders(WebUser::getUserID());
if($order_request->status == "success"):
$orderInfo = $order_request->orders;


if (isset($_POST["pk_cart_add"])) {
  $add_cart_id = $_POST["package_id"];
  $product_id = $_POST["product_id"];
  $add_cart_quantity = $_POST["package_quantity"];
  $product_t = $_POST["product_title"];
  $result = add_to_cart($add_cart_id, $add_cart_quantity,$product_id);
  if ($result) {
    add_cart_overlay_alert($product_t, "Added to your cart.", $product_id);
    $_POST = array();
  } else
    add_cart_overlay_alert($product_t, "Something is wrong. Try Again !!!", $product->ID);
}

?>
    <div class="mx-auto">

      <div class="mt-12 space-y-8 sm:mt-8">
    
        <?php foreach ($orderInfo as $key => $order): ?>
        <section aria-labelledby="4376-heading">
          <div class="space-y-1 md:flex md:items-baseline md:space-x-4 md:space-y-0">
            <h2 id="4376-heading" class="text-lg font-medium text-gray-900 md:flex-shrink-0">Order #<?= $order->id ?></h2>
            <div class="space-y-5 sm:flex sm:items-baseline sm:justify-between sm:space-y-0 md:min-w-0 md:flex-1">
              <p class="text-sm font-medium text-gray-500">Ordered on <?= $order->created ?></p>
            </div>
          </div>
            <div class="-mb-6 mt-6 flow-root divide-y divide-gray-200 border-t border-gray-200">
            <?php 
            
            foreach ($order->items as $drug):
              $product_post = get_post_by_meta_key($drug->id);
              $product_post = count($product_post) > 0 ? $product_post[0] : [];
              $package_info = get_package_info($drug->id);
              $drug_info = get_drug_info($package_info->drug_id);
              $package_quantity = packagequantity_fixer($package_info->packagequantity);              
            ?>

            <div class="py-6 sm:flex">
              <div class="flex space-x-4 sm:min-w-0 sm:flex-1 sm:space-x-6 lg:space-x-8">
              <div class="flex">
                <?= get_the_post_thumbnail($product_post->ID, 'medium', array('class' => 'h-20 w-20 flex-none rounded-lg bg-gray-100 object-cover object-center sm:h-40 sm:w-40')) ?>
                <div class="flex flex-auto flex-col">
                  <div>
                    <h4 class="font-medium text-gray-900 f32">
                    <a href="<?= get_permalink($product_post->ID); ?>" title="<?= $product_post->post_title ?>" target="_blank"  class="hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                    <?= isset($product_post) ? $product_post->post_title : "" ?>
                    </a>
                    <i
                      class="-mt-1 flag <?= isset($package_info -> vendor_country_code) ? strtolower($package_info -> vendor_country_code) : "can" ?>"></i>  
                    </h4>
                    <p class="mt-2 text-sm text-gray-600">
                      <?= get_post_meta($product_post->ID, 'product_short_content', true) ?>
                    </p>
                  </div>
                  <div class="mt-6 flex flex-1 items-end">
                    <dl class="flex space-x-4 divide-x divide-gray-200 text-sm sm:space-x-6">
                      <div class="flex">
                        <dt class="font-medium text-gray-900">Strength</dt>
                        <dd class="ml-2 text-gray-700"><?= $drug_info->strengthfreeform ?></dd>
                      </div>
                      <div class="flex pl-4 sm:pl-6">
                        <dt class="font-medium text-gray-900">Quantity</dt>
                        <dd class="ml-2 text-gray-700"><?= $drug->quantity ?></dd>
                      </div>
                      <div class="flex pl-4 sm:pl-6">
                        <dt class="font-medium text-gray-900">Price</dt>
                        <dd class="ml-2 text-gray-700">$<?= $drug->unitprice ?></dd>
                      </div>
                      <?php if($drug_info->prescriptionrequired == "1"): ?>
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

              </div>
              <div class="mt-6 space-y-4 sm:ml-6 sm:mt-0 sm:w-40 sm:flex-none">
                <form method="POST" name="pk_cart_add" value="add">
                
                    <input type="hidden" id="product_title" name="product_title" value="<?= isset($product_post) ? $product_post->post_title : "" ?>">

                    <input type="hidden" id="package_id" name="package_id" value="<?php echo $drug->id; ?>">

                     <input type="hidden" id="product_id" name="product_id" value="<?php echo $product_post->ID; ?>">

                    <input type="hidden" id="package_quantity" name="package_quantity" value="<?php echo $drug->quantity; ?>">
  

                    <div class="mt-10">
                    <button type="submit" name="pk_cart_add" value="add"
                        class="flex w-full items-center justify-center rounded-md border border-transparent bg-[<?= MAIN_COLOR ?>] px-8 py-3 text-base font-medium text-white hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-offset-2 focus:ring-offset-gray-50">Re-ORDER
                        <button>
                    </div>
                </form>

              </div>
            </div>
            <?php endforeach; ?>

          </div>
        </section>
        <?php endforeach; ?>
      </div>
    </div>

<?php endif; ?>