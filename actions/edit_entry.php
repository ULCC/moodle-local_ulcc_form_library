<?php

/**
 * Creates a entry for a form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form Library
 * @version 1.0
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE;


// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
//add the breadcrumbs
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

//the id of the report  that the field will be in
$form_id = $PARSER->required_param('form_id', PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$context_id = $PARSER->required_param('context_id', PARAM_RAW);

require_login();

// Setting the page context.
set_page_context($moodleplugintype, $context_id, $context);
// Set context.
$PAGE->set_context($context);

// instantiate the db
$dbc = new form_db();

// setup the navigation breadcrumbs

//siteadmin or modules
$PAGE->navbar->add(get_string('formpreview', 'local_ulcc_form_library'), null, 'title');


// setup the page title and heading
$SITE = $dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_field.php', $PARSER->get_params());
