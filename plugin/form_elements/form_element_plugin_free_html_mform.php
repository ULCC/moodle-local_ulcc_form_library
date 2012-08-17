<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_free_html_mform  extends form_element_plugin_mform {


	protected function specific_definition(MoodleQuickForm $mform) {

    }

	protected function specific_validation($data) {
        if( is_array( $data ) ){
            $data = (object) $data;
        }

	 	if ( empty( $data->description ) )  $this->errors['markup_required'] = get_string('form_element_plugin_free_html_markup_required','local_ulcc_form_library');
        return $this->errors;
    }

	protected function specific_process_data($data) {

    }
}
