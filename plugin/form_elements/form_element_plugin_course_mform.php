<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist_mform.class.php');

class form_element_plugin_course_mform  extends form_element_plugin_itemlist_mform {

	public $tablename;
	
	function __construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$formfield_id=null) {
		parent::__construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$formfield_id);
		$this->tablename = "ulcc_form_plg_crs";
	}

    protected function specific_definition($mform) {
        //no action necessary
    }

    protected function specific_process_data($data) {
	  	
	 	$plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record( $this->tablename, $data->formfield_id ) : false;
	 	
	 	if (empty($plgrec)) {
	 		return $this->dbc->create_form_element_record( $this->tablename, $data );
	 	} else {
	 		//get the old record from the elements plugins table 
	 		$oldrecord				=	$this->dbc->get_form_element_by_formfield( $this->tablename, $data->formfield_id );
	
	 		//create a new object to hold the updated data
	 		$pluginrecord 					=	new stdClass();
	 		$pluginrecord->id				=	$oldrecord->id;
	 			
	 		//update the plugin with the new data
	 		return $this->dbc->update_form_element_record( $this->tablename, $pluginrecord );
	 	}
	 }

	 protected function specific_validation($data) {
	 	$data = (object) $data;
	 	return $this->errors;
	 }
}
