<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_date_mform  extends form_element_plugin_mform {

    /**
     * Adds this element to the given mform
     *
     * @param $mform
     */
	  protected function specific_definition(MoodleQuickForm $mform) {
	  	//element to define a date as past, present or future
		$optionlist = array(
				FORM_PASTDATE =>    get_string( 'form_element_plugin_date_past' , 'local_ulcc_form_library' ),
				FORM_PRESENTDATE => get_string( 'form_element_plugin_date_present' , 'local_ulcc_form_library' ),
				FORM_FUTUREDATE =>  get_string( 'form_element_plugin_date_future' , 'local_ulcc_form_library' ),
				FORM_ANYDATE =>     get_string( 'form_element_plugin_date_anydate' , 'local_ulcc_form_library' )
		);

		$mform->addElement(
				'select',
				'datetense',
				get_string( 'form_element_plugin_date_tense' , 'local_ulcc_form_library' ),
				$optionlist
		);

		$mform->addRule('datetense', null, 'required', null, 'client');
        $mform->setType('datetense', PARAM_INT);
	}

    /**
     * Provides validation of the data submitted in this form element
     *
     * @param $data
     * @return mixed
     */
	 protected function specific_validation($data) {
	 	$data = (object) $data;
	 	return $this->errors;
	 }


    /**
     * Saves the data entered into the field
     *
     * @param object $data the data passed back from the form
     * @return mixed
     */
	 protected function specific_process_data($data) {

	 	$plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_dat",$data->formfield_id) : false;

	 	if (empty($plgrec)) {
	 		return $this->dbc->create_form_element_record("ulcc_form_plg_dat",$data);
	 	} else {
	 		//get the old record from the elements plugins table
	 		$oldrecord				=	$this->dbc->get_form_element_by_formfield("ulcc_form_plg_dat",$data->formfield_id);

	 		//create a new object to hold the updated data
	 		$pluginrecord 					=	new stdClass();
	 		$pluginrecord->id				=	$oldrecord->id;
			$pluginrecord->datetense		=	$data->datetense;

	 		//update the plugin with the new data
	 		return $this->dbc->update_form_element_record("ulcc_form_plg_dat",$pluginrecord);
	 	}
	 }

	 function definition_after_data() {

	 }

}
