<?php
/**
* Archive Template
*
* Template generic archives.
*
* @package FNBX Theme
* @subpackage Template
*/
?>
<?php get_header() ?>

<h1><?php post_type_archive_title(); ?></h1>
		
<?php while ( have_posts()) : the_post();  ?>
<div class="nicholls-fs-employee clear-group">

	<div class="nicholls-fs-photo">
	<a href="<?php echo get_permalink(); ?>"><?php the_post_thumbnail('nicholls-org-thumb'); ?></a>
	</div>
	<div class="nicholls-fs-info">
		<h2 class="nicholls-fs-name"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>


	</div>

</div>

<?php endwhile; ?>

		<?php do_action( 'fnbx_template_archive_end', 'template_archive' ) ?>
		<!-- END: template_archive -->

<?php get_sidebar() ?>

<?php get_footer() ?>