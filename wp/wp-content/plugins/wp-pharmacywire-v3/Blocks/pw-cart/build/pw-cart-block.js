!function(){"use strict";window.wp.i18n,window.wp.blockEditor,jQuery((t=>{function e(){const e=t(".pwire-ajax-cart-widget.pw-block-widget"),i=e.find(".line-items");return e.pwireSpinner(),t.ajax({type:"POST",url:`${pw_json.request_url}?r=get-cart&pw_nonce=${pw_json.nonce}`,success:a=>{const s=JSON.parse(a);if(1===s.success){let a="",n="";if(e.removeClass("pwire-cart-empty pwire-cart-has-items"),i.empty(),Object.prototype.hasOwnProperty.call(s,"items")&&s.items.length?(i.empty(),t.each(s.items,((t,e)=>{let i=0==e.generic?"Brand":"Generic",s=e.strengthfreeform;s||(s=e.strength+e.strength_unit);let n=parseFloat(e.price);n%1==0?n=n.toFixed(2):n.toFixed(4).split(".")[1].length>4?n=Math.round(1e4*n)/1e4:(n=parseFloat(n.toFixed(4)),n=n.toFixed(Math.max(2,n.toString().split(".")[1].length))),a+=`<div id="widget-line-item-${e.package_id}" class="cart-widget-line-item widget-line-item">\n\t\t\t\t\t\t\t\t<div class="grid-x">\n\t\t\t\t\t\t\t\t\t<div class="heading cell">\n\t\t\t\t\t\t\t\t\t<b>${e.drug_name} <span class="brand-or-generic">${i}</span> - <span class="strength-quantity"><span class="product-strength">${s}</span></span></b> <span class="product-quantity">${Number(e.package_quantity)} ${e.package_quantity_units}</span>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class="grid-x">\n\t\t\t\t\t\t\t\t\t<div class="cell">\n\t\t\t\t\t\t\t\t\t\t<span class="ordered-amount">${Number(e.amount)} x $${n}</span>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class="grid-x">\n\t\t\t\t\t\t\t\t\t<div class="cell value">\t\n\t\t\t\t\t\t\t\t\t\t$${Number(e.sub_amount).toFixed(2)}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>`})),t(a).appendTo(i),e.addClass("pwire-cart-has-items")):e.addClass("pwire-cart-empty"),t(".pwire-ajax-cart-widget .cart-footer").empty(),Object.prototype.hasOwnProperty.call(s,"coupons")&&(t.each(s.coupons,((t,e)=>{let i="false"==e.usable?"invalid":"valid",a="",s=e.description;"false"!=e.removable?a=`Coupon: ${e["coupon-code"]}`:(a=s,s="");let d=e["discount-human"];e.discount>0?d=` - ${e["discount-human"]}`:e.discount<0&&(d=` + ${e["discount-human"]}`),n+=`<div id="widget-line-item-${e["coupon-code"]}" class="coupon-line-item widget-line-item coupon-usable-${i}">\n\t\t\t\t\t\t\t\t<div class="grid-x">\n\t\t\t\t\t\t\t\t\t<div class="heading cell">${a}</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class="grid-x">\n\t\t\t\t\t\t\t\t\t<div class="coupon-discount cell">\n\t\t\t\t\t\t\t\t\t\t<div class="description">${s}</div>\n\t\t\t\t\t\t\t\t\t\t<div class="discount value">${d}</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>`})),t(n).appendTo(i)),Object.prototype.hasOwnProperty.call(s,"items")){t(".pwire-ajax-cart-widget .cart-footer").empty();let e="";if(Object.prototype.hasOwnProperty.call(s,"coupons")&&0!=s.discount_total){let t=Number(s.discount_total).toFixed(2);s.discount_total>0?t=` - $${t}`:s.discount_total<0?(t=t.replace("-",""),t=` + $${t}`):t=`$${t}`,e=`<div class="coupons grid-x"><div class="small-6 cell heading">Discount:</div> <div class="small-6 cell value">${t}</div></div>`}let i=`<div class="sub-total grid-x"><div class="small-12 medium-6 cell heading">Subtotal:</div> <div class="small-12 medium-6 cell value">$${Number(s.sub_total).toFixed(2)}</div></div>${e}<div class="shipping grid-x"><div class="small-12 medium-6 cell heading">Shipping:</div> <div class="small-12 medium-6 cell value">${s.shipping_cost_human}</div></div><div class="total grid-x"><div class="small-12 medium-6 heading">Total (USD):</div> <div class="small-12 medium-6 cell value">$${Number(s.total).toFixed(2)}</div></div>`;if(t(i).appendTo(".pwire-ajax-cart-widget .cart-footer"),window.location.href!=pw_json.shopping_cart_url&&window.location.href!=pw_json.checkout_url){const e=`<div class="action"><a href="${pw_json.shopping_cart_url}" class="button">View Cart</a></div>`;t(e).appendTo(".pwire-ajax-cart-widget .cart-footer")}}}else i.empty(),t(".pwire-ajax-cart-widget .cart-footer").empty();e.pwireSpinner().stop()}})}e(),t(".pw-pharmacy-wrap").on("pwire:cart:updateCartForm pwire:cart:removeLineItem pwire:cart:orderSubmitted",(()=>{e()}))}))}();