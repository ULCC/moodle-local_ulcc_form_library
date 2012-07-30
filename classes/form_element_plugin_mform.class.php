<?php

/**
 * Abstract class providing the template form in which the configuration of a element can be entered
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */
global $CFG;

require_once("$CFG->libdir/formslib.php");

abstract class form_element_plugin_mform extends moodleform {

    public		$form_id;
    public 		$plugin_id;
    public 		$creator_id;
    public 		$course_id;
    public      $moodleplugintype;
    public      $moodlepluginname;
    public 		$dbc;


    function __construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$context_id,$formfield_id=null) {
        global $CFG;

        $this->form_id		    =	$form_id;
        $this->formelement_id	=	$formelement_id;
        $this->creator_id		=	$creator_id;
        $this->formfield_id	    =	$formfield_id;
        $this->moodleplugintype =   $moodleplugintype;
        $this->moodlepluginname =   $moodlepluginname;
        $this->dbc				=	new form_db();

        parent::__construct("{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_field.php?formelement_id={$formelement_id}&form_id={$form_id}&moodleplugintype={$moodleplugintype}&moodlepluginname={$moodlepluginname}");

    }

    function definition() {
        global $USER, $CFG;

        //get the plugin type by getting the plugin name
        $currentplugin	=	$this->dbc->get_form_element_plugin($this->formelement_id);

        $form           =   $this->dbc->get_form_by_id($this->form_id);

        $mform =& $this->_form;
        $fieldsettitle	=	get_string("addfield",'local_ulcc_form_library');

        //define the elements that should be present on all plugin element forms

        //create a fieldset to hold the form
        $mform->addElement('html', '<fieldset id="formfieldset" class="clearfix formfieldset">');
        $mform->addElement('html', '<legend class="ftoggler">'.$form->name.'</legend>');

        //the id of the form that the element will be in
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('static', 'plugintypestatic',get_string('plugintype','local_ulcc_form_library'),get_string($currentplugin->name.'_type','local_ulcc_form_library'));


        //button to state whether the element is required
        $mform->addElement('checkbox',
            'req',
            get_string('required', 'local_ulcc_form_library')
        );

        //button to state whether the element is required
        $mform->addElement('checkbox',
            'summary',
            get_string('addtosummary', 'local_ulcc_form_library')
        );

        //the id of the form that the element will be in
        $mform->addElement('hidden', 'form_id');
        $mform->setType('form_id', PARAM_INT);
        $mform->setDefault('form_id', $this->form_id);

        //the id of the plugin in use
        $mform->addElement('hidden', 'formelement_id');
        $mform->setType('formelement_id', PARAM_INT);
        $mform->setDefault('formelement_id', $this->formelement_id);

        //the id of the form element creator
        $mform->addElement('hidden', 'creator_id');
        $mform->setType('creator_id', PARAM_INT);
        $mform->setDefault('creator_id', $this->creator_id);

        //the id of the course that the element is being created in
        $mform->addElement('hidden', 'course_id');
        $mform->setType('course_id', PARAM_INT);
        $mform->setDefault('course_id', $this->course_id);


        //the id of the formfield this is only used in edit instances
        $mform->addElement('hidden', 'formfield_id');
        $mform->setType('formfield_id', PARAM_INT);
        $mform->setDefault('formfield_id', $this->formfield_id);

        //the moodle plugin type of the form
        $mform->addElement('hidden', 'moodleplugintype');
        $mform->setType('moodleplugintype', PARAM_INT);
        $mform->setDefault('moodleplugintype', $this->moodleplugintype);

        //the moodle plugin type of the form
        $mform->addElement('hidden', 'moodlepluginname');
        $mform->setType('moodlepluginname', PARAM_RAW);
        $mform->setDefault('moodlepluginname', $this->moodlepluginname);

        //the id of the form element creator
        $mform->addElement('hidden', 'position');
        $mform->setType('position', PARAM_INT);
        //set the field position of the field
        $mform->setDefault('position', $this->dbc->get_new_form_field_position($this->form_id));



        //text field for element label
        $mform->addElement(
            'text',
            'label',
            get_string('label', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );

        $mform->addRule('label', null, 'maxlength', 255, 'client',array('size'=>'10'));
        $mform->addRule('label', null, 'required', null, 'client');
        $mform->setType('label', PARAM_RAW);


        //text field for element description
        $mform->addElement(
            'htmleditor',
            'description',
            get_string('description', 'local_ulcc_form_library'),
            array('class' => 'form_input','rows'=> '10', 'cols'=>'65')
        );

        $mform->addRule('description', null, 'maxlength', 1000, 'client');
        $mform->setType('description', PARAM_RAW);



        $this->specific_definition($mform);

        //add the submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Force extending class to add its own form fields
     */
    abstract protected function specific_definition($mform);

    /**
     * Performs server-side validation of the unique constraints.
     *
     * @param object $data The data to be saved
     */
    function validation($data, $files) {
        $this->errors = array();

       // var_dump($data);
      //  exit;

        //check that the field label does not already exist in this form
        if ($this->dbc->label_exists($data['label'],$data['form_id'],$data['id']))	{
            $this->errors['label']	=	get_string('labelexistserror','block_form',$data);
        }

        // now add fields specific to this type of evidence
        $this->specific_validation($data);

        return $this->errors;
    }

    /**
     * Force extending class to add its own server-side validation
     */
    abstract protected function specific_validation($data);

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     */
    function process_data($data) {

        $data->label	=	htmlentities($data->label);

        $data->summary  =   (isset($data->summary)) ? 1 : 0;

        if (empty($data->id)) {
            //create the form_form_field record
            $data->id	=	$this->dbc->create_form_field($data);
        } else {
            //update the form

            $formfield	=	$this->dbc->update_form_field($data);
        }

        if(!empty($data->id)) {
            $data->formfield_id = $data->id;
            $this->specific_process_data($data);
        }
        return $data->id;
    }

    /**
     * Force extending class to add its own processing method
     */
    abstract protected function specific_process_data($data);

    function unprocessed_data(&$data)   {

    }

}


?>
