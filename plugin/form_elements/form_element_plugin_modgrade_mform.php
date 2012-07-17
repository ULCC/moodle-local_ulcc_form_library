<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_modgrade_mform  extends form_element_plugin_mform {
	
	  	
	
	protected function specific_definition($mform) {

	}
	
	protected function specific_validation($data) {
 	
	 	$data = (object) $data;

	 	return $this->errors;
	 }
	 
	 protected function specific_process_data($data) {
	  	
	 	$plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_modgd",$data->formfield_id) : false;
	 	
	 	if (empty($plgrec)) {
	 		return $this->dbc->create_form_element_record("ulcc_form_plg_modgd",$data);
	 	} else {
	 		//get the old record from the elements plugins table 
	 		$oldrecord				=	$this->dbc->get_form_element_by_formfield("ulcc_form_plg_modgd",$data->formfield_id);
	
	 		//create a new object to hold the updated data
	 		$pluginrecord 					=	new stdClass();
	 		$pluginrecord->id				=	$oldrecord->id;

	 		//update the plugin with the new data
	 		return $this->dbc->update_form_element_record("ulcc_form_plg_modgd",$pluginrecord);
	 	}
	 }
	 
	 function definition_after_data() {
	 	
	 }
	
}
