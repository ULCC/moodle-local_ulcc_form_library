<?php

/**
 * Creates and edits a form field
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form library
 * @version 1.0
 */



require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER;

//include any neccessary files

// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');


//the id of the report  that the field will be in
$form_id = $PARSER->required_param('form_id', PARAM_INT);

//the id of the plugin ype the field will be
$formelement_id = $PARSER->required_param('formelement_id', PARAM_INT);

//the id of the reportfield used when editing
$formfield_id = $PARSER->optional_param('formfield_id',null ,PARAM_INT);

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


$title  =   (empty($formfield_id))  ? get_string('addfield','local_ulcc_form_library')   :   get_string('editfield','local_ulcc_form_library');

$PAGE->navbar->add($title, null, 'title');


// setup the page title and heading
$SITE	=	$dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('form');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_field.php', $PARSER->get_params());

//get the plugin record that for the plugin
$pluginrecord	=	$dbc->get_formelement_by_id($formelement_id);

//take the name field from the plugin as it will be used to call the instantiate the plugin class
$classname = $pluginrecord->name;
// include the class for the plugin
include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

if(!class_exists($classname)) {
    print_error('noclassforplugin', 'local_ulcc_form_library', '', $pluginrecord->name);
}

//instantiate the plugin class
$pluginclass	=	new $classname();

//has the maximum number of this field type in this report been reached?
if (!$pluginclass->can_add($form_id) && empty($formfield_id))	{
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_formfields.php?'.$PARSER->get_params_url(array('form_id','moodleplugintype','moodlepluginname'));
    redirect($return_url, get_string("fieldmaximum", 'local_ulcc_form_library',$pluginclass->audit_type()));
}

//call the plugin edit function inside of which the plugin configuration mform
$pluginclass->edit($form_id,$formelement_id,$formfield_id,$moodleplugintype,$moodlepluginname);


require_once($CFG->dirroot.'/local/ulcc_form_library/views/edit_field.html');

?>