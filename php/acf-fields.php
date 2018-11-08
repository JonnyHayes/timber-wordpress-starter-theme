<?php
if(function_exists('acf_add_local_field_group')){
	if(file_exists(get_template_directory() . '/php/acf-views/' . pathinfo($GLOBALS['current_theme_template'], PATHINFO_FILENAME) . '-acf.php')){
		include_once get_template_directory() . '/php/acf-views/' . pathinfo($GLOBALS['current_theme_template'], PATHINFO_FILENAME) . '-acf.php';
	}

	/* Put custom fields that do not belong to one singular page template here. ie fileds that appear on more than one page template, fileds that appear on specific post types or fileds that are included based on post relationships */

	acf_add_local_field_group(array(
		'key' => 'seo_group',
		'title' => __('SEO Options', 'mvnp_basic'),
		'menu_order' => 9999,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'post',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'event',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'faq',
				),
			),
		),
		'fields' => array(
			array(
				'key' => 'seo_tab',
				'label' => 'General SEO',
				'name' => 'seo_tab',
				'type' => 'tab',
				'placement' => 'left',
				'endpoint' => 0,
			),
			array(
				'key' => 'seo_instructions',
				'label' => __('SEO Instructions', 'mvnp_basic'),
				'name' => 'seo_instructions',
				'instructions' => __('If these fields are left blank, SEO information will be generated from the title, content and thumbnail image from the post. They are intended as overrides, so you don\'t need to fill them out. Description is limited to the recommended 160 characters.', 'mvnp_basic'),
				'type' => 'text',
				'wrapper' => array(
					'width' => '50%',
					'class' => '',
					'id' => 'seo-instructions',
				),
				'maxlength' => '160',
				'rows' => '5',
			),
			array(
				'key' => 'seo_image',
				'label' => __('SEO Image', 'mvnp_basic'),
				'name' => 'seo_image',
				'type' => 'image',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'seo-image',
				),
				'preview_size' => 'medium',
			),
			array(
				'key' => 'seo_title',
				'label' => __('SEO Title', 'mvnp_basic'),
				'name' => 'seo_title',
				'placeholder' => '',
				'type' => 'text',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'seo-title',
				),
			),
			array(
				'key' => 'seo_description',
				'label' => __('SEO Description', 'mvnp_basic'),
				'name' => 'seo_description',
				'type' => 'textarea',
				'new_lines' => '',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'seo-desc',
				),
				'maxlength' => '160',
				'rows' => '5',
			),
			array(
				'key' => 'facebook_tab',
				'label' => 'Facebook Content',
				'name' => 'facebook_tab',
				'type' => 'tab',
				'placement' => 'left',
				'endpoint' => 0,
			),
			array(
				'key' => 'facebook_instructions',
				'label' => __('Facebook Instructions', 'mvnp_basic'),
				'name' => 'facebook_instructions',
				'instructions' => __('These fields are used for the content shown when this page is shared on Facebook. If these fields are left blank, the information will be generated from the title, content and thumbnail image from the post. They are intended as overrides, so you don\'t need to fill them out. Description is limited to the recommended 160 characters.', 'mvnp_basic'),
				'type' => 'text',
				'wrapper' => array(
					'width' => '50%',
					'class' => '',
					'id' => 'facebook-instructions',
				),
				'maxlength' => '160',
				'rows' => '5',
			),
			array(
				'key' => 'facebook_image',
				'label' => __('Facebook Image', 'mvnp_basic'),
				'name' => 'facebook_image',
				'type' => 'image',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'facebook-image',
				),
				'preview_size' => 'medium',
			),
			array(
				'key' => 'facebook_title',
				'label' => __('Facebook Title', 'mvnp_basic'),
				'name' => 'facebook_title',
				'placeholder' => '',
				'type' => 'text',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'facebook-title',
				),
			),
			array(
				'key' => 'facebook_description',
				'label' => __('Facebook Description', 'mvnp_basic'),
				'name' => 'facebook_description',
				'type' => 'textarea',
				'new_lines' => '',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'facebook-desc',
				),
				'maxlength' => '160',
				'rows' => '5',
			),
			array(
				'key' => 'twitter_tab',
				'label' => 'Twitter Content',
				'name' => 'twitter_tab',
				'type' => 'tab',
				'placement' => 'left',
				'endpoint' => 0,
			),
			array(
				'key' => 'twitter_instructions',
				'label' => __('Twitter Instructions', 'mvnp_basic'),
				'name' => 'twitter_instructions',
				'instructions' => __('These fields are used for the content shown when this page is shared on Twitter. If these fields are left blank, the information will be generated from the title, content and thumbnail image from the post. They are intended as overrides, so you don\'t need to fill them out. Description is limited to the recommended 160 characters.', 'mvnp_basic'),
				'type' => 'text',
				'wrapper' => array(
					'width' => '50%',
					'class' => '',
					'id' => 'twitter-instructions',
				),
				'maxlength' => '160',
				'rows' => '5',
			),
			array(
				'key' => 'twitter_image',
				'label' => __('Twitter Image', 'mvnp_basic'),
				'name' => 'twitter_image',
				'type' => 'image',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'twitter-image',
				),
				'preview_size' => 'medium',
			),
			array(
				'key' => 'twitter_title',
				'label' => __('Twitter Title', 'mvnp_basic'),
				'name' => 'twitter_title',
				'placeholder' => '',
				'type' => 'text',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'twitter-title',
				),
			),
			array(
				'key' => 'twitter_description',
				'label' => __('Twitter Description', 'mvnp_basic'),
				'name' => 'twitter_description',
				'type' => 'textarea',
				'new_lines' => '',
				'wrapper' => array(
					'width' => '25%',
					'class' => '',
					'id' => 'twitter-desc',
				),
				'maxlength' => '160',
				'rows' => '5',
			),
		),
	));
}
