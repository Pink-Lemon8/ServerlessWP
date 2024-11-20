<?php


// if get option?? they set custom post type slug/name ... if this is set then go ahead with creating the custom post type.

//if ( !post_type_exists( 'product' ) ) {
require_once 'products.php';
require_once 'categories.php';
//}

function pwire_register_custom_posts()
{
	// register post types prior to flush_rewrite_rules
	drugs_posttype_register_product();
	drugcategories_register_categories();
	product_rewrite_rule();
}
