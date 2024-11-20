

<?php 

$rateTypes = ['percent'=>'Percentage per order','fix'=>'Fixed per order'];

if(isset($_POST["ref_user_action"])){

    $ref_user_id = sanitize_text_field($_POST["ref_user_id"]);
    $full_name = sanitize_text_field($_POST["ref_full_name"]);
    $email = sanitize_text_field($_POST["ref_email"]);
    $password = md5(sanitize_text_field($_POST["ref_password"]));
    $ref_code = sanitize_text_field($_POST["ref_code"]);
    $commission_type = sanitize_text_field($_POST["commission_type"]);
    $commission_rate = sanitize_text_field($_POST["commission_rate"]);
    $expire_date = sanitize_text_field($_POST["expire_date"]) != '' ? date_format(date_create(sanitize_text_field($_POST["expire_date"])),"Y-m-d") : null;
    $result = null;
    if(isset($ref_user_id) && $ref_user_id == -1 ){
        $result = set_ref_info($full_name,$email,$password,$ref_code,$commission_type,$commission_rate,$expire_date);
    }
    elseif( $ref_user_id != ''){
        $result = update_ref_info($ref_user_id,$full_name,$email,$password,$ref_code,$commission_type,$commission_rate,$expire_date);
    }
    
    if($result){
        echo '<div class="pr-4">';
        alert("Action is done.","green","success");
        echo '</div>';
    }
    else{
        echo '<div class="pr-4">';
        alert("Error. Please try again!","red","fail");
        echo '</div>';
    }

}

?>

<div class="relative z-50 hidden" id="pl_user_action" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed top-50 inset-0 overflow-hidden">

        <div class="absolute inset-0 overflow-hidden">

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                <div class="pointer-events-auto w-screen max-w-2xl">
                    <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                        <div class="mt-10 px-4 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-base font-semibold leading-6 text-gray-900" id="slide-over-title">Ref User</h2>
                                <div class="ml-3 flex h-7 items-center">
                                    <button type="button" onclick="clearForm('user_info');jQuery('#pl_user_action').toggle('slide');" class="relative rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-offset-2">
                                    <span class="absolute -inset-2.5"></span>
                                    <span class="sr-only">Close panel</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="relative mt-6 flex-1 px-4 sm:px-6">
                            
                            <form method="post" id="user_info">
                                <input type="hidden" id="ref_user_id" name="ref_user_id" value="-1">
                                <div class="space-y-12">
                                    <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                                        <div class="sm:col-span-3">
                                        <label for="first-name" class="block text-sm font-medium leading-6 text-gray-900">First name</label>
                                        <div class="mt-2">
                                            <input type="text" name="ref_full_name" id="ref_full_name" required autocomplete="false" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6" placeholder="Full Name" >
                                        </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                        <label for="last-name" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                                        <div class="mt-2">
                                        <input type="email" max="50" name="ref_email" id="ref_email" required autocomplete="false" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6" placeholder="Email" >
                                        </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                        <label for="first-name" class="block text-sm font-medium leading-6 text-gray-900">Ref Code</label>
                                        <div class="mt-2">
                                            <input type="text" max="50" name="ref_code" id="ref_code" required autocomplete="false" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6" placeholder="Ref Code" >
                                        </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                        <label for="last-name" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                                        <div class="mt-2">
                                            <input type="password" name="ref_password" id="ref_password" required autocomplete="false" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6" placeholder="Password" >
                                        </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                        <label for="last-name" class="block text-sm font-medium leading-6 text-gray-900">Commission Type</label>
                                        <div class="mt-2">
                                            <select name="commission_type" id="commission_type" class="block w-full rounded-md border-1 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>]">
                                            <?php 
                                                foreach ($rateTypes as $key => $value)
                                                    echo '<option value="'.$key.'">'.$value.'</option>';
                                            ?>
                                            </select>
                                        </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                        <label for="last-name" class="block text-sm font-medium leading-6 text-gray-900">Commission Rate</label>
                                        <div class="mt-2">
                                            <input type="number" name="commission_rate" id="commission_rate"  autocomplete="false" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 overflow-hidden focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6" placeholder="Price" >
                                        </div>
                                        </div>

                                        <div class="sm:col-span-6">
                                        <label for="last-name" class="block text-sm font-medium leading-6 text-gray-900">Expire Date</label>
                                        <div class="mt-2">
                                            <input type="date" name="expire_date" id="expire_date"  autocomplete="false" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 overflow-hidden focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6" placeholder="Price" >
                                        </div>
                                        </div>

                                    </div>
                                
                                </div>

                                <div class="mt-6 flex items-center justify-end gap-x-6">
                                    <button type="button" onclick="clearForm('user_info');jQuery('#pl_user_action').toggle('slide');" class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                                    <button type="submit" name="ref_user_action" value="submit" class="rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Save</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
