<?php get_header(); ?>


<div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
<?php

	while ( have_posts() ) : the_post();
	
		get_template_part( 'template-post/content', 'handler' );

	endwhile;

?>
</div>

<?php get_footer(); ?>