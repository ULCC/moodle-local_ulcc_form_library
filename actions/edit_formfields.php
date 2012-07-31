<?php

/**
 * This page allows the various form elements to be added to a form.
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */

require_once('../../../config.php');

global  $CFG, $USER, $SESSION, $OUTPUT, $PARSER;



// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');

//require the add field form
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/add_field_mform.php');

// the id of the form must be provided
$form_id    =   $PARSER->required_param('form_id',PARAM_INT);

// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

$context_id             =   $PARSER->required_param('context_id', PARAM_RAW);

// instantiate the db
$dbc = new form_db();

//  Add section name to nav bar.
$PAGE->navbar->add(get_string('administrationsite'), null, 'title');

$PAGE->navbar->add(get_string('plugins', 'admin'), null, 'title');

$plugintype     =   ($moodleplugintype  ==  'block')    ? get_string('blocks')  :  get_string('activitymodule') ;

$PAGE->navbar->add($plugintype, null, 'title');

$pluginname     =   get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);

$PAGE->navbar->add($pluginname, null, 'title');

$PAGE->navbar->add(get_string('pluginname', 'local_ulcc_form_library'), $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?'.$PARSER->get_params_url(), 'title');

$PAGE->navbar->add(get_string('formfields', 'local_ulcc_form_library'), null, 'title');


// setup the page title and heading
$SITE	=	$dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_formfields.php',$PARSER->get_params());

$fieldmform	= new add_field_mform($moodlepluginname,$moodleplugintype,$context_id,$form_id);

// has the form been submitted?
if($fieldmform->is_submitted()) {
    //get the form data submitted
    $formdata = $fieldmform->get_data();
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_field.php?formelement_id='.$formdata->formelement_id.'&'.$PARSER->get_params_url();
    redirect($return_url, get_string("addfield", 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
}

$previewurl =   $CFG->wwwroot.'/local/ulcc_form_library/actions/form_preview.php?'.$PARSER->get_params_url();

require_once($CFG->dirroot.'/local/ulcc_form_library/views/edit_formfields.html');

?>