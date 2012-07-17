<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_file_mform  extends form_element_plugin_mform {
	
	  	
	
	protected function specific_definition($mform) {


        $mbsize =   1048576;

        for($i=1;$i<=20;$i++)    {
            $optionlist[$i * $mbsize]   =   $i.'mb' ;
        }


        $mform->addElement(
            'select',
            'maxsize',
            get_string('form_element_plugin_file_maxsize', 'local_ulcc_form_library'),
            $optionlist,
            array('class' => 'form_input')
        );

        $mform->addElement('advcheckbox', 'multiple', get_string('form_element_plugin_file_multiple', 'local_ulcc_form_library'), get_string('yes'), array('group' => 1), array(0, 1));

        $mform->addElement(
            'text',
            'maxfiles',
            get_string('form_element_plugin_file_maxfiles', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );
        
        $mform->addRule('maxfiles', null, 'maxlength', 2, 'client');
        $mform->setType('maxfiles', PARAM_INT);

        $optionlist     =   array(
                                  'web_image'=>'Web image',
                                  'non_web_image'=>'Non web image',
                                  'audio'=>'Audio',
                                  'non_web_audio'=>'Non web audio',
                                  'video'=>'Video',
                                  'non_web_video'=>'Non web video',
                                  'document'=>'Document',
                                  'openoffice'=>'Open office',
                                    'text'=>'Text',
                                    'script'=>'Script',
                                    'plaintext'=>'Plain text',
                                    'moodle'=>'Moodle',
                                    'application'=>'Application',
                                    'script'=>'Script',
                                    'plaintext'=>'Plain text'
                                   );

        $select =   $mform->addElement(
                                        'select',
                                        'acceptedtypes',
                                        get_string('form_element_plugin_file_acceptedfiles', 'local_ulcc_form_library'),
                                        $optionlist,
                                        array('class' => 'form_input')
                                    );

        $select->setMultiple(true);

    }
	
	protected function specific_validation($data) {
 	
	 	$data = (object) $data;

	 	return $this->errors;
	 }
	 
	 protected function specific_process_data($data) {
	  	
	 	$plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_file",$data->formfield_id) : false;

         $data->acceptedtypes    =   base64_encode(serialize($data->acceptedtypes));

	 	if (empty($plgrec)) {
	 		return $this->dbc->create_form_element_record("ulcc_form_plg_file",$data);
	 	} else {
	 		//get the old record from the elements plugins table 
	 		$oldrecord				=	$this->dbc->get_form_element_by_formfield("ulcc_form_plg_file",$data->formfield_id);
             $pluginrecord 					=	new stdClass();
             $pluginrecord->id				=	$oldrecord->id;
             $pluginrecord->maxsize	        =	$data->maxsize;
             $pluginrecord->maxfiles	    =	$data->maxfiles;
             $pluginrecord->multiple	    =	$data->multiple;
             $pluginrecord->acceptedtypes	=	$data->acceptedtypes;
	 		//update the plugin with the new data
	 		return $this->dbc->update_form_element_record("ulcc_form_plg_file",$pluginrecord);
	 	}
	 }
	 
	 function definition_after_data() {
	 	
	 }
	
}
