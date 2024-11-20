<?php

function wp_get_menu_array($locationID) {
    $menuLocations = get_nav_menu_locations(); 
	$menuID = $menuLocations[$locationID]; 
	return wp_get_nav_menu_items($menuID);
}

function get_sub_menu_array($menuArray){
	$result[] = null;
	if($menuArray)
		foreach ( $menuArray as $item ){
			if ($item->menu_item_parent != 0){
				$result[] = ($item);
			}
		}
	return $result;
}

function check_sub_menu($menuID,$subMenuList){

		foreach ( $subMenuList as $item )
				if ($item != null && $item->menu_item_parent == $menuID)
					return $item;
	return false;
}

function check_menu_depth($menuID,$subMenuList){
		$currentMenuID = $menuID;
		$count = 0;
		while(true){
			$sub_menu = check_sub_menu($currentMenuID,$subMenuList);
			if($sub_menu){
				$currentMenuID = $sub_menu->ID;
				$count++;
			}
			else{
				return $count;
			}
		}
		return 0;
}

function copyManifestandHtaccessLine(){
	$setup_folder = "wp-content/themes/Lemon/inc/setup/";
	$manifest_file = ABSPATH.$setup_folder."manifest.php";
	add_rewrite_rule("manifest.json","manifest.php");
	copy($manifest_file,ABSPATH."manifest.php");
}

function get_top_banner(){
	return get_option("PL_top_banner");
}

function set_top_banner($banner){
	if(get_top_banner() != false)
		return set_option("PL_top_banner",$banner);
	else
		return add_option("PL_top_banner",$banner);
}

function get_icon_image($size = "full"){
	$custom_logo_id = get_theme_mod( 'custom_logo' );
    $image = wp_get_attachment_image_src( $custom_logo_id , $size );
	return $image;
}

function get_package_info($drug_db){
	global $wpdb;
	$wordpress_prefix = "wp_";
	$pharma_prefix = "pw_";
	if($drug_db){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}{$pharma_prefix}packages WHERE package_id = '$drug_db'");
		if ($result)
			return $result[0];
	}
	return null;
}

function get_drug_info($drug_id){
	global $wpdb;
	$wordpress_prefix = "wp_";
	$pharma_prefix = "pw_";
	if($drug_id){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}{$pharma_prefix}drugs WHERE drug_id='$drug_id'");
		if ($result)
			return $result[0];
	}
	return null;
}

function get_package_tier($drug_db){
	global $wpdb;
	$wordpress_prefix = "wp_";
	$pharma_prefix = "pw_";
	if($drug_db){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}{$pharma_prefix}packages_tierprice where package_id='$drug_db'");
		if ($result)
			return $result;
	}
	return [];
}

function get_drug_attribute($drug_id){
	global $wpdb;
	$wordpress_prefix = "wp_";
	$pharma_prefix = "pw_";
	if($drug_id){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}{$pharma_prefix}attributes WHERE attribute_id='$drug_id'");
		if ($result)
			return $result;
	}
	return [];
}

function get_drug_attribute_keys(){
	global $wpdb;
	$wordpress_prefix = "wp_";
	$pharma_prefix = "pw_";
	$result =  $wpdb->get_results("SELECT DISTINCT attribute_key FROM {$wordpress_prefix}{$pharma_prefix}attributes");
	if ($result)
		return $result;
	return [];
}

function get_drug_ingredients($drug_id){
	global $wpdb;
	$wordpress_prefix = "wp_";
	$pharma_prefix = "pw_";
	if($drug_id){
		$result =  $wpdb->get_results("SELECT M.ingredient_id,M.drug_id,I.ingredient_name FROM {$wordpress_prefix}{$pharma_prefix}drug_ingredient as M INNER JOIN {$wordpress_prefix}{$pharma_prefix}ingredients as I ON M.ingredient_id =I.ingredient_id WHERE drug_id = '$drug_id'");
		if ($result)
			return $result;
	}
	return [];
}

function get_post_by_meta_key($value,$prefix="%",$postfix="%"){
	global $wpdb;
	$wordpress_prefix = "wp_";
	$pharma_prefix = "pw_";
	if($value){
		$result =  $wpdb->get_results("Select * from {$wordpress_prefix}postmeta M RIGHT JOIN {$wordpress_prefix}posts P on P.ID = M.post_id where M.meta_value like '{$prefix}{$value}{$postfix}'");
		if ($result)
			return $result;
	}
	return [];
}

function packagequantity_fixer($data){
	$result = new stdClass();
	$result->data = $data;
	$temp = explode("@", $data);
	$result->string = str_replace("@"," ",$data);
	if($temp){
		$result->value = floatval($temp[0]);
		$result->unit =$temp[1];
		if($result->value == 1)
			$result->unit = rtrim($result->unit,"s");
		$result->string = $result->value. " " .$result->unit;
	}
	return $result;
}

function count_valid_product($data){
	$count = 0;
	foreach ($data as $package_id){
		$package_info = get_package_info($package_id);
		 if ($package_info != null && $package_info->public_viewable == 1)
		 	$count++;
	}
	return $count;
}

function tier_prices_fixer($data){
	$result = new stdClass();
	$result->data = $data;
	$tempArray = explode(",", $data);
	foreach ($tempArray as $item) {
		$tempItem = explode(":", $item);
		if($tempItem){
			$result->quantity[] = floatval($tempItem[0]);
			$result->price[] = floatval($tempItem[1]);
		}
	}
	return $result;
}

function package_dp_array_fixer($data){
	$result = new stdClass();
	$result->data = $data;
	$temp = explode(",", $data);
	$result->first ='';
	$result->last ='';
	$result->list =[];
	foreach ($temp as $temp)
		$result->list[] = $temp;
	if (count($result->list) > 0) $result->first =$result->list[0];
	if (count($result->list) > 0) $result->last = $result->list[count($result->list)-1];
	return $result;
}

function insert_product($main_drug_dp,$title,$post_status='publish',$drug_dps=''){

	$my_post = array(
		'post_type' => 'product',
		'post_title'    => wp_strip_all_tags($title),
		'post_content'  => '',
		'post_status'   => $post_status,
		'post_author'   => 1,
		'meta_input' => array(
			'product_dp' => rtrim($main_drug_dp.",".$drug_dps,','),
			'product_short_content' => '',
			'product_how_to_use' => '',
			'product_is_store' => 'yes'
		)
	  );
	  return wp_insert_post($my_post);
}

function add_all_product_bot($post_status="publish"){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	$pharma_prefix = "pw_";
	$packs =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}{$pharma_prefix}packages where public_viewable=1");
	foreach ($packs as $pack_key => $pack) {
		$title = str_replace("'","\'",$pack->product);
		$page =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}posts where post_title='{$title}' and post_type='product'");
		if(count($page)>0){
			$tempPage = $page[0];
			update_post_meta($tempPage->ID, 'product_dp',get_post_meta($tempPage->ID, 'product_dp',true).",".$pack->package_id.",");
		}
		else{
			insert_product($pack->package_id,$pack->product);
		}
	}
	return true;
}
function remove_all_product_post(){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	$wpdb->query("Delete FROM {$wordpress_prefix}posts where post_type='product'");
	return true;
}

function remove_all_product_dp_from_post(){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	$pharma_prefix = "pw_";
	$products =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}posts where post_type='product'");
	foreach ($products as $product) {
		update_post_meta($products->ID, 'product_dp','');
	}
	return true;
}

function add_to_cart($add_cart_id, $add_cart_quantity,$wp_product_id =''){
	$result = Cart::add($add_cart_id, $add_cart_quantity);
	if($result){
		$_SESSION[CART_NAME][$add_cart_id] = array_merge($_SESSION[CART_NAME][$add_cart_id],["PK_product_id"=>$wp_product_id]);
	}
	return $result;
}

function get_cart_raw(){
  return $_SESSION[CART_NAME];
}

function upload_image_to_website($arg){
	/*
		image_url
		post_title
		post_content
		post_excerpt
		_wp_attachment_image_alt
	*/
	$image_url = $arg['image_url'];
	$upload_dir = wp_upload_dir();
	$image_data = file_get_contents( $image_url );
	$filename = md5(basename($image_url)).".jpg";
	if ( wp_mkdir_p( $upload_dir['path'] ) ) {
	$file = $upload_dir['path'] . '/' . $filename;
	}
	else {
	$file = $upload_dir['basedir'] . '/' . $filename;
	}
	file_put_contents( $file, $image_data );
	$wp_filetype = wp_check_filetype( $filename, null );
	$attachment = array(
	'post_mime_type' => $wp_filetype['type'],
	'post_title' => $arg['post_title'],
	'post_content' => $arg['post_content'],
	'post_excerpt' => $arg['post_excerpt'],
	'post_status' => 'inherit'
	);

	$attach_id = wp_insert_attachment( $attachment, $file );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	update_post_meta( $attach_id, '_wp_attachment_image_alt', $arg['_wp_attachment_image_alt'] );

	return $attach_id;
}

 function pl_setInfor($Address1 = "", 
			$Address2 = "", 
			$Address3 = "", 
			$City = "", 
			$Province = "", 
			$Country = "", 
			$PostalCode = "", 
			$AreaCode = "", 
			$Phone = "", 
			$CreditCardType = "",
			$CreditCardNumber = "", 
			$CvvNumber = "", 
			$ExpiryMonth = "", 
			$ExpiryYear = "", 
			$firstName = "", 
			$lastName = "", 
			$nameOnCheque = "", 
			$bankName = "", 
			$bankCity = "", 
			$bankState = "", 
			$branchTransit = "", 
			$chequeAccount = "", 
			$chequeNumber = ""){

		 $infor = $_SESSION[BILLING_INFOR];
		 $infor->firstname = $firstName;
		 $infor->lastname = $lastName;
		 $infor->address1 = $Address1;
		 $infor->address2 = $Address2;
		 $infor->address3 = $Address3;
		 $infor->city = $City;
		 $infor->province = $Province;
		 $infor->country = $Country;
		 $infor->postalcode = $PostalCode;
		 $infor->areacode = $AreaCode;
		 $infor->phone = $Phone;
		 $infor->nameOnCheque = $nameOnCheque;
		 $infor->bankName = $bankName;
		 $infor->bankCity = $bankCity;
		 $infor->bankState = $bankState;
		 $infor->branchTransit = $branchTransit;
		 $infor->chequeAccount = $chequeAccount;
		 $infor->chequeNumber = $chequeNumber;
 
		//  $infor->creditcardtype = $Creditcardtype;
		//  $infor->creditcardnumber = $Creditcardnumber;
		//  $infor->cvvnumber = $Cvvnumber;
		//  $infor->expirymonth = $Expirymonth;
		//  $infor->expiryyear = $Expiryyear;

		$infor->creditcardtype = "master";
		$infor->creditcardnumber = "5425233430109903";
		$infor->cvvnumber = "111";
		$infor->expirymonth = "04";
		$infor->expiryyear = "26";

		 $_SESSION[BILLING_INFOR] = $infor;
		//  echo "<hr>";
		  //var_dump($_SESSION[BILLING_INFOR]);
		//  echo "<hr>";
}

 function setOrderData(){
	$_POST["billing_type"] = "creditCard";
	$tempName =  explode("/",$_POST["name-on-card"]);
	$_POST["billing_firstName"] = $tempName[0];
	$_POST["billing_lastName"] = isset($tempName[1]) ? $tempName[1] : $tempName[0];
	$_POST["billing_creditCard_number"] = str_replace(" ","",$_POST["card-number"]);
	$_POST["billing_creditCard_cvv"] = $_POST["cvc"];
	$temp_exp_date = $_POST["expiration-date"];
	$tempExp_date = explode("/",$temp_exp_date);
	$_POST["billing_creditCard_expiryMonth"] = isset($tempExp_date[0]) ? $tempExp_date[0] : "-1";
	$_POST["billing_creditCard_expiryYear"] = isset($tempExp_date[1]) ? "20".$tempExp_date[1] : "-1";
	if(substr($_POST["card-number"],0,1) == '4')
		$_POST["billing_creditCard_type"] = "Visa";
	elseif(substr($_POST["card-number"],0,1) == '5')
		$_POST["billing_creditCard_type"] = "Mastercard";
	else
		$_POST["billing_creditCard_type"] = "Unknown";
}



function get_ref_info($ref_code = null){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	if($ref_code){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}pl_ref_info  WHERE ref_code = '$ref_code'");
		if ($result)
			return $result;
	}
	else{
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}pl_ref_info");
		if ($result)
			return $result;
	}
	return [];
}


function set_ref_info($full_name,$email,$password,$ref_code,$commission_type="per",$commission_rate=0,$expire_date = null){
	//percent,per
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	$table ="{$wordpress_prefix}pl_ref_info";
	$data = array('full_name' => $full_name,'email' => $email,'password' => $password,'ref_code' => $ref_code,'commission_type' => $commission_type,'commission_rate' => $commission_rate,'expire_date' => $expire_date);
	$format = array('%s','%s','%s','%s','%s','%f','%s');
	$result = $wpdb->insert($table,$data,$format);
	return $result;
}

function update_ref_info($id,$full_name,$email,$password,$ref_code,$commission_type="per",$commission_rate=0,$expire_date = null){
	//percent,per
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	$table ="{$wordpress_prefix}pl_ref_info";
	$data = array('full_name' => $full_name,'email' => $email,'password' => $password,'ref_code' => $ref_code,'commission_type' => $commission_type,'commission_rate' => $commission_rate,'expire_date' => $expire_date);
	$format = array('%s','%s','%s','%s','%s','%f','%s');
	$where = array('id' => $id);
	$where_format = array('%d');
	$result = $wpdb->update($table,$data,$where,$format,$where_format);
	return $result;
}

function get_ref_ordered($ref_info_id = null){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	if($ref_info_id){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}pl_ref_ordered  WHERE ref_info_id = '$ref_info_id'");
		if ($result)
			return $result;
	}
	else{
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}pl_ref_ordered");
		if ($result)
			return $result;
	}
	return [];
}

function set_ref_ordered($client_id,$ref_info_id,$order_id,$sub_order_total){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	$table ="{$wordpress_prefix}pl_ref_ordered";
	$data = array('client_id'=> $client_id,'ref_info_id' => $ref_info_id,'order_id' => $order_id, 'sub_total_price' => $sub_order_total);
	$format = array('%s','%s','%s','%s');
	$result = $wpdb->insert($table,$data,$format);
	return $result;
}

function get_ref_both_tables($ref_code = null){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	if($ref_code){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}pl_ref_info as I inner join {$wordpress_prefix}pl_ref_ordered as O on I.id = O.ref_info_id  WHERE ref_code = '$ref_code'");
		if ($result)
			return $result;
	}
	else{
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}pl_ref_info as I inner join {$wordpress_prefix}pl_ref_ordered as O on I.id = O.ref_info_id");
		if ($result)
			return $result;
	}
	return [];
}

function check_ref_link($order_id,$sub_order_total){
	$ref_code = isset($_SESSION["PL_ref_code"]) ? $_SESSION["PL_ref_code"] : null;
	if(isset($ref_code)){
		$ref_info = get_ref_info($ref_code);
		if(isset($ref_info)){
			$temp_ref_info = null;
			foreach ($ref_info as $row) {
				$temp_ref_info = $row;
			}
			$temp_expire = new DateTime($temp_ref_info->expire_date);
			$now = new DateTime();
			if(isset($temp_ref_info->id) && $now < $temp_expire){
				set_ref_ordered(get_ip(),$temp_ref_info->id,$order_id,$sub_order_total);
			}
		}
		unset($_SESSION["PL_ref_code"]);
	}
}

function get_ref_info_by_email_and_password($email,$password){
	global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
	if(isset($email) && isset($password)){
		$result =  $wpdb->get_results("SELECT * FROM {$wordpress_prefix}pl_ref_info  WHERE email = '$email' and password = '$password'");
		if ($result)
			return $result;
	}
	return [];
}

function get_login_ref($email,$password){
	if(isset($email) && isset($password)){
		return count(get_ref_info_by_email_and_password($email,$password)) >0 ? true: false;
	}
	return false;
}

function get_ip(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}


function send_email($first_name,$last_name,$email,$subject,$message){
	// PHPMailer\PHPMailer\PHPMailer makes error on create account page 
	$first_name = sanitize_text_field($first_name);
    $last_name = sanitize_text_field($last_name);
    $email = sanitize_text_field($email);
    $message = sanitize_text_field($message);
   
    try {
		//$mail = new PHPMailer\PHPMailer\PHPMailer(true);
		$result = new stdClass();
        // Server settings
        //$mail->SMTPDebug = 0;
        // $mail->isSMTP();          
		// $mail->SMTPAuth   = true;                 
        // $mail->Host       = 'mail.buyinsulin.com';
        // $mail->Username   = 'bot@buyinsulin.com';
        // $mail->Password   = 'zRJ!^?N}M*Sv';
        // $mail->SMTPSecure = 'ssl';         
        // $mail->Port       = 465;

        // $mail->setFrom($mail->Username,get_option('home'));
        // $mail->addAddress($email, trim($first_name." ".$last_name)); 

        // $mail->isHTML(true);
        // $mail->Subject = $subject;
        // $mail->Body    = $message;

        // $mail->send();
        $result->message = 'Message has been sent.';
		$result->status = 1;
    } catch(Exception $error){
		$result->message = "Message could not be sent. Please try it again later!";
		$result->status = 0;
    }
	return $result;
}

function fix_shipping_price(){
	// $cartItems = &$_SESSION[CART_NAME];
	// foreach ($cartItems as $key => $value) {
	// 	var_dump(Cart::calculateShippingFee($value));
	// }
	// var_dump($cartItems);
	// echo "<hr>";
	// var_dump(Cart::getCartJSON());
	//  var_dump();
}

//smtp send via wp plugin
function send_email_by_plugin($email,$title,$body) {
    $content_type = function() { return 'text/html'; };
    add_filter( 'wp_mail_content_type', $content_type );
    wp_mail( $email, $title, $body );
    remove_filter( 'wp_mail_content_type', $content_type );
}

function send_order_email($email,$title) {
	ob_start();
	include 'email_templates/order.php';
	$content = ob_get_clean();
    send_email_by_plugin($email,$title,$content);
}

function send_create_account_email($email,$title) {
	ob_start();
	include 'email_templates/account.php';
	$content = ob_get_clean();
    send_email_by_plugin($email,$title,$content);
}

?>