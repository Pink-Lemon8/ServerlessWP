<?php

if(isset($_POST["add_products"])){
    if(add_all_product_bot())  
        echo '<script>alert("Products added successfully");</script>';
    else
        echo '<script>alert("Something went wrong");</script>';
}

if(isset($_POST["remove_products"])){
    if(remove_all_product_post())  
        echo '<script>alert("Products removed successfully");</script>';
    else
        echo '<script>alert("Something went wrong");</script>';
}

?>

<div class="flex flex-row gap-4 justify-center">
    <form action="#" method="post">
        <button type="submit" name="add_products" value="submit" class="rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Add Products</button>
    </form>
    <form action="#" method="post">
        <button type="submit" name="remove_products" value="submit" class="rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Remove Products</button>
    </form>
</div>

