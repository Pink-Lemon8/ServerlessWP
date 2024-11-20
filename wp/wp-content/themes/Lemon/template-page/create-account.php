<?php
$model_country = new Model_Country();
$USAStates = $model_country->getRegionsByCountry("usa");

$returnValues = null;
if (isset($_POST["action"]) && $_POST["action"] == "PL Create Account") {
    $pageRegister = new Page_Register();
    $returnValues = $pageRegister->registerSubmitJSON();
    $convertObject = json_decode($returnValues);
}

if ($returnValues == null || !$convertObject->success) {

    ?>

    <div class="flex mt-20  my-10 justify-center">
        <h3 class="currentStep absolute -mt-10 text-lg font-semibold"></h3>
        <nav aria-label="Progress" id="Progress">
            <ol role="list" class="steps flex items-center">
            </ol>
        </nav>
    </div>

    <?php

    if ($returnValues != null) {
        $Arraymessages = $convertObject->messages;
        foreach ($Arraymessages as $value) {
           alert($value->content->value);
        }
    }
    ?>

    <div class="overflow-hidden rounded-lg bg-white p-5 shadow">
        <form id="multi-step-form" enctype="application/x-www-form-urlencoded" method="post" name="Sign_Up_Edit">
            <input type="hidden" value="1" name="submitted">
            <input type="hidden" id="billing_useShippingAddress" name="billing_useShippingAddress" value="<?= isset($_POST['billing_useShippingAddress']) ? $_POST['billing_useShippingAddress'] : 'yes' ?>">
            <?php
            require('create-account/step1.php');
            require('create-account/step2.php');
            require('create-account/step3.php');
            ?>
        </form>
    </div>
    <?php
} else {
    alert_and_links('Thank you for creating an account on <strong>'.get_bloginfo('name').'</strong>.',"Account created successfully.",["Home" => network_site_url('/') ,"View Account" => network_site_url('/') . "account" ],'green',"success");
}

?>
<script src="https://unpkg.com/imask"></script>
<script src="<?= get_template_directory_uri() . '/js/from-masker.js' ?>"></script>