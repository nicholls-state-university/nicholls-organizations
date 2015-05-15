<?php
/**
 * The template for displaying faculty & staff
 */

get_header();

 ?>

		<div id="container" class="container-">
			<div id="content" class="content-" role="main">


			<h2 class="entry-title" id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>

			<div class="blogpost">

			<div class="nicholls-fs-photo">
				<?php the_post_thumbnail('nicholls-fs-medium'); ?>
			</div>

			<div class="nicholls-fs-bio">
				<?php the_content(); ?>
			</div>

			<?php nicholls_org_display_meta_item( '_nicholls_org_nickname' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_advisor_email' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_advisor_phone' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_advisor_office' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_co_advisor_name' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_co_advisor_email' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_co_advisor_phone' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_president_name' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_president_email' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_president_phone' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_vice_president_name' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_vice_president_email' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_treasurer_name' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_treasurer_email' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_secretary_name' ); ?>
			<?php nicholls_org_display_meta_item( '_nicholls_org_org_secretary_email' ); ?>

						

<?php 
/*
// ::ISSUE:: Deebug

$custom_fields = get_post_custom();

foreach ( $custom_fields as $field_key => $field_values ) {
	//if(!isset($field_values[0])) continue;
	echo $field_key . '=>' . $field_values[0] . '<br />';
}
*/			
?>
			
			</div>

			<?php endwhile; ?>
			<?php else : ?>
			<h6 class="center">Not Found</h6>
			<p class="center">Sorry, but you are looking for something that isn't here.</p>

			<?php endif; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>



