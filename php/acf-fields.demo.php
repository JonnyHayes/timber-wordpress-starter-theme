<?php
acf_add_local_field_group(array(
	'key' => 'group_1', /* (string) Unique identifier for field group. Must begin with 'group_' */
	'title' => __('My Group', 'mvnp_basic'), /* (string) Visible in metabox handle */
	'fields' => array(), /* (array) An array of fields */
	'menu_order' => 0, /* (int) Field groups are shown in order from lowest to highest. Defaults to 0 */
	'position' => 'normal', /* (string) Determines the position on the edit screen. Defaults to normal. Choices of 'acf_after_title', 'normal' or 'side' */
	'style' => 'default', /* (string) Determines the metabox style. Defaults to 'default'. Choices of 'default' or 'seamless' */
	'label_placement' => 'top', /* (string) Determines where field labels are places in relation to fields. Defaults to 'top'. Choices of 'top' (Above fields) or 'left' (Beside fields) */
	'instruction_placement' => 'label', /* (string) Determines where field instructions are places in relation to fields. Defaults to 'label'. Choices of 'label' (Below labels) or 'field' (Below fields) */
	'hide_on_screen' => '', /* (array) An array of elements to hide on the screen */
	'location' => array( /* (array) An array containing 'rule groups' where each 'rule group' is an array containing 'rules'. Each group is considered an 'or', and each rule is considered an 'and'. */
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
		),
	),
));

acf_add_local_field(array(
	'key' => 'field_1', /* (string) Unique identifier for the field. Must begin with 'field_' */
	'label' => __('Sub Title', 'mvnp_basic'), /* (string) Visible when editing the field value */
	'name' => 'sub_title', /* (string) Used to save and load data. Single word, no spaces. Underscores and dashes allowed */
	'type' => 'text', /* (string) Type of field (text, textarea, image, etc) */
	'prefix' => '',
	'instructions' => __('Sample Instructions', 'mvnp_basic'), /* (string) Instructions for authors. Shown when submitting data */
	'required' => 0, /* (int) Whether or not the field value is required. Defaults to 0 */
	'conditional_logic' => 0, /* (mixed) Conditionally hide or show this field based on other field's values. Best to use the ACF UI and export to understand the array structure. Defaults to 0 */
	'wrapper' => array( /* (array) An array of attributes given to the field element */
		'width' => '',
		'class' => '',
		'id' => '',
	),
	'default_value' => '', /* (mixed) A default value used by ACF if no value has yet been saved */
	'parent' => 'group_1',
	/* These remaining settings are specific to text inputs. For full descriptions of all types, see https://www.advancedcustomfields.com/resources/register-fields-via-php/#field-type%20settings */
	'placeholder' => '',
	'prepend' => '',
	'append' => '',
	'maxlength' => '',
	'readonly' => 0,
	'disabled' => 0,
));

acf_add_local_field(array(
	'key' => 'field_2',
	'label' => __('Repeater Test', 'mvnp_basic'),
	'name' => 'repeater_test',
	'type' => 'repeater',
	'prefix' => '',
	'instructions' => __('Sample Instructions', 'mvnp_basic'),
	'required' => 0,
	'conditional_logic' => 0,
	'wrapper' => array(
		'width' => '',
		'class' => '',
		'id' => '',
	),
	'sub_fields' => array(
		0 => array(
			'key' => 'field_label_1',
			'label' => 'Label',
			'name' => 'label',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		1 => array(
			'key' => 'field_text_area_1',
			'label' => 'Content',
			'name' => 'content',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => '',
			'new_lines' => 'wpautop',
		),
	),
	'default_value' => '',
	'placeholder' => '',
	'prepend' => '',
	'append' => '',
	'maxlength' => '',
	'readonly' => 0,
	'disabled' => 0,
	'parent' => 'group_1',
));
?>
