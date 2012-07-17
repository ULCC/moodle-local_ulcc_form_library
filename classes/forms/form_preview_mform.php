<?php

/**
 * This class provides a mform that previews the entry form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form
 * @version 1.0
 */

global  $CFG;

require_once($CFG->libdir.'/formslib.php');

// include the db class
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

class form_preview_mform extends moodleform {


    public 		$course_id;
    public		$form_id;
    public      $moodleplugintype;
    public      $moodlepluginname;
    public		$dbc;

    /**
     * TODO comment this
     */
    function __construct($form_id,$moodleplugintype,$moodlepluginname) {

        global $CFG;


        $this->form_id	            =	$form_id;
        $this->moodlepluginname     =   $moodlepluginname;
        $this->moodleplugintype     =   $moodleplugintype;

        $this->dbc			=	new form_db();

        // call the parent constructor
        parent::__construct("{$CFG->wwwroot}/local/ulcc_form_library/actions/form_preview.php?form_id={$this->form_id}&moodleplugintype={$moodleplugintype}&moodlepluginname={$moodlepluginname}");
    }


    /**
     * TODO comment this
     */
    function definition() {
        global $USER, $CFG;



        $mform =& $this->_form;

        //get all of the fields in the current report, they will be returned in order as
        //no position has been specified
        $formfields		=	$this->dbc->get_form_fields_by_position($this->form_id);

        $form				=	$this->dbc->get_form_by_id($this->form_id);

        //create a new fieldset
        $mform->addElement('html', '<fieldset id="formfieldset" class="clearfix ilpfieldset">');
        $mform->addElement('html', '<legend class="ftoggler">'.$form->name.'</legend>');

        $desc	=	html_entity_decode($form->description);

        $mform->addElement('html', '<div class="descritivetext">'.$desc.'</div>');

        foreach ($formfields as $field) {

            //get the plugin record that for the plugin
            $formelementrecord	=	$this->dbc->get_formelement_by_id($field->formelement_id);

            //take the name field from the plugin as it will be used to call the instantiate the plugin class
            $classname = $formelementrecord->name;

            // include the class for the plugin
            include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

            if(!class_exists($classname)) {
                print_error('noclassforplugin', 'local_ulcc_form_library', '', $formelementrecord->name);
            }

            //instantiate the plugin class
            $formelementclass	=	new $classname();

            $formelementclass->load($field->id);

            //call the plugins entry_form function which will add an instance of the plugin
            //to the form
            $formelementclass->entry_form($mform);
        }

        //close the fieldset
        $mform->addElement('html', '</fieldset>');
    }


    /**
     * TODO comment this
     */
    function process_data($data) {
        //no need to process data as this is just a preview of the final form
    }

    /**
     * TODO comment this
     */
    function definition_after_data() {

    }



}