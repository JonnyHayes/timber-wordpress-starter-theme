<?php
/**
 * This is an array of post types passed to the sitemapper. As you add new post types, list them here if you want them to be sitemapped.
 * @var array
 */
$sitemap_posts = array('post', 'page', 'event', 'faq');

$robots_disallow = array('gallery');

/**
 * An array of AFC field names with content you want to be searchable with wp search. Data type Strings.
 * @var array
 */
$custom_fields = array('');

/**
 * Require the built-in Timber plugin, server-side mobile detection, the XML sitemap generator, and the php used for new admin functionality.
 * Mobile detect usage:
 * 	$var = new Mobile_Detect();
 * 	$var->isMobile(),	$var->isTablet(), etc. See documentation for full features.
 * TODO Find a better way to include the plugin. Currently the plugin will not show up in the WP admin
 */
if(file_exists(get_template_directory() . '/php/vendor/calendar-php-quickstart.json') && file_exists(get_template_directory() . '/php/vendor/client_secret.json') && file_exists(get_template_directory() . '/php/vendor/autoload.php')){
	require_once get_template_directory() . '/php/google-calendar.php';
}
require_once get_template_directory() . '/plugins/timber-library/timber.php';
require_once get_template_directory() . '/plugins/advanced-custom-fields-pro/acf.php';
require_once get_template_directory() . '/plugins/wp-nested-pages/nestedpages.php';
require_once get_template_directory() . '/php/admin.php';
require_once get_template_directory() . '/php/acf-fields.php';
require_once get_template_directory() . '/php/acf-search.php';
require_once get_template_directory() . '/php/auth.php';
require_once get_template_directory() . '/php/Mobile_Detect.php';
require_once get_template_directory() . '/php/xml-sitemap.php';

use Timber\FunctionWrapper;

/**
 * Set up the AFC path to be our built in plugin path
 * @param  [string] $path path to be overwritten
 * @return [string]       The full path of the new path... yes that makes sense
 */
function my_acf_settings_path($path){
	$path = get_template_directory() . '/plugins/advanced-custom-fields-pro/';

	return $path;
}
add_filter('acf/settings/path', 'my_acf_settings_path');

/**
 * Set up the AFC dir to be our built in plugin dir
 * @param  [string] $dir dir to be overwritten
 * @return [string]       The full path of the new dir
 */
function my_acf_settings_dir($dir){
	$dir = get_template_directory_uri() . '/plugins/advanced-custom-fields-pro/';

	return $dir;
}
add_filter('acf/settings/dir', 'my_acf_settings_dir');

/* uncomment this to hide ACF from the wp-admin */
//add_filter('acf/settings/show_admin', '__return_false');

/**
 * Basic theme stuff here. Curretly just adds thumbnail support to posts
 */
function mvnp_basic_setup(){
	add_theme_support('post-thumbnails');
	add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'));
}
add_action('after_setup_theme', 'mvnp_basic_setup');

/**
 * Load text domain for i18n translations
 */
function mvnp_basic_languages(){
	$mo_path = get_template_directory() . '/languages/';
	if(file_exists($mo_path . get_locale() . '.mo')){
		load_textdomain('mvnp_basic', $mo_path . get_locale() . '.mo');
	}
}
add_action('after_setup_theme', 'mvnp_basic_languages');

/**
 * Sets the name of the php template used for the current page to $GLOBALS. Used to load page-specific styles and scripts
 * @param string $template php template name, ending in .php
 * @return string the template name, unmodified
 */
function var_template_include($template){
	$GLOBALS['current_theme_template'] = basename($template);
	return $template;
}
add_filter('template_include', 'var_template_include');

/**
 * Load scripts, but first get rid of the built-in WP version of jQuery.
 * Our concatinated, minified script includes jQuery and any other 3rd party scripts you wish to include
 * Google maps with our API key is included. Can be used for dev, but probably should be changed to a client account for prod.
 *
 * Recaptcha script is also included and disabled. By default, the signup is in a modal so the script is loaded
 * async when that modal is created. If your signup is loaded normally on the page, uncomment the script.
 */
function mvnp_basic_load_scripts(){
	wp_deregister_script('jquery');
	wp_enqueue_script('google_maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDUmWrI564wWhoAnJmync64ZZPOHAYe1Ac', false, '1.0', true);
	//wp_enqueue_script('google_recaptcha', 'https://www.google.com/recaptcha/api.js');
	wp_enqueue_script('main_js', get_template_directory_uri() . '/js/main.min.js', false, null, true);

	if(file_exists(get_template_directory() . '/js/app/views/' . pathinfo($GLOBALS['current_theme_template'], PATHINFO_FILENAME) . '.js')){
		wp_enqueue_script('page_js', get_template_directory_uri() . '/js/app/views/' . pathinfo($GLOBALS['current_theme_template'], PATHINFO_FILENAME) . '.js', 'main_js', null, true);
	}
}
add_action('wp_enqueue_scripts', 'mvnp_basic_load_scripts');

/**
 * Load our styles. Lets keep this minimal.
 */
function mvnp_basic_load_styles(){
	wp_enqueue_style('main_style', get_template_directory_uri() . '/style.css', false, null, 'screen');
	wp_style_add_data('main_style', 'rtl', 'replace');
	global $wp_styles;

	if(file_exists(get_template_directory() . '/css/views/' . pathinfo($GLOBALS['current_theme_template'], PATHINFO_FILENAME) . '.css')){
		wp_enqueue_style('page_style', get_template_directory_uri() . '/css/views/' . pathinfo($GLOBALS['current_theme_template'], PATHINFO_FILENAME) . '.css', false, null, 'screen');
		$wp_styles->add_data('page_style', 'disabled', TRUE);
	}
	$wp_styles->add_data('main_style', 'disabled', TRUE);
}
add_action('wp_enqueue_scripts', 'mvnp_basic_load_styles');

/**
 * Turn off the stupid wordpress emojis. They wouldnt be so bad but they grab all your HTML entities and replace them with img tags. Lame city.
 */
function disable_wp_emojicons(){
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
}
add_action('init', 'disable_wp_emojicons');

/**
 * This will allow wordpress to scale images up. It's not ideal to scale small images up, but its better than having malformed images in the content
 * @param  [type] $default [description]
 * @param  Number $orig_w  Image's original width
 * @param  Number $orig_h  Image's original height
 * @param  Number $new_w   Width value for upscale
 * @param  Number $new_h   Height value for upscale
 * @param  Boolean $crop   Whether cropping of the image is allowed. If not, this function exits
 * @return null|Array      Passes the new values to the WP image scaler
 */
function mvnp_basic_thumbnail_upscale($default, $orig_w, $orig_h, $new_w, $new_h, $crop){
	if(!$crop){
		return null;
	}

	$aspect_ratio = $orig_w / $orig_h;
	$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);
	$crop_w = round($new_w / $size_ratio);
	$crop_h = round($new_h / $size_ratio);
	$s_x = floor(($orig_w - $crop_w) / 2);
	$s_y = floor(($orig_h - $crop_h) / 2);

	return array(0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h);
}
add_filter('image_resize_dimensions', 'mvnp_basic_thumbnail_upscale', 10, 6);

/**
 * This will add "data-src" attruibute to images included in the wp visual editor. It takes the images, replaces the src with the
 * lazy load image size, configurable below, and moves the original size into the data-src.
 * @param String $html  the entire img tag
 * @param Int $id    ID of the image attachment
 */
function image_add_lazy_load($html, $id){
	$image_meta = wp_get_attachment_metadata($id);

	$src = (string) reset(simplexml_import_dom(DOMDocument::loadHTML($html))->xpath("//img/@src"));
	$html = str_replace($src, wp_get_attachment_image_src($id, 'lazy_orginal_ratio')[0], $html);
	$html = str_replace('/>', 'data-src="' . $src . '" />', $html);

	//this adds webp when the image is inserted into the post. thats not really a good idea considering chrome is the only thing that supports webp.
	//the webp url should be parsed out of the source when the content is being prepared

	// if(file_exists(str_ireplace(array('.jpg', '.jpeg', '.png', '.gif'), '.webp', wp_upload_dir()['basedir'] . '/' . $image_meta['file']))){
	// 	$html = str_ireplace(array('.jpg', '.jpeg', '.png', '.gif'), '.webp', $html);
	// }

	return $html;
}
add_filter('get_image_tag', 'image_add_lazy_load', 10, 4);

/**
 * This turns off the srcsets in the wp visual editor. I wanted to use lazyloading and this was complicating the issue.
 * once i figure out a good way to lazyload and use srcsets, this can be turned back on.
 * TODO figure out lazyloading srcsets
 */
add_filter( 'wp_calculate_image_srcset', '__return_false' );

/**
 * Create custom image sizes.
 * Use add_image_size( 'thumbnail_size_name', width, height, array( 'horiz_scale_origin', 'vert_scale_origin' ) );
 */
function mvnp_basic_custom_image_sizes(){
	add_image_size('fullscreen_slider', 1920, 600, array('center', 'center'));
	add_image_size('mobile_fullscreen_slider', 600, 187, array('center', 'center'));
	add_image_size('lazy_fullscreen_slider', 100, 31.25, array('center', 'center'));
	add_image_size('lazy_orginal_ratio', 100, -9999, false);
}
add_action('after_setup_theme', 'mvnp_basic_custom_image_sizes');

/**
 * Use pngquant and jpeg-recompress libraries to reduce image sizes by 50%-90%. The function operates
 * directly on the image files and leaves the meta values unchanged.
 * @param  Obj $image_meta The uploaded file's meta
 * @return Obj             The uploaded file's meta. These values have not been updated.
 */
function resample_image($image_meta){
	$original = $file_path = wp_upload_dir()['basedir'] . '/' . $image_meta['file'];
	list($image_width, $image_height, $mime_type, $image_attr) = getimagesize($file_path);

	switch ($mime_type){
	case 1: //gif
		if(file_exists(get_template_directory() . '/bin/gif2webp')){
			$webp_file = system(get_template_directory() . '/bin/gif2webp ' . $file_path . ' -o ' . str_ireplace('.gif', '.webp', $file_path));
		}
		break;
	case 2: //jpg
		if(file_exists(get_template_directory() . '/bin/jpeg-recompress') && class_exists('Imagick')){
			$file = new \Imagick(realpath($file_path));

			if($file->getImageColorspace() == \Imagick::COLORSPACE_CMYK){
				$file->transformImageColorspace(\Imagick::COLORSPACE_SRGB);

				if(file_exists(get_template_directory() . '/bin/sRGB_v4_ICC_preference.icc')){
					$icc_rgb = file_get_contents(get_template_directory() . '/bin/sRGB_v4_ICC_preference.icc');
					$file->profileImage('icc', $icc_rgb);
				}

				$file->setImageFormat('jpeg');
				file_put_contents($file_path, $file);
				$file->destroy();
			}

			$new_file = system(get_template_directory() . '/bin/jpeg-recompress -m smallfry ' . $file_path . ' ' . $file_path);
		}

		if(file_exists(get_template_directory() . '/bin/cwebp')){
			$webp_file = system(get_template_directory() . '/bin/cwebp ' . $file_path . ' -o ' . str_ireplace(array('.jpg', '.jpeg'), '.webp', $file_path));
		}
		break;
	case 3: //png
		if(file_exists(get_template_directory() . '/bin/pngquant')){
			$new_file = system(get_template_directory() . '/bin/pngquant --ext=.png --force ' . $file_path);
		}

		if(file_exists(get_template_directory() . '/bin/cwebp')){
			$webp_file = system(get_template_directory() . '/bin/cwebp ' . $file_path . ' -o ' . str_ireplace('.png', '.webp', $file_path));
		}
		break;
	}

	foreach($image_meta['sizes'] as $size => $val){
		preg_match('/.*\//', $image_meta['file'], $date_dir, PREG_OFFSET_CAPTURE);
		$file_path = wp_upload_dir()['basedir'] . '/' . $date_dir[0][0] . $val['file'];
		list($image_width, $image_height, $mime_type, $image_attr) = getimagesize($file_path);

		switch ($mime_type){
		case 1: //gif
			if(class_exists('Imagick')){
				$file = new \Imagick(realpath($original));
				$file = $file->coalesceImages();

				foreach($file as $frame){
					$frame->resizeImage($image_width, $image_height, Imagick::FILTER_LANCZOS, 0);
				}

				$file = $file->deconstructImages();
				$file->writeImages($file_path, true);

				if(file_exists(get_template_directory() . '/bin/gif2webp')){
					$webp_file = system(get_template_directory() . '/bin/gif2webp ' . $file_path . ' -o ' . str_ireplace('.gif', '.webp', $file_path));
				}

				$file->destroy();
			}
			break;
		case 2: //jpg
			if(file_exists(get_template_directory() . '/bin/jpeg-recompress') && class_exists('Imagick')){
				$file = new \Imagick(realpath($file_path));

				if($file->getImageColorspace() == \Imagick::COLORSPACE_CMYK){
					$file->transformImageColorspace(\Imagick::COLORSPACE_SRGB);

					if(file_exists(get_template_directory() . '/bin/sRGB_v4_ICC_preference.icc')){
						$icc_rgb = file_get_contents(get_template_directory() . '/bin/sRGB_v4_ICC_preference.icc');
						$file->profileImage('icc', $icc_rgb);
					}

					$file->setImageFormat('jpeg');
					file_put_contents($file_path, $file);
					$file->destroy();
				}

				$new_file = system(get_template_directory() . '/bin/jpeg-recompress -m ms-ssim ' . $file_path . ' ' . $file_path);
			}

			if(file_exists(get_template_directory() . '/bin/cwebp')){
				$webp_file = system(get_template_directory() . '/bin/cwebp ' . $file_path . ' -o ' . str_ireplace(array('.jpg', '.jpeg'), '.webp', $file_path));
			}
			break;
		case 3: //png
			if(file_exists(get_template_directory() . '/bin/pngquant')){
				$new_file = system(get_template_directory() . '/bin/pngquant --ext=.png --force ' . $file_path);
			}

			if(file_exists(get_template_directory() . '/bin/cwebp')){
				$webp_file = system(get_template_directory() . '/bin/cwebp ' . $file_path . ' -o ' . str_ireplace('.png', '.webp', $file_path));
			}
			break;
		}
	}

	return $image_meta;
}
add_action('wp_generate_attachment_metadata', 'resample_image');

/**
 * Deletes the webp files when a user removes images form the media library
 * @param  Int $post_id ID of the attachement
 */
function delete_webp($post_id){
	$image_meta = wp_get_attachment_metadata($post_id);

	wp_delete_file(str_ireplace(array('.jpg', '.jpeg', '.png', '.gif'), '.webp', wp_upload_dir()['basedir'] . '/' . $image_meta['file']));
	foreach($image_meta['sizes'] as $size => $val){
		preg_match('/.*\//', $image_meta['file'], $date_dir, PREG_OFFSET_CAPTURE);
		$file_path = wp_upload_dir()['basedir'] . '/' . $date_dir[0][0] . $val['file'];
		wp_delete_file(str_ireplace(array('.jpg', '.jpeg', '.png', '.gif'), '.webp', $file_path));
	}
}
add_action('delete_attachment', 'delete_webp');

/**
 * Removes the admin bar from the front end for chump users with no role.
 */
function remove_admin_bar(){
	if(wp_get_current_user()->roles[0] == ''){
		show_admin_bar(false);
	}
}
add_action('after_setup_theme', 'remove_admin_bar');

/**
 * Adds custom messages to the WP admin
 * @param  string $msg The message you want to display
 * @param  string $lvl The level (or color) of the notice. Valid vals are 'notice-error', 'notice-warning', 'notice-success', and 'notice-info'
 */
function mvnp_basic_notice($msg = 'no message', $lvl = 'notice-info'){
	?>
	<div class="notice <?php echo $lvl; ?>">
		<p><?php echo $msg; ?></p>
	</div>
<?php
}

/**
 * Gets the path of the user's private upload directory. Located in uploads, its their nicename
 * @param  stdObj $user wordpress user
 * @return str       the user's upload path
 */
function get_user_dir($user){
	$upload_dir = wp_upload_dir();
	$subdir = $user->user_nicename;
	$modified['subdir'] = $subdir;
	$modified['url'] = $upload_dir['baseurl'] . '/' . $subdir;
	$modified['path'] = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $subdir;

	return $modified;
}

/**
 * A simple post sorter.
 * @param  array  $posts   An array of the posts you need to sort
 * @param  string  $orderby The field you want to or by
 * @param  string  $order   ASC or DESC
 * @param  boolean $unique  When true, the sort will also remove duplicate post
 * @return array           Sorted list of posts
 */
function sort_posts($posts, $orderby, $order = 'ASC', $unique = true){
	if(!is_array($posts)){
		return false;
	}

	usort($posts, array(new Sort_Posts($orderby, $order), 'sort'));

	// use post ids as the array keys
	if($unique && count($posts)){
		$posts = array_combine(wp_list_pluck($posts, 'ID'), $posts);
	}

	return $posts;
}

/**
 * [Sort_Posts Sort Posts Class]
 */
class Sort_Posts {
	var $order, $orderby;

	function __construct($orderby, $order){
		$this->orderby = $orderby;
		$this->order = ('desc' == strtolower($order)) ? 'DESC' : 'ASC';
	}

	function sort($a, $b){
		if($a->{$this->orderby} == $b->{$this->orderby}){
			return 0;
		}

		if($a->{$this->orderby} < $b->{$this->orderby}){
			return ('ASC' == $this->order) ? -1 : 1;
		} else {
			return ('ASC' == $this->order) ? 1 : -1;
		}
	}
}

/**
 * Adds menu locations to the menu manager
 */
function make_menus(){
	register_nav_menus(array(
		'main_navigation' => __('Main Navigation', 'mvnp_basic'),
		'footer_navigation' => __('Footer Navigation', 'mvnp_basic'),
	));
}
add_action('after_setup_theme', 'make_menus');

/**
 * Adds widget locations to the widget manager
 */
function mvnp_basic_widgets_init(){
	register_sidebar(array(
		'name' => __('Sidebar Widget Area', 'mvnp_basic'),
		'id' => 'sidebar-widget-area',
		'before_widget' => '<section id="%1$s" class="%2$s">',
		'after_widget' => '</section>',
		'before_title' => '<h5>',
		'after_title' => '</h5>',
	));
}
add_action('widgets_init', 'mvnp_basic_widgets_init');

/**
 * redirects to /search instead of using url params
 */
function mvnp_basic_search_url(){
	if(is_search() && !empty($_GET['s'])){
		wp_redirect(home_url('/search/') . urlencode(get_query_var('s')));
		exit();
	}
}
add_action('template_redirect', 'mvnp_basic_search_url');

/**
 * Add global options to the timber context. Currrently adds new menu and widget locations.
 * It also adds the sidebar. If you want to disable the sidebar globally, remove the '$data['sidebar']' line
 * @param Object $data The Timber context
 * @return Object The Timber context
 */
function add_to_context($data){
	global $current_user;

	$data['main_menu'] = new TimberMenu('main_navigation');
	$data['footer_menu'] = new TimberMenu('footer_navigation');
	$data['breadcrumbs'] = new FunctionWrapper('breadcrumbs', $defaults = array('&#x221f;', get_the_title(get_option('page_on_front'))));
	$data['sidebar'] = Timber::get_widgets('sidebar-widget-area');
	$data['can_login'] = true;
	$data['is_logged_in'] = is_user_logged_in();

	if(function_exists('qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage')){
		$data['available_languages'] = get_option('qtranslate_language_names');
	}

	if(is_user_logged_in()){
		$data['user'] = $current_user;
		$data['user_link'] = member_permalink($current_user->nicename);
	}

	return $data;
}
add_filter('timber_context', 'add_to_context');

/**
 * Function for adding filters and functions to twig.
 * @param Object $twig The twig scope
 */
function add_to_twig($twig){
	$twig->addExtension(new Twig_Extension_StringLoader());
	$twig->addFilter(new Twig_SimpleFilter('request_uri', 'get_request_uri'));
	$twig->addFilter(new Twig_SimpleFilter('get_webp', 'get_webp_url'));

	return $twig;
}
add_filter('get_twig', 'add_to_twig');

/**
 * Gets the path portion of a URL
 * @param  String $text The URL you want the path from
 * @return String       The path as a string
 */
function get_request_uri($text){
	if(!$text){
		return __('Empty string!', 'mvnp_basic');
	}

	return parse_url($text)['path'];
}
/**
 * Just a simple filter to change png and jpg to webp urls
 * @param  String $url the url you need to change
 * @return String      webp formatted url
 */
function get_webp_url($url){
	if(isset($_SERVER['HTTP_USER_AGENT'])) {
	    $agent = $_SERVER['HTTP_USER_AGENT'];
	}

	if(strlen(strstr($agent, 'Chrome')) > 0) {
		return str_ireplace(array('.jpg', '.jpeg', '.png', '.gif'), '.webp', $url);
	} else return $url;
}

/**
 * Comprehensive breadcrumbs, via Stuart at thewebtaylor.com
 * https://www.thewebtaylor.com/articles/wordpress-creating-breadcrumbs-without-a-plugin
 * this has been heavily modified. do not rely on the original as a fix if something goes wrong
 * @param  string $separator  The thing that goes between page titles
 * @param  string $home_title Name the homepage something other than its title
 */
function breadcrumbs($separator, $home_title){
	$breadcrums_id = 'breadcrumbs';
	$breadcrums_class = 'breadcrumbs';
	$custom_taxonomy = '';

	global $post, $wp_query;

	// Do not display on the homepage
	if(!is_front_page()){
		// Build the breadcrums
		echo '<ul id="' . $breadcrums_id . '" class="' . $breadcrums_class . '" itemscope itemtype="http://schema.org/BreadcrumbList">';
		// Home page
		echo '<li class="item-home" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-link bread-home" href="' . get_home_url() . '" title="' . $home_title . '" itemprop="item">' . $home_title . '</a></li>';
		echo '<li class="separator separator-home"> ' . $separator . ' </li>';
		if(is_archive() && !is_tax() && !is_category() && !is_tag() && !is_day() && !is_month() && !is_year()){
			if(is_author()){
				// Auhor archive
				// Display author name
				echo '<li class="item-current item-current-' .  get_the_author_meta('user_nicename') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-current-' .  get_the_author_meta('user_nicename') . '" title="' .  get_the_author_meta('display_name') . '" itemprop="item">' . __('Author:', 'mvnp_basic') . ' ' .  get_the_author_meta('display_name') . '</span></li>';
			}else{
				echo '<li class="item-current item-archive" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-archive" itemprop="item">' . post_type_archive_title($prefix, false) . '</span></li>';
			}
		} else if(is_archive() && is_tax() && !is_category() && !is_tag()){
			// If post is a custom post type
			$post_type = get_post_type();
			// If it is a custom post type display name and link
			if($post_type != 'post'){
				$post_type_object = get_post_type_object($post_type);
				$post_type_archive = get_post_type_archive_link($post_type);
				echo '<li class="item-cat item-custom-post-type-' . $post_type . '" itemscope itemtype="http://schema.org/ListItem"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . $post_type_archive . '" title="' . $post_type_object->labels->name . '" itemprop="item">' . $post_type_object->labels->name . '</a></li>';
				echo '<li class="separator"> ' . $separator . ' </li>';
			}
			$custom_tax_name = get_queried_object()->name;
			echo '<li class="item-current item-archive" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-archive" itemprop="item">' . $custom_tax_name . '</span></li>';
		} else if(is_single()){
			// If post is a custom post type
			$post_type = get_post_type();
			// If it is a custom post type display name and link
			if($post_type != 'post'){
				$post_type_object = get_post_type_object($post_type);
				$post_type_archive = get_post_type_archive_link($post_type);
				echo '<li class="item-cat item-custom-post-type-' . $post_type . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . $post_type_archive . '" title="' . $post_type_object->labels->name . '" itemprop="item">' . $post_type_object->labels->name . '</a></li>';
				echo '<li class="separator"> ' . $separator . ' </li>';

				if($post->post_parent){
					// If child page, get parents
					$anc = get_post_ancestors($post->ID);
					// Get parents in the right order
					$anc = array_reverse($anc);
					// Parent page loop
					if(!isset($parents)){
						$parents = null;
					}

					foreach($anc as $ancestor){
						$parents .= '<li class="item-parent item-parent-' . $ancestor . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-parent bread-parent-' . $ancestor . '" href="' . get_permalink($ancestor) . '" title="' . get_the_title($ancestor) . '" itemprop="item">' . get_the_title($ancestor) . '</a></li>';
						$parents .= '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
					}
					// Display parent pages
					echo $parents;
				}
			}

			// Get post category info
			$category = get_the_category();
			if(!empty($category)){
				// Get last category post is in
				$last_category = end(array_values($category));
				// Get parent any categories and create array
				$get_cat_parents = rtrim(get_category_parents($last_category->term_id, true, ','), ',');
				$cat_parents = explode(',', $get_cat_parents);
				// Loop through parent categories and store in variable $cat_display
				$cat_display = '';
				foreach($cat_parents as $parents){
					$cat_display .= '<li class="item-cat">' . $parents . '</li>';
					$cat_display .= '<li class="separator"> ' . $separator . ' </li>';
				}
			}

			// If it's a custom post type within a custom taxonomy
			$taxonomy_exists = taxonomy_exists($custom_taxonomy);
			if(empty($last_category) && !empty($custom_taxonomy) && $taxonomy_exists){
				$taxonomy_terms = get_the_terms($post->ID, $custom_taxonomy);
				$cat_id = $taxonomy_terms[0]->term_id;
				$cat_nicename = $taxonomy_terms[0]->slug;
				$cat_link = get_term_link($taxonomy_terms[0]->term_id, $custom_taxonomy);
				$cat_name = $taxonomy_terms[0]->name;
			}

			// Check if the post is in a category
			if(!empty($last_category)){
				echo $cat_display;
				echo '<li class="item-current item-' . $post->ID . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '" itemprop="item">' . get_the_title() . '</span></li>';
				// Else if post is in a custom taxonomy
			} else if(!empty($cat_id)){
				echo '<li class="item-cat item-cat-' . $cat_id . ' item-cat-' . $cat_nicename . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-cat bread-cat-' . $cat_id . ' bread-cat-' . $cat_nicename . '" href="' . $cat_link . '" title="' . $cat_name . '" itemprop="item">' . $cat_name . '</a></li>';
				echo '<li class="separator"> ' . $separator . ' </li>';
				echo '<li class="item-current item-' . $post->ID . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '" itemprop="item">' . get_the_title() . '</span></li>';
			} else {
				echo '<li class="item-current item-' . $post->ID . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '" itemprop="item">' . get_the_title() . '</span></li>';
			}

		} else if(is_category()){
			// Category page
			echo '<li class="item-current item-cat" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-cat" itemprop="item">' . single_cat_title('', false) . '</span></li>';
		} else if(is_page()){
			// Standard page
			if($post->post_parent){
				// If child page, get parents
				$anc = get_post_ancestors($post->ID);
				// Get parents in the right order
				$anc = array_reverse($anc);
				// Parent page loop
				if(!isset($parents)){
					$parents = null;
				}

				foreach($anc as $ancestor){
					$parents .= '<li class="item-parent item-parent-' . $ancestor . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-parent bread-parent-' . $ancestor . '" href="' . get_permalink($ancestor) . '" title="' . get_the_title($ancestor) . '" itemprop="item">' . get_the_title($ancestor) . '</a></li>';
					$parents .= '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
				}
				// Display parent pages
				echo $parents;
				// Current page
				echo '<li class="item-current item-' . $post->ID . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span title="' . get_the_title() . '" itemprop="item"> ' . get_the_title() . '</span></li>';
			} else {
				// Just display current page if not parents
				echo '<li class="item-current item-' . $post->ID . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-' . $post->ID . '" itemprop="item"> ' . get_the_title() . '</span></li>';
			}

		} else if(is_tag()){
			// Tag page
			// Get tag information
			$term_id = get_query_var('tag_id');
			$taxonomy = 'post_tag';
			$args = 'include=' . $term_id;
			$terms = get_terms($taxonomy, $args);
			$get_term_id = $terms[0]->term_id;
			$get_term_slug = $terms[0]->slug;
			$get_term_name = $terms[0]->name;
			// Display the tag name
			echo '<li class="item-current item-tag-' . $get_term_id . ' item-tag-' . $get_term_slug . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-tag-' . $get_term_id . ' bread-tag-' . $get_term_slug . '" itemprop="item">' . $get_term_name . '</span></li>';
		} elseif(is_day()){
			// Day archive
			// Year link
			echo '<li class="item-year item-year-' . get_the_time('Y') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link(get_the_time('Y')) . '" title="' . get_the_time('Y') . '" itemprop="item">' . get_the_time('Y') . ' ' . __('Archives', 'mvnp_basic') . '</a></li>';
			echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';
			// Month link
			echo '<li class="item-month item-month-' . get_the_time('m') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-month bread-month-' . get_the_time('m') . '" href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '" title="' . get_the_time('M') . '" itemprop="item">' . get_the_time('M') . ' ' . __('Archives', 'mvnp_basic') . '</a></li>';
			echo '<li class="separator separator-' . get_the_time('m') . '"> ' . $separator . ' </li>';
			// Day display
			echo '<li class="item-current item-' . get_the_time('j') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-' . get_the_time('j') . '" itemprop="item"> ' . get_the_time('jS') . ' ' . get_the_time('M') . ' ' . __('Archives', 'mvnp_basic') . '</span></li>';
		} else if(is_month()){
			// Month Archive
			// Year link
			echo '<li class="item-year item-year-' . get_the_time('Y') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link(get_the_time('Y')) . '" title="' . get_the_time('Y') . '" itemprop="item">' . get_the_time('Y') . ' ' . __('Archives', 'mvnp_basic') . '</a></li>';
			echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';
			// Month display
			echo '<li class="item-month item-month-' . get_the_time('m') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-month bread-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '" itemprop="item">' . get_the_time('M') . ' ' . __('Archives', 'mvnp_basic') . '</span></li>';
		} else if(is_year()){
			// Display year archive
			echo '<li class="item-current item-current-' . get_the_time('Y') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '" itemprop="item">' . get_the_time('Y') . ' ' . __('Archives', 'mvnp_basic') . '</span></li>';
		} else if(get_query_var('paged')){
			// Paginated archives
			echo '<li class="item-current item-current-' . get_query_var('paged') . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-current-' . get_query_var('paged') . '" title="' . __('Page', 'mvnp_basic') . ' ' . get_query_var('paged') . '" itemprop="item">' . __('Page', 'mvnp_basic') . ' ' . get_query_var('paged') . '</span></li>';
		} else if(is_search()){
			// Search results page
			echo '<li class="item-current item-current-' . get_search_query() . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-current-' . get_search_query() . '" title="' . __('Search results for', 'mvnp_basic') . ' ' . get_search_query() . '" itemprop="item">' . __('Search results for', 'mvnp_basic') . ' ' . get_search_query() . '</span></li>';
		} else if(is_home()){
			echo '<li class="item-current item-' . $post->ID . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="bread-current bread-' . $post->ID . '" itemprop="item">' . get_the_title(get_option('page_for_posts', true)) . '</span></li>';
		} elseif(is_404()){
			// 404 page
			echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . __('Error 404', 'mvnp_basic') . '</li>';
		}
		echo '</ul>';
	}
}

/**
 * This big nasty thing is what expands repeating events into each component day.
 * @param  array $args       Arguments for the WP_Query
 * @param  int $date_start Unix timestamp of the start date of the range
 * @param  int $date_end   Unix timestamp of the end date of the range
 * @return array             The posts from the query, expanded to each day they run
 */
function expand_recurring_events($args, $date_start, $date_end){
	global $post;
	$query = new WP_Query($args);
	$expanded_posts = array();

	while($query->have_posts()){
		$query->the_post();
		/*
		 * If the event starts and ends on the same day and there is no recurrence, put it in the array as is
		 */
		if(date('Ymd', $post->start_date) == date('Ymd', $post->end_date) && $post->recurrence == ''){
			$expanded_posts[] = $post;
			/*
			 * Otherwise, if it spans more than one day and does not recur, loop trough from the start to end, one day at a time and add them to the array
			 */
		} elseif(date('Ymd', $post->start_date) < date('Ymd', $post->end_date) && $post->recurrence == ''){
			while($post->start_date < $post->end_date){
				$expanded_posts[] = new WP_Post($post);
				$post->start_date = strtotime('midnight', strtotime('+1 day', $post->start_date));
			}
			/*
			 * Then if it does recur, format our rrules into an array
			 */
		} elseif(is_array($post->recurrence) && strlen($post->recurrence[0]) > 0){
			$recurrence = explode('RRULE:', $post->recurrence[0])[1];
			parse_str(strtr($recurrence, ';', '&'), $recurrence);

			/*
			 * Rruel contains an interval if its more than one unit. If not, we need to set it to one
			 */
			$interval = array_key_exists('INTERVAL', $recurrence) ? $recurrence['INTERVAL'] : '1';

			/*
			 * Need to change the frequency to singular form for adding to date objects
			 */
			switch ($recurrence['FREQ']){
			case 'DAILY':
				$freq = 'day';
				break;
			case 'WEEKLY':
				$freq = 'week';
				break;
			case 'MONTHLY':
				$freq = 'month';
				break;
			case 'YEARLY':
				$freq = 'year';
				break;
			}

			/*
			 * If it recurs on specific days of the week (monthly or weekly) we need to format those days into a php date parsable format.
			 * If it is by month, it will only have one BYDAY, which also contains a number. need to split that up.
			 */
			if(array_key_exists('BYDAY', $recurrence)){
				$recurrence['BYDAY'] = explode(',', $recurrence['BYDAY']);

				if($freq == 'month' && count($recurrence['BYDAY'] == 1)){
					$recurrence['BYDAY'] = preg_split('#(?<=\d)(?=[a-z])#i', $recurrence['BYDAY'][0]);
				}

				foreach($recurrence['BYDAY'] as $key => $val){
					switch ($val){
					case 'SU':
						$recurrence['BYDAY'][$key] = 'Sunday';
						break;
					case 'MO':
						$recurrence['BYDAY'][$key] = 'Monday';
						break;
					case 'TU':
						$recurrence['BYDAY'][$key] = 'Tuesday';
						break;
					case 'WE':
						$recurrence['BYDAY'][$key] = 'Wednesday';
						break;
					case 'TH':
						$recurrence['BYDAY'][$key] = 'Thursday';
						break;
					case 'FR':
						$recurrence['BYDAY'][$key] = 'Friday';
						break;
					case 'SA':
						$recurrence['BYDAY'][$key] = 'Saturday';
						break;
					}
				}
			}

			/*
			 * Events can recur a forever, a specific number of times, or until a certain date. Until a certain date is here first.
			 *
			 *
			 */
			if(array_key_exists('UNTIL', $recurrence)){
				$until = date(strtotime($recurrence['UNTIL']) - get_option('gmt_offset') * 3600);
				if(array_key_exists('BYDAY', $recurrence)){
					if($freq == 'week'){
						while($post->start_date < $until && $post->start_date < $date_end){
							if($post->start_date >= $date_start && in_array(date('l', $post->start_date), $recurrence['BYDAY'])){
								$expanded_posts[] = new WP_Post($post);
							}
							if(date('N', $post->start_date) == 7){
								$post->start_date = date(strtotime('+1 day +' . ($interval - 1) . ' ' . $freq, $post->start_date));
								$post->end_date = date(strtotime('+1 day +' . ($interval - 1) . ' ' . $freq, $post->end_date));
							} else {
								$post->start_date = date(strtotime('+1 day', $post->start_date));
								$post->end_date = date(strtotime('+1 day', $post->end_date));
							}
						}
					} elseif($freq == 'month'){
						if($date_end < $post->start_date || $date_start > $until){
							break;
						}
						$post->start_date = date(strtotime('+' . ($recurrence['BYDAY'][0] - 1) . ' week', $date_start));
						$post->end_date = date(strtotime('+' . ($recurrence['BYDAY'][0] - 1) . ' week', $date_start . '+' . ((strtotime($post->end_date) - strtotime($post->start_date)) / 60 / 60 / 24) . ' days'));

						while($post->start_date < $until && $post->start_date < $date_end){
							if($post->start_date >= $date_start && in_array(date('l', $post->start_date), $recurrence['BYDAY'])){
								$expanded_posts[] = new WP_Post($post);
								break;
							}
							$post->start_date = date(strtotime('+1 day', $post->start_date));
							$post->end_date = date(strtotime('+1 day', $post->end_date));
						}
					}
				} else {
					while($post->start_date < $until && $post->start_date < $date_end){
						if($post->start_date >= $date_start){
							$expanded_posts[] = new WP_Post($post);
						}
						$post->start_date = date(strtotime('+' . $interval . ' ' . $freq, $post->start_date));
						$post->end_date = date(strtotime('+' . $interval . ' ' . $freq, $post->end_date));
					}
				}
			} elseif(array_key_exists('COUNT', $recurrence)){
				if(array_key_exists('BYDAY', $recurrence)){
					if($freq == 'week'){
						for($i = 0; $i < $recurrence['COUNT'] && $post->start_date < $date_end; $i++){
							if($post->start_date >= $date_start && in_array(date('l', $post->start_date), $recurrence['BYDAY'])){
								$expanded_posts[] = new WP_Post($post);
							}
							if(date('N', $post->start_date) == 7){
								$post->start_date = date(strtotime('+1 day +' . ($interval - 1) . ' ' . $freq, $post->start_date));
								$post->end_date = date(strtotime('+1 day +' . ($interval - 1) . ' ' . $freq, $post->end_date));
							} else {
								$post->start_date = date(strtotime('+1 day', $post->start_date));
								$post->end_date = date(strtotime('+1 day', $post->end_date));
							}
						}
					} elseif($freq == 'month'){
						if($date_end < $post->start_date){
							break;
						}
						$post->start_date = date(strtotime('+' . ($recurrence['BYDAY'][0] - 1) . ' week', $post->start_date));
						$post->end_date = date(strtotime('+' . ($recurrence['BYDAY'][0] - 1) . ' week', $date_start . '+' . ((strtotime($post->end_date) - strtotime($post->start_date)) / 60 / 60 / 24) . ' days'));

						for($i = 0; $i < $recurrence['COUNT'] && $post->start_date < $date_end; $i++){
							if($post->start_date >= $date_start && in_array(date('l', $post->start_date), $recurrence['BYDAY'])){
								$expanded_posts[] = new WP_Post($post);
								break;
							}
							$post->start_date = date(strtotime('+1 day', $post->start_date));
							$post->end_date = date(strtotime('+1 day', $post->end_date));
						}
					}
				} else {
					for($i = 0; $i < $recurrence['COUNT'] && $post->start_date < $date_end; $i++){
						if($post->start_date >= $date_start){
							$expanded_posts[] = new WP_Post($post);
						}
						$post->start_date = date(strtotime('+' . $interval . ' ' . $freq, $post->start_date));
						$post->end_date = date(strtotime('+' . $interval . ' ' . $freq, $post->end_date));
					}
				}
			} else {
				if(array_key_exists('BYDAY', $recurrence)){
					if($freq == 'week'){
						while($post->start_date < $date_end){
							if($post->start_date >= $date_start && in_array(date('l', $post->start_date), $recurrence['BYDAY'])){
								$expanded_posts[] = new WP_Post($post);
							}
							if(date('N', $post->start_date) == 7){
								$post->start_date = date(strtotime('+1 day +' . ($interval - 1) . ' ' . $freq, $post->start_date));
								$post->end_date = date(strtotime('+1 day +' . ($interval - 1) . ' ' . $freq, $post->end_date));
							} else {
								$post->start_date = date(strtotime('+1 day', $post->start_date));
								$post->end_date = date(strtotime('+1 day', $post->end_date));
							}
						}
					} elseif($freq == 'month'){
						if($date_end < $post->start_date){
							break;
						}
						$post->start_date = date(strtotime('+' . ($recurrence['BYDAY'][0] - 1) . ' week', $date_start));
						$post->end_date = date(strtotime('+' . ($recurrence['BYDAY'][0] - 1) . ' week', $date_start . '+' . ((strtotime($post->end_date) - strtotime($post->start_date)) / 60 / 60 / 24) . ' days'));

						while($post->start_date < $date_end){
							if($post->start_date >= $date_start && in_array(date('l', $post->start_date), $recurrence['BYDAY'])){
								$expanded_posts[] = new WP_Post($post);
								break;
							}
							$post->start_date = date(strtotime('+1 day', $post->start_date));
							$post->end_date = date(strtotime('+1 day', $post->end_date));
						}
					}
				} else {
					while($post->start_date < $date_end){
						if($post->start_date >= $date_start){
							$expanded_posts[] = new WP_Post($post);
						}
						$post->start_date = date(strtotime('+' . $interval . ' ' . $freq, $post->start_date));
						$post->end_date = date(strtotime('+' . $interval . ' ' . $freq, $post->end_date));
					}
				}
			}
		}
	}

	return $expanded_posts;
}
