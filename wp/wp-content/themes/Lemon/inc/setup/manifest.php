<?php
define( 'WP_USE_THEMES', true );
require 'wp-load.php';
global $wpdb;

$siteName = get_option('blogname');
$name =  get_option('blogname');
$pageDescription =  get_option('blogdescription');;

$manifest = [
    "name" => $siteName,
    "gcm_user_visible_only" => true,
    "short_name" => $name,
    "description" => $pageDescription,
    "start_url" => "/",
    "display" => "standalone",
    "orientation" => "portrait",
    "background_color" => '#FFF',
    "theme_color" => MAIN_COLOR_MOBILE,
    "icons" => [[
      "src" => get_site_icon_url(),
      "sizes"=> "512x512",
      "type" => "image/png",
      "purpose" => "any"],
      [
      "src" => get_site_icon_url(),
      "sizes"=> "512x512",
      "type" => "image/png",
      "purpose" => "maskable"]
    ],
    "src" => get_site_icon_url(),
    "sizes" => "512x512",
    "type" => "image/png"
];

header('Content-Type: application/json');
echo json_encode($manifest);

?>