<?php

add_action('init', 'drugs_posttype_register_product');
function drugs_posttype_register_product()
{
	register_post_type(
		'product',
		array(
			'label' => 'Product',
			'description' => '',
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'capability_type' => 'page',
			'map_meta_cap' => true,
			'hierarchical' => true,
			'query_var' => true,
			'rewrite' => array(
				'slug'	=> 'product',
				'with_front'	=> false,
			),
			'has_archive' => true,
			'menu_icon'	=> 'dashicons-products',
			'menu_position' => 15,
			'supports'	=>	array('title', 'editor', 'thumbnail'),
			'labels' => array(
				'name' => 'Product',
				'singular_name' => 'Product',
				'menu_name' => 'Product',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Product',
				'edit' => 'Edit',
				'edit_item' => 'Edit Product',
				'new_item' => 'New Product',
				'view' => 'View Product',
				'view_item' => 'View Product',
				'search_items' => 'Search Product',
				'not_found' => 'No Product Found',
				'not_found_in_trash' => 'No Product Found in Trash',
				'parent' => 'Parent',
			),
		)
	);
}

function product_query_vars( $qvars ) {
	$qvars[] = 'drugId';
	$qvars[] = 'drugName';
    return $qvars;
}
add_filter( 'query_vars', 'product_query_vars' );

function product_rewrite_rule()
{
	// add_rewrite_tag('%drugId%', '([^&]+)', 'drugId=');
	// add_rewrite_tag('%drugName%', '([^&]+)', 'drugName=');
	add_rewrite_rule('^product/([^/]*)/drugId/([^/]*)/?', 'index.php?page_id=' . PC_getSearchID() . '&drugName=$matches[1]&drugId=$matches[2]', 'top');
	add_rewrite_rule('^product/([^/]*)/strength/([^/]*)/?', 'index.php?post_type=product&name=$matches[1]&strength=$matches[2]', 'top');
}
add_action('init', 'product_rewrite_rule');
