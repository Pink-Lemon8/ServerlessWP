<?php
$blogs_per_page = 6;
$blogArg = [
  'post_type' => 'post',
  'post_status' => 'publish',
  'orderby' => 'title',
  'order' => 'DESC',
  'posts_per_page' => $blogs_per_page,
  'paged' => (get_query_var('paged') ? get_query_var('paged') : 1)
];
$blogs = get_posts($blogArg);
$max_page = ceil(wp_count_posts("post")->publish / $blogs_per_page);
$current_page = max(1, get_query_var('paged'));
?>

<div class="bg-white">
  <div class="mx-auto max-w-2xl px-4 py-7 sm:px-6 sm:py-7 md:max-w-7xl lg:px-8">
    <h2 class="text-xl font-bold text-gray-900">
      <?php echo the_title() ?>
    </h2>
    <div class="container mx-auto px-4 py-12 md:px-6 lg:py-16">
  <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
    

    <?php foreach ($blogs as $blog): ?>

      <div class="rounded-lg overflow-hidden shadow-lg">
      <?php if (has_post_thumbnail( $blog->ID ) ):
      $image = wp_get_attachment_image_src( get_post_thumbnail_id( $blog->ID ), 'single-post-thumbnail' ); ?>
      <img
        src="<?php echo $image[0]; ?>"
        alt="Blog Post Image"
        class="w-full h-48 object-cover"
        width="400"
        height="200"
        style="aspect-ratio: 400 / 200; object-fit: cover;"
      />
      <?php endif; ?>
      
      <div class="p-6">
        <h3 class="text-xl font-bold mb-2"><?php echo $blog->post_title; ?></h3>
        <p class="text-gray-500 mb-4">
        <?php echo substr(sanitize_text_field($blog->post_content),0,75)."..."; ?>
        </p>
        <a
          class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-950 dark:bg-gray-50 dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-gray-300"
          href="<?php echo get_permalink($blog->ID) ?>"
        >
          Read More
        </a>
      </div>
    </div>

    <?php endforeach; ?>
          
          
    
  </div>
    <nav aria-label="Pagination"
    class="mx-auto mt-6 flex max-w-7xl justify-between px-4 text-sm font-medium text-gray-700 sm:px-6 lg:px-8">
      <div class="min-w-0 flex-1">
        <?php if ($current_page > 1): ?>
          <a href="<?= "/blogs/page/" . ($current_page - 1) ?>"
            class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] px-4 hover:bg-gray-100 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-opacity-25 focus:ring-offset-1 focus:ring-offset-[<?= MAIN_COLOR_FOCUS ?>]">Previous</a>
        <?php endif; ?>
      </div>

      <div class="hidden space-x-2 sm:flex">
        <?php for ($i = max(1, $current_page - 2); $i <= min($current_page + 2, $max_page); $i++): ?>
          <a href="<?= "/blogs/page/" . $i ?>"
            class="<?= $current_page == $i ? "ring-1 ring-[" . MAIN_COLOR . "] " : '' ?>inline-flex h-10 items-center rounded-md border border-gray-300 bg-white hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] px-4 hover:bg-gray-100 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-opacity-25 focus:ring-offset-1 focus:ring-offset-[<?= MAIN_COLOR_FOCUS ?>]"><?= $i ?></a>
        <?php endfor; ?>

      </div>

      <div class="flex min-w-0 flex-1 justify-end">
        <?php if ($current_page < $max_page): ?>
          <a href="<?= "/blogs/page/" . ($current_page + 1) ?>"
            class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white hover:text-[<?= MAIN_COLOR_HOVER ?>] focus:text-[<?= MAIN_COLOR_FOCUS ?>] px-4 hover:bg-gray-100 focus:border-[<?= MAIN_COLOR_FOCUS ?>] focus:outline-none focus:ring-2 focus:ring-[<?= MAIN_COLOR_FOCUS ?>] focus:ring-opacity-25 focus:ring-offset-1 focus:ring-offset-[<?= MAIN_COLOR_FOCUS ?>]">Next</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</div>
  </div>
</div>
