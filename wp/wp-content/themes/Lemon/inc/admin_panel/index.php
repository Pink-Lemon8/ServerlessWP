<?php
$base_url = home_url().'/wp-admin/admin.php?page=PinkLemon8-panel&';

$tabs = [
    "dash" => "Lemon Analytics",
    "ref" => "Ref Users",
    "settings" => "Theme Settings",
];

$pageKey = "pl-theme-admin-page";
$page = "dash";
if(isset($_GET[$pageKey]))
  $page = sanitize_text_field($_GET[$pageKey]);


$pageActionKey= "pl-theme-action";
$pageAction = "";
if(isset($_GET[$pageActionKey]))
  $pageAction = sanitize_text_field($_GET[$pageActionKey]);

?>

<div style="width:99%">
  <div class="sm:hidden">
    <label for="tabs" class="sr-only">Select a tab</label>

    <select id="tabs" name="tabs" onchange="location = this.value;" class="block w-full rounded-md border-gray-300 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-[<?= MAIN_COLOR_FOCUS ?>]">
    <?php
    foreach ($tabs as $tab)
      echo '<option value="'.$base_url.$pageKey.'='.(array_search($tab,$tabs)).'" '.(array_search($tab,$tabs) == $page ? 'selected' : '').' >'.$tab.'</option>';
    ?>
    </select>
  </div>
  <div class="hidden sm:block mb-7">
    <nav class="isolate flex divide-x divide-gray-200 rounded-lg shadow" aria-label="Tabs">
    <?php

        foreach ($tabs as $tab)
        {
    ?>
      <a href="<?= $base_url.$pageKey.'='.(array_search($tab,$tabs)) ?>" class="text-gray-900 group relative min-w-0 flex-1 overflow-hidden bg-white py-4 text-center text-sm font-medium hover:bg-gray-50 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:border-[<?= MAIN_COLOR_FOCUS ?>] active:border-[<?= MAIN_COLOR_FOCUS ?>] focus:z-10">
        <span><?= $tab ?></span>
        <span aria-hidden="true" class="<?= (array_search($tab,$tabs) == $page ? 'bg-['.MAIN_COLOR.']' : '') ?> absolute inset-x-0 bottom-0 h-0.5"></span>
      </a>
      <?php
      }
      ?>
    </nav>
  </div>
</div>

<?php

if(file_exists(dirname(__FILE__).'/'.$page."/index.php")) {
    require_once(dirname(__FILE__).'/'.$page."/index.php");
}

?>
<script src="https://cdn.tailwindcss.com"></script>