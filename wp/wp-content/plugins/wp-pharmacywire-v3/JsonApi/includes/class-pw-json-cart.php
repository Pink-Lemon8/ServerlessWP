<?php

class PW_JSON_Cart extends PW_JSON
{
	public $data = array();
	public $cartListItems = array();

	public function getCart()
	{
		$response = Cart::getCartJSON();
		echo $response;
	}

	public function submitOrder()
	{
		$this->data = $_POST;

		// adaptation of GUMP::xss_clean($_POST) function to preserve array inputs
		// which were getting stripped (such as response[1], comment[1], etc.)
		foreach ($this->data as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $ak => $av) {
					$v[$ak] = filter_var($av, FILTER_SANITIZE_STRING);
				}
				$this->data[$k] = $v;
			} else {
				$this->data[$k] = filter_var($v, FILTER_SANITIZE_STRING);
			}
		}

		$this->submitMedicalQuestionnaire();

		$reply = new Model_Entity_Reply();

		$this->cartListItems = Cart::getListItems();
		$orderValidation = new Model_Entity_Reply();
		$orderValidation = $this->validate();

		if ($orderValidation->success) {
			$this->data = $orderValidation->data;

			$this->addLegalAgreement();

			$subTotal = 0;
			$this->data['patientid'] = WebUser::GetUserID();
			$this->data['items'] = array();

			foreach ($this->cartListItems as $cartItem) {
				// Required fields for order
				$product = new stdClass();
				$product->productID = $cartItem->package_id;
				$product->quantity = $cartItem->amount;
				$product->price = PC_formatPrice($cartItem->price);

				// Fields for JSON orderResponse/javascript use
				$product->package_name = $cartItem->product;
				$product->drug_name = $cartItem->drug_name;
				$product->strength = $cartItem->strengthfreeform;
				$product->country = $cartItem->country;
				$product->generic = $cartItem->generic;
				$product->prescriptionrequired = $cartItem->prescriptionrequired;

				$subTotal += $product->quantity * $cartItem->price;
				$this->data['items'][] = $product;
			}

			$this->setupShippingData();

			if (isset($this->data['billing_useShippingAddress']) && $this->data['billing_useShippingAddress'] == 'yes') {
				$this->setBillingAddressToShippingAddress();
			}

			// Add coupon item(s) to order
			// get all coupons including admin coupons (1)
			$orderCoupons = new Model_Coupon;
			$orderCoupons->applyMandatoryCoupons();
			$this->data['coupons'] = $orderCoupons->getCouponSession(1);

			$coupons = $this->data['coupons'];
			if (is_array($coupons) && !empty($coupons)) {
				foreach ($coupons as $coupon) {
					if (array_key_exists('coupon-comment', $coupon)) {
						$comments[] = 'coupon ' . $coupon['coupon-code'] . ' comment: ' . $coupon['coupon-comment'];
					}
				}
			}
			$this->data['sub_total'] = PC_formatPrice($subTotal);
			$this->data['coupon_discount'] = PC_formatPrice($orderCoupons->getDiscount($subTotal));
			$shippingFee = Cart::calculateShippingFee($this->cartListItems);
			$this->data['shippingfee'] = PC_formatPrice($shippingFee);
			$orderTotal = $orderCoupons->applyDiscount($subTotal) + $shippingFee;
			$this->data['order_total'] = PC_formatPrice($orderTotal);

			// Add order tags to order
			if (get_option('pw_order_tags')) {
				$orderTags = new Model_OrderTag;
				$this->data['tags'] = $orderTags->getTagSession();
			}

			if (!isset($comments)) {
				$comments = array();
			}
			$additionalNotes = Cart::getAdditionalNotes();
			if (strlen($additionalNotes)) {
				$comments[] = 'Patient(s) taking medications: ' . $additionalNotes;
			}
			if (!empty($this->data['order_comments'])) {
				$comments[] = 'Patient order comment: ' . $this->data['order_comments'];
			}

			$this->data['comments'] = $comments;

			// Setup payment info to be submitted with the order
			$this->setupBillingData();

			$order = new Model_Order();
			$orderInfo = new stdClass();
			$orderInfo = (object) $this->data;

			$reply = $order->submitOrder($orderInfo);

			$validationMessages = $reply->messages;

			// Add whether new patient was created for this order via a 'page' request (registration) or 'json' (typically via checkout)
			$this->data['new_patient_created'] = 0;
			if (isset($_SESSION['NewPatientCreated'])) {
				$this->data['new_patient_created'] = $_SESSION['NewPatientCreated'];
				unset($_SESSION['NewPatientCreated']);
			}

			$cartData = $this->sanitizeSubmitOrderCartResponse($this->data);

			// if order successfully placed, reset session info
			if ($reply->status == "success") {
				$reply->success = 1;
				$this->resetInfo();
			}
		} else {
			// return error patient must be created & logged in before submitting order
			$reply = $orderValidation;
			$validationMessages = $reply->messages;
		}

		$reply->messages = $this->validationMsgResponseFormat($validationMessages);

		$reply->cart = $cartData;
		$reply->patient = WebUser::getUserInfo();

		return $reply->toJSON();
	}

	public function validationMsgResponseFormat($validationMessages)
	{
		$messages = array();

		foreach ($validationMessages as $msg) {
			// only show errors to customers, if debug is on show debug/notice/etc.
			// validation error - from php validation
			// error and the rest are from pwire xml response
			if ((get_option('pw_debug_mode') == 1) || (($msg->type == 'error') || ($msg->type == 'validation error'))) {
				$m = array();
				$m['type'] = $msg->type;
				$m['content'] = $msg->content;

				if ($m['content'] != 'Unknown problem placing the order.') {
					$messages[] = $m;
				}
			}
		}

		return $messages;
	}

	public function submitMedicalQuestionnaire()
	{
		$data = $this->data;

		if (empty($data['response']) || !is_array($data['response'])) {
			return;
		}
		$medicalQuestions = new Medical_Questions();
		$medicalAnswers = $medicalQuestions->setMedicalAnswers();

		$reply = new Model_Entity_Reply();
		$reply->success = ($medicalAnswers->status == 'success') ? 1 : 0;

		if ($reply->success === 1) {
			return;
		} else {
			$reply->messages = $this->validationMsgResponseFormat($medicalAnswers->messages);
			// return questionnaire failure before submitting order
			echo $reply->toJSON();
			die;
		}
	}

	public function sanitizeSubmitOrderCartResponse($cartData)
	{
		unset($cartData['paymentinfo']);

		foreach ($cartData as $key => $data) {
			if (strpos($key, 'billing_') === 0) {
				// leave in billing type, strip out other sensitive info such as cc #'s
				if (($key != 'billing_type') && ($key != 'billing_creditCard_type') && ($key != 'billing_institution')) {
					unset($cartData[$key]);
				}
			}
		}

		return $cartData;
	}

	public function setupShippingData()
	{
		$data = $this->data;

		// if shipping ref is already set from another page
		$shippingAddressRef = SHIPPING::getAddressRef();
		if ($shippingAddressRef > 0) {
			$this->data['shippingAddressRef'] = $shippingAddressRef;
		}

		// if shipping ref set on this page/data set, take precedence
		if (isset($this->data['shippingAddressRef'])) {
			SHIPPING::setAddressRef($this->data['shippingAddressRef']);
		}

		$this->data = $data;
	}

	public function setupBillingData()
	{
		$data = $this->data;

		$billingMethodType = $data['billing_type'];

		switch ($billingMethodType) {
			case "custom":
				// fall through to same logic as draft
			case "draft":
				$payment = new stdClass();
				$payment->draftnumber = '';

				if (WebUser::isLoggedIn()) {
					$patientInfo = WebUser::getUserInfo();
					$payment->firstname = empty($data['billing_firstName']) ? $patientInfo->firstname : $data['billing_firstName'];
					$payment->middlename = '';
					$payment->lastname = empty($data['billing_lastName']) ? $patientInfo->lastname : $data['billing_lastName'];
				} else {
					$payment->firstname = empty($data['billing_firstName']) ? $data['firstName'] : $data['billing_firstName'];
					$payment->middlename = '';
					$payment->lastname = empty($data['billing_lastName']) ? $data['lastName'] : $data['billing_lastName'];
				}
				$payment->institution = empty($data['billing_institution']) ? '' : $data['billing_institution'];
				break;
			case "eft":
				$payment = new stdClass();
				$payment->bankName		= $data['billing_bank_name'];
				$payment->bankCity		= $data['billing_bank_city'];
				$payment->bankState		= $data['billing_bank_state'];
				$payment->branchTransit	= $data['billing_bank_branchTransit'];
				$payment->nameOnCheque	= $data['billing_cheque_name'];
				$payment->chequeAccount	= $data['billing_cheque_account'];
				$payment->chequeNumber	= $data['billing_cheque_number'];
				$payment->checktype		= isset($data['billing_cheque_type']) ? $data['billing_cheque_type'] : '';
				$payment->address		= $data['billing_address1'];
				$payment->address2		= $data['billing_address2'];
				$payment->city			= $data['billing_city'];
				$payment->state			= $data['billing_region'];
				$payment->country		= $data['billing_country'];
				$payment->postalcode	= $data['billing_regionCode'];
				$payment->idnumber		= isset($data['billing_idnumber']) ? $data['billing_idnumber'] : '';
				$payment->idtype		= isset($data['billing_idtype']) ? $data['billing_idtype'] : '';
				$payment->idstatecode	= isset($data['billing_idtype']) ? $data['billing_idstatecode'] : '';
				$payment->dob			= isset($data['dob']) ? $data['dob'] : '';
				$payment->areacode		= empty($data['billing_phoneAreaCode']) ? $data['phoneAreaCode'] : $data['billing_phoneAreaCode'];
				$payment->phone			= empty($data['billing_phone']) ? $data['phone'] : $data['billing_phone'];
				if (empty($payment->areacode) || empty($payment->phone)) {
					// no phone numbers submitted, should only happen when customer is logged in already
					// so use the default account phone number from session in it's place
					$payment->areacode = $_SESSION['Account_phoneAreaCode'];
					$payment->phone = $_SESSION['Account_phone'];
				}
				break;
			case "creditCard":
				$payment = new stdClass();
				$payment->creditCard_number = preg_replace('/\D/', '', $data['billing_creditCard_number']);
				$payment->creditCard_cvv    = $data['billing_creditCard_cvv'];
				$payment->creditCard_type 	= $data['billing_creditCard_type'];
				$payment->expiryMonth	= $data['billing_creditCard_expiryMonth'];
				$payment->expiryYear	= $data['billing_creditCard_expiryYear'];

				$payment->firstName		= $data['billing_firstName'];
				$payment->middleName	= '';
				$payment->lastName		= $data['billing_lastName'];

				$payment->address1		= $data['billing_address1'];
				$payment->address2		= $data['billing_address2'];
				$payment->city			= $data['billing_city'];
				$payment->region		= $data['billing_region'];
				$payment->country		= $data['billing_country'];
				$payment->regionCode	= $data['billing_regionCode'];
				$payment->phoneAreaCode	= empty($data['billing_phoneAreaCode']) ? $data['phoneAreaCode'] : $data['billing_phoneAreaCode'];
				$payment->phone			= empty($data['billing_phone']) ? $data['phone'] : $data['billing_phone'];
				if (empty($payment->phoneAreaCode) || empty($payment->phone)) {
					// no phone numbers submitted, should only happen when customer is logged in already
					// so use the default account phone number from session in it's place
					$payment->phoneAreaCode = $_SESSION['Account_phoneAreaCode'];
					$payment->phone = $_SESSION['Account_phone'];
				}
				break;
			case "moneyOrder":
				$payment = new stdClass();
				break;
		}

		$data['paymentinfo'] = $payment;

		$this->data = $data;

		return;
	}

	public function setBillingAddressToShippingAddress()
	{
		$shippingAddressRef = SHIPPING::getAddressRef();

		if ($shippingAddressRef > 0) {
			$shippingAddress = new Model_Entity_Address();
			$shippingAddress = SHIPPING::getAddessByAddressID($shippingAddressRef);
			$shippingData = $shippingAddress->getData();
			$this->data['billing_address1'] = $shippingData['address1'];
			$this->data['billing_address2'] = $shippingData['address2'];
			$this->data['billing_city'] = $shippingData['city'];
			$this->data['billing_region'] = $shippingData['province'];
			$this->data['billing_country'] = $shippingData['country'];
			$this->data['billing_regionCode'] = $shippingData['regioncode'];
			$this->data['billing_phone'] = $shippingData['phone'];
			$this->data['billing_phoneAreaCode'] = $shippingData['areacode'];
		} else {
			if (!empty($this->data['shipping_address1'])) {
				foreach ($this->data as $key => $val) {
					if (preg_match('/^shipping_/', $key)) {
						// override billing keys with shipping data
						$paymentKey = preg_replace('/^shipping_/', '', $key);
						$this->data['billing_' . $paymentKey] = $val;
					}
				}
			} else {
				// if shipping address not supplied, and no ref, use patient account address
				$patientInfo = WebUser::getUserInfo();
				$this->data['billing_address1'] = $patientInfo->address->address1;
				$this->data['billing_address2'] = $patientInfo->address->address2;
				$this->data['billing_city'] = $patientInfo->address->city;
				$this->data['billing_region'] = $patientInfo->address->region;
				$this->data['billing_country'] = $patientInfo->address->country;
				$this->data['billing_regionCode'] = $patientInfo->address->regioncode;
				$this->data['billing_phone'] = $patientInfo->address->phone;
				$this->data['billing_phoneAreaCode'] = $patientInfo->address->areacode;
			}
		}

		return;
	}

	public function addLegalAgreement()
	{
		$legalInfo = new stdClass();
		$legalInfo->patient_id	= WebUser::getUserID();
		$legalInfo->fullname	= WebUser::getFullName();
		$legalInfo->agree		= "Yes";
		$legalInfo->date		= date("Y-m-d");

		$patient = new Model_Patient();
		$status = $patient->addLegalAgreement($legalInfo);
		return $status;
	}

	public function validate()
	{
		$reply = new Model_Entity_Reply();

		$validator = new PW_JSON_Validation;
		$data = $validator->sanitize($this->data);
		$sessionValid = 1;

		$messages = array();
		$message = new Model_Entity_ReplyMessage();

		// check session is valid and conditions met for an order to be placed before validating entered data
		if (!WebUser::isLoggedIn()) {
			$sessionValid = 0;
			$message->type = 'error';
			$message->content = 'You must be logged in to place an order.';
			$messages[] = $message;
		} elseif (!Cart::haveItems()) {
			$sessionValid = 0;
			$message->type = 'error';
			$message->content = 'Cart is empty. Please add items to the cart before placing an order.';
			$messages[] = $message;
		}

		if ($sessionValid == 1) {
			$validationFields = array(
				'agree' => 'required|contains,true',
				'rx_forwarding' => 'alpha_space'
			);

			if (empty($data['rx_forwarding']) && !(Cart::isRxRequired($this->cartListItems))) {
				$data['rx_forwarding'] = 'OTC';
			}

			$shipping = new Shipping();

			// if shipping ref submitted use that, else check session for ref
			if (isset($data['shippingAddressRef']) && is_numeric($data['shippingAddressRef']) && ($data['shippingAddressRef'] > 0)) {
				$shippingRef = $data['shippingAddressRef'];
			} else {
				$shippingRef = $shipping->getAddressRef();
			}

			// if that ref is valid, use it, else show shipping form for new customers
			if (is_numeric($shippingRef) && ($shippingRef > 0)) {
				$data['shippingAddressRef'] = $shippingRef;
			} else {
				// Only used for new customers. Returning customers / additional addresses use
				// a json request to add the address first then use the ref
				if (!WebUser::isLoggedIn()) {
					$shippingAddressValidationFields = array(
						'shipping_address1' => 'required|street_address',
						'shipping_city' => 'required',
						'shipping_country' => 'required|alpha,exact_len=3',
						'shipping_region' => 'required',
						'shipping_regionCode' => 'required',
						'shipping_phoneAreaCode' => 'area_code',
						'shipping_phone' => 'phone_number',
					);
					$validationFields = array_merge($validationFields, $shippingAddressValidationFields);
				}
			}

			// If billing address is not the same as shipping and a shipping address ref is not set
			if (((isset($data['billing_useShippingAddress']) && ($data['billing_useShippingAddress'] != 'yes')) || !isset($data['billing_useShippingAddress']))
				&& ((!isset($data['shippingAddressRef'])) || (isset($data['shippingAddressRef']) && !is_int($data['shippingAddressRef'])))
			) {
				$billingAddressValidationFields = array(
					'billing_type' => 'required|alpha_dash',
					'billing_address1' => 'street_address',
					'billing_city' => 'required',
					'billing_country' => 'required|alpha,exact_len=3',
					'billing_region' => 'required',
					'billing_regionCode' => 'required',
					'billing_phoneAreaCode' => 'area_code',
					'billing_phone' => 'phone_number'
				);
				$validationFields = array_merge($validationFields, $billingAddressValidationFields);
			}

			$billingValidationFields = array();

			if ($data['billing_type'] == 'creditcard') {
				$billingValidationFields = array(
					'billing_firstName' => 'required|alpha',
					'billing_lastName' => 'required|alpha',
					'billing_creditCard_type' => 'alpha_space',
					'billing_creditCard_number' => 'valid_cc',
					'billing_creditCard_cvv' => 'integer',
					'billing_creditCard_expiryMonth' => 'numeric',
					'billing_creditCard_expiryYear' => 'integer'
				);
			} elseif ($data['billing_type'] == 'eft') {
				$billingValidationFields = array(
					'billing_cheque_name' => 'regex,/\w+\s{1}\w+/',
					'billing_bank_name' => 'required',
					'billing_bank_city' => 'required',
					'billing_bank_state' => 'required',
					'billing_bank_branchTransit' => 'numeric',
					'billing_cheque_account' => 'numeric',
					'billing_cheque_number' => 'numeric'
				);
			} elseif ($data['billing_type'] == 'draft' || $data['billing_type'] == 'custom') {
				$billingValidationFields = array(
					'billing_institution' => 'alpha_numeric_space',
				);
			}

			$validationFields = array_merge($validationFields, $billingValidationFields);

			$validated = $validator->validate($data, $validationFields);

			$reply->status = 'failed';
			$success = 0;
			if ($validated == 1) {
				$success = 1;
				$reply->status = 'success';
			} else {
				foreach ($validated as $valMsg) {
					$m = new Model_Entity_ReplyMessage();
					$m->type = 'validation error';
					$m->content = !empty($valMsg) ? (array) $valMsg : '';

					if (((get_option('pw_debug_mode') == 1) && ($m['type'] == 'debug')) || ($m['type'] != 'debug')) {
						$messages[] = $m;
					}
				}
			}
		}

		$reply->success = $success;
		$reply->messages = $messages;
		$reply->data = $data;

		return $reply;
	}

	public static function resetInfo()
	{
		PW_JSON_Session::deleteAutosave();
		Model_OrderTag::removeTagSessionAll();
		Billing::resetInfo();
		Shipping::resetInfo();
		CART::removeAllItems();
		CART::setAdditionalNotes();
	}

	public function addOrderComment($commentData)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data = $commentData;

		// create the request via XmlApi Request
		$requestOrderAddComment = new XmlApi_Request_OrderAddComment();
		$requestOrderAddComment->process($data);

		// return result
		$reply = $requestOrderAddComment->getData();
		return $reply;
	}

	// Get order questions based on whether user is logged in or not
	// As well as what order questions are turned on in the config
	public function getOrderQuestions()
	{
		$orderQuestions = array();

		// Questions always shown on cart

		if (get_option('pw_checkoutq_contact_patient', 1)) {
			$orderQuestions[] = array(
				'id' => 'contact_patient',
				'question' => 'Do you require counselling from a pharmacist for the medications you are taking?'
			);
		}
		if (get_option('pw_checkoutq_child_resistant_packaging', 1)) {
			$orderQuestions[] = array(
				'id' => 'child_resistant_packaging',
				'question' => 'Do you require child resistant packaging?'
			);
		}

		// New patient questions

		if (!WebUser::isLoggedIn()) {
			if (get_option('pw_checkoutq_call_for_refills', 1)) {
				$orderQuestions[] = array(
					'id' => 'call_for_refills',
					'question' => 'Call/Email for refills?'
				);
			}
		}

		return $orderQuestions;
	}
}
