<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_grade_mform  extends form_element_plugin_mform {



	protected function specific_definition(MoodleQuickForm $mform) {

        global  $DB;

        $mform->addElement('html','<div>'.get_string('form_element_plugin_grade_dynamicdesc', 'local_ulcc_form_library').'</div>');

        //$mform->addElement('advcheckbox', 'gradetype', get_string('form_element_plugin_grade_gradetype', 'local_ulcc_form_library'), '', array('group' => 1), array(0, 1));

        $modules     =   $DB->get_records('modules',array('visible'=>1));

        $options    =   array();

        foreach($modules as $m)   {
            $options[$m->name]    =   $m->name;
        }

        $mform->addElement('select', 'tablename', get_string('form_element_plugin_grade_module', 'local_ulcc_form_library'), $options);

        $mform->setDefault('tablename', $this->moodlepluginname);

       // $mform->disabledIf('tablename','gradetype','unchecked');

      /*  $scales     =   $DB->get_records('scale');
        $options    =   array();

        foreach($scales as $s)   {
            $options[$s->id]    =   $s->name;
        }

        $mform->addElement('select', 'gradescale', get_string('form_element_plugin_grade_gradescale', 'local_ulcc_form_library'), $options);

        $mform->disabledIf('gradescale','gradetype','checked'); */



        $mform->addElement(
            'text',
            'maxgrade',
            get_string('form_element_plugin_grade_maxgrade', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );

        $mform->addRule('maxgrade', null, 'numeric', null, 'client');
        $mform->addRule('maxgrade', null, 'required', null, 'client');

        $mform->setType('maxgrade', PARAM_RAW);



	}

	protected function specific_validation($data) {

	 	$data = (object) $data;


              if( $data->maxgrade >100){

                  $this->errors['maxgrade']	=   get_string('form_element_plugin_grade_maxgrade_error','local_ulcc_form_library');
              }

	 	return $this->errors;
	 }

	 protected function specific_process_data($data) {

	 	$plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_grade",$data->formfield_id) : false;

	 	if (empty($plgrec)) {
	 		return $this->dbc->create_form_element_record("ulcc_form_plg_grade",$data);
	 	} else {
	 		//get the old record from the elements plugins table
	 		$oldrecord				=	$this->dbc->get_form_element_by_formfield("ulcc_form_plg_grade",$data->formfield_id);

	 		//create a new object to hold the updated data
	 		$pluginrecord 					=	new stdClass();
	 		$pluginrecord->id				=	$oldrecord->id;

	 		//update the plugin with the new data
	 		return $this->dbc->update_form_element_record("ulcc_form_plg_grade",$pluginrecord);
	 	}
	 }

	 function definition_after_data() {

	 }



}
