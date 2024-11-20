</main>

<footer aria-labelledby="footer-heading" style="background-color: #141C3E;">
 <h2 id="footer-heading" class="sr-only">Footer</h2>
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

    <div class="border-t border-white py-20">

      <div class="grid grid-cols-1 md:grid-flow-col md:auto-rows-min md:grid-cols-1 md:gap-x-8 md:gap-y-16">
      
        <div class="col-span-6 mt-10 grid grid-cols-2 gap-8 sm:grid-cols-3 md:col-span-8 md:col-start-3 md:row-start-2 md:mt-0 lg:col-span-9 lg:col-start-1 lg:row-start-1">
          <div class="grid grid-cols-1 gap-y-12 sm:col-span-4 sm:grid-cols-3 sm:gap-x-8">
            <?php
              for ($i = 1; $i <= 3; $i++) {
                $location = "footer-" . $i;
                $footer = wp_get_menu_array($location);
                if ($footer) {
                  ?>
                  <div>
                    <h3 class="text-base font-bold text-white">
                      <?= wp_get_nav_menu_name($location); ?>
                    </h3>
                    <ul role="list" class="mt-6 space-y-6">
                      <?php
                      foreach ($footer as $item)
                        if ($item->menu_item_parent == 0) {
                          ?>
                          <li class="text-base">
                            <a href="<?= $item->url; ?>"
                              class="text-white hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>]"><?= $item->title; ?></a>
                          </li>
                        <?php } ?>
                    </ul>
                  </div>
                  <?php
                }
              }
            ?>
          </div>
        </div>

        <div class="row-start-1 md:col-span-8 md:col-start-3 md:mt-0 lg:col-span-9 lg:col-start-8 lg:row-start-1">
            <img class="h-8 w-auto mb-3" src="<?php header_image(); ?>"
                      width="<?php echo absint(get_custom_header()->width); ?>"
                      height="<?php echo absint(get_custom_header()->height); ?>"
                      alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" />
           
            <div class="flex mt-4">
                <a href="https://x.com" class="mr-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/X_icon_2.svg/640px-X_icon_2.svg.png" alt="Instagram" class="w-6 h-6">
                </a>
            </div>

    
        </div>
        
      </div>
    </div>

    <div class="border-t border-gray-100 py-10 text-center">
      <p class="text-sm text-white">&copy; 2024 <?php echo esc_attr(get_bloginfo('name', 'display')); ?>, Inc. All rights reserved.</p>
    </div>
  </div>
</footer>
</div>
<?php wp_footer(); ?>
<!-- BSG Trustmark -->
<script type="text/javascript" src="//guarantee-cdn.com/SealCore/api/gjs?SN=969068574&t=10"></script>

<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(function(OneSignal) {
    OneSignal.init({
      appId: "ea8f2573-add3-4248-bf90-5feb9012a129",
      safari_web_id: "web.onesignal.auto.2fc72fe0-a0df-475b-ad9a-b2dac840a493",
      notifyButton: {
        enable: true,
      },
    });
  });
</script>
</body>
</html>