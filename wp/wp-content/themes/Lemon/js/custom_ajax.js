function pl_cart_change_quantity(
  id,
  quantity,
  respondShow = "#pl_ajax_result"
) {
  if (quantity.value == "0") {
    pl_remove_from_cart(id, respondShow);
    return false;
  }
  var data = {
    action: "pl_cart",
    security: PL_AJAX_ACTION.security,
    pl_package_id: id,
    do: "update",
    pl_package_quantity: quantity.value,
  };
  jQuery.post(PL_AJAX_ACTION.ajax_url, data, function (callBack) {
    jQuery(respondShow).html(callBack);
  });
  quantity.disabled = true;
  quantity.disabled = false;
}

function pl_remove_from_cart(id, respondShow = "#pl_ajax_result") {
  var data = {
    action: "pl_cart",
    security: PL_AJAX_ACTION.security,
    do: "remove",
    pl_package_id: id,
  };
  jQuery.post(PL_AJAX_ACTION.ajax_url, data, function (callBack) {
    jQuery("li#list-" + id).fadeOut("normal", function () {
      jQuery("li#list-" + id).remove();
    });
    jQuery(respondShow).html(callBack);
  });
}

function order_ui_update(data = []) {
  console.log(data);
  const obj = JSON.parse(data);
  if (obj.items.length == 0) location.reload();
  if (obj.sub_total != null)
    jQuery("dd#pl_cart_subtotal").html("$" + obj.sub_total.toFixed(2));
  if (obj.total != null)
    jQuery("dd#pl_cart_total").html("$" + obj.total.toFixed(2));
  if (obj.shipping_cost != null)
    jQuery("dd#pl_cart_shipping").html(
      obj.shipping_cost != 0 ? "$" + obj.shipping_cost.toFixed(2) : "Free"
    );
  jQuery("#pl_cart_summary").hide();
  jQuery("#pl_cart_summary").fadeIn();
}

function copon_ui_update(data = []) {
  console.log(data);
  const obj = JSON.parse(data);
  const coponKeys = obj.coupons ? Object.keys(obj.coupons) : [];
  jQuery("#coupon-show").addClass("hidden");
  jQuery("#loading-copon").hide();
  coponKeys.forEach((key) => {
    let copon = obj.coupons[key];
    jQuery("#remove-copon").attr("data-copon", copon["coupon-code"]);
    jQuery("#coupon-text").html(copon["coupon-code"]);
    jQuery("#coupon-value").html(copon["discount-human"]);
    jQuery("#coupon-show").removeClass("hidden");
  });
  jQuery("#couponField").hide();
  jQuery("#toggleCouponBtn").show();
  order_ui_update(data);
  jQuery("#loading-copon").hide();
}

function pl_add_cupon(
  cupon_code,
  user_id = null,
  respondShow = "#pl_ajax_result"
) {
  var data = {
    action: "pl_cart_cupon",
    security: PL_AJAX_ACTION.security,
    do: "add",
    cupon_code: cupon_code,
    "": user_id != null ? user_id : -1,
  };
  jQuery.post(PL_AJAX_ACTION.ajax_url, data, function (callBack) {
    jQuery(respondShow).html(callBack);
  });
}

function pl_remove_cupon(
  cupon_code,
  user_id = null,
  respondShow = "#pl_ajax_result"
) {
  var data = {
    action: "pl_cart_cupon",
    security: PL_AJAX_ACTION.security,
    do: "remove",
    cupon_code: cupon_code,
    user_id: user_id != null ? user_id : -1,
  };
  jQuery.post(PL_AJAX_ACTION.ajax_url, data, function (callBack) {
    jQuery(respondShow).html(callBack);
  });
}

function remove_copon_button(button) {
  const copon_code = button.getAttribute("data-copon");
  pl_remove_cupon(copon_code);
}

function apply_copon_button() {
  const copon_code = jQuery("#copon_text_field").val();
  jQuery("#couponField").hide();
  jQuery("#loading-copon").fadeIn();
  pl_add_cupon(copon_code);
}
