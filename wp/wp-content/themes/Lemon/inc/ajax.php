<?php

function check_session(){
	$sessionHandler = new Utility_SessionHandler();
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_set_save_handler(array(&$sessionHandler, "open"), array(&$sessionHandler, "close"), array(&$sessionHandler, "read"), array(&$sessionHandler, "write"), array(&$sessionHandler, "destroy"), array(&$sessionHandler, "gc"));
		session_start();
	}
}

function setup_ajax() {
    wp_enqueue_script('custom_ajax', get_template_directory_uri() . '/js/custom_ajax.js',array('jquery'),'1.0',true);
	wp_localize_script('custom_ajax','PL_AJAX_ACTION',array( 
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'security' => wp_create_nonce('pl_all_ajax_action')
	) );
}
add_action('wp_enqueue_scripts', 'setup_ajax');


add_action( 'wp_ajax_nopriv_pl_cart', 'ajax_cart_json_handler' );
add_action( 'wp_ajax_pl_cart', 'ajax_cart_json_handler' );

function ajax_cart_json_handler() {
    check_session();
	if(isset($_POST['security']) && $_POST['security'] == wp_create_nonce('pl_all_ajax_action') && isset($_POST['do']) && $_POST['do'] == "remove" ){
		$removed_package_id = $_POST["pl_package_id"];
		if (isset(get_cart_raw()[$removed_package_id])){
			$temp_pk_product_id = get_cart_raw()[$removed_package_id]["PK_product_id"];
			$remove_result = Cart::remove($removed_package_id);
			
			if($remove_result){
                add_cart_overlay_alert(get_the_title($temp_pk_product_id), $content = 'Removed from your cart.',$temp_pk_product_id,"remove");
				$cart_fix = Cart::getCartJSON();
				$cart_fix = trim(preg_replace('/\s\s+/', ' ', $cart_fix));
                echo "<script>order_ui_update('".$cart_fix."');</script>"; 
            }
			else
                add_cart_overlay_alert(get_the_title($temp_pk_product_id), "Something is wrong. Try Again !!!", $temp_pk_product_id,"remove");
           
		}
        
	}

    if(isset($_POST['security']) && $_POST['security'] == wp_create_nonce('pl_all_ajax_action') && isset($_POST['do']) && $_POST['do'] == "update" ){
		$removed_package_id = $_POST["pl_package_id"];
		if (isset(get_cart_raw()[$removed_package_id])){
			$temp_pk_product_id = get_cart_raw()[$removed_package_id]["PK_product_id"];
			$remove_result = Cart::update($removed_package_id,$_POST["pl_package_quantity"]);
			if($remove_result){
                add_cart_overlay_alert(get_the_title($temp_pk_product_id), $content = 'Updated to '.get_cart_raw()[$removed_package_id]["amount"].".",$temp_pk_product_id,"remove");
				$cart_fix = Cart::getCartJSON();
				$cart_fix = trim(preg_replace('/\s\s+/', ' ', $cart_fix));
                echo "<script>order_ui_update('".$cart_fix."')</script>"; 
            }
			else
                add_cart_overlay_alert(get_the_title($temp_pk_product_id), "Something is wrong. Try Again !!!", $temp_pk_product_id,"remove");
           
		}
        
	}
	Shipping::updateShippingProductAddons();
    wp_die();
}

add_action( 'wp_ajax_nopriv_pl_cart_cupon', 'ajax_cart_cupon_json_handler' );
add_action( 'wp_ajax_pl_cart_cupon', 'ajax_cart_cupon_json_handler' );

function ajax_cart_cupon_json_handler(){
	check_session();
	$cupon_object = new Model_Coupon();
	if(isset($_POST['security']) && $_POST['security'] == wp_create_nonce('pl_all_ajax_action') && isset($_POST['do']) && $_POST['do'] == "add" ){
		$cupon_code = $_POST["cupon_code"];
	//	$user_id =   isset($_POST["cupon_code"]) ? $_POST[user_id]  : null;
		$result = json_decode($cupon_object->applyCoupon($cupon_code));
		if($result->status == "success"){
			add_cart_overlay_alert("Coupon Action", $content = 'Coupon is Applied.',null,"added");
		}
		else
			add_cart_overlay_alert("Coupon Action", "Something is wrong. Try Again !!!", null,"added");
	}

	if(isset($_POST['security']) && $_POST['security'] == wp_create_nonce('pl_all_ajax_action') && isset($_POST['do']) && $_POST['do'] == "remove" ){
		$cupon_code = $_POST["cupon_code"];
	//	$user_id =   isset($_POST["cupon_code"]) ? $_POST[user_id]  : null;
		$result = json_decode($cupon_object->removeCouponSession($cupon_code));
		if($result->status == "success"){
			add_cart_overlay_alert("Coupon Action", $content = 'Coupon is removed.',null,"remove"); 
		}
		else
			add_cart_overlay_alert("Coupon Action", "Something is wrong. Try Again !!!", null,"remove");
	}
	echo $cart_fix;
	$cart_fix = Cart::getCartJSON();
	$cart_fix = trim(preg_replace('/\s\s+/', ' ', $cart_fix));
	echo "<script>copon_ui_update('".$cart_fix."')</script>"; 
	wp_die();
}




?>