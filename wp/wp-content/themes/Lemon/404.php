<?php get_header(); ?>
<br></br>
<div class="bg-white py-12 sm:py-12">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:max-w-none">
          <div class="text-center">
            <h2
              class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                         404 Page Not Found
            </h2>
            <p class="mt-4 text-lg font-semibold leading-10 text-gray-800">
            We couldn't find the page you were looking for. This is either because:
<ul>
               <li>There is an error in the URL entered into your web browser. Please check the URL and try again.</li>
                <li>  The page you are looking for has been moved or deleted.</li>
</ul>
            Return to the <a href="<?= $HOME_URL ?>"><b>home page</b></a>.
            </p>
          </div>

        </div>
                   
      </div>
    </div>
    <?php get_footer(); ?>