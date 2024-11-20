<?php

$loginPage = new Page_Login();
$result = false;
if (isset($_POST["PL_action_ref"])) {
    $email = sanitize_text_field($_POST["email"]);
    $password = sanitize_text_field($_POST["password"]);
    $result = get_login_ref($email,md5($password));
}
if(!$result){
?>

<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Sign in to your ref account</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">

        <?php
        if (isset($_POST["PL_action_ref"]) && !$result)
            alert("Invalid email and/or password entered.");
        ?>

        <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
            <form class="space-y-6" name="login" method="post">
                <div>
                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                <div class="mt-2">
                    <input id="email" name="email" type="email" autocomplete="email" required
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                </div>
                </div>

                <div>
                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                <div class="mt-2">
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[<?= MAIN_COLOR_FOCUS ?>] sm:text-sm sm:leading-6">
                </div>
                </div>

                <div>
                <button type="submit" name="PL_action_ref" value="PL" onclick="login_loading()"
                    class="flex w-full justify-center rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Sign
                    in</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
} else{ 
    $ref_user_info = get_ref_info_by_email_and_password($email,md5($password))[0];
    $orders = get_ref_ordered($ref_user_info->id);
    $showOrders = array();
   
    foreach ($orders as $order) {
        $created_date = date_format(date_create($order->create_date),"F, Y");
        $earn = 0;
        if($ref_user_info->commission_type == "percent"){
            $earn = $order->sub_total_price * floatval($ref_user_info->commission_rate) / 100;
        }
        elseif($ref_user_info->commission_type == "fix")
            $earn = $ref_user_info->commission_rate;
        $showOrders[$created_date]["money"] = isset($showOrders[$created_date]["money"]) ? $showOrders[$created_date]["money"]+$earn : $earn;
        $showOrders[$created_date]["count"] = isset($showOrders[$created_date]["count"]) ? $showOrders[$created_date]["count"]+1 : 1; 
    }
    ?>

    <div class="py-12 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900"><?= $ref_user_info->full_name; ?></h1>
            <p class="mt-2 text-sm text-gray-700">Example : <?= esc_url(home_url('/')) ?>?ref=<strong><?= $ref_user_info->ref_code; ?></strong></p>
        </div>
        <?php /*
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add user</button>
        </div>*/
        ?>
        </div>
        <div class="mt-8 sm:-mx-0">
        <table class="min-w-full">
            <thead>
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Month</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Order Count</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estimated total income</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            <?php foreach ($showOrders as $key => $show): ?>
            <tr>
                <td class="w-full max-w-0 py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:w-auto sm:max-w-none sm:pl-0"><?= $key ?></td>
                <td class="px-3 py-4 text-sm text-gray-500"><?= $show["count"] ?></td>
                <td class="px-3 py-4 text-sm text-gray-500">$<?= $show["money"] ?></td>
            </tr>
            <?php endforeach;?>
            </tbody>
        </table>
        </div>
    </div>
  
<?php
}
?>