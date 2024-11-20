<?php
$product_search = isset($_GET["drug"])? sanitize_text_field($_GET["drug"]) : '';
$products_per_page = 12;
$productArg = [
  'post_type' => 'product',
  'post_status' => 'publish',
  'orderby' => 'title',
  'order' => 'ASC',
  'posts_per_page' => $products_per_page,
  'paged' => (get_query_var('paged') ? get_query_var('paged') : 1)
];  
if ($product_search != '' && strlen($product_search) > 2)
  $productArg = array_merge($productArg,['s' => $product_search]);
$products = get_posts($productArg);
$max_page = ceil(wp_count_posts("product")->publish / $products_per_page);
$current_page = max(1, get_query_var('paged'));

?>

<div class="bg-white">
  <div class="mx-auto max-w-2xl px-4 py-7 sm:px-6 sm:py-7 md:max-w-7xl lg:px-8">
    <h2 class="text-xl font-bold text-gray-900">
      <?= the_title() ?>
    </h2>
    <div class="mt-8 grid grid-cols-1 gap-y-12 sm:grid-cols-2 sm:gap-x-6 lg:grid-cols-4 xl:gap-x-8">

      <?php foreach ($products as $product): ?>
        <?php
        $package_id = package_dp_array_fixer(get_post_meta($product->ID, "product_dp", true));
        $package_info = get_package_info($package_id->first);
        if (!$package_info || $package_info->public_viewable != 1)
          continue;
        ?>
        
        <div>
          <div class="relative">
            <div class="relative h-72 w-full overflow-hidden rounded-lg">
              <?= get_the_post_thumbnail($product->ID, 'medium', array('class' => 'h-full w-full object-cover object-center')) ?>
            </div>
            <div class="relative mt-4">
              <p class="text-lg absolute top-1 right-3 font-semibold text-black">
                <?= "$" . $package_info->price ?>
              </p>
              <h3 class="text-sm font-medium text-gray-900">
                <?= strlen($product->post_title) > 20 ? substr($product->post_title, 0, 17) . "..." : $product->post_title ?>
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                <?= packagequantity_fixer($package_info->packagequantity)->string; ?>
              </p>

            </div>
            <div class="absolute inset-x-0 top-0 flex h-72 items-end justify-end overflow-hidden rounded-lg p-4">
              <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-black opacity-50">
              </div>
            </div>
          </div>
          <div class="mt-6">
            <a href="<?= get_permalink($product->ID) ?>"
              class="relative flex items-center justify-center rounded-md border border-transparent bg-[<?= MAIN_COLOR ?>] hover:bg-[<?= MAIN_COLOR_HOVER ?>] text-white hover:text-white focus:text-white px-8 py-2 text-sm font-medium">View
              Product<span class="sr-only"><?= strlen($product->post_title) > 28 ? substr($product->post_title, 0, 25) . "..." : $product->post_title ?></span></a>
          </div>
        </div>

      <?php endforeach; ?>

    </div>
  </div>
</div>

<nav aria-label="Pagination"
  class="mx-auto mt-6 flex max-w-7xl justify-between px-4 text-sm font-medium text-gray-700 sm:px-6 lg:px-8">
  <div class="min-w-0 flex-1">
    <?php if ($current_page > 1): ?>
      <a href="<?= "/products/page/" . ($current_page - 1) ?>"
        class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] px-4 hover:bg-gray-100 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-opacity-25 focus:ring-offset-1 focus:ring-offset-[<?= MAIN_COLOR_FOCUS ?>]">Previous</a>
    <?php endif; ?>
  </div>

  <div class="hidden space-x-2 sm:flex">
    <?php for ($i = max(1, $current_page - 2); $i <= min($current_page + 2, $max_page); $i++): ?>
      <a href="<?= "/products/page/" . $i ?>"
        class="<?= $current_page == $i ? "ring-1 ring-[" . MAIN_COLOR . "] " : '' ?>inline-flex h-10 items-center rounded-md border border-gray-300 bg-white hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] px-4 hover:bg-gray-100 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-opacity-25 focus:ring-offset-1 focus:ring-offset-[<?= MAIN_COLOR_FOCUS ?>]"><?= $i ?></a>
    <?php endfor; ?>

  </div>

  <div class="flex min-w-0 flex-1 justify-end">
    <?php if ($current_page < $max_page): ?>
      <a href="<?= "/products/page/" . ($current_page + 1) ?>"
        class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] px-4 hover:bg-gray-100 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-opacity-25 focus:ring-offset-1 focus:ring-offset-[<?= MAIN_COLOR_FOCUS ?>]">Next</a>
    <?php endif; ?>
  </div>
</nav>