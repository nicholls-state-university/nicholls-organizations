<?php
/*
Plugin Name: Nicholls - Student Organizations
Plugin URI: http://nicholls.edu
Description: Add ons and interfaces to help the Nicholls Office of Student Organizations.
Author: Jess Planck
Version: 0.5
Author URI: http://nicholls.edu

--- Documentation ---

Automatic population of placed Gravity Form Fields using CSS class name.

populate-orgs = built checkbox groups and dropdown lists with organizations special post types
populate-org-types = build dropdown list using Organiztion Type taxonomy choices and term id's

*/

/**
 * Include CMB2 framework https://github.com/WebDevStudios/CMB2
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */

if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

add_action('init', 'nicholls_org_init');
/**
* Initialize and register custom post types
*/
function nicholls_org_init() {  

    // Setup custom post type
    $args = array(  
        'label' => __( 'Nicholls Student Organizations' ),
        'labels' => array(
                'name' => __( 'Student Organizations' ),
                'singular_name' => __( 'Student Organizations' ),
                'add_new' => __( 'Add New Student Organization' ),
                'add_new_item' => __( 'Add New Student Organization' ),
                'edit_item' => __( 'Edit Student Organization' ),
                'new_item' => __( 'Add New Student Organization' ),
                'view_item' => __( 'View Student Organization' ),
                'search_items' => __( 'Search Student Organizations' ),
                'not_found' => __( 'No student organizations found' ),
                'not_found_in_trash' => __( 'No student organizations found in trash' )
		),
		'taxonomies'    => array(
			'n-organization-tags',
		),		
        'public' => true, 
        'show_ui' => true,  
        'capability_type' => 'post',  
        'hierarchical' => false,
        'has_archive' => true,
        'rewrite' => array( 
			'slug' => 'organizations',
		),  
        'supports' => array('title', 'editor', 'thumbnail', 'revisions'),
        'register_meta_box_cb' => 'nicholls_fs_add_metaboxes'
       );  
    register_post_type( 'n-organizations' , $args );
    
	register_taxonomy(
		'n-organization-type',
		array(
			'n-organizations',
		),
		array(
			'labels'            => array(
				'name'              => _x('Organization Types', 'prefix_portfolio', 'text_domain'),
				'singular_name'     => _x('Organization Type', 'prefix_portfolio', 'text_domain'),
				'menu_name'         => __('Organization Types', 'text_domain'),
				'all_items'         => __('All Organization Types', 'text_domain'),
				'edit_item'         => __('Edit Organization Type', 'text_domain'),
				'view_item'         => __('View Organization Types', 'text_domain'),
				'update_item'       => __('Update Organization Type', 'text_domain'),
				'add_new_item'      => __('Add New Organization Type', 'text_domain'),
				'new_item_name'     => __('New Organization Type Name', 'text_domain'),
				'search_items'      => __('Search Organization Types', 'text_domain'),
			),
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => array( 
				'slug' => 'organization-types'
			),
		)
	);

	register_taxonomy(
		'n-organization-tags',
		array(
			'n-organizations',
		),
		array(
			'labels'            => array(
				'name'              => _x('Organization Tags', 'prefix_portfolio', 'text_domain'),
				'singular_name'     => _x('Organization Tag', 'prefix_portfolio', 'text_domain'),
				'menu_name'         => __('Organization Tags', 'text_domain'),
				'all_items'         => __('All Organization Tags', 'text_domain'),
				'edit_item'         => __('Edit Organization Tags', 'text_domain'),
				'view_item'         => __('View Organization Tags', 'text_domain'),
				'update_item'       => __('Update Organization Tags', 'text_domain'),
				'add_new_item'      => __('Add New Organization Tag', 'text_domain'),
				'new_item_name'     => __('New Organization Tag Name', 'text_domain'),
				'search_items'      => __('Search Organization Tags', 'text_domain'),
			),
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => array( 
				'slug' => 'organization-tags'
			),
		)
	);
    
    // Setup custom image size  
    add_image_size( 'nicholls-n-medium', 360, 360 );
    add_image_size( 'nicholls-n-thumb', 180, 180 );
    
	// Needs moved to activation after testing
	flush_rewrite_rules( false );
    
}  

add_filter('template_redirect', 'nicholls_org_template_smart');
/*
* Filter the single entry and archive templates with our custom function
*/
function nicholls_org_template_smart(){

    global $post;

	$fs_template_dir = dirname(__FILE__);
    $single_template_name = 'nicholls-org-template.php';
    $archive_template_name = 'nicholls-org-archive-template.php';

    if ( is_single() && 'n-organizations' == get_post_type() ) {

        $template = locate_template( array( $single_template_name), true );
        if( empty( $template ) ) {
          include( $fs_template_dir . '/' . $single_template_name);
          exit();
        }

    } else if ( is_archive() && 'n-organizations' == get_post_type() ) {

        $template = locate_template( array( $archive_template_name ), true );
        if(empty($template)) {
          include( $fs_template_dir . '/' . $archive_template_name);
          exit();
        }

    } else if ( is_tax( 'n-organizations' ) ) {

        $template = locate_template( array( $archive_template_name ), true );
        if(empty($template)) {
          include( $fs_template_dir . '/' . $archive_template_name);
          exit();
        }
            
    }

}

/**
 * Prerender Organization dropdown list in Gravity Forms.
 * Form fields must be configured in Gravity Forms with a CSS class populate-orgs.
 * 
 */
add_filter('gform_pre_render', 'nicholls_org_populate_org_list');
function nicholls_org_populate_org_list($form){

	// ISSUE:: All forms can replace a dropdown with provider list using CSS class populate-provider
	// Remember to pass as references to modify form - &$field 
	//print_r( $form );
	foreach( $form['fields'] as &$field ){

		if( $field['type'] == 'select' && strstr( $field['cssClass'], 'populate-orgs' ) != false ) {

			// you can add additional parameters here to alter the posts that are retreieved
			// more info: http://codex.wordpress.org/Template_Tags/get_posts
			$the_posts = get_posts( array(
				'posts_per_page' => -1,
				'post_type' => 'n-organizations' 
			) );

			// update 'Select a Post' to whatever you'd like the instructive option to be
			$choices = array( array('text' => 'Select Organization', 'value' => '0') );

			foreach( $the_posts as $the_post ){
				$choices[] = array( 'text' => $the_post->post_title, 'value' => $the_post->ID);
			}

			$field['choices'] = $choices;
		}

		if( $field['type'] == 'select' && strstr( $field['cssClass'], 'populate-org-type' ) != false ) {

			// update 'Select a Post' to whatever you'd like the instructive option to be
			$choices = array( array('text' => 'Select Organization Type', 'value' => '0') );

			$n_org_types = get_terms( 'n-organization-type', array( 'hide_empty' => false ) );

			foreach( $n_org_types as $n_org_type ){
				$choices[] = array( 'text' => $n_org_type->name, 'value' => $n_org_type->term_id);
			}

			$field['choices'] = $choices;
		}
	
	}
	
	return $form;	
}

/**
* Custom Gravity Forms validation for organization type custom taxonomy 
*
*/
add_filter( 'gform_field_validation', 'nicholls_org_org_type_validation', 10, 4 );
function nicholls_org_org_type_validation( $result, $value, $form, $field ) {

    if ( $result['is_valid'] && $field['label'] == 'Organization Type' ) {
        if ( $value == 0 ) {        
			$result['is_valid'] = false;
			$result['message'] = 'Please select an organization type!';
        }
    }
    
    return $result;
}

add_action( 'cmb2_init', 'nicholls_org_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function nicholls_org_metaboxes() {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_nicholls_org_';
	
	// Start with a new metabox
	$cmb_org_info = new_cmb2_box( array(
		'id'            => 'n_org_metabox',
		'title'         => __( 'Organization Information', 'nicholls_org' ),
		'object_types'  => array( 'n-organizations' ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // true to keep the metabox closed by default
	) );
	
	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name'       => __( 'Organization Nickname', 'nicholls_org' ),
		'desc'       => __( 'Nickname or acronym for the organization.', 'nicholls_org' ),
		'id'         => $prefix . 'nickname',
		'type'       => 'text',
		// 'show_on_cb' => 'yourprefix_hide_if_no_cats', // function should return a bool value
		// 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
		// 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
		// 'on_front'        => false, // Optionally designate a field to wp-admin only
		// 'repeatable'      => true,
	) );
	
	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name'       => __( 'Advisor Name', 'nicholls_org' ),
		'desc'       => __( 'Organization advisor full name.', 'nicholls_org' ),
		'id'         => $prefix . 'advisor_name',
		'type'       => 'text'
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name'       => __( 'Advisor Email', 'nicholls_org' ),
		'desc'       => __( 'Organization advisor email adress.', 'nicholls_org' ),
		'id'         => $prefix . 'advisor_email',
		'type'       => 'text_email'
	) );
	
	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Advisor Phone Number', 'nicholls_org' ),
		'desc' => __( 'Organization advisor phone number.', 'nicholls_org' ),
		'id'   => $prefix . 'advisor_phone',
		'type' => 'text',
	) );	

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Advisor Office Location', 'nicholls_org' ),
		'desc' => __( 'Organization advisor office building and room number.', 'nicholls_org' ),
		'id'   => $prefix . 'advisor_office',
		'type' => 'text',
	) );	

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Co-Advisor Name', 'nicholls_org' ),
		'desc' => __( 'Organization co-advisor full name.', 'nicholls_org' ),
		'id'   => $prefix . 'co_advisor_name',
		'type' => 'text',
	) );
	
	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Co-Advisor Email', 'nicholls_org' ),
		'desc' => __( 'Organization co-advisor email adress.', 'nicholls_org' ),
		'id'   => $prefix . 'co_advisor_email',
		'type' => 'text_email',
	) );	

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Co-Advisor phone', 'nicholls_org' ),
		'desc' => __( 'Organization co-advisor phone number.', 'nicholls_org' ),
		'id'   => $prefix . 'co_advisor_phone',
		'type' => 'text',
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization President Name', 'nicholls_org' ),
		'desc' => __( 'Organization president full name.', 'nicholls_org' ),
		'id'   => $prefix . 'org_president_name',
		'type' => 'text',
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization President Email', 'nicholls_org' ),
		'desc' => __( 'Organization president email address.', 'nicholls_org' ),
		'id'   => $prefix . 'org_president_email',
		'type' => 'text_email',
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization President Phone', 'nicholls_org' ),
		'desc' => __( 'Organization president phone number.', 'nicholls_org' ),
		'id'   => $prefix . 'org_president_phone',
		'type' => 'text',
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization Vice President Name', 'nicholls_org' ),
		'desc' => __( 'Organization vice president full name.', 'nicholls_org' ),
		'id'   => $prefix . 'org_vice_president_name',
		'type' => 'text',
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization Vice President Email', 'nicholls_org' ),
		'desc' => __( 'Organization vice president email address.', 'nicholls_org' ),
		'id'   => $prefix . 'org_vice_president_email',
		'type' => 'text',
	) );
	
	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization Treasurer Name', 'nicholls_org' ),
		'desc' => __( 'Organization treasurer full name.', 'nicholls_org' ),
		'id'   => $prefix . 'org_treasurer_name',
		'type' => 'text',
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization Treasurer Email', 'nicholls_org' ),
		'desc' => __( 'Organization treasurer email address.', 'nicholls_org' ),
		'id'   => $prefix . 'org_treasurer_email',
		'type' => 'text',
	) );
	
	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization Secretay Name', 'nicholls_org' ),
		'desc' => __( 'Organization secretary full name.', 'nicholls_org' ),
		'id'   => $prefix . 'org_secretary_name',
		'type' => 'text',
	) );
	
	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization Secretay Email', 'nicholls_org' ),
		'desc' => __( 'Organization secretary email address.', 'nicholls_org' ),
		'id'   => $prefix . 'org_secretary_email',
		'type' => 'text',
	) );

	// Add field to metabox group
	$cmb_org_info->add_field( array(
		'name' => __( 'Organization Primary Contact Name', 'nicholls_org' ),
		'desc' => __( 'Organization primary contact full name.', 'nicholls_org' ),
		'id'   => $prefix . 'primary_contact_name',
		'type' => 'text',
	) );

	$cmb_org_info->add_field( array(
		'name'    => __( 'Organization Logo Image', 'nicholls_org' ),
		'desc'    => __( 'Upload an image to represent the organization.', 'nicholls_org' ),
		'id'      => $prefix . 'logo_image',
		'type'    => 'file',
		// Optionally hide the text input for the url:
		'options' => array(
			'url' => false,
		),
	) );

	/**
	 * Repeatable Field Group
	 */	 
	 
	$cmb_org_info_group = $cmb_org_info->add_field( array(
		'id'          => $prefix . 'repeat_group',
		'type'        => 'group',
		'title'       => __( 'Organization Members', 'nicholls_org' ),
		'description' => __( 'Input organization member information', 'nicholls_org' ),
		'options'     => array(
			'group_title'   => __( 'Member {#}', 'cmb' ), // since version 1.1.4, {#} gets replaced by row number
			'add_button'    => __( 'Add Another Member', 'cmb' ),
			'remove_button' => __( 'Remove Member', 'cmb' ),
			'sortable'      => true, // beta
		),
	) );

	$cmb_org_info->add_group_field( $cmb_org_info_group, array(
		'name' => __( 'Members name', 'nicholls_org' ),
		'description' => __( 'Member name', 'nicholls_org' ),
		'id'   => 'member_name',
		'type' => 'text',
		// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
	) );	 
	 
	$cmb_org_info->add_group_field( $cmb_org_info_group, array(
		'name' => __( 'Members email', 'nicholls_org' ),
		'description' => __( 'Member email', 'nicholls_org' ),
		'id'   => 'member_email',
		'type' => 'text_email',
		// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
	) );
	
	$cmb_org_info->add_group_field( $cmb_org_info_group, array(
		'name' => __( 'Members phone', 'nicholls_org' ),
		'description' => __( 'Member phone number', 'nicholls_org' ),
		'id'   => 'member_phone',
		'type' => 'text',
		// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
	) );		 
	 
}

add_action('save_post', 'nicholls_org_save_meta', 1, 2); // save the custom fields
/**
* Save the Metabox Data
*/
function nicholls_org_save_meta( $post_id, $post ) {

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;
	
	// Featured Images
	if ( isset( $_POST['_nicholls_org_logo_image_id'] ) && !empty( $_POST['_nicholls_org_logo_image_id'] ) ) 
		add_post_meta( $post->ID, '_thumbnail_id', $_POST['_nicholls_org_logo_image_id'] ); 
	if ( isset( $_POST['_nicholls_org_logo_image_id'] ) && empty( $_POST['_nicholls_org_logo_image_id'] ) ) 
		delete_post_meta( $post->ID, '_thumbnail_id' ); 
}

add_action("gform_after_submission", "nicholls_org_gf_create_org", 10, 2);
/**
* Filter Gravity Forms to create post.
*
*
--- Notes for entry creation ---

(
(
    [id] => 4
    [form_id] => 3
    [date_created] => 2015-03-31 20:19:27
    [is_starred] => 0
    [is_read] => 0
    [ip] => 10.60.11.166
    [source_url] => http://web2.nicholls.edu/organizations/2015/03/18/test-post-org-info/
    [post_id] => 
    [currency] => USD
    [payment_status] => 
    [payment_date] => 
    [transaction_id] => 
    [payment_amount] => 
    [payment_method] => 
    [is_fulfilled] => 
    [created_by] => 1
    [transaction_type] => 
    [user_agent] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:36.0) Gecko/20100101 Firefox/36.0
    [status] => active
    [1] => Organization Name
    [2] => nickname
    [3] => Descriiption of stuff.
    [4] => 1115
    [24.3] => Advisor Name
    [24.6] => Advisor Last Name
    [20] => adv@here.com
    [21] => (985)226-0001
    [8] => adv office room/building
    [30.3] => Avisor 2 name
    [30.6] => Advisor 2 name last
    [12] => coAdvisor@email.com
    [34] => (985)226-0002
    [25.3] => PRESIDENT
    [25.6] => PRES LAST
    [22] => PRES@HERE.COM
    [23] => (985)226-0003
    [26.3] => VICE PRES
    [26.6] => VICE PRES LAST
    [31] => VP@HERE.COM
    [27.3] => TREAS
    [27.6] => TREAS LAST
    [32] => TREAS@HERE.COM
    [28.3] => SEC NAME
    [28.6] => SEC LAST
    [33] => SEC@H34.COM
)

*/
function nicholls_org_gf_create_org( $entry, $form ){
	//First need to create the post in its basic form
	
	// Only privilged users, otherwise form sends email.
	if ( !current_user_can( 'publish_posts' ) ) return;
	
	/*
	echo '<br />--- Entry ----<br />';
	print_r( $entry );

	echo '<br />--- Form ----<br />';
	print_r( $form );	

	echo '<br />--- Logo ----<br />';	
	$org_logo = explode( '|', $entry['35'] );
	print_r( $org_logo );
	
	// Return first argument.
	return $entry;
	*/
	
	$new_org = array(
		'post_title' => sanitize_text_field( $entry[1] ),
		'post_content' => sanitize_text_field( $entry[3] ),
		'post_status' => 'publish',
		'post_date' => date('Y-m-d H:i:s'),
		'post_type' => 'n-organizations',
		'ping_status' => 'closed', // Deactivate pings
		'comment_status' => 'closed', // Deactivate comments
		// 'tax_input'      => [ array( <taxonomy> => <array | string>, <taxonomy_other> => <array | string> ) ] // For custom taxonomies. Default empty.
	);
	
	//From creating it, we now have its ID
	$the_id = wp_insert_post( $new_org );

	// Set taxonomy
	wp_set_object_terms( $the_id, intval( $entry[4] ), 'n-organization-type' );
	
	//Now we add the meta
	$prefix = '_nicholls_org_';
	
	// Attach uploaded image
	$org_logo = explode( '|', $entry['35'] );
	reset( $org_logo );	
	$org_logo_url = array_shift( $org_logo );
	$org_logo_id = end( $org_logo );
	wp_update_post( array(
			'ID' => $org_logo_id,
			'post_parent' => $the_id
		)
	);

	// Set CMB2 fields. Note the Attachement ID is stored in $prefix . 'logo_image_id', URL in $prefix . 'logo_image'
	update_post_meta( $the_id, $prefix . 'logo_image_id', $org_logo_id );
	update_post_meta( $the_id, $prefix . 'logo_image', $org_logo_url );
	
	// Set featured image
	update_post_meta( $the_id, '_thumbnail_id', $org_logo_id);

	update_post_meta( $the_id, $prefix . 'nickname', sanitize_text_field( $entry['2'] ) );
	update_post_meta( $the_id, $prefix . 'advisor_name', sanitize_text_field( $entry['24.3'] . ' ' . $entry['24.6'] ) );
	update_post_meta( $the_id, $prefix . 'advisor_email', sanitize_text_field( $entry['20'] ) );
	update_post_meta( $the_id, $prefix . 'advisor_phone', sanitize_text_field( $entry['21'] ) );
	update_post_meta( $the_id, $prefix . 'advisor_office', sanitize_text_field( $entry['8'] ) );
	update_post_meta( $the_id, $prefix . 'co_advisor_name', sanitize_text_field( $entry['30.3'] . ' ' . $entry['30.6'] ) );
	update_post_meta( $the_id, $prefix . 'co_advisor_email', sanitize_text_field( $entry['12'] ) );
	update_post_meta( $the_id, $prefix . 'co_advisor_phone', sanitize_text_field( $entry['34'] ) );
	update_post_meta( $the_id, $prefix . 'org_president_name', sanitize_text_field( $entry['25.3'] . ' ' . $entry['25.6'] ) );
	update_post_meta( $the_id, $prefix . 'org_president_email', sanitize_text_field( $entry['22'] ) );
	update_post_meta( $the_id, $prefix . 'org_president_phone', sanitize_text_field( $entry['23'] ) );
	update_post_meta( $the_id, $prefix . 'org_vice_president_name', sanitize_text_field( $entry['26.3'] . ' ' . $entry['26.6'] ) );
	update_post_meta( $the_id, $prefix . 'org_vice_president_email', sanitize_text_field( $entry['31'] ) );
	update_post_meta( $the_id, $prefix . 'org_treasurer_name', sanitize_text_field( $entry['27.3'] . ' ' . $entry['27.6'] ) );
	update_post_meta( $the_id, $prefix . 'org_treasurer_email', sanitize_text_field( $entry['32'] ) );
	update_post_meta( $the_id, $prefix . 'org_secretary_name', sanitize_text_field( $entry['28.3'] . ' ' . $entry['28.6'] ) );
	update_post_meta( $the_id, $prefix . 'org_secretary_email', sanitize_text_field( $entry['33'] ) );
	update_post_meta( $the_id, $prefix . 'primary_contact_name', sanitize_text_field( $entry['28'] ) );

	/* Handle images 
	$thePhotos = json_decode($entry[5]);
	$firstPhoto = true;
	
	foreach($thePhotos as $aPhoto){
		$path = parse_url($aPhoto, PHP_URL_PATH);
		$aPhotoPath = $_SERVER['DOCUMENT_ROOT'] . $path;
		$filetype = wp_check_filetype($aPhoto);
	
	    // The arguments for the photo
	    $args = array(
	        'post_mime_type' => $filetype['type'],
	        'post_title'     => ucwords($entry[4]), //The photo's title. I'm using the same title as the post
	        'post_content'   => '',
	        'post_status'    => 'inherit'
	    );
		header('Content-Type: text/html');
	    $thumb_id = wp_insert_attachment( $args, $aPhotoPath,  $theId );
		
	    $metadata = wp_generate_attachment_metadata( $thumb_id, $aPhotoPath );
	    wp_update_attachment_metadata( $thumb_id, $metadata );
		
		$finalPhotos[$thumb_id] = $aPhoto;
		
		// Set the first image uploaded as the post thumbnail
		if ( $firstPhoto )
	    {
			set_post_thumbnail( $theId, $thumb_id);
	        $firstPhoto = false;
	    }
	} 
	update_post_meta($theId, $thePrefix.'photos', $finalPhotos);
	*/
	
}