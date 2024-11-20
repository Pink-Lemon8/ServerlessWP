<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$installed_ver = get_option('pw_db_version');

function run_pharmacy_install($dropTable = false)
{
	global $wpdb;
	pharmacy_create_structure($dropTable);

	$countryData = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pw_countries WHERE country_code IS NOT NULL");
	$regionData = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pw_country_region WHERE country_code IS NOT NULL");

	if (empty($countryData) || empty($regionData) || $dropTable) {
		include('country_data.php');
	}

	pharmacy_create_default_page();

	$role = get_role('administrator');

	/* If the administrator role exists, add required capabilities for the plugin. */
	if (!empty($role)) {
		/* Role management capabilities. */
		$role->add_cap('pharmacywire_settings');
		$role->add_cap('pharmacywire_reports');
	}

	// add/update to latest version
	update_option('pw_db_version', PWIRE_VERSION);
}

function pharmacy_create_structure($dropTable = false)
{
	global $wpdb;

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_countries`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
		CREATE TABLE `{$wpdb->prefix}pw_countries` (
			`country_code` varchar(3) NOT NULL DEFAULT '',
			`country_name` varchar(255) NOT NULL DEFAULT '',
			PRIMARY KEY  (`country_code`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_country_region`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
		CREATE TABLE `{$wpdb->prefix}pw_country_region` (
			`country_code` varchar(3) NOT NULL,
			`region_code` varchar(5) NOT NULL DEFAULT '',
			`region_name` varchar(255) NOT NULL,
			PRIMARY KEY  (`region_code`,`country_code`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_drug_ingredient`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
		CREATE TABLE `{$wpdb->prefix}pw_drug_ingredient` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`ingredient_id` int(10) unsigned DEFAULT '0',
            `drug_id` varchar(255) DEFAULT NULL,
            `ingredient_display_order` int(10) unsigned DEFAULT '0',
			`catalog_updated` tinyint(1) DEFAULT '1',
			PRIMARY KEY  (`id`),
							KEY `drug_id` (`drug_id`),
							KEY `ingredient_id` (`ingredient_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_attributes`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
		CREATE TABLE `{$wpdb->prefix}pw_attributes` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`attribute_id` char(100) NOT NULL,
			`attribute_key` char(100),
			`attribute_value` char(255),
			`catalog_updated` tinyint(1) DEFAULT '1',
			PRIMARY KEY  (`id`),
							KEY `attribute_id` (`attribute_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_drugs`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
		CREATE TABLE `{$wpdb->prefix}pw_drugs` (
			`drug_id` varchar(20) NOT NULL DEFAULT '',
			`public_viewable` tinyint(1) DEFAULT NULL,
			`name` varchar(150) DEFAULT NULL,
			`familyname` varchar(150) DEFAULT NULL,
			`strengthfreeform` varchar(45) DEFAULT NULL,
			`strength` float DEFAULT NULL,
			`strength_unit` varchar(45) DEFAULT NULL,
			`form` varchar(128) DEFAULT NULL,
			`dosage_form` varchar(150) DEFAULT NULL,
			`ingredient_hash` varchar(45) DEFAULT NULL,
			`udn` varchar(45) DEFAULT NULL,
			`schedule` varchar(255) DEFAULT NULL,
			`manufacturer` varchar(255) DEFAULT NULL,
			`generic` tinyint(1) DEFAULT NULL,
			`comment_external` mediumtext,
			`condition` text,
			`condition_id` int(11) DEFAULT NULL,
			`category` varchar(45) DEFAULT NULL,
			`prescriptionrequired` tinyint(1) DEFAULT '0',
			`species` varchar(150) DEFAULT NULL,
			`catalog_updated` tinyint(1) DEFAULT '1',
			PRIMARY KEY  (`drug_id`),
			KEY `name` (`name`),
			KEY `familyname` (`familyname`),
			KEY `ingredient_hash` (`ingredient_hash`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_ingredients`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
		CREATE TABLE `{$wpdb->prefix}pw_ingredients` (
			`ingredient_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`ingredient_name` varchar(255) NOT NULL,
			`catalog_updated` tinyint(1) DEFAULT '1',
			PRIMARY KEY  (`ingredient_id`),
							KEY `attribute_id` (`ingredient_name`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_packages`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
		CREATE TABLE `{$wpdb->prefix}pw_packages` (
			`package_id` varchar(20) NOT NULL DEFAULT '',
			`public_viewable` tinyint(3) unsigned DEFAULT NULL,
			`product` varchar(255) DEFAULT NULL,
			`manufacturer` varchar(255) DEFAULT NULL,
			`origin_country_code` varchar(45) DEFAULT NULL,
			`upc` varchar(45) DEFAULT NULL,
			`category` varchar(255) DEFAULT NULL,
			`packagingfreeform` varchar(45) DEFAULT NULL,
			`packagequantity` varchar(45) DEFAULT NULL,
			`price` float DEFAULT NULL,
			`minitemqty` int(10) unsigned DEFAULT NULL,
			`maxitemqty` int(10) unsigned DEFAULT NULL,
			`multipleitemfactor` varchar(45) DEFAULT NULL,
			`feature` varchar(45) DEFAULT NULL,
			`comment_external` mediumtext,
			`vendor` varchar(45) DEFAULT NULL,
			`vendor_country_code` varchar(4) DEFAULT NULL,
			`filling_vendor_id` integer DEFAULT NULL,
			`created` datetime DEFAULT NULL,
			`updated` datetime DEFAULT NULL,
            `drug_id` varchar(20) DEFAULT NULL,
            `sort_value` decimal(10,5) DEFAULT NULL,
			`is_viewable` tinyint(3) unsigned NOT NULL,
			`catalog_updated` tinyint(1) DEFAULT '1',
			PRIMARY KEY  (`package_id`),
			KEY `FK_wp_pw_packages_1` (`drug_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_packages_tierprice`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
			CREATE TABLE `{$wpdb->prefix}pw_packages_tierprice` (
				`package_id` varchar(20) NOT NULL DEFAULT '',
				`price` decimal(12,6) DEFAULT NULL,
				`quantity` int(11) unsigned DEFAULT '1',
				`created` datetime DEFAULT NULL,
				`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`catalog_updated` tinyint(1) DEFAULT '1',
				PRIMARY KEY  (`package_id`, `quantity`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;

	dbDelta($sql);

	$sql = <<<EOD
			CREATE TABLE `{$wpdb->prefix}pw_sessions` (
                `sessionId` varchar(32) NOT NULL,
                `expiration` int(10) unsigned NOT NULL,
                `data` text NOT NULL,
                PRIMARY KEY  (`sessionId`),
                KEY `expiration` (`expiration`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_search`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
			CREATE TABLE `{$wpdb->prefix}pw_search` (
				`SearchID` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`Term` char(100) NOT NULL DEFAULT '',
				`Results` text,
				`Added` datetime DEFAULT NULL,
				`Updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`UseCount` int(11) DEFAULT '0',
				PRIMARY KEY  (`SearchID`),
				KEY `Term` (`Term`),
				KEY `UseCount` (`UseCount`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);

	if ($dropTable) {
		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}pw_request_state`;";
		$wpdb->query($sql);
	}
	$sql = <<<EOD
            CREATE TABLE `{$wpdb->prefix}pw_request_state` (
                `remote_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'notice',
                `req_time` datetime NOT NULL,
                `init_time` datetime NOT NULL,
                `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `attempts` tinyint(4) DEFAULT '0',
                `total_attempts` tinyint(11) DEFAULT '0',
                `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                PRIMARY KEY (`remote_ip`, `type`),
                KEY `req_time` (`req_time`),
                KEY `init_time` (`init_time`),
                KEY `state` (`state`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
EOD;
	dbDelta($sql);
}

function pharmacy_create_default_page()
{
	
	$default_pages = array(
		array('pagecode' => 'Pharmacy_Search_Drug', 'pagetitle' => 'Search'),
		array('pagecode' => 'Pharmacy_Search_Detail', 'pagetitle' => 'Search Details'),
		array('pagecode' => 'Pharmacy_Contact', 'pagetitle' => 'Contact Us'),
		array('pagecode' => 'Pharmacy_Register', 'pagetitle' => 'Create Account'),
		array('pagecode' => 'Pharmacy_login', 'pagetitle' => 'Login'),
		array('pagecode' => 'Pharmacy_logout', 'pagetitle' => 'Logout'),
		array('pagecode' => 'Checkout_Edit_Shipping_Address', 'pagetitle' => 'Shipping', 'pageslug' => 'select-shipping'),
		array('pagecode' => 'Forgot_Password', 'pagetitle' => 'Forgot Password'),
		array('pagecode' => 'Change_Password', 'pagetitle' => 'Change Password'),
		array('pagecode' => 'Pharmacy_Profile', 'pagetitle' => 'Account'),
		array('pagecode' => 'Pharmacy_ProfileAddress', 'pagetitle' => 'Account - Address'),
		array('pagecode' => 'Pharmacy_ProfileInfo', 'pagetitle' => 'Account Info'),
		array('pagecode' => 'Pharmacy_ProfileEdit', 'pagetitle' => 'Account - Edit'),
		array('pagecode' => 'Pharmacy_UploadDocument', 'pagetitle' => 'Upload Prescription Document'),
		array('pagecode' => 'Pharmacy_Reorder', 'pagetitle' => 'Re-Order'),
		array('pagecode' => 'Pharmacy_ViewOrder', 'pagetitle' => 'View Order'),
		array('pagecode' => 'Pharmacy_Checkout_Shopping', 'pagetitle' => 'Shopping Cart'),
		array('pagecode' => 'PwireJSON template="login"', 'pagetitle' => 'Checkout', 'pageslug' => 'checkout-login', 'pagecodeargs' => 'action="/shopping-cart/checkout/"'),
		array('pagecode' => 'PwireJSON template="checkout"', 'pagetitle' => 'Checkout', 'pageslug' => 'checkout', 'parent' => 'shopping-cart'),
	);

	$shoppingCartPageID = 0;

	foreach ($default_pages as $page) {
		$parentID = 0;
		$pagecode = $page['pagecode'];
		$pagetitle = $page['pagetitle'];
		$pageslug = (!empty($page['pageslug'])) ? $page['pageslug'] : '';
		$pagecodeargs = (!empty($page['pagecodeargs'])) ? $page['pagecodeargs'] : '';
		$pageinfo = get_page_detail($pagecode);
		if (is_null($pageinfo)) {
			if (($shoppingCartPageID != 0) && (($pagecode == 'PwireJSON template="login"') || ($pagecode == 'PwireJSON template="checkout"'))) {
				$parentID = $shoppingCartPageID;
			}
			// If page doesn't exist, add it and flag for SSL
			$pagecodeandargs = trim($pagecode . ' ' . $pagecodeargs);
			// Add the page unless default page generation is turned off (set to 1)
			if (get_option('pw_disable_default_page_generation', 0) == 0) {
				$post_id = insert_page($pagetitle, $pagecodeandargs, $pageslug, $parentID);
			}
			if ($post_id && $pagecode != 'Pharmacy_Search_Drug' && $pagecode != 'Pharmacy_Search_Detail' && $pagecode != 'Pharmacy_Contact') {
				$meta_key = 'force_ssl';
				$meta_value = 1;
				$unique = true;
				add_post_meta($post_id, $meta_key, $meta_value, $unique);
			}
			if ($post_id && ($pagecode === 'Pharmacy_Checkout_Shopping')) {
				$shoppingCartPageID = $post_id;
			}
		} else {
			// If page exists, restore default flag for SSL
			$post_id = $pageinfo->id;
			if ($post_id && $pagecode != 'Pharmacy_Search_Drug' && $pagecode != 'Pharmacy_Search_Detail' && $pagecode != 'Pharmacy_Contact') {
				$meta_key = 'force_ssl';
				$meta_value = 1;
				$unique = true;
				add_post_meta($post_id, $meta_key, $meta_value, $unique);
			}
		}
		$arrPages[$pagecode] = $post_id;
	}
	// Update the pw_page_list if it hasn't been set yet
	// or if it has, do the update unless default page generation is turned off (set to 1)
	if (empty(get_option('pw_page_list')) || get_option('pw_disable_default_page_generation', 0) == 0) {
		update_option('pw_page_list', $arrPages);
	}
}

function insert_page($title, $shortcode, $slug, $parentID = 0)
{
	global $wpdb;
	if (!empty($slug)) {
		$my_post = array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'post_title' => $title,
			'user_ID' => 1,
			'post_author' => 1,
			'post_parent' => $parentID,
			'post_content' => '[' . $shortcode . ']',
			'post_name' => $slug,
		);
	} else {
		$my_post = array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'post_title' => $title,
			'user_ID' => 1,
			'post_author' => 1,
			'post_parent' => $parentID,
			'post_content' => '[' . $shortcode . ']',
		);
	}
	$post_id = wp_insert_post($my_post);

	return $post_id;
}
function get_page_detail($code_page)
{
	global $wpdb;
	global $wp_rewrite;

	if (gettype($wp_rewrite) != 'object') {
		$GLOBALS['wp_rewrite'] = new WP_Rewrite();
	}
	// check for matching pagecode with wildcard at end to allow for arguments
	$page_details = $wpdb->get_row("SELECT * FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[" . $code_page . "%]%' AND `post_type`= 'page' AND post_status='publish' LIMIT 1", ARRAY_A);
	$countItems = $wpdb->num_rows;

	if ($countItems > 0) {
		$page = new stdClass();
		$page->id = $page_details['ID'];
		$page->url =  get_permalink($page->id);
	} else {
		$page = null;
	}
	return $page;
}
