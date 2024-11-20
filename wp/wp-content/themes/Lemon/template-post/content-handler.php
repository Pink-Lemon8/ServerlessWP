<?php

$id = 0; // add the ID of the page where the zero is
$page = get_page($id);
$title = $page->post_title;
echo '<h2 class="sr-only">'.apply_filters('post_title', $title).'</h2>';
$template_file = get_template_directory()."/template-post/".$page->post_type.".php";
if(file_exists($template_file)){
  require_once($template_file);
}
else
  echo apply_filters('the_content', $page->post_content);

?>