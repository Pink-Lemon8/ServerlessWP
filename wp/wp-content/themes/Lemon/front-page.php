<?php get_header(); ?>
<?php //include("template-page/how-to-order.php");  ?>

<div class="bg-Gray-50">

  <div class="relative isolate px-6 pt-14 lg:px-8">

    <div>
      <div class="absolute inset-x-0 -top-40 -z-40 transform-gpu overflow-hidden blur-3xl sm:-top-40" aria-hidden="true">
        <div
          class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[<?= MAIN_COLOR_CLOUD ?>] to-[<?= SECOND_COLOR_CLOUD ?>] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
          style="clip-path: polygon(74.1% 44.1%,100% 61.6%,97.5% 26.9%,85.5% 0.1%,80.7% 2%,72.5% 32.5%,60.2% 62.4%,52.4% 68.1%,47.5% 58.3%,45.2% 34.5%,27.5% 76.7%,0.1% 64.9%,17.9% 100%,27.6% 76.8%,76.1% 97.7%,74.1% 44.1%);"></div>
      </div>
      <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-40 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
        <div
          class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[<?= MAIN_COLOR_CLOUD ?>] to-[<?= SECOND_COLOR_CLOUD ?>] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
          style="clip-path: polygon(74.1% 44.1%,100% 61.6%,97.5% 26.9%,85.5% 0.1%,80.7% 2%,72.5% 32.5%,60.2% 62.4%,52.4% 68.1%,47.5% 58.3%,45.2% 34.5%,27.5% 76.7%,0.1% 64.9%,17.9% 100%,27.6% 76.8%,76.1% 97.7%,74.1% 44.1%);"></div>
      </div>
    </div>

    <div class="mx-auto max-w-7xl py-32 sm:py-48 lg:py-32 -mt-16">
      <div class="hidden sm:mb-8 sm:flex sm:justify-center"></div>
      <div class="text-center">
        <h1
          class="text-4xl text-[<?= MAIN_COLOR ?>] font-bold tracking-tight md:leading-16 lg:leading-9 sm:leading-24 sm:text-6xl sm:text-6xl md:text-6xl lg:text-6xl xl:text-6xl 2xl:text-6xl">
          Certified Online Prescription Drugs
        </h1>
        <h1
          class="text-4xl xl:mt-4 text-[<?= MAIN_COLOR ?>] font-bold tracking-tight md:leading-16 lg:leading-9 sm:leading-24 sm:text-6xl sm:text-6xl md:text-6xl lg:text-6xl xl:text-6xl 2xl:text-6xl">
          From Canada
        </h1>
        <p class="mt-8 text-2xl leading-8 text-gray-700 font-medium">
          Affordable prescriptions shipped nationwide to the US, making healthcare accessible
        </p>
        <?php if(!WebUser::isLoggedIn()): ?>
        <div class="text-center mt-2 sm:mt-6 md:mt-6 lg:mt-6 xl:mt-6 2xl:mt-6">
          <button type="button" onclick="window.location.href='/create-account';"
            class="mt-2 rounded-full content-center bg-[<?= MAIN_COLOR ?>] hover:bg-[<?= MAIN_COLOR_HOVER ?>] md:px-8 px-16 lg:px-8 xl:px-8 2xl:px-8 py-3.5 text-md sm:text-2xl md:text-2xl lg:text-2xl xl:text-2xl 2xl:text-2xl font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">
            Create a free account
          </button>
          <button type="button" onclick="window.location.href='/upload-prescription';"
            class="ml-4 mt-2 rounded-full content-center bg-[<?= MAIN_COLOR ?>] hover:bg-[<?= MAIN_COLOR_HOVER ?>] md:px-8 px-16 lg:px-8 xl:px-8 2xl:px-8 py-3.5 text-md sm:text-2xl md:text-2xl lg:text-2xl xl:text-2xl 2xl:text-2xl font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">
            Upload Prescription
          </button>
        </div>
        <?php  endif;?>
        
        <?php //do_shortcode('[PL_Product_Search]'); ?>
        <div class="mt-10 flex justify-center w-full">
          <div class="max-w-xl w-full">
          <?= do_shortcode('[wpdreams_ajaxsearchlite]'); ?>
          </div>
        </div>

        <div class="mt-16 grid xs:grid-cols-2 md:grid-cols-3 sm:grid-cols-2 grid-cols-2 lg:grid-cols-6 gap-6 justify-center">
           <a href="<?= home_url() ?>/product/ozempic/">
            <div class="bg-gray-100 flex flex-col items-center md:transform md:transition md:duration-300 md:hover:scale-110 md:hover:bg-gray-300 md:w-56 rounded-lg sm:aspect-w-3 sm:aspect-h-4 md:aspect-w-3 md:aspect-h-4 lg:aspect-w-3 lg:aspect-h-4 xl:aspect-w-3 xl:aspect-h-4 2xl:aspect-w-3 2xl:aspect-h-4 aspect-w-2 aspect-h-3 lg:w-36 xl:w-48 shadow-lg h-[40vw] md:h-[32vw] lg:h-64">
                <p class="text-xl font-semibold">Ozempic</p>
                <img src="https://www.novocare.com/content/dam/diabetes-patient/novocare/redesign/Logos/Ozempic_logo_LG.png" alt="Ozempic" class="max-w-full h-auto px-2 pt-6 self-center">
            </div>
            </a>
            <a href="<?= home_url() ?>/product/mounjaro/">
              <div class="bg-gray-100 flex flex-col items-center fmd:transform md:transition md:duration-300 md:hover:scale-110 md:hover:bg-gray-300 md:w-56 rounded-lg sm:aspect-w-3 sm:aspect-h-4 md:aspect-w-3 md:aspect-h-4 lg:aspect-w-3 lg:aspect-h-4 xl:aspect-w-3 xl:aspect-h-4 2xl:aspect-w-3 2xl:aspect-h-4 aspect-w-2 aspect-h-3 lg:w-36 xl:w-48 shadow-lg h-[40vw] md:h-[32vw] lg:h-64">
                <p class="text-xl font-semibold">Mounjaro</p>
                <img src="https://mounjaro.com/assets/images/mounjaro_logo.png" alt="Ozempic" class="max-w-full h-auto px-2 pt-6 self-center">
                </div>
                </a>
            <a href="<?= home_url() ?>/product/humalog-vial/">
              <div class="bg-gray-100 flex flex-col items-center fmd:transform md:transition md:duration-300 md:hover:scale-110 md:hover:bg-gray-300 md:w-56 rounded-lg sm:aspect-w-3 sm:aspect-h-4 md:aspect-w-3 md:aspect-h-4 lg:aspect-w-3 lg:aspect-h-4 xl:aspect-w-3 xl:aspect-h-4 2xl:aspect-w-3 2xl:aspect-h-4 aspect-w-2 aspect-h-3 lg:w-36 xl:w-48 shadow-lg h-[40vw] md:h-[32vw] lg:h-64">
                <p class="text-xl font-semibold">Humalog</p>
                <img src="https://humalog.com/assets/images/humalog_logo.svg" alt="Ozempic" class="max-w-full h-auto px-2 pt-6 self-center">
                </div>
                </a>
            <a href="<?= home_url() ?>/product/saxenda-pen-18mg-3ml/">
                  <div class="bg-gray-100 flex flex-col items-center fmd:transform md:transition md:duration-300 md:hover:scale-110 md:hover:bg-gray-300 md:w-56 rounded-lg sm:aspect-w-3 sm:aspect-h-4 md:aspect-w-3 md:aspect-h-4 lg:aspect-w-3 lg:aspect-h-4 xl:aspect-w-3 xl:aspect-h-4 2xl:aspect-w-3 2xl:aspect-h-4 aspect-w-2 aspect-h-3 lg:w-36 xl:w-48 shadow-lg h-[40vw] md:h-[32vw] lg:h-64">
                    <p class="text-xl font-semibold">Saxenda</p>
                    <img src="https://saxendahcp.ca/images/header-logo-saxenda@3x.png" alt="Ozempic" class="max-w-full h-auto px-2 pt-6 self-center">
                    </div>
                </a>
                <a href="<?= home_url() ?>/product/rybelsus/">
                  <div class="bg-gray-100 flex flex-col items-center fmd:transform md:transition md:duration-300 md:hover:scale-110 md:hover:bg-gray-300 md:w-56 rounded-lg sm:aspect-w-3 sm:aspect-h-4 md:aspect-w-3 md:aspect-h-4 lg:aspect-w-3 lg:aspect-h-4 xl:aspect-w-3 xl:aspect-h-4 2xl:aspect-w-3 2xl:aspect-h-4 aspect-w-2 aspect-h-3 lg:w-36 xl:w-48 shadow-lg h-[40vw] md:h-[32vw] lg:h-64">
                    <p class="text-xl font-semibold">Rybelsus</p>
                    <img src="https://www.novocare.com/content/dam/diabetes-patient/novocare/redesign/Logos/logo-rybelsus.png" alt="Ozempic" class="max-w-full h-auto px-2 pt-6 self-center">
                    </div>
                </a>
                <a href="<?= home_url() ?>/product/flovent-diskus/">
                  <div class="bg-gray-100 flex flex-col items-center fmd:transform md:transition md:duration-300 md:hover:scale-110 md:hover:bg-gray-300 md:w-56 rounded-lg sm:aspect-w-3 sm:aspect-h-4 md:aspect-w-3 md:aspect-h-4 lg:aspect-w-3 lg:aspect-h-4 xl:aspect-w-3 xl:aspect-h-4 2xl:aspect-w-3 2xl:aspect-h-4 aspect-w-2 aspect-h-3 lg:w-36 xl:w-48 shadow-lg h-[40vw] md:h-[32vw] lg:h-64">
                    <p class="text-xl font-semibold">Flovent</p>
                    <img src="https://s3.amazonaws.com/files.innovicares.ca/mobile/640x300/640x300_floventhfa_en.png" alt="Ozempic" class="max-w-full h-auto px-2 pt-6 self-center">
                    </div>
                </a>
        </div>
      </div>
    </div>  

  </div>
  
</div>

<?php $phone_area = get_option('pw_phone_area');
$phone = get_option('pw_phone');
$fax_area = get_option('pw_fax_area');
$fax = get_option('pw_fax');  ?>

<div class="bg-white py-24 sm:py-28">
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <dl class="text-center">
      <div class="mx-auto">
        <dd class="order-first text-3xl font-semibold tracking-tight text-gray-700 sm:text-3xl">Need help? Call our
          friendly support team at <a href="tel:+1-<?= $phone_area ?>-<?= $phone ?>"><?= $phone_area ?>-<?= $phone ?></a> or fill out our Contact Form
        </dd>
      </div>
    </dl>
  </div>
</div>


</div>

</div>

</div>
  
<div class="relative isolate bg-white pt-24 sm:pt-32">

  <div>
    <div class="absolute inset-x-0 top-1/2 -z-50 -translate-y-1/2 transform-gpu overflow-hidden opacity-30 blur-3xl" aria-hidden="true">
      <div class="ml-[max(50%,38rem)] aspect-[1313/771] w-[82.0625rem] bg-gradient-to-tr from-[<?= MAIN_COLOR_CLOUD ?>] to-[<?= SECOND_COLOR_CLOUD ?>]"
           style="clip-path: polygon(74.1% 44.1%,100% 61.6%,97.5% 26.9%,85.5% 0.1%,80.7% 2%,72.5% 32.5%,60.2% 62.4%,52.4% 68.1%,47.5% 58.3%,45.2% 34.5%,27.5% 76.7%,0.1% 64.9%,17.9% 100%,27.6% 76.8%,76.1% 97.7%,74.1% 44.1%);"></div>
    </div>
    <div class="absolute inset-x-0 top-0 -z-50 flex transform-gpu overflow-hidden pt-32 opacity-25 blur-3xl sm:pt-40 xl:justify-end"aria-hidden="true">
      <div class="ml-[-22rem] aspect-[1313/771] w-[82.0625rem] flex-none origin-top-right rotate-[30deg] bg-gradient-to-tr from-[<?= MAIN_COLOR_CLOUD ?>] to-[<?= SECOND_COLOR_CLOUD ?>] xl:ml-0 xl:mr-[calc(50%-12rem)]"
           style="clip-path: polygon(74.1% 44.1%,100% 61.6%,97.5% 26.9%,85.5% 0.1%,80.7% 2%,72.5% 32.5%,60.2% 62.4%,52.4% 68.1%,47.5% 58.3%,45.2% 34.5%,27.5% 76.7%,0.1% 64.9%,17.9% 100%,27.6% 76.8%,76.1% 97.7%,74.1% 44.1%);"></div>
    </div>
  </div>

  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="mx-auto max-w-xl text-center">
      <h2 class="text-4xl xl:text-6xl font-semibold leading-8 tracking-tight text-[<?= MAIN_COLOR ?>]">
        Testimonials
      </h2>
      <p class="mt-2 xl:mt-6 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
        We always make sure our customers get the best prices possible, as
        fast as possible
      </p>
    </div>
    <div
      class="mx-auto mt-16 grid max-w-2xl grid-cols-1 grid-rows-1 gap-8 text-sm leading-6 text-gray-900 sm:mt-20 sm:grid-cols-2 xl:mx-0 xl:max-w-none xl:grid-flow-col xl:grid-cols-4">
      <figure
        class="col-span-2 hidden sm:block sm:rounded-2xl sm:bg-white sm:shadow-lg sm:ring-1 sm:ring-gray-900/5 xl:col-start-2 xl:row-end-1">
        <blockquote class="p-12 leading-8 tracking-tight text-gray-900 font-semibold text-lg">

          “The customer service was outstanding. Very quick to respond and so helpful. <?= bloginfo('name'); ?> took the time to call
          my doctor to have them send my prescription and helped manage my time delivery time since I would be away from
          home for a few days.”

        </blockquote>
        <figcaption class="flex items-center gap-x-4 border-t border-gray-900/10 px-6 py-4">
          <img class="h-10 w-10 flex-none rounded-full bg-gray-50"
            src="https://images.unsplash.com/photo-1550525811-e5869dd03032?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=1024&h=1024&q=80"
            alt="" />
          <div class="flex-auto">
            <div class="font-semibold">Brenna Goyette</div>
          </div>
        </figcaption>
      </figure>
      <div class="space-y-8 xl:contents xl:space-y-0">
        <div class="space-y-8 xl:row-span-2">
          <figure class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-900/5">
            <blockquote class="text-gray-900 font-semibold text-lg">

              “The customer service is outstanding. I appreciate it.”

            </blockquote>
            <figcaption class="mt-6 flex items-center gap-x-4">
              <img class="h-10 w-10 rounded-full bg-gray-50"
                src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                alt="" />
              <div>
                <div class="font-semibold">Leslie Alexander</div>
              </div>
            </figcaption>
          </figure>

          <!-- More testimonials... -->
        </div>
        <div class="space-y-8 xl:row-start-1">
          <figure class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-900/5">
            <blockquote class="text-gray-900 font-semibold text-lg">

              “It is very easy to order with them. The prices are great and the shipping is so quick. Highly Recommend
              them!”

            </blockquote>
            <figcaption class="mt-6 flex items-center gap-x-4">
              <img class="h-10 w-10 rounded-full bg-gray-50"
                src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                alt="" />
              <div>
                <div class="font-semibold">Lindsay Walton</div>
              </div>
            </figcaption>
          </figure>

          <!-- More testimonials... -->
        </div>
      </div>
      <div class="space-y-8 xl:contents xl:space-y-0">
        <div class="space-y-8 xl:row-start-1">
          <figure class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-900/5">
            <blockquote class="text-gray-900 font-semibold text-lg">

              “First time ordering from this pharmacy. The entire process was so easy and the questions I did have the
              customer service representative was very helpful and knew what he was talking about. I felt so good having
              found them

              ”

            </blockquote>
            <figcaption class="mt-6 flex items-center gap-x-4">
              <img class="h-10 w-10 rounded-full bg-gray-50" src="https://images.unsplash.com/flagged/photo-1570612861542-284f4c12e75f?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" />
              <div>
                <div class="font-semibold">Tom Stone</div>
              </div>
            </figcaption>
          </figure>

          <!-- More testimonials... -->
        </div>
        <div class="space-y-8 xl:row-span-2">
          <figure class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-900/5">
            <blockquote class="text-gray-900 font-semibold text-lg">

              “Anytime I call and need help, they are always there and help me in every way possible. I love working
              with them. Best service ever!”

            </blockquote>
            <figcaption class="mt-6 flex items-center gap-x-4">
              <img class="h-10 w-10 rounded-full bg-gray-50"
                src="https://images.unsplash.com/photo-1519345182560-3f2917c472ef?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                alt="" />
              <div>
                <div class="font-semibold">Leonard Krasner</div>
              </div>
            </figcaption>
          </figure>

          <!-- More testimonials... -->
        </div>
      </div>
    </div>
  </div>
</div>

<div class="bg-white py-24 sm:py-32">
<?php include("template-page/how-to-order.php");  ?>
</div>



<div class="bg-gray-900 mt-20 py-24 sm:py-24">
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <dl class="text-center">

      <div class="mx-auto">
        <dd class="order-first text-3xl font-semibold tracking-tight text-white sm:text-4xl">Need help? Call our
          friendly support team at <a href="tel:+1-<?= $phone_area ?>-<?= $phone ?>"><?= $phone_area ?>-<?= $phone ?></a> or fill out the Contact Form
        </dd>
      </div>
    </dl>
  </div>
</div>

<?php get_footer(); ?>
