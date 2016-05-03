<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Field_Post_Content extends GF_Field {

	public $type = 'post_content';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Body', 'gravityforms' );
	}

	function get_form_editor_field_settings() {
		return array(
			'post_content_template_setting',
			'post_status_setting',
			'post_category_setting',
			'post_author_setting',
			'post_format_setting',
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'size_setting',
			'maxlen_setting',
			'rules_setting',
			'visibility_setting',
			'default_value_textarea_setting',
			'placeholder_textarea_setting',
			'description_setting',
			'css_class_setting',
			'rich_text_editor_setting',
		);
	}

	public function is_conditional_logic_supported() {
		return true;
	}

	public function enqueue_rich_text_editor_scripts(){
		//have to print scripts/styles to footer for the editor to work on the preview page
		wp_print_footer_scripts();
	}

	public function get_field_input( $form, $value = '', $entry = null ) {

		$field = new GF_Field_Textarea( clone $this );

		return $field->get_field_input( $form, $value, $entry );
	}

	public function allow_html() {
		return true;
	}

	public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br ) {

		return $format == 'html' && ! $nl2br ? nl2br( $value ) : $value;
	}
}

GF_Fields::register( new GF_Field_Post_Content() );