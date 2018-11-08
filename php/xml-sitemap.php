<?php
/*-----------------------------------------------*/
/*  SitemapXML by stephenscaff https://gist.github.com/stephenscaff
/*  @description: builds and saves an xml sitemap of posts & pages
/*  @todo: define priorities for sitemap schema, via check for CPTs, etc.
/*-----------------------------------------------*/

if($error = get_transient('xml_sitemap_error')){
	mvnp_basic_notice($error, 'notice-error');
	delete_transient('xml_sitemap_error');
}

SitemapXML::init();

class SitemapXML {
	// Add filters and actions here
	function init(){
		// Rebuild sitemap on save
		add_action('save_post', array(__CLASS__, 'create_sitemap'));
	}
	// create the sitemap.xml from posts
	function create_sitemap(){
		global $sitemap_posts;
		global $robots_disallow;

		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$root_path = str_replace($protocol . $_SERVER['SERVER_NAME'], '', get_site_url());

		// get_posts and pages for sitemap
		$postsForSitemap = get_posts(array(
			'numberposts' => -1,
			'orderby' => 'modified',
			'post_type' => $sitemap_posts,
			'order' => 'DESC',
		));

		$sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
		$sitemap .= "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		// Loop through our posts
		foreach($postsForSitemap as $post){
			// Sets postdata to fill global post variables
			setup_postdata($post);

			// Get post Date (maybe use to further establish frequency for sitemap schema?)
			$postdate = $post->post_modified;

			// Build the sitemap schema
			$sitemap .=
			"\t<url>\n" .
				"\t\t<loc>" . get_permalink($post->ID) . "</loc>" .
				"\n\t\t<lastmod>$postdate</lastmod>" .
				"\n\t\t<changefreq>monthly</changefreq>" .
			"\n\t</url>\n";
		}
		// Close sitemap schema st
		$sitemap .= '</urlset>';
		$robots =
		"User-agent: *\n" .
		"Disallow: $root_path/wp-admin\n" .
		"Disallow: $root_path/wp-includes\n" .
		"Disallow: $root_path/search\n" .
		"Disallow: $root_path/*?*\n" .
		"Disallow: $root_path/*?\n";

		foreach($robots_disallow as $disallowed){
			$robots .= "Disallow: $root_path/$disallowed\n";
		}

		$robots .= "\nSitemap: $root_path/sitemap.xml\n";

		// our sitemap.xml file
		if(is_writable(ABSPATH)){
			$sitemap_file = fopen(ABSPATH . 'sitemap.xml', 'w');
			fwrite($sitemap_file, $sitemap);
			fclose($sitemap_file);
			$robots_file = fopen(ABSPATH . 'robots.txt', 'w');
			fwrite($robots_file, $robots);
			fclose($robots_file);
		} else {
			set_transient('xml_sitemap_error', __('Could not write to XML sitemap. Please check file permissions.', 'mvnp_basic'), 45);
			return false;
		}
	}
}
