<?php
 add_action( 'init', 'register_shortcodes');
 
function register_shortcodes(){
    add_shortcode('PL_Drower', 'shortcode_drower_box');
    add_shortcode('PL_Product', 'shortcode_product_box');
    add_shortcode('PL_Product_Search', 'shortcode_product_search');
 }

 function shortcode_drower_box($atts = []){
   
	$result = '<dl class="mt-10 space-y-6 divide-y divide-gray-900/10">';
    $changeData = '<div class="pt-6">
                    <dt>
                      <button type="button" class="faq-toggle flex w-full items-start justify-between text-left text-gray-900"
                        aria-controls="faq-{ID}" aria-expanded="false">
                        <span class="text-base font-semibold leading-7">{TITLE}</span>
                        <span class="ml-6 flex h-7 items-center">
                          <svg class="faq-icon-expand h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                          </svg>
                          <svg class="faq-icon-collapse hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                          </svg>
                        </span>
                      </button>
                    </dt>
                    <dd class="faq-content mt-2 pr-12" id="faq-{ID}" style="max-height: 0; overflow: hidden;">
                      <p class="text-md leading-7 text-black">{CONTENT}</p>
                    </dd>
                  </div>';
    for ($i=1; $i <= count($atts)/2 ; $i++) {
        $temp = str_replace("{ID}",($i-1),$changeData);
        $temp = str_replace("{TITLE}",trim($atts["title".$i]),$temp);
        $temp = str_replace("{CONTENT}",trim($atts["content".$i]),$temp);
        $result .=  $temp;
    }
 	$result .= '</dl>';
	return $result;
}


function shortcode_product_box($atts = []){
  $atts = shortcode_atts(
		array(
			'drug' => '-1',
      'size' => 'medium'
		), $atts, 'PL_Product' );
  $result = '';
  $chengeData = '<div>
                  <div class="relative">
                    <div class="relative h-72 w-full overflow-hidden rounded-lg">
                      {IMAGE}
                    </div>
                    <div class="relative mt-4">
                      <p class="text-lg absolute top-1 right-3 font-semibold text-black">
                        {PRICE}
                      </p>
                      <h3 class="text-sm font-medium text-gray-900">
                        {TITLE}
                      </h3>
                      <p class="mt-1 text-sm text-gray-500">
                        {QUANTITY}
                      </p>
                    </div>
                    <div class="absolute inset-x-0 top-0 flex h-72 items-end justify-end overflow-hidden rounded-lg p-4">
                      <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-black opacity-50">
                      </div>
                    </div>
                  </div>
                  <div class="mt-6">
                    <a href="{URL}"
                      class="relative flex items-center justify-center rounded-md border border-transparent bg-['.MAIN_COLOR.'] hover:bg-['.MAIN_COLOR_HOVER.'] text-white hover:text-white focus:text-white px-8 py-2 text-sm font-medium">View
                      Product<span class="sr-only">{TITLE}</span></a>
                  </div>
                </div>';

      if($atts["drug"] != "-1"){
        $productArg = [
          'posts_per_page' => -1,
          'post_type' => 'product',
          'post_status' => 'publish',
          'orderby' => 'title',
          'order' => 'ASC'
        ];
       $modify_id = $atts["drug"];
       $products = get_posts($productArg);
       foreach ($products as $product){
        $package_id = package_dp_array_fixer(get_post_meta($product->ID, "product_dp", true));

        if (strpos($modify_id,$package_id->first) === false)
            continue;

        $package_info = get_package_info($package_id->first);
        if (!$package_info || $package_info->public_viewable != 1)
          continue;
        
        $modify_id = str_replace( $package_id->first,'',$modify_id);
        $checkAll = str_replace(",",'',$modify_id);

        $temp = $chengeData;
        $temp = str_replace("{IMAGE}",get_the_post_thumbnail($product->ID, $atts["size"], array('class' => 'h-full w-full object-cover object-center')),$temp);
        $temp = str_replace("{PRICE}","$" . $package_info->price,$temp);
        $temp = str_replace("{TITLE}",strlen($product->post_title) > 17 ? substr($product->post_title, 0, 20) . "..." : $product->post_title,$temp);
        $temp = str_replace("{QUANTITY}",packagequantity_fixer($package_info->packagequantity)->string,$temp);
        $temp = str_replace("{URL}",get_permalink($product->ID),$temp);
        $result .=$temp;
         
          if($checkAll == '') break;
       }
      }
      $result .= '';
      return $result;
}

function shortcode_product_search($atts=[]){
  $result = '<div class="mt-10 flex justify-center w-full">
              <div class="max-w-xl w-full border-2 border-['.MAIN_COLOR.'] rounded-xl bg-white shadow-md"
                data-headlessui-state="open">
                <div class="relative flex items-center">
                  <div id="searchbox" class="w-full">
                    <form action="/search"  method="get">
                    <input name="drug"
                      class="block mt-2 w-full bg-transparent pl-4 pr-12 text-xl text-slate-900 placeholder:text-slate-600 focus:outline-none outline-0 border-none focus:border-none shadow-none focus:shadow-none"
                      placeholder="Find anything..." aria-label="Search components" role="combobox" type="text"
                      aria-expanded="false" aria-autocomplete="list" data-headlessui-state="" value=""
                      style="caret-color: rgb(107, 114, 128);" data-protonpass-ignore="" tabindex="0">
                    </form>
                  </div>
                  <svg class="pointer-events-none absolute right-4 top-4 h-6 w-6 fill-slate-400"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M20.47 21.53a.75.75 0 1 0 1.06-1.06l-1.06 1.06Zm-9.97-4.28a6.75 6.75 0 0 1-6.75-6.75h-1.5a8.25 8.25 0 0 0 8.25 8.25v-1.5ZM3.75 10.5a6.75 6.75 0 0 1 6.75-6.75v-1.5a8.25 8.25 0 0 0-8.25 8.25h1.5Zm6.75-6.75a6.75 6.75 0 0 1 6.75 6.75h1.5a8.25 8.25 0 0 0-8.25-8.25v1.5Zm11.03 16.72-5.196-5.197-1.061 1.06 5.197 5.197 1.06-1.06Zm-4.28-9.97c0 1.864-.755 3.55-1.977 4.773l1.06 1.06A8.226 8.226 0 0 0 18.75 10.5h-1.5Zm-1.977 4.773A6.727 6.727 0 0 1 10.5 17.25v1.5a8.226 8.226 0 0 0 5.834-2.416l-1.061-1.061Z">
                    </path>
                  </svg>
                </div>
              </div>
            </div>';
    return $result;
}
?>