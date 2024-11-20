<?php
require_once("ref_user.php");

?>

<div class="pr-4">
  <div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
      <h1 class="text-base font-semibold leading-6 text-gray-900">Ref Links</h1>
      <p class="mt-2 text-sm text-gray-700">Example : <?= site_url("/")."?ref=" ?><strong>REF_CODE</strong></p>
    </div>
    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
      <button type="button" onclick="clearForm('user_info');jQuery('#ref_user_id').val('-1');jQuery('#pl_user_action').toggle('slide');" class="block rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_FOCUS ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Add Ref User</button>
    </div>
  </div>
  <div class="mt-8">
    <table class="min-w-full divide-y divide-gray-300">
      <thead>
        <tr>
          <th scope="col" class="py-3.5 pl-2 pr-3 text-left text-sm font-semibold text-gray-900">Full Name</th>
          <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Email</th>
          <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Ref Code</th>
          <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Password</th>
          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Expire Date</th>
          <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Create Date</th>

          <th scope="col" class="relative py-3.5 pr-3">
            <span class="sr-only">Edit</span>
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white">
        <?php foreach (get_ref_info() as $row): ?>
        <tr>
          <td class="hidden px-3 py-3.5 text-sm text-gray-500"><?= $row->id ?></td>
          <td class="py-4 pl-2 pr-3 text-sm font-medium text-gray-900 sm:w-auto sm:max-w-none">
            <?= $row->full_name ?>
            <dl class="font-normal lg:hidden">
              <dt class="sr-only">User Info</dt>
              <dd class="mt-1 truncate text-gray-500 sm:hidden"><?= $row->email ?></dd>
              <dd class="mt-1 truncate text-gray-500 sm:hidden"><?= $row->ref_code ?></dd>
            </dl>
          </td>

          <td class="hidden px-3 py-6 text-sm text-gray-500 sm:table-cell"><?= $row->email ?></td>
          <td class="hidden px-3 py-3.5 text-sm text-gray-500 sm:table-cell">
            <span>
            <?= site_url("/")."?ref=" ?><strong><?= $row->ref_code ?></strong>
          </span>
          <button type="button" onclick="copy_ref_link('<?= site_url('/').'?ref='.$row->ref_code ?>')" class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Copy</button></td>
          <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell">********</td>
          <td class="px-3 py-3.5 text-sm text-gray-500"><?= $row->expire_date != null ? date_format(date_create($row->expire_date),"Y-m-d") : "No" ?></td>
          <td class="px-3 py-3.5 text-sm text-gray-500"><?= date_format(date_create($row->create_date),"Y-m-d") ?></td>
          <td class="py-3.5 pr-3 text-center text-sm font-medium">
            <a href="#" onclick="copy_ref_info_to_form('<?= $row->id ?>','<?= $row->full_name ?>','<?= $row->email ?>','<?= $row->ref_code ?>','<?= $row->commission_type	 ?>','<?= $row->commission_rate	 ?>','<?= $row->expire_date != null ? date_format(date_create($row->expire_date),'Y-m-d') : 'No' ?>');" class="text-[<?= MAIN_COLOR ?>] hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">Edit <span class="sr-only">, <?= $row->full_name ?></span></a>
          </td>

        </tr>              
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="pr-4 mt-20">
  <div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
      <h1 class="text-base font-semibold leading-6 text-gray-900">Orders</h1>
    </div>
  </div>
  <div class="mt-8 flow-root">
    <div class="-my-2 overflow-x-auto">
      <div class="inline-block min-w-full py-2 align-middle">
        <table class="min-w-full divide-y divide-gray-300">
          <thead>
            <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 lg:pl-8">Name</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Ref Code</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Order ID</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Sub Total Price</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            
            <?php foreach (get_ref_both_tables() as $row): ?>
            <tr>
              <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8"><?= $row->full_name ?></td>
              <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900"><?= $row->ref_code ?></td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= $row->order_id ?></td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= "$".$row->sub_total_price ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function copy_ref_link($adress){
navigator.clipboard.writeText($adress);
alert("Copied the Link: " + $adress);
}
function clearForm(fromID){
  let inputs = jQuery("form#"+fromID+" input");
  inputs.val('');
}
function copy_ref_info_to_form(id,full_name,email,ref_code,commission_type,commission_rate,expire_date){
  jQuery("form#user_info input#ref_user_id").val(id);
  jQuery("form#user_info input#ref_full_name").val(full_name);
  jQuery("form#user_info input#ref_email").val(email);
  jQuery("form#user_info input#ref_code").val(ref_code);
  jQuery("form#user_info input#ref_password").val('');
  jQuery("form#user_info input#commission_rate").val(commission_rate);
  jQuery("form#user_info input#expire_date").val(expire_date);
  jQuery("form#user_info select#commission_type option").removeAttr('selected').filter('[value='+commission_type+']').attr('selected', true);
  jQuery('#pl_user_action').toggle('slide');
}
</script>