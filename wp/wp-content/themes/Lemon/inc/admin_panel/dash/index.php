<div class="grid grid-cols-3 grid-rows-4 gap-2">
    
    <div class="border-dashed border-2 border-[<?= MAIN_COLOR?>] p-5 rounded-lg">
        <?php
            if(file_exists(get_template_directory()."/inc/admin_panel/ref/index.php")) {
                require(get_template_directory()."/inc/admin_panel/ref/index.php");
            }
        ?>
    </div>
    <div></div>
    <div></div>
</div>