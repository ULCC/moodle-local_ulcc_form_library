<?php

/**
 * Displays a preview of the form with the given id
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form Library
 * @version 1.0
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER;

//include any neccessary files

// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');

//include the report entry preview mform class
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/form_preview_mform.php');


//the id of the report  that the field will be in
$form_id = $PARSER->required_param('form_id', PARAM_INT);

// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

// instantiate the db
$dbc = new form_db();

// setup the navigation breadcrumbs

//siteadmin or modules

//  Add section name to nav bar.
$PAGE->navbar->add(get_string('administrationsite'), null, 'title');

$PAGE->navbar->add(get_string('plugins', 'admin'), null, 'title');

$plugintype     =   ($moodleplugintype  ==  'block')    ? get_string('blocks')  :  get_string('modules') ;

$PAGE->navbar->add($plugintype, null, 'title');

$pluginname     =   get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);

$PAGE->navbar->add($pluginname, null, 'title');

$PAGE->navbar->add(get_string('pluginname', 'local_ulcc_form_library'), $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?'.$PARSER->get_params_url(), 'title');

$PAGE->navbar->add(get_string('formpreview','local_ulcc_form_library'), null, 'title');


// setup the page title and heading
$SITE	=	$dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('form');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_field.php', $PARSER->get_params());

$mform	= new	form_preview_mform($form_id,$moodleplugintype,$moodlepluginname);

$previewform    =   $dbc->get_form_by_id($form_id);

require_once($CFG->dirroot.'/local/ulcc_form_library/views/form_preview.html');
