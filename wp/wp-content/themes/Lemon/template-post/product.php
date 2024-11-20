<?php
$product = get_post();
$package = package_dp_array_fixer(get_post_meta($product->ID, "product_dp", true));
$package_main_info = get_package_info($package->first);
$short_content = get_post_meta($product->ID, 'product_short_content', true);
$product_how_to_use = get_post_meta($product->ID, 'product_how_to_use', true);
$is_store = get_post_meta($product->ID, 'product_is_store', true);
$is_store = strtolower($is_store) == "yes" ? 'yes' : 'no';
$ingredients = get_drug_ingredients($package_main_info ? $package_main_info->drug_id : null);
$is_prescriptionrequired = get_drug_info($package_main_info->drug_id)->prescriptionrequired == "1" ? true : false;
if (isset($_POST["pk_cart_add"])) {
  $add_cart_id = $_POST["package_id"];
  $add_cart_quantity = $_POST["package_quantity"];
  $result = add_to_cart($add_cart_id, $add_cart_quantity,$product->ID);
  if ($result) {
    add_cart_overlay_alert($product->post_title, "Added to your cart.", $product->ID);
    $_POST = array();
  } else
    add_cart_overlay_alert($product->post_title, "Something is wrong. Try Again !!!", $product->ID);
}
?>

<div class="bg-white">
  <div
    class="mx-auto px-4 py-7 sm:px-6 sm:py-7 lg:grid xl:grid-cols-[7fr,3fr] lg:grid-cols-[3fr,2fr] lg:gap-x-8 lg:px-8">

    <div class="lg:self-end">
      <nav aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-2">
          <li>
            <div>
              <a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-400 hover:text-gray-500 focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd"
                    d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z"
                    clip-rule="evenodd" />
                </svg>
                <span class="sr-only">Home</span>
              </a>
            </div>
          </li>
          <li>
            <div class="flex items-center text-sm">
              <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                class="mr-2 h-5 w-5 flex-shrink-0 text-gray-300">
                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
              </svg>
              <a href="/products" class="font-medium text-gray-500 hover:text-gray-900 focus:text-[<?= MAIN_COLOR_FOCUS ?>]">Products</a>
            </div>
          </li>
          <li>
            <div class="flex items-center text-sm">
              <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                class="mr-2 h-5 w-5 flex-shrink-0 text-gray-300">
                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
              </svg>
              <p class="font-medium text-gray-500 hover:text-gray-900 cursor-pointer focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                <?= the_title() ?>
              </p>
            </div>
          </li>
        </ol>
      </nav>

      <div class="mt-4">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
          <?= the_title() ?>
        </h1>
        <p class="mt-2 text-sm text-gray-500">
          <?php
          foreach ($ingredients as $ingredient)
            echo $ingredient->ingredient_name . " ";
          ?>
        </p>
      </div>

      <section aria-labelledby="information-heading" class="mt-4">
        <h2 id="information-heading" class="sr-only">Product information</h2>


        <div class="mt-7 space-y-6">
          <p class="text-base text-gray-500 indent-3">
            <?= $short_content ?>
          </p>
        </div>

        <div class="mt-6 flex items-center">
          <?php if ($is_store == 'yes'): ?>
            <svg class="h-5 w-5 flex-shrink-0 text-[<?= MAIN_COLOR ?>]" viewBox="0 0 20 20" fill="currentColor"
              aria-hidden="true">
              <path fill-rule="evenodd"
                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                clip-rule="evenodd" />
            </svg>
            <p class="ml-2 text-sm text-gray-500">In stock and ready to ship.</p>
          <?php else: ?>
            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                clip-rule="evenodd"></path>
            </svg>
            <p class="ml-2 text-sm text-gray-500">Not in stock yet.</p>
          <?php endif; ?>
        </div>
        <?php if( $is_prescriptionrequired == true): ?>
        <div class="mt-6 flex items-center">
          <p class="mt-4 flex space-x-2 text-sm text-gray-700">
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
      </section>
    </div>


    <div class="mt-10 lg:col-start-2 lg:row-span-2 lg:mt-0 lg:self-center">
      <div class="aspect-h-1 aspect-w-1 overflow-hidden rounded-lg">
        <?= get_the_post_thumbnail($product->ID, 'large', array('class' => 'h-full w-full object-cover object-center')) ?>
      </div>
    </div>
          
    <?php 
    $valid_package_count = count_valid_product($package->list);
    if($valid_package_count > 0): ?>
      <div class="mt-10 lg:col-start-1 lg:row-start-2 lg:self-start xl:w-full xl:max-w-full">
        <section aria-labelledby="options-heading">

          <h2 id="options-heading" class="sr-only">Product options</h2>

          <fieldset>
            <div class="mt-1 grid grid-cols-1 gap-6 xl:grid-cols-<?= $valid_package_count % 2 == 0 ? "2" : "1" ?>">

              <?php
              $count = 0;
              foreach ($package->list as $package_id):
                $package_main_info = get_package_info($package->first);
                $package_info = get_package_info($package_id);
                if (!$package_info || $package_info->public_viewable != 1)
                  continue;
                $count++;
                $package_quantity = packagequantity_fixer($package_info->packagequantity);
                $tiers = get_package_tier($package_id);
                $drug = get_drug_info($package_info->drug_id);
                ?>
                <div id="<?= $package_id ?>"
                  class="packages relative flex-1 cursor-pointer rounded-lg p-4 focus:outline-none sm:w-auto ring-[<?= MAIN_COLOR ?>] ring-offset-2 <?= $count == 1 ? "ring-2" : "" ?>">

                  <p id="size-choice-0-label" class="flex text-md font-semibold items-center text-gray-900 f32">
                  <i
                      class="pr-10
                      flag <?= isset($package_info -> vendor_country_code) ? strtolower($package_info -> vendor_country_code) : "can" ?>"></i>  
                  Product strength &amp;
                    quantity</p>
                  <div class="relative inline-block text-left w-full mr-4 mt-4">
                    <div>
                      <button type="button"
                        class="inline-flex rounded-md bg-white px-3 py-2 text-md font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dropdown-button w-full focus:ring-2 focus:ring-inset focus:ring-<?= MAIN_COLOR ?>-600"
                        aria-expanded="false" aria-haspopup="true">
                        <span class="dropdown-label">
                          <?= $drug->strengthfreeform . " - " . ($package_quantity->value * 1) . " " . $package_quantity->unit . " - $" . floatval($package_info->price) ?>
                        </span>
                        <svg class="ml-auto h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                          aria-hidden="true">
                          <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd"></path>
                        </svg>
                      </button>
                    </div>
                    <div
                      class="dropdown-content absolute left-0 z-10 mt-2 w-full origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden">
                      <?php foreach ($tiers as $tier): ?>
                        <div class="py-1" role="none">
                          <a onclick="select_package('<?= $package_id ?>','<?= floatval($tier->quantity) ?>');"
                            class="text-gray-700 font-semibold block px-4 py-2 text-md text-black hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] hover:bg-gray-50"
                            role="menuitem" tabindex="-1" id="menu-item-0">
                            <?= $drug->strengthfreeform . " - " . ($package_quantity->value * floatval($tier->quantity)) . " " . $package_quantity->unit . " - $" . (floatval($tier->price) * floatval($tier->quantity)) ?>
                          </a>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <span class="absolute top-4 right-4 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                    <?=  $drug->generic == 1 ? "Generic" : "Brand" ?>
                  </span>
                  <div class="pointer-events-none absolute -inset-px rounded-lg border-2" aria-hidden="true"></div>
                </div>

              <?php endforeach; ?>

            </div>
          </fieldset>

          <div class="mt-4">
            <a href="/contact-us/" class="group inline-flex text-sm text-gray-500 hover:text-gray-700 focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
              <span>Need help? Contact us</span>
              <svg class="ml-2 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-500" viewBox="0 0 20 20"
                fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.94 6.94a.75.75 0 11-1.061-1.061 3 3 0 112.871 5.026v.345a.75.75 0 01-1.5 0v-.5c0-.72.57-1.172 1.081-1.287A1.5 1.5 0 108.94 6.94zM10 15a1 1 0 100-2 1 1 0 000 2z"
                  clip-rule="evenodd" />
              </svg>
            </a>
          </div>
          <span id="_GUARANTEE_Kicker" name="_GUARANTEE_Kicker" type="Kicker Custom Mobile"></span>
          <form method="POST" name="pk_cart_add" value="add">
            <input type="hidden" id="package_id" name="package_id" value="<?= $package_main_info->package_id ?>">
            <input type="hidden" id="package_quantity" name="package_quantity" value="1">
            <div class="mt-10">
              <button type="submit" name="pk_cart_add" value="add"
                class="flex w-full items-center justify-center rounded-md border border-transparent bg-[<?= MAIN_COLOR ?>] px-8 py-3 text-base font-medium text-white hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-offset-2 focus:ring-offset-gray-50">Add
                Cart<button>
            </div>

            <div class="mt-6 text-center">
              <a href="#" class="group inline-flex text-base font-medium focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                <svg class="mr-2 h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-gray-500" fill="none"
                  viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span class="text-gray-500 hover:text-gray-700">You can only order up to 90 days of medication at a
                  time</span>
              </a>
            </div>

          </form>

        </section>
      </div>
    <?php endif; ?>
  </div>
</div>


<?php if ($product->post_content != "" || $product_how_to_use != ""):?>
<div class="p-8">
  <ul class="grid grid-flow-col text-center text-gray-500 p-1">
  <?php if ($product->post_content != ''): ?>
    <li>
      <a role="button" onclick="show_tab(this,'tab1')" name="tab-select"
        class="flex justify-center py-4 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] text-[<?= MAIN_COLOR ?>] bg-white rounded-tl-lg rounded-tr-lg border-l border-t border-r border-gray-100">Description</a>
    </li>
    <?php endif; 
      if ($product_how_to_use != "" ):?>
    <li>
      <a role="button" onclick="show_tab(this,'tab2')" name="tab-select"
        class="flex justify-center py-4 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] text-[<?= MAIN_COLOR ?>]">Consumption</a>
    </li>
    <?php endif; ?>
  </ul>
  <?php if ($product->post_content != ''): ?>
  <div id="tab1" name="tab" class="bg-white shadow border border-gray-100 p-8 text-gray-700 rounded-lg -mt-2">
    <?= $product->post_content ?>
  </div>
  <?php endif; 
  if ($product_how_to_use != ""):?>
  <div id="tab2" name="tab" class="bg-white shadow border border-gray-100 p-8 text-gray-700 rounded-lg -mt-2"
    style="display: none;">
    <?= $product_how_to_use ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>