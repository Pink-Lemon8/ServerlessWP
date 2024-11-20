<?php 
   $orderDone = $_SESSION["PL_recent_order_result"];
   $orderPostAction = $_SESSION["PL_recent_order_post_datas"];
   $cartDone = $orderDone->cart;
   $patientDone = $orderDone->patient;
   var_dump($orderDone,$orderPostAction,$cartDone,$patientDone);
   ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
   <head>
      <META http-equiv="Content-Type" content="text/html; charset=utf-8">
   </head>
   <body>
      <table role="presentation" style="margin:0 auto;background-color:#f6f5f5;border-radius:12px;">
         <tbody>
            <tr>
               <td style="width:640px;padding:20px 20px 0 20px;"><img src="https://pandarx.com/wp-content/uploads/2024/01/logo-2x.png" style="display:block;margin-bottom:10px" alt="pandaRX"/>				
            </tr>
            <tr>
               <td style="width:640px;padding:20px;font-family:arial;font-size:13px;color:#333;">
                  <table style="width: 100%;">
                     <tr>
                        <td colspan="2" style="padding-bottom: 10px; border-bottom: 3px solid #406593;">
                           Hi testing,<br /><br />
                           Thank you for choosing <a href="http://polarbearmeds.com">PolarBearMeds</a>. We have started to process your order. If you haven't already sent in your prescription, remember to do so as soon as possible so your order is not delayed.<br /><br />
                           Ways to send in your prescription:<br /> 
                           <ul>
                           <li style="padding-bottom:10px;"><b>Fax</b> a copy over to 1-888-348-6515</li>
                           <li style="padding-bottom:10px;"><b>Email</b> a copy to <a href="mailto:info@polarbearmeds.com">info@polarbearmeds.com</a></li>
                           <li style="padding-bottom:10px;"><a href="http://polarbearmeds.com/upload-prescription-document/" class="strong">Upload</a> an image of your prescription through our website</li>
                           <li style="padding-bottom:10px;">
                              <b>Mail</b> the prescription to:<br />PolarBearMeds<br/>PO Box 55057<br>
                              RPO Dakota Crossing<br>
                              Winnipeg, Manitoba<br>
                              Canada<br>
                              R2N 0A8<br />
                        </td>
                     </tr>
                     <tr>
                     <td class="address-final background" style="vertical-align: top; padding-top: 10px;">
                     <strong>Shipping Address</strong><br /><div class="address" data-address-id="User-127472">556 De la Seigneurie Blvd<br/>Winnipeg, Arizona&nbsp;12345<br/>United States&nbsp;<br/>p: (204) 123-1232<br></div>	</td>
                     <td style="vertical-align: top; padding-top: 10px; text-align: right;"><b>Order #:</b> 241855</td>
                     </tr>
                  </table>
                  <table style="width: 100%; border: none; padding-bottom: 1rem;">
                  <tr>
                  <td align="left" width="100%" colspan="2"><hr size="1" width="100%" noshade="noshade" color="#999999" /></td>
                  </tr>
                  <tr>
                  <td align="right" style="vertical-align: top;" colspan="2">
                  <table border="0" cellspacing="3" cellpadding="0" style="width: 100%;">
                  <tr>
                  <td style="text-align: left;">&nbsp;</td>
                  <td style="text-align: left;"><b>Item</b></td>
                  <td style="text-align: center;"><b>Quantity</b></td>
                  <td style="text-align: right;"><b>Price</b></td>				
                  </tr><tbody data-user-id="127472" data-patient-id="127472">
                  <tr class="header">
                  <td class=" patient-name show-member " colspan="2">test, testing</td>
                  </tr><tr class="odd lineitem" id="MCL-957997" data-line-id="957997" data-product-id="DP-9777" data-part-id="DP-9777" data-option-id="DP-9777"> 
                  <td class="line-info" valign="top" style="vertical-align: top;"><td class="description" valign="top" style="vertical-align: top;"><span title="DP-9777">Ozempic (4mg/3mL) (1 pens qty)</span></td><td class="number quantity" id="quantity_957997" style="text-align: center;"><span>1</span>&nbsp;<span>(1 pens total)</span>	
                  </td><td class="number line-total currency  positive " style="text-align: right;">$469.79</td>					</tr></tbody>				
                  </table>
                  </td>
                  </tr>
                  <tr>
                  <td colspan="2"><hr size="1" width="100%" noshade="noshade" color="#999999" /></td>
                  </tr>
                  <tfoot class="summary">
                  <tr class="summary-total-start"><td width="300px" class="summary-total-heading" style="text-align: right;">Subtotal</td>
                  <td class="summary-total-value currency positive subtotal" style="text-align: right;">$469.79</td>
                  </tr> 
                  <tr>
                  <td class="summary-total-heading" style="text-align: right;">Shipping</td>
                  <td class="summary-total-value currency positive shipping"  style="text-align: right;">$29.99</td>
                  </tr><tr class="summary-total-stop">
                  <td class="summary-total-heading" style="text-align: right;">Total</td>
                  <td class="summary-total-value total-summary currency positive"  style="text-align: right;">$499.78</td>
                  </tr>
                  </tfoot>	
                  </table>				
               </td>
            </tr>
         </tbody>
      </table>
      <div class="social-links" style="margin: 1.5rem auto; width: 640px;">
      <!-- <p>For the latest updates and sales follow us... <a href="https://facebook.com/polarbearmeds" style="text-decoration: none; color: #000;" target="_blank"><img src="https://polarbearmeds.com/wp-content/uploads/2021/12/f_logo_RGB-Blue_250.png" style="width: 35px; height: 35px; vertical-align: middle;" /> facebook.com/polarbearmeds</a></p> -->
      </div>
   </body>
</html>