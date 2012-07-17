<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_page_break_mform  extends form_element_plugin_mform {

	protected function specific_definition($mform) {
        //no need for this in this form element
	}
	
	protected function specific_validation($data) {
 	
	 	//no need for this
	 	return $this->errors;
	 }
	 
	 protected function specific_process_data($data) {
	  	
	 	$plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_pb",$data->formfield_id) : false;

	 	if (empty($plgrec)) {
	 		return $this->dbc->create_form_element_record("ulcc_form_plg_pb",$data);
	 	} else {
            return true;
	 	}
	 }
	 
	 function definition_after_data() {
	 	
	 }


    function unprocessed_data(&$data)   {
        $data->position         =   $this->dbc->get_new_form_field_position($this->form_id);
        $data->label            =   '--------';
        $data->form_id          =   $this->form_id;
        $this->summary          =   0;
    }
}
