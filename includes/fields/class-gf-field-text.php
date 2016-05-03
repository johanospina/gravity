<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}


class GF_Field_Text extends GF_Field {

	public $type = 'text';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Single Line Text', 'gravityforms' );
	}

	function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'size_setting',
			'input_mask_setting',
			'maxlen_setting',
			'password_field_setting',
			'rules_setting',
			'visibility_setting',
			'duplicate_setting',
			'default_value_setting',
			'placeholder_setting',
			'description_setting',
			'css_class_setting',
		);
	}

	public function is_conditional_logic_supported() {
		return true;
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$html_input_type = 'text';

		if ( $this->enablePasswordInput && ! $is_entry_detail ) {
			$html_input_type = 'password';
		}

		$logic_event = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
		$id          = (int) $this->id;
		$field_id    = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$value        = esc_attr( $value );
		$size         = $this->size;
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $size . $class_suffix;

		$max_length = is_numeric( $this->maxLength ) ? "maxlength='{$this->maxLength}'" : '';

		$tabindex              = $this->get_tabindex();
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

		$input = "<input name='input_{$id}' id='{$field_id}' type='{$html_input_type}' value='{$value}' class='{$class}' {$max_length} {$tabindex} {$logic_event} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text}/>";

		return sprintf( "<div class='ginput_container ginput_container_text'>%s</div>", $input );
	}

	public function allow_html() {
		return in_array( $this->type, array( 'post_custom_field', 'post_tags' ) ) ? true : false;
	}

	public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br ) {

		if ( $format === 'html' ) {
			$value = $raw_value;
			if ( $nl2br ) {
				$value = nl2br( $value );
			}

			$allow_html = $this->allow_html();
			$form_id = absint( $form['id'] );
			$allowable_tags = apply_filters( 'gform_allowable_tags', $allow_html, $this, $form_id );
			$allowable_tags = apply_filters( "gform_allowable_tags_{$form_id}", $allowable_tags, $this, $form_id );

			if ( $allowable_tags === false ) {
				// The value is unsafe so encode the value.
				$return = esc_html( $value );
			} else {
				// The value contains HTML but the value was sanitized before saving.
				$return = $value;
			}
		} else {
			$return = $value;
		}

		return $return;
	}

	/**
	 * Sanitizes the value before saving if HTML is enabled or by allowing tags using the gform_allowable_tags filter.
	 *
	 * @param string $value
	 * @param int $form_id
	 *
	 * @return string
	 */
	public function sanitize_entry_value( $value, $form_id ) {
		if ( is_array( $value ) ) {
			return '';
		}

		$allow_html = $this->allow_html();

		$allowable_tags = apply_filters( 'gform_allowable_tags', $allow_html, $this, $form_id );
		$allowable_tags = apply_filters( "gform_allowable_tags_{$form_id}", $allowable_tags, $this, $form_id );


		switch ( $allowable_tags ) {
			case false :
				// HTML is not expected so return the value as submitted.
				$return = $value;
				break;
			case true :
				// HTML is expected. Value will stripped of scripts and some tags and encoded.
				$return = wp_kses_post( $value );
				break;
			default:
				// Some HTML is expected. Value will stripped of scripts and some tags and encoded.
				$value = wp_kses_post( $value );

				// Strip all tags except those allowed by the gform_allowable_tags filter.
				$return = strip_tags( $value, $allowable_tags );

		}
		return $return;
	}

	/**
	 * Format the entry value safe for displaying on the entry list page.
	 *
	 * @param string $value The field value.
	 * @param array $entry The Entry Object currently being processed.
	 * @param string $field_id The field or input ID currently being processed.
	 * @param array $columns The properties for the columns being displayed on the entry list page.
	 * @param array $form The Form Object currently being processed.
	 *
	 * @return string
	 */
	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {

		if ( is_array( $value ) ) {
			return '';
		}

		$allow_html = $this->allow_html();
		$form_id = absint( $form['id'] );
		$allowable_tags = apply_filters( 'gform_allowable_tags', $allow_html, $this, $form_id );
		$allowable_tags = apply_filters( "gform_allowable_tags_{$form_id}", $allowable_tags, $this, $form_id );

		if ( $allowable_tags === false ) {
			// The value is unsafe so encode the value.
			$return = esc_html( $value );
		} else {
			// The value contains HTML but the value was sanitized before saving.
			$return = $value;
		}

		return $return;
	}

	/**
	 * Format the entry value safe for displaying on the entry detail page and for the {all_fields} merge tag.
	 *
	 * @param string|array $value The field value.
	 * @param string $currency The entry currency code.
	 * @param bool|false $use_text When processing choice based fields should the choice text be returned instead of the value.
	 * @param string $format The format requested for the location the merge is being used. Possible values: html, text or url.
	 * @param string $media The location where the value will be displayed. Possible values: screen or email.
	 *
	 * @return string
	 */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		if ( is_array( $value ) ) {
			return '';
		}

		if ( $format === 'html' ) {
			$value = nl2br( $value );

			$allow_html = $this->allow_html();
			$allowable_tags = apply_filters( 'gform_allowable_tags', $allow_html, $this, null );

			if ( $allowable_tags === false ) {
				// The value is unsafe so encode the value.
				$return = esc_html( $value );
			} else {
				// The value contains HTML but the value was sanitized before saving.
				$return = $value;
			}
		} else {
			$return = $value;
		}

		return $return;
	}
}

GF_Fields::register( new GF_Field_Text() );