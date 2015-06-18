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

<?php 
	$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

	$custom_args = array(
		'post_type' => 'n-organizations',
		'paged' => $paged
	);
	$custom_query = new WP_Query( $custom_args ); 
?>

<?php nicholls_org_tax_display_types(); ?>

	<?php if ( have_posts() ) : ?>

		<?php echo paginate_links( $args ); ?>

		<?php while ( have_posts() ) : the_post(); ?>
	
			<div class="nicholls-org-organization clear-group">

				<div class="nicholls-org-logo">
				<a href="<?php echo get_permalink(); ?>"><?php the_post_thumbnail('nicholls-org-thumb'); ?></a>
				</div>
				<div class="nicholls-org-info">
					<h2 class="nicholls-org-name"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>

					<h4>Organization Information</h4>

					<?php nicholls_org_display_meta_item( '_nicholls_org_nickname' ); ?>

					<?php nicholls_org_display_meta_item( '_nicholls_org_org_president_name' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_org_president_email' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_org_vice_president_name' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_org_treasurer_name' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_org_secretary_name' ); ?>

					<h4>Advisor Information</h4>
					<?php nicholls_org_display_meta_item( '_nicholls_org_advisor_email' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_advisor_phone' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_advisor_office' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_co_advisor_name' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_co_advisor_email' ); ?>
					<?php nicholls_org_display_meta_item( '_nicholls_org_co_advisor_phone' ); ?>
					
				</div>

			</div>

		<?php endwhile; ?>
				
		<?php echo paginate_links( $args ); ?>

		<?php wp_reset_postdata(); ?>
  
	<?php else: ?>
	
		<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
    
	<?php endif; ?>

	<?php nicholls_org_email_form(); ?>

		<?php do_action( 'fnbx_template_archive_end', 'template_archive' ) ?>
		<!-- END: template_archive -->

<?php get_sidebar() ?>

<?php get_footer() ?>
