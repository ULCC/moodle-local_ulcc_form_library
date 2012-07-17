<?php

/**
 * Deletes a report field from a report
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER;

//include any neccessary files

// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');

//the id of the form that the field is in
$form_id = $PARSER->required_param('form_id', PARAM_INT);

//the id of the formfield used when editing
$formfield_id = $PARSER->required_param('formfield_id' ,PARAM_INT);

// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

// instantiate the db
$dbc = new form_db();

//get the form field record
$formfield		=	$dbc->get_form_field_data($formfield_id);

//check if the report field was found
if (!empty($formfield)) {
    //get the plugin used for the form field
    $formelementrecord	=	$dbc->get_formelement_by_id($formfield->formelement_id);

    $classname = $formelementrecord->name;

    // include the moodle form for this table
    include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

    if(!class_exists($classname)) {
        print_error('noclassforplugin', 'local_ulcc_form_library', '', $formelementrecord->name);
    }

    $pluginclass	=	new $classname();

    $deletedposition	=	$formfield->position;


    if ($pluginclass->delete_form_element($formfield_id)) {
        $resulttext	=	get_string('fielddeletesuc','local_ulcc_form_library');

        //we now need to change the positions of all fields in the report move everything under the deleted position up
        $formfields 	= 	$dbc->get_form_fields_by_position($form_id);

        //loop through fields returned
        if (!empty($formfields)) {
            foreach($formfields as $field) {

                if ($field->position > $deletedposition) {

                    //if the field is being moved up all other fields have postion value increased
                    //if the field is being moved down all other fields have postion value decreased
                    //move up = 1 move down = 0
                    if (!$dbc->set_new_position($field->id,$field->position-1));

                }
            }
        }


    }	else {
        $resulttext	=	get_string('fielddeleteerror','local_ulcc_form_library');
    }
} else {
    $resulttext	=	get_string('fielddeleteerror','local_ulcc_form_library');
}

$return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_formfields.php?'.$PARSER->get_params_url(array('form_id','moodleplugintype','moodlepluginname'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);
