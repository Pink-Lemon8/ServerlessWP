
<div id="address" class="mt-20" style="display: none;">
    <div class="mt-6 border-t border-gray-100">
      <dl class="divide-y divide-gray-100">
        <div class="bg-gray-50 px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
          <dt class="text-sm font-medium leading-6 text-gray-900">Billing Address</dt>
          <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
          <?= 
            Billing::getInfo()->address1." ".Billing::getInfo()->address2." ".Billing::getInfo()->address3." ".
            Billing::getInfo()->city. ", ". Billing::getInfo()->province ." ". Billing::getInfo()->postalcode. " ".Billing::getInfo()->country
            ?>
          </dd>
        </div>
      </dl>
    </div>
  </div>