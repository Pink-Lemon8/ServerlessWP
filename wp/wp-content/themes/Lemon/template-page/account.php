<?php

$patientModel = new Model_Patient();

$patient = new Model_Entity_Patient();
$patient->patientid = WebUser::getUserID();
$result = $patientModel->getPatientInfo($patient)->patient;

?>

<div>
  <div class="sm:hidden mt-8">
      <label for="tabs" class="sr-only">Select a tab</label>
      <select id="content-tabs-mobile" onchange="show_profile_content_mobile(this,'<?= MAIN_COLOR ?>')"
          class="block w-full rounded-md border-gray-300 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-[<?= MAIN_COLOR_FOCUS ?>]  pl-3 pr-7">
          <option selected value="#profile">Profile</option>
          <option value="#orders">Orders</option>
          <option value="#address">Billing Address</option>
          <option value="#document-upload">Document Upload</option>
          <option value="#medical-questionnaire">Medical Questionnaire</option>
      </select>
  </div>
  <div class="hidden sm:block sm:mt-7">
      <nav id="content-tabs" class="isolate flex divide-x divide-gray-200 rounded-lg shadow">
          <a href="#" do="#profile" onclick="show_profile_content(this,'<?= MAIN_COLOR ?>')"
              class="text-gray-900 rounded-l-lg group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
              <span>Profile</span>
              <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-0.5 bg-[<?= MAIN_COLOR ?>]"></span>
          </a>
          <a href="#" do="#orders" onclick="show_profile_content(this,'<?= MAIN_COLOR ?>')"
              class="text-gray-500 group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
              <span>Orders</span>
              <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-0.5"></span>
          </a>
          <a href="#" do="#address" onclick="show_profile_content(this,'<?= MAIN_COLOR ?>')"
              class="text-gray-500 group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
              <span>Billing Address</span>
              <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-0.5"></span>
          </a>
          <a href="#" do="#document-upload" onclick="show_profile_content(this,'<?= MAIN_COLOR ?>')"
              class="text-gray-500 group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
              <span>Document Upload</span>
              <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-0.5"></span>
          </a>
          <!-- <a href="#" do="#medical-questionnaire" onclick="show_profile_content(this,'<?= MAIN_COLOR ?>')"
              class="text-gray-500 rounded-r-lg group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
              <span>Medical Questionnaire</span>
              <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-0.5"></span>
          </a> -->
      </nav>
  </div>
</div>



<div id="profile-content">

<?php 
require('account/profile.php');
require('account/order.php');
require('account/billing.php');
require('account/document.php');
// require('account/medical.php');
?>

</div>