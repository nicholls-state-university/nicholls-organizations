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
			'n-organization-type'
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
        'register_meta_box_cb' => 'nicholls_org_add_metaboxes'
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
    add_image_size( 'nicholls-org-medium', 360, 360 );
    add_image_size( 'nicholls-org-thumb', 180, 180 );
    
	// Needs moved to activation after testing
	flush_rewrite_rules( false );
    
	if ( is_user_logged_in() ) 
		add_action( 'wp_ajax_nicholls-org-form-email', 'nicholls_org_ajax_simple_contact_form' );
	else
		add_action( 'wp_ajax_nopriv_nicholls-org-form-email', 'nicholls_org_ajax_simple_contact_form' );
    
}

add_action( 'wp_enqueue_scripts', 'nicholls_org_js_enqueue' );
/**
* Email contact form - JavaScript
*
*/
function nicholls_org_js_enqueue() {

	if ( 'n-organizations' != get_post_type() ) return;
	
	//Enqueue Javascript & jQuery if not already loaded
	wp_enqueue_script('jquery');
	wp_enqueue_script('nicholls-org-js', plugins_url( 'js/nicholls-org.js' , __FILE__ ), array('jquery'));
	wp_enqueue_script('magnific-popup-js', plugins_url( 'Magnific-Popup-master/dist/jquery.magnific-popup.min.js' , __FILE__ ), array('jquery'));
	
	// Enqueue CSS
	wp_enqueue_style( 'magnific-popup-css', plugins_url( 'Magnific-Popup-master/dist/magnific-popup.css' , __FILE__ ) );

	$localize = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	);
	wp_localize_script('nicholls-org-js', 'nicholls_org_js_obj', $localize);
}

/**
* Email contact form - Ajax actions
*
*/
function nicholls_org_ajax_simple_contact_form() {

	if ( isset( $_POST['nicholls_org_email_form_nonce'] ) && wp_verify_nonce( $_POST['nicholls_org_email_form_nonce'], 'nicholls_org_email_form' ) ) {

		$name = sanitize_text_field($_POST['nicholls-org-form-email-name']);
		$email = sanitize_email($_POST['nicholls-org-form-email-email']);
		$message = stripslashes( wp_filter_kses($_POST['nicholls-org-form-email-message']) );
		$form_url = esc_url( $_POST['nicholls-org-form-url'] );
		$subject = 'Nicholls Web Email - Message from ' . $name; 
		
		$to = sanitize_email($_POST['nicholls-org-form-email-addr']);

		$headers[] = 'From: ' . $name . ' <' . $email . '>' . "\r\n";
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>' . "\r\n";
		$headers[] = 'Content-type: text/html' . "\r\n"; //Enables HTML ContentType. Remove it for Plain Text Messages
		
		$message = '<br/>' . $message . '<br/><br/><hr/>This messange sent using a form found at: ' . $form_url . '<br/>Please contact nichweb@nicholls.edu for support.';
		
		add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
		$check = wp_mail( $to, $subject, $message, $headers );
		
		wp_send_json_error( $check );

	}

	die(); // Important
}

add_action( 'phpmailer_init', 'nicholls_org_configure_smtp', 999 );
/**
* Configure SMTP & Advanced PHPMail options
*/
function nicholls_org_configure_smtp( $phpmailer ){

    $phpmailer->From = 'nichweb@nicholls.edu';
    $phpmailer->FromName='Nicholls Webmanager';
	$phpmailer->Sender = 'nichweb@nicholls.edu';

	/*
	* Expception handling for PHPMailer to catch errors for ajax requests.
	* see: https://gist.github.com/franz-josef-kaiser/5840282
	*/
	
	/*
	$error = null;
	try {
		$sent = $phpmailer->Send();
		! $sent AND $error = new WP_Error( 'phpmailerError', $sent->ErrorInfo );
	}
	catch ( phpmailerException $e ) {
		$error = new WP_Error( 'phpmailerException', $e->errorMessage() );
	}
	catch ( Exception $e ) {
		$error = new WP_Error( 'defaultException', $e->getMessage() );
	}
 
	if ( is_wp_error( $error ) )
		return printf( "%s: %s", $error->get_error_code(), $error->get_error_message() );
	*/
	
}

/**
* Email contact form
*
*/
function nicholls_org_email_form() { ?>
	<div id="nicholls-org-form" class="nicholls-org-form-">
		<form id="nicholls-org-form-email" class="white-popup-block mfp-hide">
			<div id="nicholls-org-form-message-top" class="nicholls-org-form-message-top-"></div>
			<div id="nicholls-org-form-name" class="nicholls-org-form-name-">
				Your Name <br/>
				<input id="nicholls-org-form-email-name" class="text" type="text" name="nicholls-org-form-email-name"/>
			</div>
			<div id="nicholls-org-form-email" class="nicholls-org-form-email-">
				Your Email <br/>
				<input id="nicholls-org-form-email-email" class="text" type="text" name="nicholls-org-form-email-email"/>
			</div>
			<div id="nicholls-org-form-message" class="nicholls-org-form-message-">
				Your Message <br/>
				<textarea id="nicholls-org-form-email-message" class="textarea" name="nicholls-org-form-email-message"></textarea>
			</div>
			<input name="action" type="hidden" value="nicholls-org-form-email" />
			<input name="nicholls-org-form-email-addr" type="hidden" value="" />
			<input name="nicholls-org-form-url" type="hidden" value="<?php the_permalink(); ?>" />
			<?php wp_nonce_field( 'nicholls_org_email_form', 'nicholls_org_email_form_nonce' ); ?>
			<input id="scfs" class="button" type="submit" name="scfs" value="Send Message"/>
			<img class="nicholls-org-form-email-ajax-image" src="<?php echo plugins_url( 'images/11kguf4.gif', __FILE__ ); ?>" alt="Sending Message">
			<div class="nicholls-org-form-email-message"><p></p></div>
		<form>
	</div>
<?php } 

add_filter( 'single_template', 'nicholls_org_template_single' ) ;
/*
* Filter the archive templates with our custom function
*/
function nicholls_org_template_single( $single_template ) {
	global $post, $query;
	
    $single_template_name = 'nicholls-org-template.php';
    
     if ( get_post_type() == 'n-organizations' && is_single() ) {
          $single_template = dirname( __FILE__ ) . '/' . $single_template_name;
     }
     return $single_template;
}

add_filter( 'archive_template', 'nicholls_org_template_archive' ) ;
/*
* Filter the archive templates with our custom function
*/
function nicholls_org_template_archive( $archive_template ) {
	global $post, $query;
	
	$archive_template_name = 'nicholls-org-archive-template.php';
    
     if ( get_post_type() == 'n-organizations' && is_archive() ) {
          $archive_template = dirname( __FILE__ ) . '/' . $archive_template_name;
     }
     return $archive_template;
}


add_filter('gform_pre_render', 'nicholls_org_populate_org_list');
/**
 * Prerender Organization dropdown list in Gravity Forms.
 * Form fields must be configured in Gravity Forms with a CSS class populate-orgs.
 * 
 */
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

add_filter( 'gform_field_validation', 'nicholls_org_org_type_validation', 10, 4 );
/**
* Custom Gravity Forms validation for organization type custom taxonomy 
*
*/
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
    [id] => 10
    [form_id] => 3
    [date_created] => 2015-07-17 19:28:59
    [is_starred] => 0
    [is_read] => 0
    [ip] => 10.60.11.166
    [source_url] => http://web2.nicholls.edu/organizations/2015/07/17/form-test/
    [post_id] => 832
    [currency] => USD
    [payment_status] => 
    [payment_date] => 
    [transaction_id] => 
    [payment_amount] => 
    [payment_method] => 
    [is_fulfilled] => 
    [created_by] => 1
    [transaction_type] => 
    [user_agent] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:39.0) Gecko/20100101 Firefox/39.0
    [status] => active
    [1] => Jess's Club
    [3] => This a test!!!
    [4] => 8
    [24.3] => Jess
    [24.6] => Planck
    [20] => jess@funroe.net
    [21] => (985)226-3538
    [8] => Elkins 132
    [25.3] => Jess
    [25.6] => Planck
    [22] => jess@funroe.net
    [23] => (985)226-3538
    [27.3] => Jess
    [27.6] => Planck
    [35] => http://web2.nicholls.edu/organizations/files/gravity_forms/3-60a8f88581c9ae6f3805251f3a0c1286/2015/07/mardi11.jpg|:||:||:||:|833
    [36] => a:2:{i:0;a:3:{s:4:"Name";s:10:"Jesse Uggg";s:5:"Email";s:9:"jp@me.com";s:5:"Phone";s:9:"no number";}i:1;a:3:{s:4:"Name";s:3:"bob";s:5:"Email";s:10:"bob@me.com";s:5:"Phone";s:4:"gggg";}}
    [2] => 
    [30.3] => 
    [30.6] => 
    [12] => 
    [34] => 
    [26.3] => 
    [26.6] => 
    [31] => 
    [32] => 
    [28.3] => 
    [28.6] => 
    [33] => 
*/
function nicholls_org_gf_create_org( $entry, $form ){
	//First need to create the post in its basic form
	
	// Only privilged users, otherwise form sends email.
	if ( !current_user_can( 'publish_posts' ) ) return;
	

	echo '<br />--- Entry ----<br />';
	print_r( $entry );

	echo '<br />--- Form ----<br />';
	print_r( $form );	

	echo '<br />--- Logo ----<br />';	
	$org_logo = explode( '|', $entry['35'] );
	print_r( $org_logo );
	
	// Return first argument.
	return $entry;

	
	$new_org = array(
		'post_title' => sanitize_text_field( $entry[1] ),
		'post_content' => sanitize_text_field( $entry[3] ),
		'post_status' => 'pending',
//		'post_date' => date('Y-m-d H:i:s'),
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

add_filter('pre_get_posts', 'nicholls_org_pre_get_posts');
/**
 * Limit, change number of posts in archive pages
 */
function nicholls_org_pre_get_posts($query){
        
    if ( get_query_var('post_type') == 'n-organizations' || get_query_var('n-organization-type', 0) ) {
		if ( !is_single() ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );	
		}
    }

	return $query;

/* This is OLD but interesting
	// Get and set the paging variable
	$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
	
	$postsperpage = get_option('posts_per_page');

	if (  is_archive() && get_query_var('n-organization-type') ) {
	
		$query->set( 'paged', $paged );
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
		$query->set( 'posts_per_page', $postsperpage );

		$requested_area = get_query_var('n-organization-type');
		if ( !empty( $requested_area ) ) {

			$tax_query = array(
				array(
					'taxonomy' => 'n-organization-type',
					'field' => 'slug',
					'terms' => array( $requested_area ),
					'operator'=>'IN'
				)
			);
			$query->set('tax_query',$tax_query);
		}
	
	}

	return $query;
*/

}

/**
* Display Organization types
*/
function nicholls_org_tax_display_types() {		
	
	echo '<div class="nicholls-org-types clear-group">';

	$taxonomy = 'n-organization-type';
	$terms = get_terms( $taxonomy, '' );

	if ($terms) {
		echo '<strong>Organziation Types</strong><br />';
		echo '<ul class="nicholls-org-type-links">';
		echo '<li class="nicholls-org-type-link">' . '<a href="' . esc_attr( get_post_type_archive_link( 'n-organizations' ) ) . '" title="' . __( "View all" ) . '" ' . '>' . __( "View all" ) . '</a></li>';
		foreach($terms as $term) {
			echo '<li class="nicholls-org-type-link">' . '<a href="' . esc_attr( get_term_link($term, $taxonomy) ) . '" title="' . sprintf( __( "View all posts in %s" ), $term->name ) . '" ' . '>' . $term->name.'</a></li>';
		}
		echo '</ul>';
	}

	echo '</div>';
}

/**
* Display custom meta based on meta key
*/
function nicholls_org_display_meta_item( $meta_item = '', $the_post_id = 0, $return = false ) {

	if ( $the_post_id == 0 || empty( $the_post_id ) ) {
		$the_post_id = get_the_ID();
	}
	
	$meta_item_data = get_post_meta( $the_post_id, $meta_item, true );
	
	if ( empty( $meta_item_data ) ) return;	
	
	$meta_items = array(
		'_nicholls_org_nickname' => array(
			'name' => 'Nickname',
			'class' => 'nicholls-org-nickname'
		),
		'_nicholls_org_advisor_name' => array(
			'name' => 'Advisor Name',
			'class' => 'nicholls-org-advisor-name'
		),		
		'_nicholls_org_advisor_email' => array(
			'name' => 'Advisor Email',
			'class' => 'nicholls-org-advisor-email'
		),	
		'_nicholls_org_advisor_phone' => array(
			'name' => 'Advisor Phone Number',
			'class' => 'nicholls-org-advisor-phone'
		),	
		'_nicholls_org_advisor_office' => array(
			'name' => 'Advisor Office Location',
			'class' => 'nicholls-org-advisor-office'
		),	
		'_nicholls_org_co_advisor_name' => array(
			'name' => 'Co-Advisor Name',
			'class' => 'nicholls-org-co-advisor-name'
		),
		'_nicholls_org_co_advisor_email' => array(
			'name' => 'Co-Advisor Email',
			'class' => 'nicholls-org-co-advisor-email'
		),
		'_nicholls_org_co_advisor_phone' => array(
			'name' => 'Co-Advisor Phone',
			'class' => 'nicholls-org-co-advisor-phone'
		),
		'_nicholls_org_org_president_name' => array(
			'name' => 'Organization President Name',
			'class' => 'nicholls-org-org-president-name'
		),
		'_nicholls_org_org_president_email' => array(
			'name' => 'Organization President Email',
			'class' => 'nicholls-org-org-president-email'
		),
		'_nicholls_org_org_president_phone' => array(
			'name' => 'Organization President Phone',
			'class' => 'nicholls-org-org-president-phone'
		),						
		'_nicholls_org_org_vice_president_name' => array(
			'name' => 'Organization Vice President Name',
			'class' => 'nicholls-org-org-vice-president-name'
		),
		'_nicholls_org_org_vice_president_email' => array(
			'name' => 'Organization Vice President Email',
			'class' => 'nicholls-org-org-vice-president-email'
		),
		'_nicholls_org_org_treasurer_name' => array(
			'name' => 'Organization Treasurer Name',
			'class' => 'nicholls-org-org-treasurer-name'
		),
		'_nicholls_org_org_treasurer_email' => array(
			'name' => 'Organization Treasurer Email',
			'class' => 'nicholls-org-org-treasurer-email'
		),
		'_nicholls_org_org_secretary_name' => array(
			'name' => 'Organization Secretary Name',
			'class' => 'nicholls-org-org-secretary-name'
		),		
		'_nicholls_org_org_secretary_email' => array(
			'name' => 'Organization Secretary Email',
			'class' => 'nicholls-org-org-secretary-email'
		)
	);
	
	// Assign email addresses that can use the email form system.
	$email_meta_items = array(
		'_nicholls_org_org_president_email',
		'_nicholls_org_advisor_email'
	);
	
	if ( in_array( $meta_item, $email_meta_items ) ) {
		
		$e_email = explode( '@', $meta_item_data );

		$display .= '<div class="' . $meta_items[$meta_item]['class'] . '"><strong>' . $meta_items[$meta_item]['name'] . ':</strong> ';
		
		$display .= '<script type="text/javascript">' . "\n";
		$display .= '//<![CDATA[' . "\n";
		$display .= 'var n_u = "' . $e_email[0] . '";' . "\n";
		$display .= 'var n_dd = "' . $e_email[1] . '";' . "\n";
		$display .= 'var n_dot = "' . $employee_name . '";' . "\n";
		$display .= '//]]>' . "\n";
		$display .= '</script>' . "\n";
				
		$display .= '<script type="text/javascript">' . "\n";
		$display .= '//<![CDATA[' . "\n";
		$display .= '<!--' . "\n";
		$display .= ' var u = "";' . "\n";
		$display .= 'var d = "";' . "\n";
		$display .= 'var cmd = "m"+""+"a";' . "\n";
		$display .= 'var to = "t";' . "\n";
			   
		$display .= 'cmd = cmd + ""+""+"i";' . "\n";
		$display .= 'to = to+"o:";' . "\n";
		$display .= 'cmd = cmd +"l"+to;' . "\n";
		$display .= 'loc = cmd+n_u;' . "\n";
		$display .= 'loc = loc + "%40";' . "\n";
		$display .= 'loc = loc + n_dd;' . "\n";
		$display .= 'document.write("<a class=\"nicholls-org-modal-email\" href=\""+loc+"\">"+n_u+"&#64;"+n_dd+"<\/a>");' . "\n";

		$display .= '//-->' . "\n";
		$display .= '//]]>' . "\n";
		$display .= '</script>' . "\n";
		
		$display .= '</div>';		
		
	} else
		$display = '<div class="' . $meta_items[$meta_item]['class'] . '"><strong>' . $meta_items[$meta_item]['name'] . ':</strong> ' . $meta_item_data . '</div>';
	
	if ( !$return )
		echo $display;
	else
		return $display;

}