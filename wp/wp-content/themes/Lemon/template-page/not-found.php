<div class="text-center m-40">
    <p class="text-base font-semibold text-[<?= MAIN_COLOR ?>]">404</p>
    <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">
    Page not found
    </h1>
    <p class="mt-6 text-base leading-7 text-gray-600">
    Sorry, we couldn’t find the page you’re looking for.
    </p>
    <div class="mt-10 flex items-center justify-center gap-x-6">
    <a href="<?= home_url() ?>"
        class="rounded-md bg-[<?= MAIN_COLOR ?>] hover:text-gray-50 focus:text-gray-50 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Go
        back home</a>
    <a href="/contact-us" class="text-sm font-semibold text-gray-900 hover:text-[<?= MAIN_COLOR ?>] focus:text-[<?= MAIN_COLOR ?>]">Contact us <span
        aria-hidden="true">&rarr;</span></a>
    </div>
</div>