<?php
/**
 * Load scripts and css specific to the admin
 */
function admin_load_files(){?>
	<script type="text/javascript">
		var templateUrl = '<?php echo get_stylesheet_directory_uri(); ?>',
			siteUrl = '<?php echo get_site_url(); ?>',
			repeating, no_location;
	</script>
	<?php wp_enqueue_media();
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('google_maps', '//maps.googleapis.com/maps/api/js?key=AIzaSyDUmWrI564wWhoAnJmync64ZZPOHAYe1Ac');
	wp_enqueue_script('admin_js', get_stylesheet_directory_uri() . '/js/app/admin.js');
	wp_enqueue_style('admin_css', get_stylesheet_directory_uri() . '/css/admin.css');
}
add_action('admin_enqueue_scripts', 'admin_load_files');

/**
 * Create the post type for the gallery.
 */
function create_gallery_post_type(){
	register_post_type('mvnp_gallery',
		array(
			'labels' => array(
				'name' => __('Galleries', 'mvnp_basic'),
				'singular_name' => __('Gallery', 'mvnp_basic'),
				'menu_name' => __('Galleries', 'mvnp_basic'),
			),
			'supports' => array('title'),
			'menu_icon' => 'dashicons-format-gallery',
			'public' => true,
			'publicly_queriable' => true,
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'rewrite' => true,
		)
	);
}
add_action('init', 'create_gallery_post_type');

/**
 * Create the post type for FAQs.
 */
function create_faq_post_type(){
	register_post_type('faq',
		array(
			'labels' => array(
				'name' => __('FAQs', 'mvnp_basic'),
				'singular_name' => __('FAQ', 'mvnp_basic'),
				'menu_name' => __('FAQs', 'mvnp_basic'),
			),
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'page-attributes'),
			'menu_icon' => 'dashicons-editor-help',
			'public' => true,
			'has_archive' => true,
			'publicly_queriable' => true,
			'rewrite' => array('slug' => 'faqs'),
		)
	);
}
add_action('init', 'create_faq_post_type');

/**
 * Create the post type for the events.
 */
function create_event_post_type(){
	register_post_type('event',
		array(
			'labels' => array(
				'name' => __('Events', 'mvnp_basic'),
				'singular_name' => __('Event', 'mvnp_basic'),
				'menu_name' => __('Events', 'mvnp_basic'),
			),
			'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
			'taxonomies' => array('event-category'),
			'menu_icon' => 'dashicons-calendar-alt',
			'public' => true,
			'has_archive' => true,
			'publicly_queriable' => true,
			'rewrite' => array('slug' => 'events'),
		)
	);
}
add_action('init', 'create_event_post_type');

/**
 * Create event category taxonomy
 */
function create_event_tax(){
	register_taxonomy(
		'event-category',
		'event',
		array(
			'label' => __('Event Categories', 'mvnp_basic'),
			'rewrite' => true,
			'hierarchical' => true,
			'show_in_nav_menus' => false,
		)
	);
}
add_action('init', 'create_event_tax');

/**
 * Create the user page post type. This is completely hidden from the admin. Right now it can be searched, but we'll probably turn that off.
 */
function create_member_post_type(){
	register_post_type('member',
		array(
			'label' => __('Users', 'mvnp_basic'),
			'public' => true,
			'exclude_from_search' => true,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'hierarchical' => false,
			'has_archive' => true,
			'publicly_queryable' => true,
			'rewrite' => array('slug' => 'user'),
			'query_var' => 'member',
		)
	);
}
add_action('init', 'create_member_post_type');

/**
 * Hook into user update and creation to make a private, personal post for each user. Admin can access.
 * Check the DB to see if that page has been created, set the page author to that person (that how we make sure only they can access)
 * @param  string $user_id The id of the user that the page is being created for
 * @return null
 */
function create_member_page($user_id = ''){
	global $wpdb;
	$user = new WP_User($user_id);

	if(!$user->ID){
		return '';
	}

	// check if the user whose profile is updating has already a post
	$member_post_exists = $wpdb->get_var($wpdb->prepare(
		"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'member' and post_status = 'publish'", $user->user_nicename
	));

	$user_info = array_map(
		function($a){
			return $a[0];
		},
		get_user_meta($user->ID)
	);

	$post = array(
		'post_title' => $user->display_name,
		'post_name' => $user->user_nicename,
		'post_status' => 'publish',
		'post_type' => 'member',
		'post_author' => $user->ID,
	);

	if(!file_exists(get_user_dir($user)['path'])){
		wp_mkdir_p(get_user_dir($user)['path']);
	}

	if($member_post_exists){
		$post['ID'] = $member_post_exists;
		wp_update_post($post);
	} else {
		wp_insert_post($post);
	}
}
add_action('user_register', 'create_member_page');
add_action('personal_options_update', 'create_member_page');
add_action('edit_user_profile_update', 'create_member_page');

/**
 * A QOL function to get the permalink of user pages based off user id or user nicename
 * @param  string $user The user the link is being made for
 * @return fn       The permalink for the page.
 */
function member_permalink($user = ''){
	global $wpdb;

	if(!empty($user)){
		if(is_numeric($user)){ // user id
			$userObj = get_user_by('ID', $user);
		} else { // user nicename
			$userObj = -1;
		}
	} else {
		$userObj = wp_get_current_user();
		$name = isset($userObj->user_nicename) ? $userObj->user_nicename : '';
	}

	if(!isset($name)){
		$name = $userObj == -1 ? $user : $userObj->user_nicename;
	}

	$id = $wpdb->get_var($wpdb->prepare(
		"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'member' AND post_status = 'publish'", $name
	));

	return $id ? get_permalink($id) : '';
}

/**
 * Deletes the user's page when the user is removed.
 * @param int $user_id the user's id
 */
function delete_user_page($user_id, $reassign_id){
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

	global $wpdb;
	$dir = new WP_Filesystem_Direct(null);
	$user = new WP_User($user_id);

	if($reassign_id){
		$assignee = new WP_User($reassign_id);
	}

	$post_id = url_to_postid(member_permalink($user_id));
	wp_delete_post($post_id, true);

	if(file_exists(get_user_dir($user)['path'])){
		$dir->rmdir(get_user_dir($user)['path'], true);
	}
}
add_action('delete_user', 'delete_user_page');

/**
 * This hides the permalink from the gallery post type
 * @param  Object $return    The default permalink
 * @param  Number $post_id   The post ID
 * @param  string $new_title Title of the post
 * @param  string $new_slug  Post Slug
 * @param  Object $post      The post itself
 * @return Object            The default permalink or nothing, if its a gallery post type
 */
function my_hide_permalinks($return, $post_id, $new_title, $new_slug, $post){
	if($post->post_type == 'mvnp_gallery'){
		return '';
	}
	return $return;
}
add_filter('get_sample_permalink_html', 'my_hide_permalinks', 10, 5);

/**
 * This adds custom fields to the theme customization. This is for social media, contact options, and the google analytics hook ID
 * @param  Object $wp_customize The WP customize object that we are ading to.
 */
function mvnp_basic_customize_register($wp_customize){
	$wp_customize->add_section('company_info', array(
		'title' => __('Company Information', 'mvnp_basic'),
		'priority' => 30,
	));

	$wp_customize->add_setting('logo', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'logo_control', array(
		'label' => __('Company logo', 'mvnp_basic'),
		'description' => __('This logo will only be used for your Google rich card. Site-wide logos will need to be changed by a developer.', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'logo',
	)));

	$wp_customize->add_setting('phone_number', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'phone_number_control', array(
		'label' => __('Phone Number', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'phone_number',
		'type' => 'tel',
	)));

	$wp_customize->add_setting('email', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'email_control', array(
		'label' => __('Email', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'email',
		'type' => 'email',
	)));

	$wp_customize->add_setting('street_address_1', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'street_address_1_control', array(
		'label' => __('Street Address 1', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'street_address_1',
	)));

	$wp_customize->add_setting('street_address_2', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'street_address_2_control', array(
		'label' => __('Street Address 2', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'street_address_2',
	)));

	$wp_customize->add_setting('city', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'city_control', array(
		'label' => __('City', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'city',
	)));

	$wp_customize->add_setting('state', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'state_control', array(
		'label' => __('State', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'state',
	)));

	$wp_customize->add_setting('postal', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'postal_control', array(
		'label' => __('Postal', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'postal',
		'type' => 'number',
	)));

	$wp_customize->add_setting('twitter_link', array(
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'company_twitter_control', array(
		'label' => __('Twitter Link', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'twitter_link',
		'type' => 'url',
	)));

	$wp_customize->add_setting('facebook_link', array(
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'company_facebook_control', array(
		'label' => __('FaceBook Link', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'facebook_link',
		'type' => 'url',
	)));

	$wp_customize->add_setting('instagram_link', array(
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'company_instagram_control', array(
		'label' => __('Instagram Link', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'instagram_link',
		'type' => 'url',
	)));

	$wp_customize->add_setting('gplus_link', array(
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'company_gplus_control', array(
		'label' => __('Google+ Link', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'gplus_link',
		'type' => 'url',
	)));

	$wp_customize->add_setting('google_analytics_code', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'google_analytics_code_control', array(
		'label' => __('Google Anylitics Code', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'google_analytics_code',
	)));

	$wp_customize->add_setting('google_tags_code', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'google_tags_code_control', array(
		'label' => __('Google Tags Code', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'google_tags_code',
	)));

	$wp_customize->add_setting('fb_pixel_code', array(
		'default' => '',
		'transport' => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'fb_pixel_code_control', array(
		'label' => __('Facebook Pixel Code', 'mvnp_basic'),
		'section' => 'company_info',
		'settings' => 'fb_pixel_code',
	)));
}
add_action('customize_register', 'mvnp_basic_customize_register');

/**
 * Adds the start date column
 * @param array $defaults the default wp post table headers
 * @return array the new table headers
 */
function event_table_head($defaults){
	$new = array();

	foreach($defaults as $key => $title){
		$new[$key] = $title;
		if($key == 'title'){
			$new['start_date'] = __('Start Date', 'mvnp_basic');
		}
	}
	return $new;
}
add_filter('manage_event_posts_columns', 'event_table_head');

/**
 * Populates the start date column
 * @param  string $column_name Name of the column
 * @param  int $post_id     the post id
 */
function event_table_content($column_name, $post_id){
	if($column_name == 'start_date'){
		if(get_post_meta($post_id, 'recurrence', true) == ''){
			$start_date = get_post_meta($post_id, 'start_date', true);
			echo date('F j, Y', intval($start_date));
		} else {
			_e('Recurring Event', 'mvnp_basic');
		}
	}
}
add_action('manage_event_posts_custom_column', 'event_table_content', 10, 2);

/**
 * Makes the start date column sortable
 * @param  array $columns The posts column headers
 * @return array          The posts column headers
 */
function event_table_sorting($columns){
	$columns['start_date'] = 'start_date';
	return $columns;
}
add_filter('manage_edit-event_sortable_columns', 'event_table_sorting');

/**
 * Orderes the evenst by date when you click it in the menu
 * @param  array $vars the url query vars
 * @return array       the modified url query vars
 */
function start_date_column_orderby($vars){
	if(isset($vars['orderby']) && 'start_date' == $vars['orderby']){
		$vars = array_merge($vars, array(
			'meta_key' => 'start_date',
			'orderby' => 'meta_value',
		));
	}

	return $vars;
}
add_filter('request', 'start_date_column_orderby');

/**
 * Orders the evenst by date by default.
 * @param  array $query the url query vars
 */
function auto_start_date_column_orderby($query){
	global $pagenow;
	if(is_admin() && 'edit.php' == $pagenow && !isset($_GET['orderby']) && $_GET['post_type'] == 'event'){
		$query->set('meta_key', 'start_date');
		$query->set('orderby', 'meta_value');
		$query->set('order', 'ASC');
	}
}
add_action('pre_get_posts', 'auto_start_date_column_orderby');

/**
 * Adds the droptdown filter
 * @param  string $screen the name of the current wp admin view
 */
function event_table_filtering($screen){
	if($screen == 'event'){?>
		<select name="end_date">
			<option value="all" <?php if($_GET['end_date'] == 'all'): ?>selected="selected"<?php endif ?>><?php _e('All events', 'mvnp_basic') ?></option>
			<option value="past" <?php if($_GET['end_date'] == 'past'): ?>selected="selected"<?php endif ?>><?php _e('Past events', 'mvnp_basic') ?></option>
			<option value="upcoming" <?php if(!$_GET['end_date'] || $_GET['end_date'] == 'upcoming'): ?>selected="selected"<?php endif ?>><?php _e('Upcoming events', 'mvnp_basic') ?></option>
		</select>
		<?php
	}
}
add_action('restrict_manage_posts', 'event_table_filtering');

/**
 * This is for the dropdown filter. It shows upcoming, past or all Events.
 * @param array $query the url query vars
 * TODO right now upcoming events shows all repeating events as well, even if they are done. Need to write in some logic to check if its over
 */
function event_table_filter($query){
	if(is_admin() AND $query->query['post_type'] == 'event'){
		$qv = &$query->query_vars;
		$qv['meta_query'] = array();
		$now = date('U', time());

		switch ($_GET['end_date']){
		case 'past':
			$qv['meta_query'][] = array(
				'key' => 'end_date',
				'value' => $now,
				'compare' => '<',
			);
			break;
		case 'upcoming':
			$qv['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key' => 'recurrence',
					'compare' => '!=',
					'value' => '',
				),
				array(
					'key' => 'end_date',
					'value' => $now,
					'compare' => '>=',
				),
			);
			break;
		case 'all':
			$qv['meta_query'][] = array();
			break;
		default:
			$qv['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key' => 'recurrence',
					'compare' => '!=',
					'value' => '',
				),
				array(
					'key' => 'end_date',
					'value' => $now,
					'compare' => '>=',
				),
			);
			break;
		}

		if(!empty($_GET['orderby']) AND $_GET['orderby'] == 'end_date'){
			$qv['orderby'] = 'meta_value';
			$qv['meta_key'] = 'end_date';
			$qv['order'] = strtoupper($_GET['order']);
		}
	}
}
add_filter('parse_query', 'event_table_filter');

/**
 * Adds the import calendar button on events page
 * @param  string $screen the name of the current wp admin view
 */
function g_cal_import_button($screen){
	$screen = get_current_screen();
	if($screen->post_type == 'event' && function_exists('getClient')){
		echo '<button class="button button-primary import-google-cal" type="button" title="Import Google Calendar">' . __('Import Google Calendar', 'mvnp_basic') . '</button>';
	}
}
add_action('manage_posts_extra_tablenav', 'g_cal_import_button');

/**
 * Grabs all the events from your google cal from today forward. Checks to see if that event exists in WP and either updates or inserts a new post with meta values.
 */
function import_google_cal(){
	global $wpdb;

	$google_calendar_client = getClient();
	$calendarId = 'primary';
	$optParams = array(
		'maxResults' => 50,
		'timeMin' => date('c'),
	);

	$google_calendar_service = new Google_Service_Calendar($google_calendar_client);

	$results = $google_calendar_service->events->listEvents($calendarId, $optParams);

	foreach($results->getItems() as $event){
		$event_post_exists = $wpdb->get_var($wpdb->prepare(
			"SELECT DISTINCT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta
			WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND
			$wpdb->posts.post_type = 'event' AND
			$wpdb->postmeta.meta_key = 'google_id' AND
			meta_value = %s",
			$event->id
		));
		$post = array(
			'post_title' => $event->getSummary() ? $event->getSummary() : 'Untitled Event',
			'post_content' => $event->description ? $event->description : '',
			'post_status' => 'publish',
			'post_type' => 'event',
		);

		if($event_post_exists){
			$post['ID'] = $event_post_exists;
			$post_id = wp_update_post($post);
			update_post_meta($post_id, 'recurrence', $event->recurrence);
			update_post_meta($post_id, 'location', $event->location);

			if($event->start->dateTime && $event->end->dateTime){
				update_post_meta($post_id, 'start_date', strtotime($event->start->dateTime));
				update_post_meta($post_id, 'end_date', strtotime($event->end->dateTime));
				update_post_meta($post_id, 'all_day', false);
			} else {
				update_post_meta($post_id, 'start_date', strtotime($event->start->date));
				update_post_meta($post_id, 'end_date', strtotime($event->end->date));
				update_post_meta($post_id, 'all_day', true);
			}
		} else {
			$post_id = wp_insert_post($post);
			add_post_meta($post_id, 'google_id', $event->id, true);
			add_post_meta($post_id, 'recurrence', $event->recurrence, true);
			add_post_meta($post_id, 'location', $event->location, true);

			if($event->start->dateTime && $event->end->dateTime){
				add_post_meta($post_id, 'start_date', strtotime($event->start->dateTime), true);
				add_post_meta($post_id, 'end_date', strtotime($event->end->dateTime), true);
				add_post_meta($post_id, 'all_day', false, true);
			} else {
				add_post_meta($post_id, 'start_date', strtotime($event->start->date), true);
				add_post_meta($post_id, 'end_date', strtotime($event->end->date), true);
				add_post_meta($post_id, 'all_day', true, true);
			}
		}
	}
}
add_action('wp_ajax_import_google_cal', 'import_google_cal');

/**
 * Repeatable Custom Fields in a Metabox
 * Author: Helen Hou-Sandi

 * This adds metaboxes to posts. Currently adds repeating fields and usage info to galleries
 */
function mvnp_add_meta_boxes(){
	global $post;

	add_meta_box('repeatable-fields', __('Media', 'mvnp_basic'), 'hhs_repeatable_meta_box_display', 'mvnp_gallery', 'normal', 'default');
	add_meta_box('gallery-post-id', __('Usage', 'mvnp_basic'), 'gallery_id', 'mvnp_gallery', 'side', 'default');
	add_meta_box('event-metadata', __('Event Details', 'mvnp_basic'), 'event_metaboxes', 'event', 'normal', 'default');
	if(get_option('mvnp-contact-page-mapping') == $post->ID){
		add_meta_box('contact_meta', __('Contact Information', 'mvnp_basic'), 'display_contact_information', 'page', 'side', 'default');
	}
}
add_action('add_meta_boxes', 'mvnp_add_meta_boxes', 1);

/**
 * Makes all the events meta boxes.
 * Recurring values are extracted from the ics string and put into an array. Boring docs here: https://tools.ietf.org/html/rfc5545
 */
function event_metaboxes(){
	global $post;

	if($error = get_transient('event_save_error')){
		mvnp_basic_notice($error, 'notice-error');
		delete_transient('event_save_error');
	}

	$location = get_post_meta($post->ID, 'location', true);
	$location_name = get_post_meta($post->ID, 'location_name', true);
	$start = get_post_meta($post->ID, 'start_date', true);
	$end = get_post_meta($post->ID, 'end_date', true);
	$all_day = get_post_meta($post->ID, 'all_day', true);
	$recurrence = explode('RRULE:', get_post_meta($post->ID, 'recurrence', true)[0])[1];
	$start_date = $start ? date('Y-m-d', $start) : '';
	$start_time = $start ? date('H:i', $start) : '';
	$end_date = $end ? date('Y-m-d', $end) : '';
	$end_time = $end ? date('H:i', $end) : '';

	parse_str(strtr($recurrence, ';', '&'), $recurrence);

	if(array_key_exists('BYDAY', $recurrence)){
		$recurrence['BYDAY'] = explode(',', $recurrence['BYDAY']);
	}

	wp_nonce_field('event_metabox_nonce', 'event_metabox_nonce');
	require_once get_template_directory() . '/php/event-view.php';
}

/**
 * Save event posts. Lots going on here, but it basically writes post meta and pushes values to the google calendar.
 * Recurring values are extracted from the array and put into ics format. Boring docs here: https://tools.ietf.org/html/rfc5545
 * @param  int $post_id ID of the post being saved.
 */
function event_metaboxes_save($post_id){
	if(!isset($_POST['event_metabox_nonce']) || !wp_verify_nonce($_POST['event_metabox_nonce'], 'event_metabox_nonce')){
		return;
	}

	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;
	}

	if(!current_user_can('edit_post', $post_id)){
		return;
	}

	$google_id = get_post_meta($post_id, 'google_id', true) or 0;

	$location = $_POST['no_location'] == 'on' ? '' : sanitize_text_field($_POST['location']);
	$location_name = $_POST['no_location'] == 'on' ? '' : sanitize_text_field($_POST['location_name']);
	$start = strtotime($_POST['start_date'] . ' ' . $_POST['start_time']);
	$end = strtotime($_POST['end_date'] . ' ' . $_POST['end_time']);
	$all_day = $_POST['all_day'];

	$content = get_post($post_id)->post_content;
	$title = get_the_title($post_id);

	if($_POST['recurring'] == 'on' && (empty($_POST['freq']) || !isset($_POST['freq']))){
		set_transient('event_save_error', 'Frequency is required', 45);
		return false;
	} else {
		$recurrence['FREQ'] = $_POST['freq'];
	}

	if($recurrence['FREQ'] != 'DAILY' && $_POST['INTERVAL'] > 1){
		$recurrence['INTERVAL'] = $_POST['INTERVAL'];
	}

	if($recurrence['FREQ'] == 'WEEKLY'){
		$recurrence['BYDAY'] = array();

		foreach($_POST['recurr_days'] as $day){
			if(!empty($day)){
				$recurrence['BYDAY'][] = $day;
			} else {
				set_transient('event_save_error', __('Must select at least one day per week to recur.', 'mvnp_basic'), 45);
				return false;
			}
		}
	}

	if($recurrence['FREQ'] == 'MONTHLY' && $_POST['recurr_by'] == 'by_day'){

		$recurrence['BYDAY'] = array();
		$date = strtotime($_POST['start_date']);
		$day = date('l', $date);
		$week = date('W', $date);

		$first_the_day_of_month = date('W', strtotime('first ' . $day . ' of ' . date('F Y', $date)));
		$nth = 1 + ($week < $first_the_day_of_month ? $week : $week - $first_the_day_of_month);

		switch ($day){
			case 'Sunday':
				$day = 'SU';
				break;
			case 'Monday':
				$day = 'MO';
				break;
			case 'Tuesday':
				$day = 'TU';
				break;
			case 'Wednesday':
				$day = 'WE';
				break;
			case 'Thursday':
				$day = 'TH';
				break;
			case 'Friday':
				$day = 'FR';
				break;
			case 'Saturday':
				$day = 'SA';
				break;
		}
		$recurrence['BYDAY'][] = $nth . $day;
	}

	if($_POST['ends'] == 'count_opt'){
		$recurrence['COUNT'] = $_POST['COUNT'];
	} elseif($_POST['ends'] == 'until_opt'){
		$recurrence['UNTIL'] = date('Ymd\\THis\\Z', strtotime($_POST['UNTIL']));
	}

	if($_POST['recurring'] == 'on' && count($recurrence) > 0){
		$recurrence_str = 'RRULE:';
		foreach($recurrence as $key => $value){
			if(gettype($value) == 'array'){
				$value = implode(',', $value);
			}
			$recurrence_str .= $key . '=' . $value . ';';
		}
	}

	$args = array(
		'summary' => $title,
		'location' => $location,
		'description' => $content,
		'start' => array(
			'dateTime' => date('c', $start),
			'timeZone' => get_option('timezone_string'),
		),
		'end' => array(
			'dateTime' => date('c', $end),
			'timeZone' => get_option('timezone_string'),
		),
	);

	if($_POST['recurring'] == 'on' && strlen($recurrence_str) > 0){
		$args['recurrence'] = array($recurrence_str);
		update_post_meta($post_id, 'recurrence', array($recurrence_str));
	} else {
		$args['recurrence'] = '';
		update_post_meta($post_id, 'recurrence', '');
	}

	if($all_day == 'on'){
		$args['start'] = array(
			'date' => date('Y-m-d', $start),
		);

		$args['end'] = array(
			'date' => date('Y-m-d', $end),
		);

		update_post_meta($post_id, 'all_day', true);
	} else {
		update_post_meta($post_id, 'all_day', false);
	}

	if(function_exists('getClient')){
		$google_calendar_client = getClient();
		$google_calendar_service = new Google_Service_Calendar($google_calendar_client);

		$event = new Google_Service_Calendar_Event($args);

		$exists = $google_calendar_service->events->get('primary', $google_id);

		if($exists->id){
			$event = $google_calendar_service->events->update('primary', $google_id, $event);
		} else {
			$event = $google_calendar_service->events->insert('primary', $event);
			add_post_meta($post_id, 'google_id', $event->id);
		}
	}

	update_post_meta($post_id, 'location', $location);
	update_post_meta($post_id, 'location_name', $location_name);
	update_post_meta($post_id, 'start_date', $start);
	update_post_meta($post_id, 'end_date', $end);
}
add_action('save_post', 'event_metaboxes_save');

/**
 * Just spits out copyable text about how to use the gallery
 */
function gallery_id(){
	global $post;?>
	<div id="gallery-usage">
		<p><?php _e('Add the following to your .php template file:', 'mvnp_basic');?></p>
		<pre>$context['gallery'] = Timber::get_post(<?php echo $post->ID ?>);</pre>
		<?php if($post->post_name){?>
			<p><?php _e('or:', 'mvnp_basic');?></p>
			<pre>
$args = array(
	'post_type' => '<?php echo $post->post_type; ?>',
	'name' => '<?php echo $post->post_name; ?>',
);
$context['gallery'] = Timber::get_post($args);
			</pre>
		<?php }?>
		<p><?php _e('And in your .twig view file, you can access the images with:', 'mvnp_basic');?></p>
		<pre>
{% for image in gallery.gallery_images %}
	<xmp><img src="{{TimberImage(image.props).src('<?php _e('your-image-size', 'mvnp_basic');?>')}}"></xmp>
{% endfor %}
		</pre>
	</div>
<?php };

/**
 * This is the front end for the gallery fields. Here is where we enqueue the style and scripts to make the repeater run.
 * The image props are kinda janky. They are saved as a php object, but need to be converted to JSON on the front end becuse using a php object in a text field destroys its usability
 */
function hhs_repeatable_meta_box_display(){
	global $post;
	$gallery_images = get_post_meta($post->ID, 'gallery_images', true);
	wp_nonce_field('hhs_repeatable_meta_box_nonce', 'hhs_repeatable_meta_box_nonce');
	require_once get_template_directory() . '/php/gallery-view.php';
}

/**
 * This saves our fields with the post.
 * The image props are kinda janky though. They are saved as a php object, but need to be converted to JSON on the front end
 * becuse using a php object in a text field destroys its usability
 * @param  int $post_id The post we're working with
 */
function hhs_repeatable_meta_box_save($post_id){
	if(!isset($_POST['hhs_repeatable_meta_box_nonce']) || !wp_verify_nonce($_POST['hhs_repeatable_meta_box_nonce'], 'hhs_repeatable_meta_box_nonce')){
		return;
	}

	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;
	}

	if(!current_user_can('edit_post', $post_id)){
		return;
	}

	$old = get_post_meta($post_id, 'gallery_images', true);
	$new = array();

	$names = $_POST['name'];
	$alts = $_POST['alt'];
	$properties = $_POST['props'];
	$links = $_POST['link'];
	$copys = $_POST['copy'];
	$iframes = $_POST['iframe'];
	$count = count($names);

	//die($count);

	for ($i = 0; $i < $count; $i++){
		$new[$i]['name'] = stripslashes(strip_tags($names[$i]));
		$new[$i]['alt'] = stripslashes(strip_tags($alts[$i]));
		$new[$i]['link'] = stripslashes(strip_tags($links[$i]));
		$new[$i]['copy'] = stripslashes(strip_tags($copys[$i]));
		$new[$i]['iframe'] = stripslashes(strip_tags($iframes[$i]));

		if(!empty($properties[$i])){
			$inputs = json_decode(stripslashes($properties[$i]));
			$filtered = array();

			if(is_array($inputs) || is_object($inputs)){
				foreach($inputs as $key => $value){
					$filtered[$key] = $value;
				}

				$new[$i]['props'] = $filtered;
			} else {
				$new[$i]['props'] = null;
			}
		}
	}
	if(!empty($new) && $new != $old){
		update_post_meta($post_id, 'gallery_images', $new);
	} elseif(empty($new) && $old){
		delete_post_meta($post_id, 'gallery_images', $old);
	}
}
add_action('save_post', 'hhs_repeatable_meta_box_save');

/**
 * Sets up page mapping for "standard" pages with static templates
 */
function page_mapping_init(){
	register_setting(
		'reading',
		'mvnp-about-page-mapping',
		'about_page_mapping_sanitize'
	);

	register_setting(
		'reading',
		'mvnp-contact-page-mapping',
		'contact_page_mapping_sanitize'
	);

	add_settings_field(
		'mvnp-page-mapping',
		__('Page Mapping', 'mvnp_basic'),
		'page_mapping_callback',
		'reading'
	);
}
add_action('admin_init', 'page_mapping_init');

/**
 * Just makes sure the values are valid
 * @param  [int] $input  the value (page id) from the select
 * @return [int]        if the value not a valid page or is already one of the other defaults
 */
function about_page_mapping_sanitize($input){
	if('publish' == get_post_status($input) && get_option('page_on_front') != $input && get_option('page_for_posts') != $input && get_option('mvnp-contact-page-mapping') != $input){
		return $input;
	}

	return 0;
}

function contact_page_mapping_sanitize($input){
	if('publish' == get_post_status($input) && get_option('page_on_front') != $input && get_option('page_for_posts') != $input && get_option('mvnp-about-page-mapping') != $input){
		return $input;
	}

	return 0;
}

/**
 * The markup inserted into the "Reading" section of the settings
 */
function page_mapping_callback(){?>
	<fieldset>
		<legend class="screen-reader-text"><?php _e('Page Mapping', 'mvnp_basic');?></legend>
		<ul id="page-mapping-list">
			<li>
				<label for="about_page_mapping">
					<?php echo __('About page', 'mvnp_basic') . ': ';
						wp_dropdown_pages(array(
							'name' => 'mvnp-about-page-mapping',
							'id' => 'about_page_mapping',
							'selected' => get_option('mvnp-about-page-mapping', true),
							'option_none_value' => '0',
							'show_option_none' => '- ' . __('Select', 'mvnp_basic') . ' -',
						)); ?>
				</label>
			</li>
			<li>
				<label for="contact_page_mapping">
					<?php echo __('Contact page', 'mvnp_basic') . ': ';
						wp_dropdown_pages(array(
							'name' => 'mvnp-contact-page-mapping',
							'id' => 'contact_page_mapping',
							'selected' => get_option('mvnp-contact-page-mapping', true),
							'option_none_value' => '0',
							'show_option_none' => '- ' . __('Select', 'mvnp_basic') . ' -',
						)); ?>
				</label>
			</li>
		</ul>
	</fieldset>
	<?php }

/**
 * Hook into page_states to add the content after the page name in the pages list
 * @param  [array] $post_states array of post states. like "draft" or "posts page"
 * @param  [stdObj] $post        the global $post
 * @return [array]              our modified array
 */
function filter_display_post_states($post_states, $post){
	if('page' === get_option('show_on_front')){
		if(intval(get_option('mvnp-about-page-mapping')) === $post->ID){
			$post_states['mvnp-about-page-mapping'] = __('About Page', 'mvnp_basic');
		}

		if(intval(get_option('mvnp-contact-page-mapping')) === $post->ID){
			$post_states['mvnp-contact-page-mapping'] = __('Contact Page', 'mvnp_basic');
		}
	}

	return $post_states;
};
add_filter('display_post_states', 'filter_display_post_states', 10, 2);

/**
 * override the default template hierarchy for our new post "states"
 * @param  [string] $template string of the location of the template
 * @return [string]           string of the location of our predetermined template
 */
function template_override($template){
	global $post;

	if(get_option('mvnp-about-page-mapping') == $post->ID && $post->post_type == 'page' && !$post->page_template && !get_search_query()){
		return file_exists(locate_template('about-page.php')) ? locate_template('about-page.php') : $template;
	} elseif(get_option('mvnp-contact-page-mapping') == $post->ID && $post->post_type == 'page' && !$post->page_template && !get_search_query()){
		return file_exists(locate_template('contact-page.php')) ? locate_template('contact-page.php') : $template;
	}
	return $template;
}
add_filter('template_include', 'template_override');

/**
 * Removes the template selector from pages with "states"
 */
function remove_page_template_select(){
	global $post;

	if(in_array($post->ID, array(get_option('mvnp-about-page-mapping'), get_option('mvnp-contact-page-mapping'), get_option('page_on_front')))){?>
		<script type="text/javascript">
			var elem = document.getElementById('page_template');
			
			if(elem){
				var sib = elem.previousElementSibling;

				elem = elem.parentNode.removeChild(elem);
				sib = sib.parentNode.removeChild(sib);
			}
		</script>
	<?php }
}
add_action('admin_footer', 'remove_page_template_select', 100);

/**
 * Markup for the contact info.
 */
function display_contact_information(){
	wp_nonce_field('contact_meta_box_nonce', 'contact_meta_box_nonce');
	$contact_info = get_theme_mods();
	require_once get_template_directory() . '/php/contact-view.php';
}

/**
 * Saves the values to theme mod on save
 * @param  int $post_id the post ID
 * @return null         null
 */
function contact_meta_box_save($post_id){
	if(!isset($_POST['contact_meta_box_nonce']) || !wp_verify_nonce($_POST['contact_meta_box_nonce'], 'contact_meta_box_nonce')){
		return;
	}

	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;
	}

	if(!current_user_can('edit_post', $post_id) || !current_user_can('edit_theme_options')){
		return;
	}

	set_theme_mod('street_address_1', $_POST['street_1']);
	set_theme_mod('street_address_2', $_POST['street_2']);
	set_theme_mod('city', $_POST['city']);
	set_theme_mod('state', $_POST['state']);
	set_theme_mod('postal', $_POST['postal']);
	set_theme_mod('phone_number', $_POST['phone_number']);
	set_theme_mod('email', $_POST['email']);
};
add_action('save_post', 'contact_meta_box_save');

function faq_func($atts) {
	$out = '';
    $a = shortcode_atts(array(
        'id' => '0',
        'type' => 'content',
    ), $atts);

	$children = get_children($a['id']);

	if(count($children) < 1){
		$out .= 'Error: No FAQs found';
	}

	if($a['type'] === 'content'){
		$out .= '<ul>';

		foreach($children as $child){
			$out .= '<li class="faq-slide-content" itemscope itemtype="http://schema.org/Question">';
			$out .= '<h4 itemprop="name">' . $child->post_title . '</h4>';
			$out .= '<div itemprop="suggestedAnswer acceptedAnswer" itemscope itemtype="http://schema.org/Answer">' . $child->post_content . '</div>';
			$out .= '</li>';
		}

		$out .= '</ul>';
	}else if($a['type'] === 'links'){
		$out .= '<ul>';

		foreach($children as $child){
			$out .= '<li itemscope itemtype="http://schema.org/Question">';
			$out .= '<a href="' . get_permalink($child->ID) . '">' . $child->post_title . '</a>';
			$out .= '</li>';
		}

		$out .= '</ul>';
	}else{
		$out .= 'Error: Unknown format';
	}

    return $out;
}
add_shortcode( 'faq', 'faq_func' );
