<?php get_header(); ?>
<span id="_GUARANTEE_Kicker" name="_GUARANTEE_Kicker" type="Kicker Custom Minimal1" style="align-center"></span>
<div class="bg-white py-12 sm:py-12">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:max-w-none">
          <div class="text-center">
            <h2
              class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl"
            >
              Frequently Asked Questions
            </h2>
            <p class="mt-4 text-lg font-semibold leading-10 text-gray-800">
              Most frequent questions and answers
            </p>
          </div>
          <div class="mx-auto max-w-7xl px-6 py-12 sm:py-12 lg:px-8 lg:py-12">
            <div class="mx-auto max-w-4xl divide-y divide-gray-900/10">
              <h2 class="text-2xl font-bold leading-10 tracking-tight text-gray-900">
                
              </h2>
			  
				  <?php
$phone_area = get_option('pw_phone_area');
$phone = get_option('pw_phone');

$PL_Drower_titles = [
    'Do I Need a Prescription for Medication?',
    'Is Delivery Available Throughout the United States?',
    'What Costs Are Associated with Shipping?',
    'Proper Storage Instructions for Insulin',
    'Does Your Pharmacy Match Competitor Prices?',
    'How Can I Speak with a Pharmacist?',
    'Are Generic Medications as Effective as Brand-Name Ones?',
    'Can I Pay with Insurance?',
    'What Is Your Policy on Returns?',
    'How Do You Protect Customer Information?'
];
$PL_Drower_contents = [
    'Yes, a valid prescription is necessary for all prescription drugs and insulin. Provide your doctor or pharmacy’s contact details after your order, and we will obtain the prescription for you.',
    'We provide delivery services to every state in the USA, including Alaska and Hawaii, ensuring everyone has access to our products.',
    'Our shipping fees are straightforward: $29.99 for refrigerated items and $14.99 for items that do not require refrigeration.',
    'Insulin should be refrigerated immediately if it arrives warm. It can remain at room temperature safely for up to 30 days if needed.',
    'We offer a price matching service when you present a valid proof of a lower price from a competitor. This cannot be used in conjunction with other promotions.',
    'You can discuss your medications with our pharmacists by calling 1-$phone_area-$phone. They are ready to assist you with any queries.',
    'Generic medications offer the same effectiveness as their brand-name counterparts, containing identical active ingredients, though their appearance may differ.',
    'We currently do not accept direct insurance payments, but we provide detailed invoices to help you claim insurance reimbursements.',
    'Returns are not accepted for medications unless they are defective. If your order is delayed beyond 10 business days, please contact our customer service.',
    'We ensure the confidentiality of your personal data under the strict guidelines of Canada’s PIPEDA, sharing information only with healthcare professionals involved in your care unless explicitly authorized by you.'
];

$temp_shortcode_string = '';
foreach ($PL_Drower_titles as $key => $value) {
    $temp_shortcode_string .= 'title' . ($key + 1) . '="' . $value . '" content' . ($key + 1) . '="' . htmlentities($PL_Drower_contents[$key], ENT_QUOTES) . '" ';
}

echo do_shortcode('[PL_Drower ' . $temp_shortcode_string . ']');
?>
              
            </div>
          </div>
        </div>
                   
      </div>
    </div>
    <?php get_footer(); ?>