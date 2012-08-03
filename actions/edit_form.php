<?php

/**
 * Allows a form to be created and edited
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version
 */

require_once('../../../config.php');

global  $CFG, $USER, $SESSION, $OUTPUT, $PARSER;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/edit_form_mform.php');

//add the breadcrumbs
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');

$form_id    =   optional_param('form_id',null,PARAM_INT);

// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

$context_id             =   $PARSER->required_param('context_id', PARAM_RAW);

$dbc        =   new form_db();

//instantiate the edit_report_mform class
$mform	=	new edit_form_mform($moodlepluginname,$moodleplugintype,$context_id,$form_id);


//was the form cancelled?
if ($mform->is_cancelled()) {
    //send the user back
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?'.$PARSER->get_params_url();
    redirect($return_url, '', FORM_REDIRECT_DELAY);
}


//was the form submitted?
// has the form been submitted?
if($mform->is_submitted()) {
    // check the validation rules
    if($mform->is_validated()) {

        //get the form data submitted
        $formdata = $mform->get_data();

        //only try to change the icon if a file was submitted
        if ($mform->get_file_content('binary_icon') != false) {
            $formdata->binary_icon	=	$mform->get_file_content('binary_icon');
        } else {
            $formdata->binary_icon	=	'';
        }

        // process the data
        $success = $mform->process_data($formdata);

        //if saving the data was not successful
        if(!$success) {
            //print an error message
            print_error(get_string("formcreationerror", 'ulcc_form_library'), 'ulcc_form_library');
        }

        //if the report_id has not already been set
        $form_id	= (empty($form_id)) ? $success : $form_id;

        //decide whether the user has chosen to save and exit or save or display
        if (isset($formdata->saveanddisplaybutton)) {
            $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_formfields.php?form_id='.$form_id.'&'.$PARSER->get_params_url();
            redirect($return_url, get_string("formcreation", 'ulcc_form_library'), FORM_REDIRECT_DELAY);
        }
    }
}

//set the page title
$pagetitle	=	(empty($form_id)) ? get_string('createform', 'local_ulcc_form_library') : get_string('editform', 'local_ulcc_form_library');


if (!empty($form_id)) {
    $formrecord	=	$dbc->get_form_by_id($form_id);
    $mform->set_data($formrecord);
}

//add create form title to nav
$PAGE->navbar->add(get_string('createform', 'local_ulcc_form_library'), null, 'title');



// setup the page title and heading
$SITE	=	$dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_form.php',$PARSER->get_params());


require_once($CFG->dirroot.'/local/ulcc_form_library/views/edit_form.html');

?>