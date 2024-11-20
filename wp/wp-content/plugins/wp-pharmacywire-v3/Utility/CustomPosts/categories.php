<?php

add_action('init', 'drugcategories_register_categories');
function drugcategories_register_categories()
{
	register_post_type(
		'product_categories',
		array(
			'label' => 'Product Categories',
			'description' => '',
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'capability_type' => 'page',
			'map_meta_cap' => true,
			'hierarchical' => true,
			'query_var' => true,
			'rewrite' => array(
				'slug'                  => 'categories',
				'with_front'            => false,
				'pages'                 => true,
				'feeds'                 => true,
			),
			'has_archive' => true,
			'menu_icon' => 'dashicons-pressthis',
			'menu_position' => 15,
			'supports'	=>	array('title', 'editor', 'thumbnail'),
			'labels' => array(
				'name' => 'Product Categories',
				'singular_name' => 'Product Category',
				'menu_name' => 'Product Categories',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Product Category',
				'edit' => 'Edit',
				'edit_item' => 'Edit Product Category',
				'new_item' => 'New Product Category',
				'view' => 'View Product Categories',
				'view_item' => 'View Product Category',
				'search_items' => 'Search Product Categories',
				'not_found' => 'No Product Category Found',
				'not_found_in_trash' => 'No Product Category Found in Trash',
				'parent' => 'Parent',
			)
		)
	);
}

// Register Custom Taxonomy
function medical_symptoms()
{
	$labels = array(
		'name'                       => _x('Symptoms', 'Taxonomy General Name', 'text_domain'),
		'singular_name'              => _x('Symptom', 'Taxonomy Singular Name', 'text_domain'),
		'menu_name'                  => __('Symptoms', 'text_domain'),
		'all_items'                  => __('All Symptoms', 'text_domain'),
		'parent_item'                => __('Parent Symptom', 'text_domain'),
		'parent_item_colon'          => __('Parent Symptom:', 'text_domain'),
		'new_item_name'              => __('New Symptom Name', 'text_domain'),
		'add_new_item'               => __('Add New Symptom', 'text_domain'),
		'edit_item'                  => __('Edit Symptom', 'text_domain'),
		'update_item'                => __('Update Symptom', 'text_domain'),
		'view_item'                  => __('View Symptom', 'text_domain'),
		'separate_items_with_commas' => __('Separate symptoms with commas', 'text_domain'),
		'add_or_remove_items'        => __('Add or remove symptoms', 'text_domain'),
		'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
		'popular_items'              => __('Popular Symptoms', 'text_domain'),
		'search_items'               => __('Search Symptoms', 'text_domain'),
		'not_found'                  => __('Not Found', 'text_domain'),
		'no_terms'                   => __('No symptoms', 'text_domain'),
		'items_list'                 => __('Symptoms list', 'text_domain'),
		'items_list_navigation'      => __('Symptoms list navigation', 'text_domain'),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy('symptoms', array('product_categories'), $args);
}
add_action('init', 'medical_symptoms');
