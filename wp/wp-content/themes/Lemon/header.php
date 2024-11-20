<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <!-- BSG Trustmark -->
<script type="text/javascript" src="//guarantee-cdn.com/SealCore/api/gjs?SN=969068574&t=10"></script>

  <meta name="description" content="<?php bloginfo('description'); ?>" />
  <title>
    <?php bloginfo('name'); ?>
    <?php wp_title(); ?>
  </title>
  <meta name="theme-color" content="<?= MAIN_COLOR_MOBILE ?>" />
  <link rel="manifest" href="/manifest.json">
  <meta name="viewport" content="width=device-width">
  <?php wp_head(); ?>
</head>

<body class="bg-[<?= MAIN_BG_COLOR ?>]" color-data="<?= MAIN_COLOR.",".MAIN_COLOR_HOVER.",".MAIN_COLOR_FOCUS.",".MAIN_COLOR_ACTIVE.",".MAIN_COLOR_MOBILE ?>">
  <?php

  wp_body_open();
  template_loading(false);
  $menu_bar = wp_get_menu_array("header-menu-bar");
  $menu_bar_sub = get_sub_menu_array($menu_bar);


  $menu_bar_mobile = wp_get_menu_array("header-menu-bar-mobile");
  $menu_bar_mobile_sub = get_sub_menu_array($menu_bar_mobile);


  ?>
  <div class="bg-[<?= MAIN_BG_COLOR ?>]">

    <div id="nav-mobile-side" class="relative z-40 lg:hidden hidden" role="dialog" aria-modal="true">

      <div class="fixed inset-0 z-40 flex">

        <div class="relative flex w-full flex-col overflow-y-auto bg-white pb-12 shadow-xl">

          <div class="flex px-4 pb-2 pt-5">
            <button type="button" onclick="sideToggle();"
              class="-m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400">
              <span class="sr-only">Close</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <div class="grow flex justify-center">
              <?php if (get_header_image()): ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                  <span class="sr-only">
                    <?php echo esc_attr(get_bloginfo('name', 'display')); ?>
                  </span>
                  <img class="h-8 w-auto" src="<?php header_image(); ?>"
                    width="<?php echo absint(get_custom_header()->width); ?>"
                    height="<?php echo absint(get_custom_header()->height); ?>"
                    alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>">
                    </a>
              <?php endif; ?>
            </div>
          </div>
          <?php
          if ($menu_bar_mobile)
            foreach ($menu_bar_mobile as $item) {
              $check_menu_depth = check_menu_depth($item->ID, $menu_bar_mobile_sub);
              if ($item->menu_item_parent == 0)
                if ($check_menu_depth == 2) {
                  ?>

                  <div class="mt-2">

                    <div class="border-b border-gray-200">
                      <div class="-mb-px flex space-x-8 px-4" aria-orientation="horizontal" role="tablist">
                        <button
                          class="border-transparent text-gray-700 flex-1 whitespace-nowrap border-b-2 px-1 py-4 text-base font-semibold text-left"
                          type="button">
                          <?= $item->title ?>
                        </button>
                      </div>
                    </div>

                    <div class="space-y-12 px-4 py-6 max-w-xs" aria-labelledby="tabs-1-tab-1" role="tabpanel" tabindex="0">

                      <div class="grid grid-cols-2 gap-x-4 gap-y-10">
                        <?php

                        foreach ($menu_bar_mobile_sub as $first_sub_item)
                          if ($first_sub_item != null && $first_sub_item->menu_item_parent == $item->ID) foreach ($menu_bar_mobile_sub as $second_sub_item)
                              if ($second_sub_item != null && $second_sub_item->menu_item_parent == $first_sub_item->ID) {
                                ?>
                                <div class="group relative">
                                  <div class="aspect-h-1 aspect-w-1 overflow-hidden rounded-md bg-gray-100 group-hover:opacity-75">
                                    <?= get_the_post_thumbnail($second_sub_item->object_id, 'large', array('class' => 'object-cover object-center')); ?>
                                  </div>

                                  <a href="<?= $second_sub_item->url ?>"
                                    class="mt-6 block text-sm font-medium text-gray-900 hover:text-[<?= MAIN_COLOR ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                                    <span class="absolute inset-0 z-10" aria-hidden="true"></span>
                                    <?= $second_sub_item->title ?>

                                  </a>

                                  <p aria-hidden="true" class="mt-1 text-sm text-gray-500">Go Now</p>
                                </div>
                                <?php
                              }
                        ?>
                      </div>

                    </div>

                  </div>
                  <?php
                } else if ($check_menu_depth == 1) {

                  ?>
                    <div class="space-y-6 border-t  border-gray-200 py-6">

                      <div class="border-b border-gray-200">
                        <div class="-mb-px flex space-x-8 px-4" aria-orientation="horizontal" role="tablist">
                          <button
                            class="border-transparent text-gray-700 flex-1 whitespace-nowrap border-b-2 px-1 py-4 text-base font-semibold text-left"
                            aria-controls="tabs-1-panel-1" role="tab" type="button">
                          <?= $item->title ?>
                          </button>
                        </div>
                      </div>

                      <?php
                      foreach ($menu_bar_mobile_sub as $sub_item)
                        if ($sub_item != null && $sub_item->menu_item_parent == $item->ID) {
                          ?>
                          <div class="flow-root px-4">
                            <a href="<?= $sub_item->url ?>"
                              class="-m-2 block p-2 font-medium text-gray-900 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]"><?= $sub_item->title ?></a>
                          </div>
                        <?php
                        }
                      ?>
                    </div>
                  <?php

                } else if ($check_menu_depth == 0) {
                  ?>
                      <div class="space-y-6 border-t border-gray-200 px-4 py-6">
                        <div class="flow-root">
                          <a href="<?= $item->url ?>" title="<?= $item->title ?>"
                            class="-m-2 block p-2 font-medium text-gray-900 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]"><?= $item->title ?></a>
                        </div>
                      </div>
                  <?php
                }
            }
          ?>
        </div>

      </div>

    </div>

    <header class="relative z-30">
      <nav aria-label="Top" class="fixed top-0 z-30 w-full drop-shadow-lg" style="background-color: #141C3E;">

        <div class="bg-[<?= MAIN_COLOR_MOBILE ?>] py-4 sm:py-0">
          <div class="mx-auto flex h-10 max-w-7xl items-center justify-between text-white px-4 sm:px-6 lg:px-8">
            <div class="grow pr-5 pb-1 w-auto">
              <?= get_top_banner() != false ? "get_top_banner()" : "" ?>
              <p class="sm:text-center text-left mx-auto font-bold w-full">Phone: <a href="tel:+1<?php echo get_option('pw_phone_area').get_option('pw_phone');?>">1-<?php echo get_option('pw_phone_area')."-".get_option('pw_phone');?>&nbsp;&nbsp;&nbsp; Fax: <a href="tel:+1<?php echo get_option('pw_fax_area').get_option('pw_fax');?>">1-<?php echo get_option('pw_fax_area')."-".get_option('pw_fax');?>&nbsp;&nbsp; Email: <a href="mailto:<?php echo get_option('pw_email_area').get_option('pw_email'); ?>"><?php echo get_option('pw_email_area').get_option('pw_email'); ?></a></p>
            
            </div>
            <?php

            if (WebUser::isLoggedIn()) {
              $client_bar = wp_get_menu_array("header-client-info");
              $client_bar_sub = get_sub_menu_array($client_bar);
            } else {
              $client_bar = wp_get_menu_array("header-client-log-in");
              $client_bar_sub = get_sub_menu_array($client_bar);
            }
            ?>

            <div class="min-w-fit flex items-center space-x-3">
              <?php
              if ($client_bar)
                foreach ($client_bar as $item) {
                  ?>
                  <a href="<?= $item->url ?>" class="text-sm font-medium text-white hover:text-gray-100 focus:text-gray-100"><?= $item->title ?></a>
                <?php } ?>
            </div>

          </div>
        </div>

        <div>
          <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="flex h-16 items-center justify-between">


              <?php if (get_header_image()): ?>
                <div class="hidden lg:flex lg:flex-1 lg:items-center">
                  <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                    <span class="sr-only">
                      <?php echo esc_attr(get_bloginfo('name', 'display')); ?>
                    </span>
                    <img class="h-8 w-auto" src="<?php header_image(); ?>"
                      width="<?php echo absint(get_custom_header()->width); ?>"
                      height="<?php echo absint(get_custom_header()->height); ?>"
                      alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>">
                  </a>
                </div>
              <?php endif; ?>

              <div class="hidden h-full lg:flex">

                <div class="inset-x-0 bottom-0 px-4">
                  <div class="flex h-full justify-center space-x-8">
                    <?php
                    //Menu
                    if ($menu_bar)
                      foreach ($menu_bar as $item)
                        if ($item->menu_item_parent == 0 && check_sub_menu($item->ID, $menu_bar_sub)) {
                          ?>
                          <div class="flex">

                            <div class="relative flex">
                              <button type="button" onClick="ShowSubMenu('menu-sub-<?php echo $item->ID ?>');"
                                class="text-gray-700 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] relative flex items-center justify-center text-sm font-medium transition-colors duration-200 ease-out">
                                <?php echo $item->title ?>
                                <span class="absolute inset-x-0 -bottom-px z-20 h-0.5 transition duration-200 ease-out"
                                  aria-hidden="true"></span>
                              </button>
                            </div>

                            <div id="menu-sub-<?php echo $item->ID ?>"
                              class="absolute hidden sub-menu inset-x-0 top-full z-10 bg-white text-sm text-gray-500">

                              <div class="absolute inset-0 top-1/2 bg-white shadow" aria-hidden="true"></div>

                              <div class="absolute inset-0 top-0 mx-auto h-px max-w-7xl px-8" aria-hidden="true">
                                <div class="bg-transparent h-px w-full transition-colors duration-200 ease-out"></div>
                              </div>

                              <div class="relative">

                                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                                  <div class="grid grid-cols-4 gap-x-8 gap-y-10 py-16">

                                    <?php
                                    //Sub-Menu
                                    foreach ($menu_bar_sub as $sub_item)
                                      if ($sub_item != null && $sub_item->menu_item_parent == $item->ID) {
                                        ?>

                                        <div class="group relative">
                                          <div
                                            class="aspect-h-1 aspect-w-1 overflow-hidden rounded-md bg-gray-100 group-hover:opacity-75">
                                            <?= get_the_post_thumbnail($sub_item->object_id, 'large', array('class' => 'object-cover object-center')); ?>
                                          </div>
                                          <a href="<?= $sub_item->url ?>" title="<?= $sub_item->title ?>"
                                            class="mt-4 block font-medium text-gray-900 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                                            <span class="absolute inset-0 z-10" aria-hidden="true"></span>
                                            <?= $sub_item->title ?>
                                          </a>
                                          <p aria-hidden="true" class="mt-1">Shop now</p>
                                        </div>
                                      <?php } ?>
                                  </div>

                                </div>

                              </div>

                            </div>

                          </div>

                          <?php
                        } else if ($item->menu_item_parent == 0)
                          echo '<a class="flex items-center text-sm font-medium text-white hover:text-green-200 focus:text-green-500" href="' . $item->url . '"  title="' . $item->title . '">' . $item->title . '</a>';
                    ?>

                  </div>
                </div>
              </div>


              <div class="flex flex-1 items-center lg:hidden">

                <button type="button" onclick="sideToggle();" @click="open = true"
                  class="-ml-2 rounded-md bg-white p-2 text-white">
                  <span class="sr-only">Open menu</span>
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                  </svg>
                </button>

              </div>

              <?php if (get_header_image()): ?>
                <a class="lg:hidden" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                  <span class="sr-only">
                    <?php echo esc_attr(get_bloginfo('name', 'display')); ?>
                  </span>
                  <img class="h-8 w-auto" src="<?php header_image(); ?>"
                    width="<?php echo absint(get_custom_header()->width); ?>"
                    height="<?php echo absint(get_custom_header()->height); ?>"
                    alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>">
                </a>
              <?php endif; ?>

              <div class="flex flex-1 items-center justify-end">

                <div class="flex items-center lg:ml-8">
                  
                  <div class="ml-4 flow-root lg:ml-8">
                    <a href="<?= network_site_url('/') . "shopping-cart"; ?>"
                      class="group -m-2 flex items-center p-2 hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]">
                      <svg class="h-6 w-6 flex-shrink-0 text-white group-hover:text-green-300" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                      </svg>
                      <span id="cart-header" class="ml-2 text-sm font-medium text-white group-hover:text-green-200">
                        <?= Cart::getItemCount() ?>
                      </span>
                      <span class="sr-only">items in cart, view bag</span>
                    </a>
                  </div>
                </div>

              </div>

            </div>

          </div>

        </div>

      </nav>

    </header>

    <main class="mt-[30px]">