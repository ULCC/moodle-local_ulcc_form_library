<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_html_editor_mform  extends form_element_plugin_mform {
	
	  	
	
	  protected function specific_definition($mform) {

	  	//set the maximum length of the field default to 255
        $mform->addElement(
            'text',
            'minimumlength',
            get_string('form_element_plugin_html_editor_minimumlength', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );
        
        $mform->addRule('minimumlength', null, 'maxlength', 3, 'client');
        //$mform->addRule('minimumlength', null, 'required', null, 'client');
        $mform->setType('minimumlength', PARAM_INT);
	  	
        //set the maximum length of the field default to 255
        $mform->addElement(
            'text',
            'maximumlength',
            get_string('form_element_plugin_html_editor_maximumlength', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );
        
        $mform->addRule('maximumlength', null, 'maxlength', 4, 'client');
        //$mform->addRule('maximumlength', null, 'required', null, 'client');
        $mform->setType('maximumlength', PARAM_INT);
	}
	
	 protected function specific_validation($data) {
 	
	 	$data = (object) $data;
 	
	 	if ($data->maximumlength < 0 || $data->maximumlength > 9999) $this->errors['maximumlength'] = get_string('form_element_plugin_html_editor_maxlengthrange','local_ulcc_form_library');
	 	if ($data->maximumlength < $data->minimumlength) $this->errors['maximumlength'] = get_string('form_element_plugin_html_editor_maxlessthanmin','local_ulcc_form_library');
	 	
	 	return $this->errors;
	 }
	 
	 protected function specific_process_data($data) {
	  	
	 	$plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_hte",$data->formfield_id) : false;
	 	
	 	if (empty($plgrec)) {
	 		return $this->dbc->create_form_element_record("ulcc_form_plg_hte",$data);
	 	} else {
	 		//get the old record from the elements plugins table 
	 		$oldrecord				=	$this->dbc->get_form_element_by_formfield("ulcc_form_plg_hte",$data->formfield_id);
	
	 		//create a new object to hold the updated data
	 		$pluginrecord 					=	new stdClass();
	 		$pluginrecord->id				=	$oldrecord->id;
	 		$pluginrecord->minimumlength	=	$data->minimumlength;
	 		$pluginrecord->maximumlength	=	$data->maximumlength;
	 			
	 		//update the plugin with the new data
	 		return $this->dbc->update_form_element_record("ulcc_form_plg_hte",$pluginrecord);
	 	}
	 }
	 
	 function definition_after_data() {
	 	
	 }
	
}
